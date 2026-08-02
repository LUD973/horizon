<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Consent;

/**
 * Catégories de consentement gérées côté site (traceurs/services, via le CMP
 * — tarteaucitron.js).
 *
 * IMPORTANT : « functional » exprime en interne « toujours autorisé »
 * (fonctionnalités strictement nécessaires : session WP, nonce, navigation).
 * Ce n'est PAS un consentement géré par le CMP — aucune catégorie
 * « functional » n'est enregistrée comme service tarteaucitron. Seules
 * ANALYTICS et MARKETING sont gérées par le CMP.
 *
 * Sans lien avec les consentements MÉTIER Horizon (traitement de la demande,
 * marketing du formulaire), qui restent enregistrés dans `contacts` et ne
 * transitent jamais par le CMP (séparation stricte imposée).
 */
final class ConsentCategory
{
    public const FUNCTIONAL = 'functional';
    public const ANALYTICS = 'analytics';
    public const MARKETING = 'marketing';

    /** Catégories réellement soumises au consentement du CMP. */
    public const CMP_MANAGED = [self::ANALYTICS, self::MARKETING];

    public static function isValid(string $category): bool
    {
        return in_array($category, [self::FUNCTIONAL, self::ANALYTICS, self::MARKETING], true);
    }

    public static function isCmpManaged(string $category): bool
    {
        return in_array($category, self::CMP_MANAGED, true);
    }
}
