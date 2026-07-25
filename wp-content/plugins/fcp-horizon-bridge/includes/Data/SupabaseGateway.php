<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Frontière d'accès aux données Supabase.
 *
 * Les repositories dépendent de cette interface (et non de la classe concrète) :
 * la logique d'orchestration devient testable hors ligne, avec un double en
 * mémoire, sans Supabase réel.
 */
interface SupabaseGateway
{
    public function healthCheck(): bool;

    /**
     * @param array<string,string> $query
     * @return array<int,array<string,mixed>>
     */
    public function select(string $table, array $query = []): array;

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    public function insert(string $table, array $row): array;

    /**
     * @param array<string,string> $filters
     * @param array<string,mixed>  $patch
     */
    public function update(string $table, array $filters, array $patch): void;

    /**
     * UPDATE renvoyant les lignes affectées (pour un « claim » atomique).
     *
     * @param array<string,string> $filters
     * @param array<string,mixed>  $patch
     * @return array<int,array<string,mixed>>
     */
    public function updateReturning(string $table, array $filters, array $patch): array;

    /** @param array<string,mixed> $args */
    public function rpc(string $function, array $args = []): void;
}
