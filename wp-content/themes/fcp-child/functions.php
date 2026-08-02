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
 * Chargement des styles : parent Divi puis jetons de design puis styles
 * globaux sitewide.
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
        'fcp-global',
        get_stylesheet_directory_uri() . '/assets/css/global.css',
        ['fcp-tokens'],
        $theme->get('Version')
    );
}, 20);
