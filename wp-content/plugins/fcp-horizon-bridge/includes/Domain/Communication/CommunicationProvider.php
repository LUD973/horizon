<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Contrat de base d'un fournisseur de communication.
 *
 * Un provider dessert UN canal (channel()) et sait envoyer un OutboundMessage
 * générique. Ajouter un fournisseur = implémenter ce contrat, sans toucher au
 * domaine ni à la logique métier.
 */
interface CommunicationProvider
{
    public function channel(): string;

    /** Étiquette technique du fournisseur (ex. « brevo ») — pour la traçabilité, jamais pour brancher la logique métier. */
    public function name(): string;

    public function send(OutboundMessage $message): SendOutcome;
}
