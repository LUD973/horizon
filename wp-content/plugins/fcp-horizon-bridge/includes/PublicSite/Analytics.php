<?php
declare(strict_types=1);

namespace FCP\Horizon\PublicSite;

use FCP\Horizon\Support\Config;

/**
 * Intégration GoatCounter — présentation uniquement.
 *
 * N'enqueue JAMAIS le script GoatCounter lui-même côté PHP (ce serait le
 * télécharger avant tout consentement). Le script réel est injecté
 * dynamiquement par assets/js/analytics.js, uniquement après confirmation du
 * consentement analytics via window.fcpConsent (façade du lot consentement).
 */
final class Analytics
{
    public function __construct(private Config $config)
    {
    }

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        wp_register_script(
            'fcp-analytics',
            FCP_HORIZON_URL . 'assets/js/analytics.js',
            ['fcp-consent'], // ordre de chargement garanti : fcpConsent existe avant fcpAnalytics
            FCP_HORIZON_VERSION,
            false
        );

        wp_localize_script('fcp-analytics', 'fcpAnalyticsConfig', [
            'configured' => $this->config->goatcounterConfigured(),
            'endpoint'   => $this->config->goatcounterEndpoint(),
            'scriptUrl'  => $this->config->goatcounterScriptUrl(),
        ]);

        wp_enqueue_script('fcp-analytics');
    }
}
