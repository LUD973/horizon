<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;

/**
 * Client HTTP minimal pour Supabase (PostgREST + RPC).
 *
 * Accès CÔTÉ SERVEUR uniquement, via la clé service_role. Cette clé ne doit
 * jamais transiter par le navigateur. Toutes les écritures métier passent par
 * les repositories (couche Data), jamais directement par la présentation.
 */
final class SupabaseClient
{
    public function __construct(private Config $config)
    {
    }

    /**
     * Vérifie la disponibilité de l'API PostgREST.
     * Retourne true si l'endpoint racine répond avec un statut < 500.
     */
    public function healthCheck(): bool
    {
        if (!$this->config->supabaseConfigured()) {
            return false;
        }

        $response = wp_remote_get(
            rtrim($this->config->get('SUPABASE_URL'), '/') . '/rest/v1/',
            [
                'timeout' => 5,
                'headers' => $this->headers(),
            ]
        );

        if (is_wp_error($response)) {
            Logger::error('Supabase health check failed', ['error' => $response->get_error_message()]);
            return false;
        }

        return (int) wp_remote_retrieve_response_code($response) < 500;
    }

    /** @return array<string,string> */
    private function headers(): array
    {
        $key = $this->config->get('SUPABASE_SERVICE_ROLE_KEY');
        return [
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ];
    }
}
