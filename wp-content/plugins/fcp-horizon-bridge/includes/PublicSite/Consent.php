<?php
declare(strict_types=1);

namespace FCP\Horizon\PublicSite;

use FCP\Horizon\Support\Config;

/**
 * Intégration du consentement (Didomi) — présentation uniquement.
 *
 * N'imprime QUE le snippet officiel Didomi tel que configuré (aucune
 * reconstruction, aucun identifiant fictif). Sans configuration, rien n'est
 * imprimé ni chargé : le site reste pleinement fonctionnel.
 *
 * L'activation conditionnelle des scripts soumis à consentement (ex. futur
 * Plausible) relève du mécanisme NATIF Didomi (cf. docs/CONSENT_DIDOMI.md) —
 * cette classe ne réimplémente aucun moteur de chargement.
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

    /** Imprime le snippet officiel Didomi, tel quel, au plus haut de <head>. */
    public function printSdkLoader(): void
    {
        if (self::$printed) {
            return;
        }

        $embed = $this->config->didomiSdkEmbed();
        if ($embed === '') {
            return; // Aucune configuration : rien n'est chargé, aucune bannière.
        }

        self::$printed = true;

        // Configuration de confiance (jamais alimentée par une entrée
        // utilisateur), donc imprimée sans échappement — c'est du balisage
        // <script> fourni par Didomi, pas une donnée à assainir. Ne doit
        // jamais être committé avec une valeur réelle (voir .env.example).
        echo "\n" . $embed . "\n";
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
            'configured'       => $this->config->didomiConfigured(),
            'purposeAnalytics' => $this->config->didomiPurposeAnalytics(),
            'purposeMarketing' => $this->config->didomiPurposeMarketing(),
            'vendorPlausible'  => $this->config->didomiVendorPlausible(),
        ]);

        wp_enqueue_script('fcp-consent');
    }
}
