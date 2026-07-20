<?php
declare(strict_types=1);

namespace FCP\Horizon\Api;

use FCP\Horizon\Data\SupabaseClient;
use FCP\Horizon\Support\Config;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Exposition de l'API v1.
 *
 * Semaine 1 : GET /fcp/v1/health.
 * Semaines suivantes : POST /enquiries, GET /enquiries/{ref}/receipt, etc.
 * Le namespace REST « fcp/v1 » matérialise le versionnage imposé.
 */
final class RestController
{
    private const NAMESPACE = 'fcp/v1';

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

        $healthy = $data['checks']['supabase_reachable'];

        return new WP_REST_Response($data, $healthy ? 200 : 503);
    }
}
