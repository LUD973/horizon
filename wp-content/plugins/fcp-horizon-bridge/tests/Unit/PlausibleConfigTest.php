<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Support\Config;
use PHPUnit\Framework\TestCase;

/**
 * Configuration Plausible : centralisée, sans valeur en dur, neutralisée si
 * invalide (domaine ou URL de script malformés).
 */
final class PlausibleConfigTest extends TestCase
{
    private const ENV_KEYS = ['PLAUSIBLE_DOMAIN', 'PLAUSIBLE_SCRIPT_URL'];

    protected function tearDown(): void
    {
        foreach (self::ENV_KEYS as $key) {
            putenv($key);
        }
    }

    public function testWithoutConfigurationNothingIsEnabled(): void
    {
        foreach (self::ENV_KEYS as $key) {
            putenv($key);
        }

        $config = Config::fromEnvironment();

        self::assertFalse($config->plausibleConfigured());
        self::assertSame('', $config->plausibleDomain());
        // URL par défaut documentée, pas une valeur fictive/secrète.
        self::assertSame('https://plausible.io/js/script.js', $config->plausibleScriptUrl());
    }

    public function testValidDomainIsAccepted(): void
    {
        putenv('PLAUSIBLE_DOMAIN=frenchclassprestige.com');

        $config = Config::fromEnvironment();

        self::assertTrue($config->plausibleConfigured());
        self::assertSame('frenchclassprestige.com', $config->plausibleDomain());
    }

    public function testInvalidDomainIsNeutralized(): void
    {
        putenv('PLAUSIBLE_DOMAIN=not a domain!');

        $config = Config::fromEnvironment();

        self::assertFalse($config->plausibleConfigured());
        self::assertSame('', $config->plausibleDomain());
    }

    public function testCustomHttpsScriptUrlIsAccepted(): void
    {
        putenv('PLAUSIBLE_SCRIPT_URL=https://analytics.example.com/js/script.js');

        $config = Config::fromEnvironment();

        self::assertSame('https://analytics.example.com/js/script.js', $config->plausibleScriptUrl());
    }

    public function testInvalidScriptUrlFallsBackToDefault(): void
    {
        putenv('PLAUSIBLE_SCRIPT_URL=not-a-valid-url');

        $config = Config::fromEnvironment();

        self::assertSame('https://plausible.io/js/script.js', $config->plausibleScriptUrl());
    }

    public function testNonHttpsScriptUrlIsRejected(): void
    {
        // http (non chiffré) refusé : neutralisé au profit du défaut.
        putenv('PLAUSIBLE_SCRIPT_URL=http://plausible.io/js/script.js');

        $config = Config::fromEnvironment();

        self::assertSame('https://plausible.io/js/script.js', $config->plausibleScriptUrl());
    }
}
