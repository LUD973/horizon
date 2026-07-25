<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\Domain\Communication\EmailTemplateRegistry;
use PHPUnit\Framework\TestCase;

final class EmailTemplateRegistryTest extends TestCase
{
    private EmailTemplateRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new EmailTemplateRegistry();
    }

    public function testClientAckContainsReferenceAndNonBookingNotice(): void
    {
        $email = $this->registry->render(EmailTemplateRegistry::CLIENT_ACK, 'fr', [
            'reference'    => 'FCP-2026-000123',
            'first_name'   => 'Jean',
            'service_label' => 'Mobilité',
            'service_date' => '2026-08-01',
            'trajet'       => 'Paris CDG → Paris 8e',
        ]);

        self::assertStringContainsString('FCP-2026-000123', $email->subject());
        self::assertStringContainsString('Jean', $email->text());
        self::assertStringContainsString('Paris CDG', $email->text());
        // ADN : jamais de promesse de réservation confirmée.
        self::assertStringContainsString('pas encore une réservation confirmée', $email->text());
        self::assertStringContainsString('French Class Prestige', $email->text());
    }

    public function testInternalAlertContainsFicheUrl(): void
    {
        $email = $this->registry->render(EmailTemplateRegistry::INTERNAL_ALERT, 'fr', [
            'reference' => 'FCP-2026-000123',
            'summary'   => 'Paris CDG → Paris 8e · 2 pax',
            'fiche_url' => 'https://staging.example/wp-admin/admin.php?page=fcp-horizon-enquiry&ref=FCP-2026-000123',
        ]);

        self::assertStringContainsString('[Horizon] Nouvelle demande FCP-2026-000123', $email->subject());
        self::assertStringContainsString('fcp-horizon-enquiry', $email->text());
        self::assertStringContainsString('Paris CDG', $email->text());
    }

    public function testClientAckHasNoForbiddenWords(): void
    {
        $email = $this->registry->render(EmailTemplateRegistry::CLIENT_ACK, 'fr', ['reference' => 'FCP-2026-000001']);
        $haystack = mb_strtolower($email->text() . ' ' . $email->subject());
        foreach (['partenaire', 'plateforme', 'marketplace', 'intermédiaire', 'comparateur'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $haystack);
        }
    }

    public function testUnknownTemplateThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->render('bidon', 'fr', []);
    }

    public function testIsKnown(): void
    {
        self::assertTrue(EmailTemplateRegistry::isKnown(EmailTemplateRegistry::CLIENT_ACK));
        self::assertFalse(EmailTemplateRegistry::isKnown('bidon'));
    }
}
