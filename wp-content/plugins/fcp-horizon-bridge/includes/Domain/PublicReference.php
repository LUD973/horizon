<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Objet-valeur : référence publique d'une demande.
 *
 * Format imposé : FCP-<AAAA>-<6 chiffres>  (ex. FCP-2026-000123).
 *
 * IMPORTANT : la GÉNÉRATION (attribution du compteur) est faite côté serveur,
 * en base (trigger Postgres), jamais côté navigateur. Cette classe ne fait que
 * VALIDER et FORMATER — logique métier pure, testable unitairement.
 */
final class PublicReference
{
    public const PREFIX = 'FCP';
    private const PATTERN = '/^FCP-(\d{4})-(\d{6})$/';

    private function __construct(
        private int $year,
        private int $sequence,
    ) {
    }

    /**
     * Construit une référence à partir d'une année et d'un compteur.
     *
     * @throws \InvalidArgumentException si le compteur dépasse 6 chiffres.
     */
    public static function fromParts(int $year, int $sequence): self
    {
        if ($year < 2000 || $year > 9999) {
            throw new \InvalidArgumentException('Année de référence invalide.');
        }
        if ($sequence < 1 || $sequence > 999999) {
            throw new \InvalidArgumentException('Compteur de référence hors plage (1..999999).');
        }
        return new self($year, $sequence);
    }

    /** Analyse une chaîne au format FCP-AAAA-NNNNNN. */
    public static function parse(string $value): self
    {
        if (preg_match(self::PATTERN, trim($value), $m) !== 1) {
            throw new \InvalidArgumentException('Référence publique invalide : ' . $value);
        }
        return new self((int) $m[1], (int) $m[2]);
    }

    public static function isValid(string $value): bool
    {
        return preg_match(self::PATTERN, trim($value)) === 1;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function sequence(): int
    {
        return $this->sequence;
    }

    public function toString(): string
    {
        return sprintf('%s-%04d-%06d', self::PREFIX, $this->year, $this->sequence);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
