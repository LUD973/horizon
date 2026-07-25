<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Résultat d'un envoi, exprimé dans le vocabulaire INTERNE Horizon.
 *
 * Chaque provider (Brevo…) traduit la réponse propriétaire du fournisseur vers
 * cet objet. Le métier ne voit jamais les codes/statuts spécifiques du fournisseur.
 */
final class SendOutcome
{
    private function __construct(
        private bool $accepted,
        private ?string $providerMessageId,
        private ?string $errorCode,
        private ?string $errorMessage,
    ) {
    }

    public static function accepted(?string $providerMessageId): self
    {
        return new self(true, $providerMessageId, null, null);
    }

    public static function failed(string $errorCode, string $errorMessage): self
    {
        return new self(false, null, $errorCode, $errorMessage);
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function providerMessageId(): ?string
    {
        return $this->providerMessageId;
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
