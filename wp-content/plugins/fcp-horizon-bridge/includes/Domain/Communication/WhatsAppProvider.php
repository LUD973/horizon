<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Contrat des fournisseurs du canal WhatsApp — PRÉVU pour un lot dédié.
 *
 * Aucune implémentation n'est fournie à ce stade (l'automatisation WhatsApp par
 * API est reportée). Ce contrat permettra d'ajouter, sans modifier le domaine
 * ni la logique métier :
 *   - BrevoWhatsAppProvider ;
 *   - MetaCloudWhatsAppProvider (API Cloud Meta directe, avec coexistence).
 *
 * Il est volontairement identique au contrat de base (aucune fuite de
 * spécificité fournisseur) : l'implémentation future se branchera via le
 * ProviderRegistry par son channel().
 */
interface WhatsAppProvider extends CommunicationProvider
{
}
