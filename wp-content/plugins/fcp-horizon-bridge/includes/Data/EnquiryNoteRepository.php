<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Notes internes courtes attachées à une demande (écriture back-office autorisée).
 */
final class EnquiryNoteRepository
{
    public function __construct(private SupabaseGateway $client)
    {
    }

    public function add(string $enquiryId, string $author, string $body): void
    {
        $this->client->insert('enquiry_notes', [
            'enquiry_id' => $enquiryId,
            'author'     => $author,
            'body'       => $body,
        ]);
    }

    /** @return array<int,array<string,mixed>> */
    public function listForEnquiry(string $enquiryId): array
    {
        return $this->client->select('enquiry_notes', [
            'enquiry_id' => 'eq.' . $enquiryId,
            'select'     => 'author,body,created_at',
            'order'      => 'created_at.desc',
        ]);
    }
}
