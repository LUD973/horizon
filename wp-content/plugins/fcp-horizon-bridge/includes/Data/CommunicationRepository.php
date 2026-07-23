<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Traçabilité des communications (arbitrage 7).
 *
 * Statuts : prepared | opened | sent | failed.
 *   - « opened » : l'utilisateur a ouvert le lien wa.me (aucune preuve d'envoi).
 *   - « sent »   : réservé aux canaux fournissant une preuve technique (Brevo).
 */
final class CommunicationRepository
{
    public function __construct(private SupabaseGateway $client)
    {
    }

    /**
     * Enregistre une communication préparée. Retourne son id.
     */
    public function prepare(string $enquiryId, string $channel, string $type, ?string $recipient = null): string
    {
        $row = $this->client->insert('communications', [
            'enquiry_id' => $enquiryId,
            'channel'    => $channel,
            'type'       => $type,
            'recipient'  => $recipient,
            'status'     => 'prepared',
        ]);
        return (string) $row['id'];
    }

    /** Marque une communication comme « opened » (jamais « sent »). */
    public function markOpened(string $communicationId): void
    {
        $this->client->update('communications', ['id' => 'eq.' . $communicationId], [
            'status' => 'opened',
        ]);
    }

    /** Marque la (dernière) communication WhatsApp d'une demande comme « opened ». */
    public function markWhatsappOpenedForEnquiry(string $enquiryId): void
    {
        $this->client->update(
            'communications',
            ['enquiry_id' => 'eq.' . $enquiryId, 'channel' => 'eq.whatsapp', 'status' => 'eq.prepared'],
            ['status' => 'opened']
        );
    }

    /** @return array<int,array<string,mixed>> communications d'une demande (back-office) */
    public function listForEnquiry(string $enquiryId): array
    {
        return $this->client->select('communications', [
            'enquiry_id' => 'eq.' . $enquiryId,
            'select'     => 'channel,type,status,recipient,created_at',
            'order'      => 'created_at.desc',
        ]);
    }
}
