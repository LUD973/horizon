<?php
/**
 * Pied de page persistant. Bloc de confiance « Ils nous font confiance »
 * (libellé validé, arbitrage 4). Accès humain permanent.
 *
 * @package fcp-child
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="fcp-trust" aria-labelledby="fcp-trust-title" style="text-align:center;padding:var(--fcp-space-lg);">
    <h2 id="fcp-trust-title" style="font-size:var(--fcp-fs-h2);color:var(--fcp-navy);">
        <?php esc_html_e('Ils nous font confiance', 'fcp-child'); ?>
    </h2>
</section>

<footer class="fcp-footer">
    <p>French Class Prestige — <?php esc_html_e('Maison française de mobilité et de services premium.', 'fcp-child'); ?></p>
    <p><?php esc_html_e('Nos équipes restent joignables à chaque étape.', 'fcp-child'); ?></p>
</footer>
