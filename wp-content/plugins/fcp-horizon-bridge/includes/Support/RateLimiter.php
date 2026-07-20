<?php
declare(strict_types=1);

namespace FCP\Horizon\Support;

/**
 * Limitation de débit par IP, fenêtre glissante d'une minute (transients WP).
 * Objectif : 5 requêtes/min/IP sur le formulaire public (arbitrage 8).
 */
final class RateLimiter
{
    public function __construct(private int $maxPerMinute)
    {
    }

    /** @return bool true si la requête est autorisée, false si le seuil est dépassé. */
    public function allow(string $key): bool
    {
        $transient = 'fcp_rl_' . md5($key);
        $count = (int) get_transient($transient);

        if ($count >= $this->maxPerMinute) {
            return false;
        }

        // Première requête de la fenêtre : pose une expiration de 60 s.
        if ($count === 0) {
            set_transient($transient, 1, MINUTE_IN_SECONDS);
        } else {
            set_transient($transient, $count + 1, MINUTE_IN_SECONDS);
        }

        return true;
    }
}
