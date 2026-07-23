<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\EnquiryStatus;
use PHPUnit\Framework\TestCase;

final class EnquiryStatusTest extends TestCase
{
    public function testKnownStatusesAreValid(): void
    {
        self::assertTrue(EnquiryStatus::isValid('new'));
        self::assertTrue(EnquiryStatus::isValid('mission'));
        self::assertFalse(EnquiryStatus::isValid('bidon'));
    }

    public function testLabelsAreFrench(): void
    {
        self::assertSame('Nouvelle', EnquiryStatus::label('new'));
        self::assertSame('Devis envoyé', EnquiryStatus::label('quote_sent'));
    }

    public function testAllowedTransition(): void
    {
        self::assertTrue(EnquiryStatus::canTransition('new', 'qualified'));
        self::assertTrue(EnquiryStatus::canTransition('quote_sent', 'accepted'));
    }

    public function testForbiddenTransition(): void
    {
        self::assertFalse(EnquiryStatus::canTransition('new', 'mission')); // saut interdit
        self::assertFalse(EnquiryStatus::canTransition('new', 'new'));     // identité
        self::assertFalse(EnquiryStatus::canTransition('closed', 'new'));  // terminal
        self::assertFalse(EnquiryStatus::canTransition('new', 'bidon'));   // inconnu
    }

    public function testNextStatuses(): void
    {
        self::assertSame(['qualified', 'rejected', 'closed'], EnquiryStatus::nextStatuses('new'));
        self::assertSame([], EnquiryStatus::nextStatuses('closed'));
    }
}
