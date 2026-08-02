<?php
/**
 * Plugin Name:       FCP Horizon Bridge
 * Description:        Pont entre le site public French Class Prestige (WordPress/Divi) et Horizon Core (Supabase). Logique métier, API v1, formulaire de demande, back-office. Aucune logique métier dans Divi.
 * Version:           0.8.0
 * Requires PHP:      8.1
 * Requires at least: 6.4
 * Text Domain:       fcp-horizon
 * Domain Path:       /languages
 *
 * @package FCP\Horizon
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Accès direct interdit.
}

define('FCP_HORIZON_VERSION', '0.8.0');
define('FCP_HORIZON_FILE', __FILE__);
define('FCP_HORIZON_DIR', plugin_dir_path(__FILE__));
define('FCP_HORIZON_URL', plugin_dir_url(__FILE__));

// --- Autoloader ---
// Utilise l'autoloader Composer s'il existe (mono-dépôt), sinon un autoloader
// PSR-4 minimal interne pour rester fonctionnel sans `composer install`.
$fcp_composer_autoload = dirname(FCP_HORIZON_DIR, 3) . '/vendor/autoload.php';
if (is_readable($fcp_composer_autoload)) {
    require_once $fcp_composer_autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'FCP\\Horizon\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = FCP_HORIZON_DIR . 'includes/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require_once $path;
        }
    });
}

// --- Démarrage ---
add_action('plugins_loaded', static function (): void {
    (new \FCP\Horizon\Plugin())->boot();
});
