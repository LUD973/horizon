<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Integration;

use FCP\Horizon\Application\EnquiryService;
use FCP\Horizon\Data\AuditLogRepository;
use FCP\Horizon\Data\CommunicationRepository;
use FCP\Horizon\Data\ContactRepository;
use FCP\Horizon\Data\EnquiryRepository;
use FCP\Horizon\Domain\EnquiryValidator;
use FCP\Horizon\Domain\PublicReference;
use PHPUnit\Framework\TestCase;

/**
 * Tests d'intégration hors ligne : orchestration complète (service +
 * repositories) contre un double Supabase en mémoire.
 */
final class EnquiryFlowTest extends TestCase
{
    private FakeSupabase $db;
    private EnquiryService $service;

    protected function setUp(): void
    {
        $this->db = new FakeSupabase();
        $this->service = new EnquiryService(
            new ContactRepository($this->db),
            new EnquiryRepository($this->db),
            new CommunicationRepository($this->db),
            new AuditLogRepository($this->db),
            '+33612345678',
        );
    }

    /** @param array<string,string> $overrides */
    private function submit(array $overrides = []): array
    {
        $raw = array_merge([
            'profile' => 'individual', 'first_name' => 'Jean', 'last_name' => 'Dupont',
            'email' => 'jean@example.com', 'phone' => '+33612345678',
            'service_date' => '2026-08-01', 'origin' => 'Paris CDG',
            'destination' => 'Paris 8e', 'passengers' => '2', 'consent_processing' => '1',
        ], $overrides);

        $result = (new EnquiryValidator())->validate($raw, new \DateTimeImmutable('2026-07-20'));
        self::assertTrue($result->isValid(), 'payload de test invalide');

        return $this->service->submit($result->input(), ['ip' => '203.0.113.1', 'request_id' => 'req-1']);
    }

    public function testFullSubmitCreatesEveryRow(): void
    {
        $out = $this->submit();

        self::assertTrue(PublicReference::isValid($out['reference']));
        self::assertStringStartsWith('https://wa.me/33612345678', $out['whatsapp_url']);

        self::assertCount(1, $this->db->store['contacts']);
        self::assertCount(1, $this->db->store['enquiries']);
        self::assertCount(1, $this->db->store['enquiry_details']);

        self::assertSame('new', $this->db->store['enquiries'][0]['status']);

        self::assertCount(1, $this->db->store['communications']);
        self::assertSame('whatsapp', $this->db->store['communications'][0]['channel']);
        self::assertSame('prepared', $this->db->store['communications'][0]['status']);

        self::assertCount(1, $this->db->store['audit_logs']);
        self::assertSame('enquiry.created', $this->db->store['audit_logs'][0]['action']);
        // Minimisation : pas de PII dans le journal.
        self::assertArrayNotHasKey('email', $this->db->store['audit_logs'][0]['new_values']);
    }

    public function testSameEmailGivesOneContactManyEnquiries(): void
    {
        $a = $this->submit();
        $b = $this->submit();

        self::assertCount(1, $this->db->store['contacts'], 'un seul contact attendu');
        self::assertCount(2, $this->db->store['enquiries'], 'deux demandes attendues');
        self::assertNotSame($a['reference'], $b['reference']);
    }

    public function testUniqueViolationReReadsExistingContact(): void
    {
        // 1re demande : crée le contact.
        $this->submit();
        // On force une COURSE : le prochain SELECT rate, l'INSERT lèvera 409,
        // le repository doit relire le contact existant sans erreur.
        $this->db->missOnceEmails[] = 'jean@example.com';

        $out = $this->submit();

        self::assertTrue(PublicReference::isValid($out['reference']));
        self::assertCount(1, $this->db->store['contacts'], 'toujours un seul contact malgré la course');
        self::assertCount(2, $this->db->store['enquiries']);
    }

    public function testWhatsappOpenedFlipsPreparedToOpened(): void
    {
        $out = $this->submit();
        $communications = new CommunicationRepository($this->db);

        $communications->markWhatsappOpenedForEnquiry($out['enquiry_id']);

        self::assertSame('opened', $this->db->store['communications'][0]['status']);
        // Jamais « sent » sans preuve technique.
        self::assertNotSame('sent', $this->db->store['communications'][0]['status']);
    }
}
