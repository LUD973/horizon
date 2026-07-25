<?php
declare(strict_types=1);

namespace FCP\Horizon\Data\Providers\Brevo;

use FCP\Horizon\Domain\Communication\Channel;
use FCP\Horizon\Domain\Communication\EmailProvider;
use FCP\Horizon\Domain\Communication\EmailTemplateRegistry;
use FCP\Horizon\Domain\Communication\OutboundMessage;
use FCP\Horizon\Domain\Communication\SendOutcome;

/**
 * Fournisseur e-mail Brevo.
 *
 * Rend le template (logique de domaine) puis délègue le transport à BrevoClient.
 * Traduit la réponse Brevo vers un SendOutcome interne : le métier ne voit
 * jamais les codes/statuts propriétaires.
 */
final class BrevoEmailProvider implements EmailProvider
{
    public function __construct(
        private BrevoClient $client,
        private EmailTemplateRegistry $templates,
        private string $senderEmail,
        private string $senderName,
    ) {
    }

    public function channel(): string
    {
        return Channel::EMAIL;
    }

    public function name(): string
    {
        return 'brevo';
    }

    public function send(OutboundMessage $message): SendOutcome
    {
        try {
            $email = $this->templates->render($message->templateKey(), $message->locale(), $message->params());
            $messageId = $this->client->sendEmail(
                $this->senderEmail,
                $this->senderName,
                $message->recipient(),
                $email->subject(),
                $email->html(),
                $email->text(),
            );
            return SendOutcome::accepted($messageId !== '' ? $messageId : null);
        } catch (\RuntimeException $e) {
            return SendOutcome::failed('provider_error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return SendOutcome::failed('template_error', $e->getMessage());
        }
    }
}
