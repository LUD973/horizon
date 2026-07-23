<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Compose le reçu public d'une demande — logique pure, testable.
 *
 * Minimisation : n'expose QUE des informations de mission non identifiantes
 * (référence, statut, date, trajet, passagers). Aucune donnée personnelle
 * (nom, e-mail, téléphone) n'est jamais incluse, la référence étant séquentielle.
 */
final class ReceiptPresenter
{
    /**
     * @param array<string,mixed> $row ligne enquiries (+ enquiry_details imbriqués)
     * @return array<string,mixed> sous-ensemble sûr, prêt à sérialiser
     */
    public static function present(array $row): array
    {
        $details = $row['enquiry_details'] ?? [];
        if (isset($details[0])) {
            $details = $details[0];
        }
        if (!is_array($details)) {
            $details = [];
        }

        return [
            'reference'   => (string) ($row['public_reference'] ?? ''),
            'status'      => (string) ($row['status'] ?? ''),
            'received_at' => self::dateOnly((string) ($row['created_at'] ?? '')),
            'service'     => [
                'date'        => (string) ($details['service_date'] ?? ''),
                'from'        => (string) ($details['origin'] ?? ''),
                'to'          => (string) ($details['destination'] ?? ''),
                'passengers'  => isset($details['passengers']) ? (int) $details['passengers'] : null,
            ],
        ];
    }

    private static function dateOnly(string $timestamp): string
    {
        if ($timestamp === '') {
            return '';
        }
        // Conserve la date (AAAA-MM-JJ) sans l'heure précise.
        return substr($timestamp, 0, 10);
    }
}
