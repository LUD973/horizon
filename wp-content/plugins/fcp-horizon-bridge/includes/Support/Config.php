<?php
declare(strict_types=1);

namespace FCP\Horizon\Support;

/**
 * Configuration lue depuis les variables d'environnement.
 *
 * Aucun secret n'est stocké en base ni exposé au navigateur.
 * Ordre de résolution : $_ENV / getenv() puis constantes wp-config si définies.
 */
final class Config
{
    /** @param array<string,string> $values */
    private function __construct(private array $values)
    {
    }

    public static function fromEnvironment(): self
    {
        $keys = [
            'FCP_ENV', 'FCP_DEBUG',
            'SUPABASE_URL', 'SUPABASE_ANON_KEY', 'SUPABASE_SERVICE_ROLE_KEY',
            'FCP_ALLOWED_ORIGINS', 'FCP_RATE_LIMIT_PER_MIN',
            'FCP_WHATSAPP_NUMBER',
            'BREVO_API_KEY', 'FCP_MAIL_FROM', 'FCP_MAIL_FROM_NAME', 'FCP_MAIL_INTERNAL',
            'GOATCOUNTER_ENDPOINT', 'GOATCOUNTER_SCRIPT_URL',
            'TARTEAUCITRON_PRIVACY_URL', 'TARTEAUCITRON_SCRIPT_URL',
        ];

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = self::read($key);
        }

        return new self($values);
    }

    private static function read(string $key): string
    {
        $value = getenv($key);
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        if (($value === false || $value === '') && defined($key)) {
            $value = (string) constant($key);
        }
        return is_string($value) ? trim($value) : '';
    }

    public function get(string $key, string $default = ''): string
    {
        $value = $this->values[$key] ?? '';
        return $value !== '' ? $value : $default;
    }

    public function isProduction(): bool
    {
        return $this->get('FCP_ENV', 'local') === 'production';
    }

    public function debugEnabled(): bool
    {
        return filter_var($this->get('FCP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    }

    public function rateLimitPerMinute(): int
    {
        return max(1, (int) $this->get('FCP_RATE_LIMIT_PER_MIN', '5'));
    }

    /** @return string[] */
    public function allowedOrigins(): array
    {
        $raw = $this->get('FCP_ALLOWED_ORIGINS', '');
        if ($raw === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public function supabaseConfigured(): bool
    {
        return $this->get('SUPABASE_URL') !== '' && $this->get('SUPABASE_SERVICE_ROLE_KEY') !== '';
    }

    private const DEFAULT_GOATCOUNTER_SCRIPT_URL = 'https://gc.zgo.at/count.js';
    private const DEFAULT_TARTEAUCITRON_SCRIPT_URL = 'https://cdn.jsdelivr.net/npm/tarteaucitronjs@1/tarteaucitron.min.js';

    /**
     * Point d'entrée GoatCounter (ex. https://code.goatcounter.com/count),
     * validé (sinon neutralisé -> non configuré). Une valeur malformée ne
     * doit jamais atteindre le navigateur.
     */
    public function goatcounterEndpoint(): string
    {
        $endpoint = $this->get('GOATCOUNTER_ENDPOINT');
        return $this->looksLikeHttpsUrl($endpoint) ? $endpoint : '';
    }

    public function goatcounterConfigured(): bool
    {
        return $this->goatcounterEndpoint() !== '';
    }

    /**
     * URL du script GoatCounter (auto-hébergement possible). Une URL invalide
     * ou non HTTPS est neutralisée au profit de la valeur par défaut — jamais
     * envoyée telle quelle au navigateur.
     */
    public function goatcounterScriptUrl(): string
    {
        $url = $this->get('GOATCOUNTER_SCRIPT_URL', self::DEFAULT_GOATCOUNTER_SCRIPT_URL);
        return $this->looksLikeHttpsUrl($url) ? $url : self::DEFAULT_GOATCOUNTER_SCRIPT_URL;
    }

    /**
     * Lien vers la politique de confidentialité, passé tel quel à
     * `tarteaucitron.init()` (option publique du CMP, pas un secret). Une
     * valeur absente ou invalide désactive le CMP (aucune bannière chargée).
     */
    public function tarteaucitronPrivacyUrl(): string
    {
        $url = $this->get('TARTEAUCITRON_PRIVACY_URL');
        return $this->looksLikeHttpsUrl($url) ? $url : '';
    }

    public function tarteaucitronConfigured(): bool
    {
        return $this->tarteaucitronPrivacyUrl() !== '';
    }

    /**
     * URL du script coeur tarteaucitron.js (CDN officiel par défaut,
     * auto-hébergement possible). Une URL invalide ou non HTTPS est
     * neutralisée au profit de la valeur par défaut.
     */
    public function tarteaucitronScriptUrl(): string
    {
        $url = $this->get('TARTEAUCITRON_SCRIPT_URL', self::DEFAULT_TARTEAUCITRON_SCRIPT_URL);
        return $this->looksLikeHttpsUrl($url) ? $url : self::DEFAULT_TARTEAUCITRON_SCRIPT_URL;
    }

    private function looksLikeHttpsUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        return parse_url($url, PHP_URL_SCHEME) === 'https';
    }
}
