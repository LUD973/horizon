<?php
declare(strict_types=1);

namespace FCP\Horizon\Api;

use FCP\Horizon\Application\EnquiryService;
use FCP\Horizon\Data\AuditLogRepository;
use FCP\Horizon\Data\CommunicationRepository;
use FCP\Horizon\Data\ContactRepository;
use FCP\Horizon\Data\EnquiryRepository;
use FCP\Horizon\Data\SupabaseClient;
use FCP\Horizon\Data\SupabaseException;
use FCP\Horizon\Domain\EnquiryValidator;
use FCP\Horizon\Domain\PublicReference;
use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;
use FCP\Horizon\Support\RateLimiter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * API REST versionnée « fcp/v1 ».
 *
 * Routes S2 :
 *   GET  /health
 *   POST /enquiries
 *   POST /enquiries/{reference}/whatsapp-opened
 */
final class RestController
{
    private const NAMESPACE = 'fcp/v1';
    private const NONCE_ACTION = 'fcp_enquiry';
    private const HONEYPOT_FIELD = 'company_website';

    public function __construct(private Config $config)
    {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/health', [
            'methods'             => 'GET',
            'callback'            => [$this, 'health'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/enquiries', [
            'methods'             => 'POST',
            'callback'            => [$this, 'createEnquiry'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/enquiries/(?P<reference>FCP-\d{4}-\d{6})/whatsapp-opened', [
            'methods'             => 'POST',
            'callback'            => [$this, 'markWhatsappOpened'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function health(WP_REST_Request $request): WP_REST_Response
    {
        $supabase = new SupabaseClient($this->config);
        $data = [
            'status'  => 'ok',
            'service' => 'fcp-horizon-bridge',
            'version' => FCP_HORIZON_VERSION,
            'env'     => $this->config->get('FCP_ENV', 'local'),
            'checks'  => [
                'supabase_configured' => $this->config->supabaseConfigured(),
                'supabase_reachable'  => $supabase->healthCheck(),
            ],
        ];
        return new WP_REST_Response($data, $data['checks']['supabase_reachable'] ? 200 : 503);
    }

    /**
     * Crée une demande. Enregistre AVANT de fournir le lien WhatsApp.
     */
    public function createEnquiry(WP_REST_Request $request)
    {
        // 1. CORS restrictif.
        if (($cors = $this->guardOrigin($request)) instanceof WP_Error) {
            return $cors;
        }

        $params = $request->get_json_params();
        if (!is_array($params)) {
            $params = $request->get_body_params();
        }

        // 2. Honeypot anti-spam (champ caché qui doit rester vide).
        if (trim((string) ($params[self::HONEYPOT_FIELD] ?? '')) !== '') {
            Logger::info('Honeypot déclenché sur /enquiries');
            return new WP_Error('fcp_spam', 'Requête rejetée.', ['status' => 400]);
        }

        // 3. Nonce.
        if (!wp_verify_nonce((string) ($params['_fcp_nonce'] ?? ''), self::NONCE_ACTION)) {
            return new WP_Error('fcp_bad_nonce', 'Jeton de sécurité invalide, veuillez recharger la page.', ['status' => 403]);
        }

        // 4. Limitation de débit.
        $ip = $this->clientIp();
        $limiter = new RateLimiter($this->config->rateLimitPerMinute());
        if (!$limiter->allow($ip)) {
            return new WP_Error('fcp_rate_limited', 'Trop de demandes, veuillez patienter une minute.', ['status' => 429]);
        }

        // 5. Validation + normalisation serveur.
        $result = (new EnquiryValidator())->validate($params);
        if (!$result->isValid()) {
            return new WP_REST_Response([
                'success' => false,
                'errors'  => $result->errors(),
            ], 422);
        }

        // 6. Enregistrement (contact → enquiry → details → audit → communication).
        try {
            $service = $this->makeEnquiryService();
            $outcome = $service->submit($result->input(), [
                'ip'         => $ip,
                'request_id' => wp_generate_uuid4(),
            ]);
        } catch (SupabaseException $e) {
            Logger::error('Échec enregistrement demande', ['code' => $e->getCode()]);
            // Règle 4 : aucun lien WhatsApp n'est renvoyé en cas d'échec.
            return new WP_Error('fcp_persist_failed', 'La demande n’a pas pu être enregistrée. Merci de réessayer.', ['status' => 502]);
        }

        // 7. Succès : la demande est enregistrée, on peut fournir le lien wa.me.
        $response = new WP_REST_Response([
            'success'      => true,
            'reference'    => $outcome['reference'],
            'summary'      => $outcome['summary'],
            'whatsapp_url' => $outcome['whatsapp_url'],
            'message'      => 'Votre demande est enregistrée. Notre Maison revient vers vous rapidement.',
        ], 201);
        $this->applyCorsHeader($response, $request);
        return $response;
    }

    /**
     * Marque la communication WhatsApp comme « opened » (jamais « sent »).
     */
    public function markWhatsappOpened(WP_REST_Request $request)
    {
        if (($cors = $this->guardOrigin($request)) instanceof WP_Error) {
            return $cors;
        }

        $reference = (string) $request['reference'];
        if (!PublicReference::isValid($reference)) {
            return new WP_Error('fcp_bad_reference', 'Référence invalide.', ['status' => 400]);
        }

        $params = $request->get_json_params() ?: [];
        if (!wp_verify_nonce((string) ($params['_fcp_nonce'] ?? ''), self::NONCE_ACTION)) {
            return new WP_Error('fcp_bad_nonce', 'Jeton de sécurité invalide.', ['status' => 403]);
        }

        // Limitation de débit AVANT toute opération coûteuse (lecture Supabase).
        $ip = $this->clientIp();
        $limiter = new RateLimiter($this->config->rateLimitPerMinute());
        if (!$limiter->allow('wa_' . $ip)) {
            return new WP_Error('fcp_rate_limited', 'Trop de requêtes, veuillez patienter.', ['status' => 429]);
        }

        try {
            $client = new SupabaseClient($this->config);
            $enquiries = new EnquiryRepository($client);
            $communications = new CommunicationRepository($client);

            $enquiryId = $enquiries->findIdByReference($reference);
            // Réponse générique : on ne révèle jamais l'existence (ou non) d'une
            // référence (les références sont séquentielles). Aucune donnée
            // personnelle n'est renvoyée. On flippe uniquement la communication
            // WhatsApp « prepared » → « opened » de CETTE demande (jamais « sent »).
            if ($enquiryId !== null) {
                $communications->markWhatsappOpenedForEnquiry($enquiryId);
                (new AuditLogRepository($client))->record(
                    'whatsapp.opened',
                    'communications',
                    $enquiryId,
                    null,
                    $ip,
                );
            }
        } catch (SupabaseException $e) {
            Logger::error('Échec marquage whatsapp opened', ['code' => $e->getCode()]);
            return new WP_Error('fcp_update_failed', 'Mise à jour impossible.', ['status' => 502]);
        }

        $response = new WP_REST_Response(['success' => true], 200);
        $this->applyCorsHeader($response, $request);
        return $response;
    }

    // --- Fabrique de dépendances (câblage couche Data ↔ Application) ---

    private function makeEnquiryService(): EnquiryService
    {
        $client = new SupabaseClient($this->config);
        return new EnquiryService(
            new ContactRepository($client),
            new EnquiryRepository($client),
            new CommunicationRepository($client),
            new AuditLogRepository($client),
            $this->config->get('FCP_WHATSAPP_NUMBER'),
        );
    }

    // --- Sécurité : CORS ---

    private function guardOrigin(WP_REST_Request $request): ?WP_Error
    {
        $allowed = $this->config->allowedOrigins();
        if ($allowed === []) {
            return null; // Aucune restriction configurée (dev) : même-origine par défaut.
        }
        $origin = $request->get_header('origin');
        if ($origin !== null && $origin !== '' && !in_array($origin, $allowed, true)) {
            return new WP_Error('fcp_cors', 'Origine non autorisée.', ['status' => 403]);
        }
        return null;
    }

    private function applyCorsHeader(WP_REST_Response $response, WP_REST_Request $request): void
    {
        $allowed = $this->config->allowedOrigins();
        $origin = (string) $request->get_header('origin');
        if ($origin !== '' && in_array($origin, $allowed, true)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Vary', 'Origin');
        }
    }

    private function clientIp(): string
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
