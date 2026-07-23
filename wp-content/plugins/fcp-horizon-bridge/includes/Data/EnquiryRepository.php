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
    public function __construct(private SupabaseGateway $client)
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

    /**
     * Récupère les données NON SENSIBLES nécessaires au reçu public.
     * Aucune donnée de contact n'est sélectionnée (minimisation).
     *
     * @return array<string,mixed>|null
     */
    public function findReceiptByReference(string $reference): ?array
    {
        $rows = $this->client->select('enquiries', [
            'public_reference' => 'eq.' . $reference,
            'select'           => 'public_reference,status,created_at,enquiry_details(service_date,origin,destination,passengers)',
            'limit'            => '1',
        ]);
        return $rows[0] ?? null;
    }

    // --- Back-office (lecture + statut contrôlé) ---

    /**
     * Liste les demandes récentes (avec contact et détails) pour la liste
     * back-office.
     *
     * @return array<int,array<string,mixed>>
     */
    public function listRecent(int $limit = 25, int $offset = 0): array
    {
        return $this->client->select('enquiries', [
            'select' => 'id,public_reference,status,category,created_at,'
                . 'contacts(first_name,last_name,email),'
                . 'enquiry_details(service_date,origin,destination,passengers)',
            'order'  => 'created_at.desc',
            'limit'  => (string) $limit,
            'offset' => (string) $offset,
        ]);
    }

    /**
     * Fiche complète d'une demande (contact + détails) pour le back-office.
     *
     * @return array<string,mixed>|null
     */
    public function findFullByReference(string $reference): ?array
    {
        $rows = $this->client->select('enquiries', [
            'public_reference' => 'eq.' . $reference,
            'select' => 'id,public_reference,status,category,subcategory,source,'
                . 'preferred_channel,locale,summary,created_at,updated_at,'
                . 'contacts(type,first_name,last_name,email,phone,preferred_language,preferred_channel,consent_marketing),'
                . 'enquiry_details(service_date,service_time,origin,destination,passengers,luggage,flight_number,train_number,notes,flexible_json)',
            'limit'  => '1',
        ]);
        return $rows[0] ?? null;
    }

    /** @return array<string,int> nombre de demandes par statut */
    public function countByStatus(): array
    {
        $rows = $this->client->select('enquiries', ['select' => 'status']);
        $counts = [];
        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? 'new');
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        return $counts;
    }

    /** Met à jour le statut d'une demande (écriture contrôlée back-office). */
    public function updateStatusById(string $enquiryId, string $status): void
    {
        $this->client->update('enquiries', ['id' => 'eq.' . $enquiryId], ['status' => $status]);
    }
}
