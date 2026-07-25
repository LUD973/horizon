<?php
declare(strict_types=1);

namespace FCP\Horizon\Application\Communication;

use FCP\Horizon\Data\Providers\Brevo\BrevoClient;
use FCP\Horizon\Data\Providers\Brevo\BrevoEmailProvider;
use FCP\Horizon\Domain\Communication\Channel;
use FCP\Horizon\Domain\Communication\CommunicationProvider;
use FCP\Horizon\Domain\Communication\EmailTemplateRegistry;
use FCP\Horizon\Support\Config;

/**
 * Résout le fournisseur à utiliser pour un canal, à partir de la configuration.
 *
 * Ajouter un canal/fournisseur = l'enregistrer ici (ou via un futur registre
 * dynamique). Le reste de l'application ne change pas.
 */
final class ProviderRegistry implements ProviderResolver
{
    /** @var array<string,CommunicationProvider|null> */
    private array $cache = [];

    public function __construct(private Config $config)
    {
    }

    public function providerFor(string $channel): ?CommunicationProvider
    {
        if (array_key_exists($channel, $this->cache)) {
            return $this->cache[$channel];
        }
        return $this->cache[$channel] = $this->build($channel);
    }

    private function build(string $channel): ?CommunicationProvider
    {
        if ($channel === Channel::EMAIL) {
            $client = new BrevoClient($this->config);
            if (!$client->isConfigured() || $this->config->get('FCP_MAIL_FROM') === '') {
                return null; // e-mail non configuré : l'outbox garde le message en attente
            }
            return new BrevoEmailProvider(
                $client,
                new EmailTemplateRegistry(),
                $this->config->get('FCP_MAIL_FROM'),
                $this->config->get('FCP_MAIL_FROM_NAME', 'French Class Prestige'),
            );
        }

        // whatsapp / sms / push : aucun fournisseur activé (lot dédié).
        return null;
    }
}
