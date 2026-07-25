<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Accès à communication_messages : file d'attente (outbox) + historique.
 * Source de vérité des messages sortants/entrants.
 */
final class MessageRepository
{
    public function __construct(private SupabaseGateway $client)
    {
    }

    /**
     * Enregistre une intention d'envoi au statut « pending » (outbox).
     *
     * @param array<string,mixed> $row
     * @return string id du message
     */
    public function enqueue(array $row): string
    {
        $row['status'] = 'pending';
        $created = $this->client->insert('communication_messages', $row);
        return (string) $created['id'];
    }

    /**
     * Messages prêts à être traités (pending / retry échu).
     *
     * @return array<int,array<string,mixed>>
     */
    public function listDue(int $limit = 20): array
    {
        return $this->client->select('communication_messages', [
            'status' => 'in.(pending,retry_scheduled)',
            'or'     => '(next_retry_at.is.null,next_retry_at.lte.' . gmdate('c') . ')',
            'order'  => 'created_at.asc',
            'limit'  => (string) $limit,
        ]);
    }

    /**
     * « Claim » atomique : passe le message à processing seulement s'il est
     * encore pending/retry_scheduled. Retourne false si déjà pris par un autre
     * worker (évite le double envoi).
     */
    public function claim(string $id, int $attempt): bool
    {
        $rows = $this->client->updateReturning(
            'communication_messages',
            ['id' => 'eq.' . $id, 'status' => 'in.(pending,retry_scheduled)'],
            ['status' => 'processing', 'attempt_count' => $attempt, 'last_attempt_at' => gmdate('c')]
        );
        return $rows !== [];
    }

    public function markSent(string $id, string $provider, ?string $providerMessageId): void
    {
        $this->client->update('communication_messages', ['id' => 'eq.' . $id], [
            'status'              => 'sent',
            'provider'            => $provider,
            'provider_message_id' => $providerMessageId,
            'sent_at'             => gmdate('c'),
            'error_code'          => null,
            'error_message'       => null,
        ]);
    }

    public function markRetry(string $id, string $errorCode, string $errorMessage, string $nextRetryAtIso): void
    {
        $this->client->update('communication_messages', ['id' => 'eq.' . $id], [
            'status'        => 'retry_scheduled',
            'error_code'    => $errorCode,
            'error_message' => $errorMessage,
            'next_retry_at' => $nextRetryAtIso,
        ]);
    }

    public function markFailed(string $id, string $errorCode, string $errorMessage): void
    {
        $this->client->update('communication_messages', ['id' => 'eq.' . $id], [
            'status'        => 'failed',
            'error_code'    => $errorCode,
            'error_message' => $errorMessage,
            'failed_at'     => gmdate('c'),
        ]);
    }

    /** Relance manuelle contrôlée : remet un message échoué en file. */
    public function requeue(string $id): void
    {
        $this->client->update(
            'communication_messages',
            ['id' => 'eq.' . $id, 'status' => 'eq.failed'],
            ['status' => 'pending', 'next_retry_at' => null, 'error_code' => null, 'error_message' => null]
        );
    }

    /** @return array<int,array<string,mixed>> historique d'une demande (back-office) */
    public function listForEnquiry(string $enquiryId): array
    {
        return $this->client->select('communication_messages', [
            'enquiry_id' => 'eq.' . $enquiryId,
            'select'     => 'id,channel,direction,message_type,status,recipient,subject,'
                . 'provider,provider_message_id,error_code,error_message,attempt_count,'
                . 'sent_at,delivered_at,read_at,failed_at,created_at',
            'order'      => 'created_at.desc',
        ]);
    }

    /** @return array<string,mixed>|null */
    public function find(string $id): ?array
    {
        $rows = $this->client->select('communication_messages', [
            'id'    => 'eq.' . $id,
            'limit' => '1',
        ]);
        return $rows[0] ?? null;
    }
}
