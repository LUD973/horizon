<?php
declare(strict_types=1);

namespace FCP\Horizon;

use FCP\Horizon\Admin\BackOffice;
use FCP\Horizon\Api\RestController;
use FCP\Horizon\Application\Communication\OutboxProcessor;
use FCP\Horizon\Application\Communication\ProviderRegistry;
use FCP\Horizon\Application\IpRetention;
use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Data\SupabaseClient;
use FCP\Horizon\PublicSite\FormRenderer;
use FCP\Horizon\Support\Config;

/**
 * Point d'entrée du plugin : câble les hooks WordPress.
 *
 * Respecte la séparation imposée :
 *   - Support/     : configuration, drapeaux, journalisation.
 *   - Data/        : accès aux données (Supabase).
 *   - Domain/      : logique métier pure.
 *   - Application/ : orchestration des cas d'usage.
 *   - Api/         : exposition REST v1.
 *   - PublicSite/ / Admin/ : présentation.
 */
final class Plugin
{
    private Config $config;

    public function boot(): void
    {
        $this->config = Config::fromEnvironment();

        add_action('init', static function (): void {
            load_plugin_textdomain('fcp-horizon', false, dirname(plugin_basename(FCP_HORIZON_FILE)) . '/languages');
        });

        // API v1.
        $rest = new RestController($this->config);
        add_action('rest_api_init', [$rest, 'registerRoutes']);

        // Présentation publique : shortcode du formulaire.
        add_action('init', [$this, 'registerAssets']);
        add_shortcode('fcp_enquiry_form', [$this, 'renderEnquiryForm']);

        // Rétention IP : purge quotidienne (12 mois glissants).
        add_action('fcp_horizon_purge_ips', [$this, 'runIpPurge']);
        add_action('init', [$this, 'scheduleIpPurge']);

        // Back-office Horizon (accès protégé par capacité).
        add_action('admin_init', [$this, 'ensureCapability']);
        (new BackOffice($this->config))->register();

        // Outbox de communication : traitement asynchrone (résilience).
        add_filter('cron_schedules', [$this, 'registerCronSchedule']);
        add_action('fcp_horizon_process_outbox', [$this, 'runOutbox']);      // récurrent (filet)
        add_action('fcp_horizon_process_outbox_now', [$this, 'runOutbox']);  // immédiat (après une demande)
        add_action('init', [$this, 'scheduleOutbox']);
    }

    /** @param array<string,array{interval:int,display:string}> $schedules */
    public function registerCronSchedule(array $schedules): array
    {
        if (!isset($schedules['fcp_5min'])) {
            $schedules['fcp_5min'] = ['interval' => 300, 'display' => 'Toutes les 5 minutes (FCP)'];
        }
        return $schedules;
    }

    public function scheduleOutbox(): void
    {
        if (!wp_next_scheduled('fcp_horizon_process_outbox')) {
            wp_schedule_event(time() + 60, 'fcp_5min', 'fcp_horizon_process_outbox');
        }
    }

    public function runOutbox(): void
    {
        $client = new SupabaseClient($this->config);
        (new OutboxProcessor(
            new MessageRepository($client),
            new ProviderRegistry($this->config),
        ))->process();
    }

    /** Accorde la capacité d'accès Horizon à l'administrateur (idempotent). */
    public function ensureCapability(): void
    {
        $role = get_role('administrator');
        if ($role && !$role->has_cap(BackOffice::CAP)) {
            $role->add_cap(BackOffice::CAP);
        }
    }

    public function scheduleIpPurge(): void
    {
        if (!wp_next_scheduled('fcp_horizon_purge_ips')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'fcp_horizon_purge_ips');
        }
    }

    public function runIpPurge(): void
    {
        (new IpRetention($this->config))->purge();
    }

    public function registerAssets(): void
    {
        wp_register_style(
            'fcp-form',
            FCP_HORIZON_URL . 'assets/css/form.css',
            [],
            FCP_HORIZON_VERSION
        );

        wp_register_script(
            'fcp-form',
            FCP_HORIZON_URL . 'assets/js/form.js',
            [],
            FCP_HORIZON_VERSION,
            true
        );

        wp_localize_script('fcp-form', 'fcpEnquiry', [
            'enquiriesUrl' => esc_url_raw(rest_url('fcp/v1/enquiries')),
            'openedBase'   => esc_url_raw(rest_url('fcp/v1/enquiries/')),
            'tokenUrl'     => esc_url_raw(rest_url('fcp/v1/form-token')),
        ]);
    }

    public function renderEnquiryForm(): string
    {
        wp_enqueue_style('fcp-form');
        wp_enqueue_script('fcp-form');
        return (new FormRenderer())->render();
    }

    public function config(): Config
    {
        return $this->config;
    }
}
