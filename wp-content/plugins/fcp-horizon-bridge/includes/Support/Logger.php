<?php
declare(strict_types=1);

namespace FCP\Horizon\Support;

/**
 * Journalisation sobre : aucun secret, aucune donnée personnelle superflue.
 *
 * Les clés sensibles connues sont masquées avant écriture (récursivement),
 * sur la base du NOM de la clé — une valeur secrète transmise sous un nom
 * absent de SENSITIVE_KEYS ne serait pas masquée.
 *
 * N'écrit que si la constante WP_DEBUG est définie ET vraie. La destination
 * réelle du message dépend ensuite de la configuration PHP/WordPress
 * (`error_log`, donc `WP_DEBUG_LOG` le cas échéant).
 */
final class Logger
{
    private const REDACTED = '[redacted]';

    private const SENSITIVE_KEYS = [
        'authorization', 'apikey', 'api_key', 'service_role_key',
        'supabase_service_role_key', 'brevo_api_key', 'password', 'token',
    ];

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        $line = sprintf('[fcp-horizon][%s] %s', $level, $message);
        if ($context !== []) {
            $line .= ' ' . wp_json_encode(self::redact($context));
        }
        error_log($line);
    }

    /** @param array<string,mixed> $context @return array<string,mixed> */
    private static function redact(array $context): array
    {
        foreach ($context as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $context[$key] = self::REDACTED;
            } elseif (is_array($value)) {
                $context[$key] = self::redact($value);
            }
        }
        return $context;
    }
}
