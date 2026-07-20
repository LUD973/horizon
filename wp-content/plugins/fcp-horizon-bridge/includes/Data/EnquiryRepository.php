<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Accès aux demandes et à leurs détails.
 *
 * La référence publique (public_reference) est attribuée par le trigger Postgres
 * à l'insertion : elle n'est jamais fournie par le client ni par le navigateur.
 */
final class EnquiryRepository
{
    public function __construct(private SupabaseClient $client)
    {
    }

    /**
     * Crée la demande puis ses détails.
     *
     * @param array<string,mixed> $enquiryRow
     * @param array<string,mixed> $detailsRow
     * @return array{id:string, public_reference:string}
     */
    public function create(string $contactId, array $enquiryRow, array $detailsRow): array
    {
        $enquiryRow['contact_id'] = $contactId;
        // status par défaut = 'new' (défini en base) — non forcé ici.

        $enquiry = $this->client->insert('enquiries', $enquiryRow);

        $detailsRow['enquiry_id'] = (string) $enquiry['id'];
        $this->client->insert('enquiry_details', $detailsRow);

        return [
            'id'               => (string) $enquiry['id'],
            'public_reference' => (string) $enquiry['public_reference'],
        ];
    }

    /** Retourne l'id interne d'une demande à partir de sa référence publique, ou null. */
    public function findIdByReference(string $reference): ?string
    {
        $rows = $this->client->select('enquiries', [
            'public_reference' => 'eq.' . $reference,
            'select'           => 'id',
            'limit'            => '1',
        ]);
        return isset($rows[0]['id']) ? (string) $rows[0]['id'] : null;
    }
}
