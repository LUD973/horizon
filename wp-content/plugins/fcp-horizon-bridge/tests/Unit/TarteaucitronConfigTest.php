<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Support\Config;
use PHPUnit\Framework\TestCase;

/**
 * Vérifie que la configuration tarteaucitron.js est centralisée, sans valeur
 * en dur, et sûre par défaut (aucun secret, deny-by-default en l'absence de
 * config).
 */
final class TarteaucitronConfigTest extends TestCase
{
    private const ENV_KEYS = ['TARTEAUCITRON_PRIVACY_URL', 'TARTEAUCITRON_SCRIPT_URL'];

    protected function tearDown(): void
    {
        foreach (self::ENV_KEYS as $key) {
            putenv($key); // nettoie l'environnement entre les tests
        }
    }

    public function testWithoutConfigurationNothingIsEnabled(): void
    {
        foreach (self::ENV_KEYS as $key) {
            putenv($key);
        }

        $config = Config::fromEnvironment();

        self::assertFalse($config->tarteaucitronConfigured());
        self::assertSame('', $config->tarteaucitronPrivacyUrl());
        // URL par défaut documentée (CDN officiel), pas une valeur fictive/secrète.
        self::assertSame(
            'https://cdn.jsdelivr.net/npm/tarteaucitronjs@1/tarteaucitron.min.js',
            $config->tarteaucitronScriptUrl()
        );
    }

    public function testValidPrivacyUrlIsAccepted(): void
    {
        putenv('TARTEAUCITRON_PRIVACY_URL=https://frenchclassprestige.com/confidentialite');

        $config = Config::fromEnvironment();

        self::assertTrue($config->tarteaucitronConfigured());
        self::assertSame('https://frenchclassprestige.com/confidentialite', $config->tarteaucitronPrivacyUrl());
    }

    public function testNonHttpsPrivacyUrlIsRejected(): void
    {
        putenv('TARTEAUCITRON_PRIVACY_URL=http://frenchclassprestige.com/confidentialite');

        $config = Config::fromEnvironment();

        self::assertFalse($config->tarteaucitronConfigured());
        self::assertSame('', $config->tarteaucitronPrivacyUrl());
    }

    public function testCustomHttpsScriptUrlIsAccepted(): void
    {
        putenv('TARTEAUCITRON_SCRIPT_URL=https://self-hosted.example.com/tarteaucitron.min.js');

        $config = Config::fromEnvironment();

        self::assertSame('https://self-hosted.example.com/tarteaucitron.min.js', $config->tarteaucitronScriptUrl());
    }

    public function testInvalidScriptUrlFallsBackToDefault(): void
    {
        putenv('TARTEAUCITRON_SCRIPT_URL=not-a-valid-url');

        $config = Config::fromEnvironment();

        self::assertSame(
            'https://cdn.jsdelivr.net/npm/tarteaucitronjs@1/tarteaucitron.min.js',
            $config->tarteaucitronScriptUrl()
        );
    }
}
