<?php
declare(strict_types=1);

namespace FCP\Horizon;

use FCP\Horizon\Api\RestController;
use FCP\Horizon\Support\Config;

/**
 * Point d'entrée du plugin : câble les hooks WordPress.
 *
 * Respecte la séparation imposée :
 *   - Support/ : configuration, drapeaux, journalisation.
 *   - Data/    : accès aux données (Supabase).
 *   - Domain/  : logique métier.
 *   - Api/     : exposition REST v1 (présentation d'API).
 *   - PublicSite/ / Admin/ : présentation.
 */
final class Plugin
{
    private Config $config;

    public function boot(): void
    {
        $this->config = Config::fromEnvironment();

        // Traductions.
        add_action('init', static function (): void {
            load_plugin_textdomain('fcp-horizon', false, dirname(plugin_basename(FCP_HORIZON_FILE)) . '/languages');
        });

        // API v1 (Semaine 1 : /health ; les routes d'écriture arrivent en Semaine 2).
        $rest = new RestController($this->config);
        add_action('rest_api_init', [$rest, 'registerRoutes']);
    }

    public function config(): Config
    {
        return $this->config;
    }
}
