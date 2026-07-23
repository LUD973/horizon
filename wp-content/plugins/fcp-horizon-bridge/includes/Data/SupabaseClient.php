<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;

/**
 * Client HTTP pour Supabase (PostgREST).
 *
 * Accès CÔTÉ SERVEUR uniquement, via la clé service_role — jamais exposée au
 * navigateur. Les écritures métier passent par les repositories.
 */
final class SupabaseClient implements SupabaseGateway
{
    public function __construct(private Config $config)
    {
    }

    public function healthCheck(): bool
    {
        if (!$this->config->supabaseConfigured()) {
            return false;
        }
        $response = wp_remote_get($this->restUrl(''), [
            'timeout' => 5,
            'headers' => $this->headers(),
        ]);
        if (is_wp_error($response)) {
            Logger::error('Supabase health check failed', ['error' => $response->get_error_message()]);
            return false;
        }
        return (int) wp_remote_retrieve_response_code($response) < 500;
    }

    /**
     * SELECT sur une table.
     *
     * @param array<string,string> $query paramètres PostgREST (ex: ['email' => 'eq.a@b.c'])
     * @return array<int,array<string,mixed>>
     */
    public function select(string $table, array $query = []): array
    {
        $url = add_query_arg($query, $this->restUrl($table));
        $response = $this->request('GET', $url);
        return is_array($response) ? $response : [];
    }

    /**
     * INSERT et retour de la ligne créée (Prefer: return=representation).
     *
     * @param array<string,mixed> $row
     * @return array<string,mixed> la ligne insérée
     */
    public function insert(string $table, array $row): array
    {
        $response = $this->request('POST', $this->restUrl($table), $row, [
            'Prefer' => 'return=representation',
        ]);
        if (!is_array($response) || !isset($response[0])) {
            throw new SupabaseException("Insertion sans représentation retournée pour {$table}.");
        }
        return $response[0];
    }

    /**
     * UPDATE ciblé.
     *
     * @param array<string,string> $filters paramètres PostgREST
     * @param array<string,mixed>  $patch
     */
    public function update(string $table, array $filters, array $patch): void
    {
        $url = add_query_arg($filters, $this->restUrl($table));
        $this->request('PATCH', $url, $patch, ['Prefer' => 'return=minimal']);
    }

    /**
     * Appelle une fonction Postgres exposée (PostgREST RPC).
     *
     * @param array<string,mixed> $args
     */
    public function rpc(string $function, array $args = []): void
    {
        $this->request('POST', $this->restUrl('rpc/' . $function), $args, [
            'Prefer' => 'return=minimal',
        ]);
    }

    /**
     * @param array<string,mixed>|null $body
     * @param array<string,string>     $extraHeaders
     * @return array<int|string,mixed>|null
     */
    private function request(string $method, string $url, ?array $body = null, array $extraHeaders = []): ?array
    {
        if (!$this->config->supabaseConfigured()) {
            throw new SupabaseException('Supabase non configuré (variables d’environnement manquantes).');
        }

        $args = [
            'method'  => $method,
            'timeout' => 10,
            'headers' => array_merge($this->headers(), $extraHeaders),
        ];
        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            Logger::error('Supabase request failed', ['method' => $method, 'error' => $response->get_error_message()]);
            throw new SupabaseException('Requête Supabase en échec.');
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw  = wp_remote_retrieve_body($response);

        if ($code >= 400) {
            // On journalise sobrement, sans exposer de secret ni le corps complet.
            Logger::error('Supabase HTTP error', ['method' => $method, 'code' => $code]);
            throw new SupabaseException("Erreur Supabase HTTP {$code}.", $code);
        }

        $decoded = $raw !== '' ? json_decode($raw, true) : null;
        return is_array($decoded) ? $decoded : null;
    }

    private function restUrl(string $table): string
    {
        return rtrim($this->config->get('SUPABASE_URL'), '/') . '/rest/v1/' . ltrim($table, '/');
    }

    /** @return array<string,string> */
    private function headers(): array
    {
        $key = $this->config->get('SUPABASE_SERVICE_ROLE_KEY');
        $headers = [
            'apikey'       => $key,
            'Content-Type' => 'application/json',
        ];
        // Compatibilité clés :
        //  - clés JWT legacy (anon/service_role, commencent par « ey… ») : PostgREST
        //    détermine le rôle via l'en-tête Authorization Bearer <JWT>.
        //  - nouvelles clés « sb_secret_… » : le rôle est résolu via l'en-tête apikey ;
        //    envoyer un Bearer non-JWT casserait l'analyse côté PostgREST.
        if ($this->looksLikeJwt($key)) {
            $headers['Authorization'] = 'Bearer ' . $key;
        }
        return $headers;
    }

    private function looksLikeJwt(string $key): bool
    {
        return str_starts_with($key, 'ey') && substr_count($key, '.') === 2;
    }
}
