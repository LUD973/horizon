<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Integration;

use FCP\Horizon\Application\Communication\ProviderResolver;
use FCP\Horizon\Domain\Communication\Channel;
use FCP\Horizon\Domain\Communication\CommunicationProvider;
use FCP\Horizon\Domain\Communication\OutboundMessage;
use FCP\Horizon\Domain\Communication\SendOutcome;

/**
 * Double de fournisseur pour les tests d'outbox : renvoie un résultat configurable
 * et compte les envois. Sert aussi de resolver.
 */
final class FakeProvider implements CommunicationProvider, ProviderResolver
{
    public int $sendCount = 0;
    /** @var array<int,string> destinataires reçus */
    public array $recipients = [];

    public function __construct(
        private string $channel = Channel::EMAIL,
        private bool $succeeds = true,
        private ?string $messageId = 'prov-msg-1',
    ) {
    }

    public function channel(): string
    {
        return $this->channel;
    }

    public function name(): string
    {
        return 'fake';
    }

    public function send(OutboundMessage $message): SendOutcome
    {
        $this->sendCount++;
        $this->recipients[] = $message->recipient();
        return $this->succeeds
            ? SendOutcome::accepted($this->messageId)
            : SendOutcome::failed('provider_error', 'échec simulé');
    }

    public function providerFor(string $channel): ?CommunicationProvider
    {
        return $channel === $this->channel ? $this : null;
    }
}
