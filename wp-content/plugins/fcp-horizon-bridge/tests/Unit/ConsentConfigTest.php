<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Support\Config;
use PHPUnit\Framework\TestCase;

/**
 * Vérifie que la configuration Didomi est centralisée, sans valeur en dur,
 * et sûre par défaut (aucun secret, deny-by-default en l'absence de config).
 */
final class ConsentConfigTest extends TestCase
{
    private const ENV_KEYS = [
        'DIDOMI_SDK_EMBED', 'DIDOMI_PURPOSE_ANALYTICS',
        'DIDOMI_PURPOSE_MARKETING', 'DIDOMI_VENDOR_PLAUSIBLE',
    ];

    protected function tearDown(): void
    {
        foreach (self::ENV_KEYS as $key) {
            putenv($key); // nettoie l'environnement entre les tests
        }
    }

    public function testWithoutConfigurationNothingIsEnabled(): void
    {
        foreach (self::ENV_KEYS as $key) {
            putenv($key); // garantit l'absence de valeur
        }

        $config = Config::fromEnvironment();

        self::assertSame('', $config->didomiSdkEmbed());
        self::assertFalse($config->didomiConfigured());
        self::assertSame('', $config->didomiVendorPlausible());
        // Aucune valeur fictive : les identifiants de purpose ont un défaut
        // documenté, pas un secret ni un identifiant Didomi supposé réel.
        self::assertSame('analytics', $config->didomiPurposeAnalytics());
        self::assertSame('advertising', $config->didomiPurposeMarketing());
    }

    public function testConfiguredValuesAreReadFromEnvironment(): void
    {
        putenv('DIDOMI_SDK_EMBED=<script>window.didomiConfig={};</script>');
        putenv('DIDOMI_PURPOSE_ANALYTICS=custom_analytics_purpose');
        putenv('DIDOMI_VENDOR_PLAUSIBLE=c:plausible-XXXX');

        $config = Config::fromEnvironment();

        self::assertTrue($config->didomiConfigured());
        self::assertStringContainsString('didomiConfig', $config->didomiSdkEmbed());
        self::assertSame('custom_analytics_purpose', $config->didomiPurposeAnalytics());
        self::assertSame('c:plausible-XXXX', $config->didomiVendorPlausible());
    }
}
