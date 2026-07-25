<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Consent;

/**
 * Catégories de consentement gérées côté site (traceurs/services, via Didomi).
 *
 * IMPORTANT : « functional » exprime en interne « toujours autorisé »
 * (fonctionnalités strictement nécessaires : session WP, nonce, navigation).
 * Ce n'est PAS un consentement Didomi — aucune catégorie « functional » n'est
 * envoyée à leur API. Seules ANALYTICS et MARKETING sont gérées par Didomi.
 *
 * Sans lien avec les consentements MÉTIER Horizon (traitement de la demande,
 * marketing du formulaire), qui restent enregistrés dans `contacts` et ne
 * transitent jamais par Didomi (séparation stricte imposée).
 */
final class ConsentCategory
{
    public const FUNCTIONAL = 'functional';
    public const ANALYTICS = 'analytics';
    public const MARKETING = 'marketing';

    /** Catégories réellement soumises au consentement Didomi. */
    public const DIDOMI_MANAGED = [self::ANALYTICS, self::MARKETING];

    public static function isValid(string $category): bool
    {
        return in_array($category, [self::FUNCTIONAL, self::ANALYTICS, self::MARKETING], true);
    }

    public static function isDidomiManaged(string $category): bool
    {
        return in_array($category, self::DIDOMI_MANAGED, true);
    }
}
