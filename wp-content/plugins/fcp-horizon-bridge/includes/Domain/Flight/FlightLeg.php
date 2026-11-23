<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Flight;

/**
 * Un tronçon de vol tel que renvoyé par le fournisseur (aéroports IATA + heures locales).
 */
final class FlightLeg
{
    public function __construct(
        public readonly string $depIata,
        public readonly string $depName,
        public readonly string $arrIata,
        public readonly string $arrName,
        public readonly string $depLocal = '',
        public readonly string $arrLocal = '',
        public readonly string $airlineName = '',
        public readonly string $status = '',
    ) {
    }

    /** Heure « HH:MM » extraite d'une date locale « 2026-12-20 07:35+01:00 ». */
    public static function hour(string $local): string
    {
        return preg_match('/(\d{2}):(\d{2})/', $local, $m) ? $m[1] . ':' . $m[2] : '';
    }
}
