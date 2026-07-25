<?php
declare(strict_types=1);

namespace FCP\Horizon\Application\Communication;

use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Domain\Communication\Channel;
use FCP\Horizon\Domain\Communication\EmailMessage;
use FCP\Horizon\Domain\Communication\OutboundMessage;

/**
 * Traite la file d'attente (outbox) de façon résiliente.
 *
 * - « claim » atomique pour éviter tout double envoi.
 * - Réessais avec délai progressif et nombre de tentatives borné.
 * - Un échec de fournisseur n'affecte JAMAIS la demande Horizon (déjà créée) :
 *   il ne touche que le message.
 */
final class OutboxProcessor
{
    private const MAX_ATTEMPTS = 5;
    private const BASE_BACKOFF = 60;   // secondes
    private const MAX_BACKOFF = 3600;  // 1 h

    public function __construct(
        private MessageRepository $messages,
        private ProviderResolver $providers,
    ) {
    }

    /** @return int nombre de messages traités */
    public function process(int $limit = 20, ?\DateTimeImmutable $now = null): int
    {
        $now ??= new \DateTimeImmutable('now');
        $processed = 0;

        foreach ($this->messages->listDue($limit) as $row) {
            $channel = (string) ($row['channel'] ?? '');
            $provider = $this->providers->providerFor($channel);
            if ($provider === null) {
                continue; // canal non configuré : le message reste « pending »
            }

            $id = (string) $row['id'];
            $attempt = (int) ($row['attempt_count'] ?? 0) + 1;

            if (!$this->messages->claim($id, $attempt)) {
                continue; // déjà pris par un autre worker
            }

            $message = $this->buildMessage($row);
            if ($message === null) {
                $this->messages->markFailed($id, 'unsupported_channel', 'Canal non pris en charge.');
                $processed++;
                continue;
            }

            $outcome = $provider->send($message);
            if ($outcome->isAccepted()) {
                $this->messages->markSent($id, $provider->name(), $outcome->providerMessageId());
            } elseif ($attempt >= self::MAX_ATTEMPTS) {
                $this->messages->markFailed($id, (string) $outcome->errorCode(), (string) $outcome->errorMessage());
            } else {
                $this->messages->markRetry(
                    $id,
                    (string) $outcome->errorCode(),
                    (string) $outcome->errorMessage(),
                    $this->nextRetryIso($now, $attempt),
                );
            }
            $processed++;
        }

        return $processed;
    }

    /** @param array<string,mixed> $row */
    private function buildMessage(array $row): ?OutboundMessage
    {
        if ((string) ($row['channel'] ?? '') !== Channel::EMAIL) {
            return null;
        }
        $payload = is_array($row['payload'] ?? null) ? $row['payload'] : [];
        $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];

        return new EmailMessage(
            (string) ($row['recipient'] ?? ''),
            (string) ($row['template_id'] ?? ''),
            $params,
            isset($row['enquiry_id']) ? (string) $row['enquiry_id'] : null,
            (string) ($row['message_type'] ?? 'transactional'),
            (string) ($row['locale'] ?? 'fr'),
        );
    }

    private function nextRetryIso(\DateTimeImmutable $now, int $attempt): string
    {
        $delay = min(self::MAX_BACKOFF, self::BASE_BACKOFF * (2 ** ($attempt - 1)));
        return $now->modify('+' . $delay . ' seconds')->format('c');
    }
}
