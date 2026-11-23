<?php
declare(strict_types=1);

namespace FCP\Horizon\Data\Providers\AeroDataBox;

use FCP\Horizon\Domain\Flight\FlightLeg;
use FCP\Horizon\Domain\Flight\FlightNumber;
use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;

/**
 * Recherche d'un vol par numéro et date (AeroDataBox, via RapidAPI par défaut).
 * La clé reste côté serveur (wp-config : AERODATABOX_API_KEY).
 * Réponses mises en cache 30 minutes pour limiter la consommation.
 */
final class AeroDataBoxClient
{
    private const CACHE_TTL = 1800;

    public function __construct(private Config $config)
    {
    }

    public function configured(): bool
    {
        return $this->config->get('AERODATABOX_API_KEY') !== '';
    }

    /**
     * @return FlightLeg[]|null null si le fournisseur est indisponible / non configuré.
     */
    public function lookup(FlightNumber $flight, string $date): ?array
    {
        if (!$this->configured() || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }
        $cacheKey = 'fcp_fl_' . md5($flight->compact() . '|' . $date);
        $cached = get_transient($cacheKey);
        if (is_array($cached)) {
            return array_map(static fn (array $l) => new FlightLeg(...$l), $cached);
        }

        $host = $this->config->get('AERODATABOX_HOST', 'aerodatabox.p.rapidapi.com');
        $url = 'https://' . $host . '/flights/number/' . rawurlencode($flight->compact()) . '/' . $date
            . '?withAircraftImage=false&withLocation=false&dateLocalRole=Both';
        $response = wp_remote_get($url, [
            'timeout' => 8,
            'headers' => [
                'X-RapidAPI-Key'  => $this->config->get('AERODATABOX_API_KEY'),
                'X-RapidAPI-Host' => $host,
                'Accept'          => 'application/json',
            ],
        ]);
        if (is_wp_error($response)) {
            Logger::error('AeroDataBox injoignable', []);
            return null;
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code === 204 || $code === 404) {
            set_transient($cacheKey, [], self::CACHE_TTL);
            return [];
        }
        if ($code !== 200) {
            Logger::error('AeroDataBox réponse inattendue', ['code' => $code]);
            return null;
        }
        $data = json_decode((string) wp_remote_retrieve_body($response), true);
        if (!is_array($data)) {
            return null;
        }
        $legs = self::parse($data);
        set_transient($cacheKey, array_map(static fn (FlightLeg $l) => [
            $l->depIata, $l->depName, $l->arrIata, $l->arrName, $l->depLocal, $l->arrLocal, $l->airlineName, $l->status,
        ], $legs), self::CACHE_TTL);
        return $legs;
    }

    /**
     * @param array<int|string,mixed> $data
     * @return FlightLeg[]
     */
    public static function parse(array $data): array
    {
        $items = array_is_list($data) ? $data : [$data];
        $legs = [];
        foreach ($items as $f) {
            if (!is_array($f)) {
                continue;
            }
            $dep = is_array($f['departure'] ?? null) ? $f['departure'] : [];
            $arr = is_array($f['arrival'] ?? null) ? $f['arrival'] : [];
            $legs[] = new FlightLeg(
                strtoupper((string) ($dep['airport']['iata'] ?? '')),
                (string) ($dep['airport']['name'] ?? ''),
                strtoupper((string) ($arr['airport']['iata'] ?? '')),
                (string) ($arr['airport']['name'] ?? ''),
                self::local($dep),
                self::local($arr),
                (string) ($f['airline']['name'] ?? ''),
                (string) ($f['status'] ?? ''),
            );
        }
        return $legs;
    }

    /** @param array<string,mixed> $m */
    private static function local(array $m): string
    {
        foreach (['revisedTime', 'scheduledTime'] as $k) {
            if (is_array($m[$k] ?? null) && !empty($m[$k]['local'])) {
                return (string) $m[$k]['local'];
            }
        }
        foreach (['revisedTimeLocal', 'scheduledTimeLocal'] as $k) {
            if (!empty($m[$k])) {
                return (string) $m[$k];
            }
        }
        return '';
    }
}
