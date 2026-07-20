<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\PublicReference;
use PHPUnit\Framework\TestCase;

final class PublicReferenceTest extends TestCase
{
    public function testFormatsWithSixDigitZeroPadding(): void
    {
        $ref = PublicReference::fromParts(2026, 123);
        self::assertSame('FCP-2026-000123', $ref->toString());
    }

    public function testParsesValidReference(): void
    {
        $ref = PublicReference::parse('FCP-2026-000123');
        self::assertSame(2026, $ref->year());
        self::assertSame(123, $ref->sequence());
    }

    public function testRoundTrip(): void
    {
        $value = 'FCP-2026-004096';
        self::assertSame($value, (string) PublicReference::parse($value));
    }

    /** @dataProvider invalidReferences */
    public function testIsValidRejectsMalformed(string $value): void
    {
        self::assertFalse(PublicReference::isValid($value));
    }

    /** @return array<string,array{0:string}> */
    public static function invalidReferences(): array
    {
        return [
            'trois chiffres'   => ['FCP-2026-123'],
            'mauvais prefixe'  => ['XXX-2026-000123'],
            'annee courte'     => ['FCP-26-000123'],
            'vide'             => [''],
            'sept chiffres'    => ['FCP-2026-1234567'],
        ];
    }

    public function testRejectsSequenceOutOfRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PublicReference::fromParts(2026, 1000000);
    }
}
