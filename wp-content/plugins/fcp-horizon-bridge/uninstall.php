<?php
/**
 * Désinstallation du plugin.
 *
 * Le plugin ne crée pas de tables WordPress : les données métier vivent dans
 * Supabase (Horizon Core) et ne sont jamais supprimées depuis WordPress.
 * On se contente ici de retirer d'éventuelles options transitoires.
 *
 * @package FCP\Horizon
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Options transitoires éventuelles (rate limiting, caches courts).
delete_option('fcp_horizon_ratelimit');

// Retire les tâches planifiées.
foreach (['fcp_horizon_purge_ips', 'fcp_horizon_process_outbox'] as $fcp_hook) {
    $timestamp = wp_next_scheduled($fcp_hook);
    if ($timestamp) {
        wp_unschedule_event($timestamp, $fcp_hook);
    }
}
