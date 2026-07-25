<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Integration;

use FCP\Horizon\Application\Communication\NotificationService;
use FCP\Horizon\Application\EnquiryService;
use FCP\Horizon\Data\AuditLogRepository;
use FCP\Horizon\Data\CommunicationRepository;
use FCP\Horizon\Data\ContactRepository;
use FCP\Horizon\Data\EnquiryRepository;
use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Domain\EnquiryValidator;
use PHPUnit\Framework\TestCase;

/**
 * Résilience : les accusés sont mis en file, et un échec de communication ne
 * fait JAMAIS perdre la demande Horizon.
 */
final class CommunicationResilienceTest extends TestCase
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
            new NotificationService(new MessageRepository($this->db), 'ops@example.com'),
        );
    }

    private function submit(): array
    {
        $result = (new EnquiryValidator())->validate([
            'profile' => 'individual', 'first_name' => 'Jean', 'last_name' => 'Dupont',
            'email' => 'jean@example.com', 'phone' => '+33612345678',
            'service_date' => '2026-08-01', 'origin' => 'Paris CDG',
            'destination' => 'Paris 8e', 'passengers' => '2', 'consent_processing' => '1',
        ], new \DateTimeImmutable('2026-07-20'));

        return $this->service->submit($result->input(), ['fiche_base' => 'https://staging.example/ref=']);
    }

    public function testAcknowledgementsAreQueued(): void
    {
        $this->submit();

        $messages = $this->db->store['communication_messages'];
        self::assertCount(2, $messages, 'accusé client + alerte équipe');

        $recipients = array_column($messages, 'recipient');
        self::assertContains('jean@example.com', $recipients);
        self::assertContains('ops@example.com', $recipients);

        foreach ($messages as $m) {
            self::assertSame('email', $m['channel']);
            self::assertSame('pending', $m['status']);
            self::assertSame('transactional', $m['message_type']);
        }
    }

    public function testEmailFailureNeverLosesEnquiry(): void
    {
        // La file de messages est indisponible.
        $this->db->failInsertTables = ['communication_messages'];

        $outcome = $this->submit();

        // La demande est bien créée et retournée malgré l'échec de mise en file.
        self::assertNotSame('', $outcome['reference']);
        self::assertCount(1, $this->db->store['enquiries']);
        self::assertCount(1, $this->db->store['enquiry_details']);
        self::assertCount(0, $this->db->store['communication_messages']);
    }
}
