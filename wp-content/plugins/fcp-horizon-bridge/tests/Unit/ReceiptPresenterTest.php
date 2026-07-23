<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\ReceiptPresenter;
use PHPUnit\Framework\TestCase;

final class ReceiptPresenterTest extends TestCase
{
    /** @return array<string,mixed> */
    private function row(): array
    {
        return [
            'public_reference' => 'FCP-2026-000123',
            'status'           => 'new',
            'created_at'       => '2026-07-23T09:31:45.123Z',
            'enquiry_details'  => [[
                'service_date' => '2026-08-01',
                'origin'       => 'Paris CDG',
                'destination'  => 'Paris 8e',
                'passengers'   => 2,
            ]],
        ];
    }

    public function testPresentsSafeSubset(): void
    {
        $out = ReceiptPresenter::present($this->row());

        self::assertSame('FCP-2026-000123', $out['reference']);
        self::assertSame('new', $out['status']);
        self::assertSame('2026-07-23', $out['received_at']); // date seule, sans heure
        self::assertSame('Paris CDG', $out['service']['from']);
        self::assertSame('Paris 8e', $out['service']['to']);
        self::assertSame(2, $out['service']['passengers']);
    }

    public function testNeverExposesContactData(): void
    {
        $row = $this->row();
        $row['contact_id'] = 'secret-uuid';
        $row['enquiry_details'][0]['notes'] = 'note privée';

        $out = ReceiptPresenter::present($row);
        $flat = json_encode($out);

        self::assertStringNotContainsString('secret-uuid', (string) $flat);
        self::assertStringNotContainsString('note privée', (string) $flat);
        self::assertArrayNotHasKey('contact_id', $out);
    }

    public function testHandlesMissingDetails(): void
    {
        $out = ReceiptPresenter::present([
            'public_reference' => 'FCP-2026-000001',
            'status'           => 'new',
            'created_at'       => '2026-07-23T09:31:45Z',
        ]);
        self::assertSame('', $out['service']['from']);
        self::assertNull($out['service']['passengers']);
    }
}
