<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Integration;

use FCP\Horizon\Application\Communication\OutboxProcessor;
use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Domain\Communication\Channel;
use PHPUnit\Framework\TestCase;

/**
 * Outbox résiliente : envoi, réessais bornés, non-configuration, sans double envoi.
 */
final class OutboxProcessorTest extends TestCase
{
    private FakeSupabase $db;
    private MessageRepository $repo;
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->db = new FakeSupabase();
        $this->repo = new MessageRepository($this->db);
        $this->now = new \DateTimeImmutable('2026-07-25T10:00:00Z');
    }

    private function enqueueEmail(): string
    {
        return $this->repo->enqueue([
            'enquiry_id'   => 'enq-1',
            'channel'      => Channel::EMAIL,
            'direction'    => 'outbound',
            'message_type' => 'transactional',
            'recipient'    => 'client@example.com',
            'template_id'  => 'enquiry_received_client',
            'locale'       => 'fr',
            'payload'      => ['params' => ['reference' => 'FCP-2026-000001']],
            'attempt_count' => 0,
        ]);
    }

    public function testSuccessfulSendMarksSent(): void
    {
        $id = $this->enqueueEmail();
        $provider = new FakeProvider(Channel::EMAIL, true, 'brevo-123');

        $processed = (new OutboxProcessor($this->repo, $provider))->process(10, $this->now);

        self::assertSame(1, $processed);
        self::assertSame(1, $provider->sendCount);
        $row = $this->db->store['communication_messages'][0];
        self::assertSame('sent', $row['status']);
        self::assertSame('brevo-123', $row['provider_message_id']);
        self::assertSame('fake', $row['provider']);
    }

    public function testFailureSchedulesRetryThenFailsAfterMax(): void
    {
        $id = $this->enqueueEmail();
        $provider = new FakeProvider(Channel::EMAIL, false);
        $processor = new OutboxProcessor($this->repo, $provider);

        $processor->process(10, $this->now);
        self::assertSame('retry_scheduled', $this->db->store['communication_messages'][0]['status']);
        self::assertNotNull($this->db->store['communication_messages'][0]['next_retry_at']);

        // Simule l'atteinte du plafond de tentatives.
        $this->db->store['communication_messages'][0]['attempt_count'] = 4;
        $this->db->store['communication_messages'][0]['status'] = 'retry_scheduled';
        $this->db->store['communication_messages'][0]['next_retry_at'] = null;

        $processor->process(10, $this->now);
        self::assertSame('failed', $this->db->store['communication_messages'][0]['status']);
        self::assertSame('provider_error', $this->db->store['communication_messages'][0]['error_code']);
    }

    public function testNotConfiguredLeavesPending(): void
    {
        $this->enqueueEmail();
        // Resolver sans fournisseur pour l'e-mail.
        $noProvider = new FakeProvider('sms', true);

        $processed = (new OutboxProcessor($this->repo, $noProvider))->process(10, $this->now);

        self::assertSame(0, $processed);
        self::assertSame('pending', $this->db->store['communication_messages'][0]['status']);
    }

    public function testRequeueResetsFailedToPending(): void
    {
        $id = $this->enqueueEmail();
        $this->db->store['communication_messages'][0]['status'] = 'failed';

        $this->repo->requeue($id);

        self::assertSame('pending', $this->db->store['communication_messages'][0]['status']);
    }
}
