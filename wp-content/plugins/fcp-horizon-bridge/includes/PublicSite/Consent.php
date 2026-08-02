<?php
declare(strict_types=1);

namespace FCP\Horizon\PublicSite;

use FCP\Horizon\Support\Config;

/**
 * Intégration du consentement (tarteaucitron.js) — présentation uniquement.
 *
 * N'imprime que le chargement du script officiel (CDN par défaut, jamais
 * reconstruit) et n'appelle que des points d'intégration publics et
 * documentés (`tarteaucitron.services`, `tarteaucitron.job`,
 * `tarteaucitron.init()`) — cette classe ne réimplémente aucun moteur de
 * chargement/bannière. Sans configuration (aucun lien de politique de
 * confidentialité renseigné), rien n'est imprimé ni chargé : le site reste
 * pleinement fonctionnel.
 *
 * L'enregistrement des services gated (analytics, marketing) et l'appel
 * `tarteaucitron.init()` sont faits côté JS (`assets/js/consent.js`), qui
 * seul construit les options à partir de la configuration serveur.
 */
final class Consent
{
    /** Empêche une double injection même si wp_head est déclenché plusieurs fois. */
    private static bool $printed = false;

    public function __construct(private Config $config)
    {
    }

    public function register(): void
    {
        add_action('wp_head', [$this, 'printSdkLoader'], 1);
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    /** Charge le script coeur tarteaucitron.js, tel quel, au plus haut de <head>. */
    public function printSdkLoader(): void
    {
        if (self::$printed) {
            return;
        }

        if (!$this->config->tarteaucitronConfigured()) {
            return; // Aucune configuration : rien n'est chargé, aucune bannière.
        }

        self::$printed = true;

        $scriptUrl = $this->config->tarteaucitronScriptUrl();
        echo "\n" . '<script src="' . esc_url($scriptUrl) . '"></script>' . "\n";
    }

    public function enqueue(): void
    {
        wp_register_script(
            'fcp-consent',
            FCP_HORIZON_URL . 'assets/js/consent.js',
            [],
            FCP_HORIZON_VERSION,
            false // chargé tôt : pilote l'état de consentement pour les futurs scripts gated
        );

        wp_localize_script('fcp-consent', 'fcpConsentConfig', [
            'configured' => $this->config->tarteaucitronConfigured(),
            'privacyUrl' => $this->config->tarteaucitronPrivacyUrl(),
        ]);

        wp_enqueue_script('fcp-consent');
    }
}
