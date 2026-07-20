<?php
/**
 * Hero : message court, compréhension de l'offre en moins de 10 secondes.
 * Positionnement : Maison française de mobilité et de services premium.
 *
 * @package fcp-child
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="fcp-hero">
    <h1 class="fcp-hero__title"><?php esc_html_e('La Maison française de la mobilité et des services premium', 'fcp-child'); ?></h1>
    <p class="fcp-hero__lead"><?php esc_html_e('Nous orchestrons vos déplacements, vos événements et vos expériences avec une exigence constante.', 'fcp-child'); ?></p>
    <a class="fcp-cta" href="<?php echo esc_url(home_url('/demande/')); ?>"><?php esc_html_e('Formuler une demande', 'fcp-child'); ?></a>
</section>
