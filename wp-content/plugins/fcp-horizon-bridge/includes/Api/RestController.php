<?php
declare(strict_types=1);

namespace FCP\Horizon\Api;

use FCP\Horizon\Application\Communication\NotificationService;
use FCP\Horizon\Application\EnquiryService;
use FCP\Horizon\Data\AuditLogRepository;
use FCP\Horizon\Data\CommunicationRepository;
use FCP\Horizon\Data\ContactRepository;
use FCP\Horizon\Data\EnquiryRepository;
use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Data\SupabaseClient;
use FCP\Horizon\Data\SupabaseException;
use FCP\Horizon\Domain\EnquiryValidator;
use FCP\Horizon\Domain\PublicReference;
use FCP\Horizon\Domain\ReceiptPresenter;
use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;
use FCP\Horizon\Support\RateLimiter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * API REST versionnée « fcp/v1 ».
 *
 * Routes :
 *   GET  /health
 *   GET  /form-token                              (jeton frais, robuste au cache)
 *   POST /enquiries                               (Idempotency-Key supporté)
 *   GET  /enquiries/{reference}/receipt           (données non sensibles)
 *   POST /enquiries/{reference}/whatsapp-opened
 */
final class RestController
{
    private const NAMESPACE = 'fcp/v1';
    private const NONCE_ACTION = 'fcp_enquiry';
    private const HONEYPOT_FIELD = 'company_website';
    private const IDEMPOTENCY_TTL = 600; // secondes

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

        register_rest_route(self::NAMESPACE, '/form-token', [
            'methods'             => 'GET',
            'callback'            => [$this, 'formToken'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/enquiries', [
            'methods'             => 'POST',
            'callback'            => [$this, 'createEnquiry'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/enquiries/(?P<reference>FCP-\d{4}-\d{6})/receipt', [
            'methods'             => 'GET',
            'callback'            => [$this, 'receipt'],
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
     * Renvoie un jeton frais, généré dans le MÊME contexte REST que la
     * soumission. Résout la fragilité des nonces mis en cache dans le HTML et
     * l'incohérence entre page rendue connecté / requête traitée anonyme.
     */
    public function formToken(WP_REST_Request $request): WP_REST_Response
    {
        $response = new WP_REST_Response(['token' => wp_create_nonce(self::NONCE_ACTION)], 200);
        // Ne jamais mettre ce jeton en cache.
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->applyCorsHeader($response, $request);
        return $response;
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

        // 5b. Idempotency-Key : si la même clé a déjà abouti, on renvoie la
        // réponse précédente sans recréer de demande (retries réseau).
        $idempotencyStore = $this->idempotencyStoreKey($request);
        if ($idempotencyStore !== null) {
            $cached = get_transient($idempotencyStore);
            if (is_array($cached)) {
                $replay = new WP_REST_Response($cached, 200);
                $this->applyCorsHeader($replay, $request);
                return $replay;
            }
        }

        // 6. Enregistrement (contact → enquiry → details → audit → communication).
        try {
            $service = $this->makeEnquiryService();
            $outcome = $service->submit($result->input(), [
                'ip'         => $ip,
                'request_id' => wp_generate_uuid4(),
                'fiche_base' => admin_url('admin.php?page=fcp-horizon-enquiry&ref='),
            ]);
        } catch (SupabaseException $e) {
            Logger::error('Échec enregistrement demande', ['code' => $e->getCode()]);
            // Règle 4 : aucun lien WhatsApp n'est renvoyé en cas d'échec.
            return new WP_Error('fcp_persist_failed', 'La demande n’a pas pu être enregistrée. Merci de réessayer.', ['status' => 502]);
        }

        // 7. Succès : la demande est enregistrée, on peut fournir le lien wa.me.
        $payload = [
            'success'      => true,
            'reference'    => $outcome['reference'],
            'summary'      => $outcome['summary'],
            'whatsapp_url' => $outcome['whatsapp_url'],
            'message'      => 'Votre demande est enregistrée. Notre Maison revient vers vous rapidement.',
        ];
        if ($idempotencyStore !== null) {
            set_transient($idempotencyStore, $payload, self::IDEMPOTENCY_TTL);
        }

        // Déclenche le traitement asynchrone de l'outbox (hors requête client).
        $this->triggerOutbox();

        $response = new WP_REST_Response($payload, 201);
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

    /**
     * Reçu public d'une demande — données NON sensibles uniquement.
     * Réponse générique (aucun oracle d'existence) + rate limiting.
     */
    public function receipt(WP_REST_Request $request)
    {
        if (($cors = $this->guardOrigin($request)) instanceof WP_Error) {
            return $cors;
        }

        $reference = (string) $request['reference'];
        if (!PublicReference::isValid($reference)) {
            return new WP_Error('fcp_bad_reference', 'Référence invalide.', ['status' => 400]);
        }

        $limiter = new RateLimiter($this->config->rateLimitPerMinute());
        if (!$limiter->allow('receipt_' . $this->clientIp())) {
            return new WP_Error('fcp_rate_limited', 'Trop de requêtes, veuillez patienter.', ['status' => 429]);
        }

        try {
            $repo = new EnquiryRepository(new SupabaseClient($this->config));
            $row = $repo->findReceiptByReference($reference);
        } catch (SupabaseException $e) {
            Logger::error('Échec lecture reçu', ['code' => $e->getCode()]);
            return new WP_Error('fcp_read_failed', 'Lecture impossible.', ['status' => 502]);
        }

        if ($row === null) {
            return new WP_Error('fcp_not_found', 'Reçu introuvable.', ['status' => 404]);
        }

        $response = new WP_REST_Response(['success' => true, 'receipt' => ReceiptPresenter::present($row)], 200);
        $this->applyCorsHeader($response, $request);
        return $response;
    }

    // --- Fabrique de dépendances (câblage couche Data ↔ Application) ---

    /** Clé de stockage idempotent, ou null si aucune Idempotency-Key fournie. */
    private function idempotencyStoreKey(WP_REST_Request $request): ?string
    {
        $key = $request->get_header('idempotency_key'); // « Idempotency-Key » normalisé par WP
        if (!is_string($key) || trim($key) === '') {
            return null;
        }
        return 'fcp_idem_' . md5(trim($key));
    }

    private function makeEnquiryService(): EnquiryService
    {
        $client = new SupabaseClient($this->config);
        $notifications = new NotificationService(
            new MessageRepository($client),
            $this->config->get('FCP_MAIL_INTERNAL'),
        );
        return new EnquiryService(
            new ContactRepository($client),
            new EnquiryRepository($client),
            new CommunicationRepository($client),
            new AuditLogRepository($client),
            $this->config->get('FCP_WHATSAPP_NUMBER'),
            $notifications,
        );
    }

    /** Réveille le traitement de l'outbox sans bloquer la réponse client. */
    private function triggerOutbox(): void
    {
        try {
            wp_schedule_single_event(time(), 'fcp_horizon_process_outbox_now');
            if (function_exists('spawn_cron')) {
                spawn_cron();
            }
        } catch (\Throwable $e) {
            Logger::error('Déclenchement outbox impossible', []);
        }
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
