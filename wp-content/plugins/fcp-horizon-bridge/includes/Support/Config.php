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
            'BREVO_API_KEY', 'FCP_MAIL_FROM', 'FCP_MAIL_INTERNAL',
            'PLAUSIBLE_DOMAIN', 'DIDOMI_NOTICE_ID',
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
}
