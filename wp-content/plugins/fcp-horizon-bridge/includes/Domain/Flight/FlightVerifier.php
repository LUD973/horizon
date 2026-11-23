<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Flight;

/**
 * Règle métier : le vol doit arriver (ou partir) EXACTEMENT de l'aéroport du trajet.
 *   - direction « arr » : le client atterrit à l'aéroport → l'arrivée du vol doit être cet aéroport ;
 *   - direction « dep » : le client s'envole de l'aéroport → le départ du vol doit être cet aéroport.
 * Un aéroport de la même ville ne suffit pas : CDG ≠ Orly ≠ Le Bourget.
 */
final class FlightVerifier
{
    public const OK = 'ok';
    public const MISMATCH = 'mismatch';
    public const NOT_FOUND = 'not_found';
    public const UNAVAILABLE = 'unavailable';

    /** @var array<string,string> */
    public const AIRPORT_NAMES = [
        'CDG' => 'Paris-Charles de Gaulle', 'ORY' => 'Paris-Orly', 'LBG' => 'Paris-Le Bourget',
        'BVA' => 'Paris-Beauvais', 'LYS' => 'Lyon-Saint-Exupéry', 'NCE' => 'Nice-Côte d’Azur',
        'MRS' => 'Marseille-Provence', 'GVA' => 'Genève', 'MCM' => 'Monaco (héliport)',
    ];

    /**
     * @param FlightLeg[]|null $legs null = fournisseur indisponible ; [] = aucun vol ce jour-là.
     * @return array{status:string,message:string,leg:?FlightLeg}
     */
    public function verify(?array $legs, string $expectedIata, string $direction, FlightNumber $flight): array
    {
        $expected = strtoupper($expectedIata);
        $name = self::AIRPORT_NAMES[$expected] ?? $expected;

        if ($legs === null) {
            return ['status' => self::UNAVAILABLE, 'message' => 'Vérification du vol indisponible pour le moment : la Maison contrôlera votre numéro.', 'leg' => null];
        }
        if ($legs === []) {
            return ['status' => self::NOT_FOUND, 'message' => 'Aucun vol ' . $flight->display() . ' trouvé à cette date. Vérifiez le numéro et la date.', 'leg' => null];
        }

        foreach ($legs as $leg) {
            $iata = $direction === 'dep' ? $leg->depIata : $leg->arrIata;
            if (strtoupper($iata) === $expected) {
                return ['status' => self::OK, 'message' => '', 'leg' => $leg];
            }
        }

        $first = $legs[0];
        if ($direction === 'dep') {
            $msg = 'Le vol ' . $flight->display() . ' part de ' . self::label($first->depName, $first->depIata) . ', pas de ' . $name . '. Vérifiez le numéro ou la date.';
        } else {
            $msg = 'Le vol ' . $flight->display() . ' arrive à ' . self::label($first->arrName, $first->arrIata) . ', pas à ' . $name . '. Vérifiez le numéro ou la date.';
        }
        return ['status' => self::MISMATCH, 'message' => $msg, 'leg' => $first];
    }

    private static function label(string $name, string $iata): string
    {
        $known = self::AIRPORT_NAMES[strtoupper($iata)] ?? '';
        $n = $known !== '' ? $known : ($name !== '' ? $name : $iata);
        return $iata !== '' ? $n . ' (' . strtoupper($iata) . ')' : $n;
    }
}
