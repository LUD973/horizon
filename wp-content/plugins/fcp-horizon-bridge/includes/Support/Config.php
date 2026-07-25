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
            'PLAUSIBLE_DOMAIN', 'PLAUSIBLE_SCRIPT_URL',
            // Didomi : DIDOMI_NOTICE_ID reste informatif (non utilisé pour
            // reconstruire un snippet). Le SDK est piloté par DIDOMI_SDK_EMBED
            // (snippet officiel copié depuis la rubrique Publish de la console).
            'DIDOMI_NOTICE_ID', 'DIDOMI_SDK_EMBED',
            'DIDOMI_PURPOSE_ANALYTICS', 'DIDOMI_PURPOSE_MARKETING', 'DIDOMI_VENDOR_PLAUSIBLE',
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

    /**
     * Snippet officiel Didomi (copié tel quel depuis la rubrique Publish de la
     * console Didomi). Jamais reconstruit à partir d'un simple identifiant :
     * la configuration doit contenir le code exact fourni par Didomi.
     */
    public function didomiSdkEmbed(): string
    {
        return $this->get('DIDOMI_SDK_EMBED');
    }

    public function didomiConfigured(): bool
    {
        return $this->didomiSdkEmbed() !== '';
    }

    /** Identifiant de purpose Didomi pour la catégorie analytics (à vérifier dans la console). */
    public function didomiPurposeAnalytics(): string
    {
        return $this->get('DIDOMI_PURPOSE_ANALYTICS', 'analytics');
    }

    /** Identifiant de purpose Didomi pour la catégorie marketing (à vérifier dans la console). */
    public function didomiPurposeMarketing(): string
    {
        return $this->get('DIDOMI_PURPOSE_MARKETING', 'advertising');
    }

    /** Identifiant de vendor Didomi pour Plausible, si déclaré (réservé — lot Plausible). */
    public function didomiVendorPlausible(): string
    {
        return $this->get('DIDOMI_VENDOR_PLAUSIBLE');
    }

    private const DEFAULT_PLAUSIBLE_SCRIPT_URL = 'https://plausible.io/js/script.js';

    /**
     * Domaine Plausible, validé (sinon neutralisé -> non configuré). Un
     * domaine malformé ne doit jamais atteindre le navigateur.
     */
    public function plausibleDomain(): string
    {
        $domain = $this->get('PLAUSIBLE_DOMAIN');
        return $this->looksLikeDomain($domain) ? $domain : '';
    }

    public function plausibleConfigured(): bool
    {
        return $this->plausibleDomain() !== '';
    }

    /**
     * URL du script Plausible (auto-hébergement possible). Une URL invalide
     * ou non HTTPS est neutralisée au profit de la valeur par défaut — jamais
     * envoyée telle quelle au navigateur.
     */
    public function plausibleScriptUrl(): string
    {
        $url = $this->get('PLAUSIBLE_SCRIPT_URL', self::DEFAULT_PLAUSIBLE_SCRIPT_URL);
        return $this->looksLikeHttpsUrl($url) ? $url : self::DEFAULT_PLAUSIBLE_SCRIPT_URL;
    }

    private function looksLikeDomain(string $domain): bool
    {
        if ($domain === '') {
            return false;
        }
        return (bool) preg_match(
            '/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$/i',
            $domain
        );
    }

    private function looksLikeHttpsUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        return parse_url($url, PHP_URL_SCHEME) === 'https';
    }
}
