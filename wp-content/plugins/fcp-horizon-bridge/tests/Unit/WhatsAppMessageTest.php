<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\EnquiryInput;
use FCP\Horizon\Domain\WhatsAppMessage;
use PHPUnit\Framework\TestCase;

final class WhatsAppMessageTest extends TestCase
{
    /** @param array<string,mixed> $overrides */
    private function input(array $overrides = []): EnquiryInput
    {
        return new EnquiryInput(array_merge([
            'profile' => 'individual', 'organization_name' => '',
            'first_name' => 'Jean', 'last_name' => 'Dupont',
            'email' => 'jean@example.com', 'phone' => '+33612345678',
            'preferred_channel' => 'whatsapp', 'locale' => 'fr',
            'category' => 'mobility', 'subcategory' => '',
            'service_date' => '2026-08-01', 'service_time' => '09:30',
            'origin' => 'Paris CDG', 'destination' => 'Paris 8e',
            'passengers' => 2, 'luggage' => null,
            'flight_number' => 'AF123', 'train_number' => '',
            'range' => '', 'notes' => '', 'consent_marketing' => false,
        ], $overrides));
    }

    public function testTextContainsReferenceAndTrajet(): void
    {
        $msg = new WhatsAppMessage('FCP-2026-000123', $this->input());
        $text = $msg->text();
        self::assertStringContainsString('FCP-2026-000123', $text);
        self::assertStringContainsString('Paris CDG → Paris 8e', $text);
        self::assertStringContainsString('AF123', $text);
    }

    public function testUrlIsWaMeWithDigitsOnly(): void
    {
        $msg = new WhatsAppMessage('FCP-2026-000123', $this->input());
        $url = $msg->url('+33 6 99 99 99 99');
        self::assertStringStartsWith('https://wa.me/33699999999?text=', $url);
        self::assertStringContainsString(rawurlencode('FCP-2026-000123'), $url);
    }

    public function testEmptyNumberReturnsEmptyUrl(): void
    {
        $msg = new WhatsAppMessage('FCP-2026-000123', $this->input());
        self::assertSame('', $msg->url(''));
    }

    public function testLengthGuardCapsText(): void
    {
        $long = str_repeat('A', 4000);
        $msg = new WhatsAppMessage('FCP-2026-000123', $this->input(['origin' => $long, 'destination' => $long]));
        self::assertLessThanOrEqual(1200, mb_strlen($msg->text()));
    }
}
