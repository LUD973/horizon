<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Support\Config;
use PHPUnit\Framework\TestCase;

/**
 * Configuration GoatCounter : centralisée, sans valeur en dur, neutralisée si
 * invalide (point d'entrée ou URL de script malformés).
 */
final class GoatcounterConfigTest extends TestCase
{
    private const ENV_KEYS = ['GOATCOUNTER_ENDPOINT', 'GOATCOUNTER_SCRIPT_URL'];

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

        self::assertFalse($config->goatcounterConfigured());
        self::assertSame('', $config->goatcounterEndpoint());
        // URL par défaut documentée, pas une valeur fictive/secrète.
        self::assertSame('https://gc.zgo.at/count.js', $config->goatcounterScriptUrl());
    }

    public function testValidEndpointIsAccepted(): void
    {
        putenv('GOATCOUNTER_ENDPOINT=https://fcp-horizon.goatcounter.com/count');

        $config = Config::fromEnvironment();

        self::assertTrue($config->goatcounterConfigured());
        self::assertSame('https://fcp-horizon.goatcounter.com/count', $config->goatcounterEndpoint());
    }

    public function testNonHttpsEndpointIsRejected(): void
    {
        putenv('GOATCOUNTER_ENDPOINT=http://fcp-horizon.goatcounter.com/count');

        $config = Config::fromEnvironment();

        self::assertFalse($config->goatcounterConfigured());
        self::assertSame('', $config->goatcounterEndpoint());
    }

    public function testCustomHttpsScriptUrlIsAccepted(): void
    {
        putenv('GOATCOUNTER_SCRIPT_URL=https://analytics.example.com/count.js');

        $config = Config::fromEnvironment();

        self::assertSame('https://analytics.example.com/count.js', $config->goatcounterScriptUrl());
    }

    public function testInvalidScriptUrlFallsBackToDefault(): void
    {
        putenv('GOATCOUNTER_SCRIPT_URL=not-a-valid-url');

        $config = Config::fromEnvironment();

        self::assertSame('https://gc.zgo.at/count.js', $config->goatcounterScriptUrl());
    }

    public function testNonHttpsScriptUrlIsRejected(): void
    {
        putenv('GOATCOUNTER_SCRIPT_URL=http://gc.zgo.at/count.js');

        $config = Config::fromEnvironment();

        self::assertSame('https://gc.zgo.at/count.js', $config->goatcounterScriptUrl());
    }
}
