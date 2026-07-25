<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\Consent\ConsentCategory;
use PHPUnit\Framework\TestCase;

final class ConsentCategoryTest extends TestCase
{
    public function testKnownCategoriesAreValid(): void
    {
        self::assertTrue(ConsentCategory::isValid('functional'));
        self::assertTrue(ConsentCategory::isValid('analytics'));
        self::assertTrue(ConsentCategory::isValid('marketing'));
        self::assertFalse(ConsentCategory::isValid('bidon'));
    }

    public function testOnlyAnalyticsAndMarketingAreDidomiManaged(): void
    {
        self::assertFalse(ConsentCategory::isDidomiManaged(ConsentCategory::FUNCTIONAL));
        self::assertTrue(ConsentCategory::isDidomiManaged(ConsentCategory::ANALYTICS));
        self::assertTrue(ConsentCategory::isDidomiManaged(ConsentCategory::MARKETING));
    }

    public function testFunctionalIsNeverSentToDidomiApi(): void
    {
        // Garde-fou explicite : « functional » ne doit jamais figurer dans la
        // liste des catégories gérées par Didomi (règle 5 du GO Didomi).
        self::assertNotContains(ConsentCategory::FUNCTIONAL, ConsentCategory::DIDOMI_MANAGED);
    }
}
