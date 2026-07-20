<?php
/**
 * En-tête sticky + navigation persistante.
 * Libellés conformes à l'ADN (aucun mot interdit).
 *
 * @package fcp-child
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<header class="fcp-header">
    <div class="fcp-header__inner">
        <a class="fcp-brand" href="<?php echo esc_url(home_url('/')); ?>">French Class Prestige</a>

        <nav class="fcp-nav" aria-label="<?php esc_attr_e('Navigation principale', 'fcp-child'); ?>">
            <button class="fcp-nav__toggle" type="button" aria-expanded="false"
                    aria-controls="fcp-nav-list">
                <span class="screen-reader-text"><?php esc_html_e('Ouvrir le menu', 'fcp-child'); ?></span>
                &#9776;
            </button>

            <ul class="fcp-nav__list" id="fcp-nav-list">
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Accueil', 'fcp-child'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/mobilite/')); ?>"><?php esc_html_e('Mobilité', 'fcp-child'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/evenements/')); ?>"><?php esc_html_e('Événements', 'fcp-child'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/experiences/')); ?>"><?php esc_html_e('Expériences', 'fcp-child'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/membership/')); ?>"><?php esc_html_e('Membership', 'fcp-child'); ?></a></li>
                <li><a class="fcp-cta fcp-cta--gold" href="<?php echo esc_url(home_url('/demande/')); ?>"><?php esc_html_e('Demander un devis', 'fcp-child'); ?></a></li>
            </ul>
        </nav>
    </div>
</header>
