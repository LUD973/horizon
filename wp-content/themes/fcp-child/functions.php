<?php
/**
 * Thème enfant French Class Prestige (Divi).
 * Présentation uniquement : aucune logique métier ici.
 *
 * @package fcp-child
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Chargement des styles : parent Divi puis jetons de design puis Home.
 */
add_action('wp_enqueue_scripts', static function (): void {
    $theme = wp_get_theme();

    wp_enqueue_style('divi-parent', get_template_directory_uri() . '/style.css', [], null);

    wp_enqueue_style(
        'fcp-tokens',
        get_stylesheet_directory_uri() . '/assets/css/tokens.css',
        ['divi-parent'],
        $theme->get('Version')
    );

    wp_enqueue_style(
        'fcp-home',
        get_stylesheet_directory_uri() . '/assets/css/home.css',
        ['fcp-tokens'],
        $theme->get('Version')
    );

    wp_enqueue_script(
        'fcp-nav',
        get_stylesheet_directory_uri() . '/assets/js/nav.js',
        [],
        $theme->get('Version'),
        true
    );
}, 20);

/**
 * Menu de navigation persistante (présentation publique).
 * NB : conforme à l'ADN — aucun libellé interdit. L'entrée « recrutement de
 * prestataires » de l'IA n'est pas exposée publiquement (traitée en interne).
 * Une entrée discrète « Collaborations » pourra être ajoutée plus tard.
 */
add_action('after_setup_theme', static function (): void {
    register_nav_menus([
        'fcp_primary' => __('Navigation principale FCP', 'fcp-child'),
    ]);
});
