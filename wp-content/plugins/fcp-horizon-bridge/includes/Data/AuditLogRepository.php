<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Journal minimal des événements (arbitrage 6).
 * Aucun secret ni donnée inutilement sensible n'est journalisé.
 */
final class AuditLogRepository
{
    public function __construct(private SupabaseClient $client)
    {
    }

    /**
     * @param array<string,mixed>|null $newValues valeurs métier non sensibles
     */
    public function record(
        string $action,
        string $tableName,
        string $recordId,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $requestId = null
    ): void {
        $this->client->insert('audit_logs', [
            'action'     => $action,
            'table_name' => $tableName,
            'record_id'  => $recordId,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'request_id' => $requestId,
        ]);
    }
}
