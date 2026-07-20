<?php
/**
 * Bootstrap PHPUnit.
 *
 * Les tests unitaires de la couche Domain sont du PHP pur (sans WordPress) :
 * on enregistre un autoloader PSR-4 minimal pointant sur includes/.
 * Les tests d'intégration (formulaire -> Supabase) nécessiteront un
 * environnement WordPress dédié, ajouté ultérieurement.
 *
 * @package FCP\Horizon\Tests
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'FCP\\Horizon\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__) . '/includes/' . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($path)) {
        require_once $path;
    }
});
