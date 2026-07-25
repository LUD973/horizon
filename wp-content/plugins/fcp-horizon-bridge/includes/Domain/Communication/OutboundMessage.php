<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Message sortant, indépendant du fournisseur et du canal.
 *
 * Le domaine et les services applicatifs manipulent ce contrat ; ils ne
 * connaissent ni Brevo, ni ses structures JSON, ni ses identifiants de template.
 */
interface OutboundMessage
{
    public function channel(): string;

    public function recipient(): string;

    public function messageType(): string;

    public function locale(): string;

    /** Clé de template INTERNE Horizon (ex. « enquiry_received_client »). */
    public function templateKey(): string;

    /** @return array<string,scalar> variables de rendu (nom, référence, trajet…) */
    public function params(): array;

    public function enquiryId(): ?string;
}
