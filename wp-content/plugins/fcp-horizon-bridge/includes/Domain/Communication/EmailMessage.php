<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Message e-mail sortant (objet-valeur, pur). Porte une clé de template interne
 * et ses variables ; le rendu (sujet + corps) est produit par le TemplateRegistry.
 */
final class EmailMessage implements OutboundMessage
{
    /**
     * @param array<string,scalar> $params
     */
    public function __construct(
        private string $recipient,
        private string $templateKey,
        private array $params,
        private ?string $enquiryId = null,
        private string $messageType = MessageType::TRANSACTIONAL,
        private string $locale = 'fr',
    ) {
    }

    public function channel(): string
    {
        return Channel::EMAIL;
    }

    public function recipient(): string
    {
        return $this->recipient;
    }

    public function messageType(): string
    {
        return $this->messageType;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function templateKey(): string
    {
        return $this->templateKey;
    }

    /** @return array<string,scalar> */
    public function params(): array
    {
        return $this->params;
    }

    public function enquiryId(): ?string
    {
        return $this->enquiryId;
    }
}
