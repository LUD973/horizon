<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Integration;

use FCP\Horizon\Data\SupabaseException;
use FCP\Horizon\Data\SupabaseGateway;

/**
 * Double en mémoire de Supabase/PostgREST pour les tests d'intégration hors ligne.
 *
 * Simule : filtres « eq. », unicité e-mail (contrainte UNIQUE → 409) et la
 * génération de public_reference par le trigger Postgres à l'insertion.
 */
final class FakeSupabase implements SupabaseGateway
{
    /** @var array<string,array<int,array<string,mixed>>> */
    public array $store = [
        'contacts'               => [],
        'organizations'          => [],
        'enquiries'              => [],
        'enquiry_details'        => [],
        'communications'         => [],
        'communication_messages' => [],
        'enquiry_notes'          => [],
        'audit_logs'             => [],
    ];

    /** @var string[] e-mails pour lesquels le prochain SELECT « rate » (simule une course). */
    public array $missOnceEmails = [];

    /** @var string[] tables dont l'INSERT échoue (simule une indisponibilité). */
    public array $failInsertTables = [];

    /** @var array<int,array{fn:string,args:array<string,mixed>}> */
    public array $rpcCalls = [];

    private int $idSeq = 0;
    private int $refSeq = 0;

    public function healthCheck(): bool
    {
        return true;
    }

    public function select(string $table, array $query = []): array
    {
        $rows = $this->store[$table] ?? [];

        // Simulation de course : on rate une fois le rapprochement par e-mail.
        if ($table === 'contacts' && isset($query['email'])) {
            $email = $this->eqValue($query['email']);
            $idx = array_search($email, $this->missOnceEmails, true);
            if ($idx !== false) {
                unset($this->missOnceEmails[$idx]);
                return [];
            }
        }

        foreach ($query as $field => $expr) {
            if (in_array($field, ['select', 'limit', 'order'], true) || !is_string($expr)) {
                continue;
            }
            if (str_starts_with($expr, 'eq.')) {
                $value = $this->eqValue($expr);
                $rows = array_values(array_filter($rows, static fn ($r) => (string) ($r[$field] ?? '') === $value));
            }
        }

        return $rows;
    }

    public function insert(string $table, array $row): array
    {
        if (in_array($table, $this->failInsertTables, true)) {
            throw new SupabaseException('insert failed (simulated) for ' . $table, 503);
        }
        if (!isset($row['id'])) {
            $row['id'] = 'id-' . (++$this->idSeq);
        }

        if ($table === 'contacts') {
            $email = strtolower((string) ($row['email'] ?? ''));
            foreach ($this->store['contacts'] as $existing) {
                if (strtolower((string) ($existing['email'] ?? '')) === $email) {
                    throw new SupabaseException('duplicate key value violates unique constraint', 409);
                }
            }
        }

        if ($table === 'enquiries' && empty($row['public_reference'])) {
            $row['public_reference'] = 'FCP-2026-' . str_pad((string) (++$this->refSeq), 6, '0', STR_PAD_LEFT);
            $row['status'] = $row['status'] ?? 'new';
        }

        $this->store[$table][] = $row;
        return $row;
    }

    public function update(string $table, array $filters, array $patch): void
    {
        $this->applyUpdate($table, $filters, $patch);
    }

    public function updateReturning(string $table, array $filters, array $patch): array
    {
        return $this->applyUpdate($table, $filters, $patch);
    }

    /**
     * @param array<string,string> $filters
     * @param array<string,mixed>  $patch
     * @return array<int,array<string,mixed>>
     */
    private function applyUpdate(string $table, array $filters, array $patch): array
    {
        $affected = [];
        foreach ($this->store[$table] ?? [] as $i => $row) {
            $match = true;
            foreach ($filters as $field => $expr) {
                if (!is_string($expr) || !str_starts_with($expr, 'eq.')) {
                    continue;
                }
                if ((string) ($row[$field] ?? '') !== $this->eqValue($expr)) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $this->store[$table][$i] = array_merge($row, $patch);
                $affected[] = $this->store[$table][$i];
            }
        }
        return $affected;
    }

    public function rpc(string $function, array $args = []): void
    {
        $this->rpcCalls[] = ['fn' => $function, 'args' => $args];
    }

    private function eqValue(string $expr): string
    {
        return str_starts_with($expr, 'eq.') ? substr($expr, 3) : $expr;
    }
}
