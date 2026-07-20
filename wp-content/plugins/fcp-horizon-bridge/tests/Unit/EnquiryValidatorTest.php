<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\EnquiryValidator;
use PHPUnit\Framework\TestCase;

final class EnquiryValidatorTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-07-20');
    }

    /** @return array<string,mixed> */
    private function validPayload(): array
    {
        return [
            'profile'            => 'individual',
            'first_name'         => 'Jean',
            'last_name'          => 'Dupont',
            'email'              => 'JEAN.DUPONT@Example.COM',
            'phone'              => '+33 6 12 34 56 78',
            'service_date'       => '2026-08-01',
            'service_time'       => '09:30',
            'origin'             => 'Paris CDG',
            'destination'        => 'Paris 8e',
            'passengers'         => '2',
            'consent_processing' => '1',
        ];
    }

    public function testValidPayloadPasses(): void
    {
        $result = (new EnquiryValidator())->validate($this->validPayload(), $this->now);
        self::assertTrue($result->isValid());

        $input = $result->input();
        self::assertSame('jean.dupont@example.com', $input->email());          // e-mail normalisé
        self::assertSame('+33612345678', $input->contactRow()['phone']);       // téléphone normalisé
        self::assertFalse($input->consentMarketing());                          // marketing séparé, non coché
    }

    public function testMissingProcessingConsentFails(): void
    {
        $payload = $this->validPayload();
        unset($payload['consent_processing']);
        $result = (new EnquiryValidator())->validate($payload, $this->now);
        self::assertFalse($result->isValid());
        self::assertArrayHasKey('consent_processing', $result->errors());
    }

    public function testPastDateFails(): void
    {
        $payload = $this->validPayload();
        $payload['service_date'] = '2026-07-19';
        $result = (new EnquiryValidator())->validate($payload, $this->now);
        self::assertArrayHasKey('service_date', $result->errors());
    }

    public function testInvalidEmailFails(): void
    {
        $payload = $this->validPayload();
        $payload['email'] = 'not-an-email';
        $result = (new EnquiryValidator())->validate($payload, $this->now);
        self::assertArrayHasKey('email', $result->errors());
    }

    public function testCompanyRequiresOrganizationName(): void
    {
        $payload = $this->validPayload();
        $payload['profile'] = 'company';
        $result = (new EnquiryValidator())->validate($payload, $this->now);
        self::assertArrayHasKey('organization_name', $result->errors());
    }

    public function testZeroPassengersFails(): void
    {
        $payload = $this->validPayload();
        $payload['passengers'] = '0';
        $result = (new EnquiryValidator())->validate($payload, $this->now);
        self::assertArrayHasKey('passengers', $result->errors());
    }

    public function testMarketingConsentIsCapturedSeparately(): void
    {
        $payload = $this->validPayload();
        $payload['consent_marketing'] = '1';
        $result = (new EnquiryValidator())->validate($payload, $this->now);
        self::assertTrue($result->isValid());
        self::assertTrue($result->input()->consentMarketing());
    }
}
