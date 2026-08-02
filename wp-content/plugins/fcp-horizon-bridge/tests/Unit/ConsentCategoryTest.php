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

    public function testOnlyAnalyticsAndMarketingAreCmpManaged(): void
    {
        self::assertFalse(ConsentCategory::isCmpManaged(ConsentCategory::FUNCTIONAL));
        self::assertTrue(ConsentCategory::isCmpManaged(ConsentCategory::ANALYTICS));
        self::assertTrue(ConsentCategory::isCmpManaged(ConsentCategory::MARKETING));
    }

    public function testFunctionalIsNeverSentToCmp(): void
    {
        // Garde-fou explicite : « functional » ne doit jamais figurer dans la
        // liste des catégories gérées par le CMP (tarteaucitron.js).
        self::assertNotContains(ConsentCategory::FUNCTIONAL, ConsentCategory::CMP_MANAGED);
    }
}
