<?php
declare(strict_types=1);

namespace FCP\Horizon\Support;

/**
 * Limitation de débit par IP (transients WP).
 * Objectif : 5 requêtes/min/IP sur le formulaire public (arbitrage 8).
 *
 * Fenêtre FIXE, et non glissante : chaque requête AUTORISÉE repose une
 * expiration de 60 s, ancrant donc la fin de fenêtre sur la dernière requête
 * autorisée. Une requête refusée ne prolonge pas la fenêtre (aucune écriture),
 * le compteur retombe à zéro 60 s après la dernière requête autorisée.
 *
 * Limite connue : lecture puis écriture non atomiques — deux requêtes
 * concurrentes peuvent lire le même compteur.
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

        // Les deux branches posent la même expiration (60 s) : la distinction
        // est sans effet fonctionnel, conservée telle quelle ici — toute
        // refonte du comportement relève d'un lot dédié, pas d'un correctif
        // documentaire.
        if ($count === 0) {
            set_transient($transient, 1, MINUTE_IN_SECONDS);
        } else {
            set_transient($transient, $count + 1, MINUTE_IN_SECONDS);
        }

        return true;
    }
}
