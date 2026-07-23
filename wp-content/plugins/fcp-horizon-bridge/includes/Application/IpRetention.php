<?php
declare(strict_types=1);

namespace FCP\Horizon\Application;

use FCP\Horizon\Data\SupabaseClient;
use FCP\Horizon\Data\SupabaseException;
use FCP\Horizon\Support\Config;
use FCP\Horizon\Support\Logger;

/**
 * Purge de rétention des adresses IP (12 mois glissants).
 *
 * Anonymise les IP expirées dans audit_logs via la fonction SQL
 * public.purge_audit_ip(). La journalisation ne consigne QUE l'événement
 * (jamais les adresses concernées).
 */
final class IpRetention
{
    private const RETENTION_MONTHS = 12;

    public function __construct(private Config $config)
    {
    }

    public function purge(): void
    {
        if (!$this->config->supabaseConfigured()) {
            return;
        }
        try {
            (new SupabaseClient($this->config))->rpc('purge_audit_ip', [
                'retention_months' => self::RETENTION_MONTHS,
            ]);
            Logger::info('Purge IP audit_logs exécutée', ['retention_months' => self::RETENTION_MONTHS]);
        } catch (SupabaseException $e) {
            Logger::error('Purge IP audit_logs en échec', ['code' => $e->getCode()]);
        }
    }
}
