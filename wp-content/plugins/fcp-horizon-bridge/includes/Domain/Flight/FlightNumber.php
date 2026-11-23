<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Flight;

/**
 * Numéro de vol normalisé : « af 1234 » → « AF1234 » (code compagnie IATA + numéro).
 */
final class FlightNumber
{
    private function __construct(private string $airline, private string $number)
    {
    }

    public static function parse(string $raw): ?self
    {
        $t = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $raw));
        if (!preg_match('/^([A-Z0-9]{2})(\d{1,4})([A-Z]?)$/', $t, $m) || !preg_match('/[A-Z]/', $m[1])) {
            return null;
        }
        return new self($m[1], ltrim($m[2], '0') === '' ? '0' : ltrim($m[2], '0') . $m[3]);
    }

    public function airline(): string
    {
        return $this->airline;
    }

    /** « AF1234 », format attendu par les fournisseurs de données de vol. */
    public function compact(): string
    {
        return $this->airline . $this->number;
    }

    /** « AF 1234 », format d'affichage. */
    public function display(): string
    {
        return $this->airline . ' ' . $this->number;
    }
}
