<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Contrat des fournisseurs du canal e-mail.
 *
 * Réalisé aujourd'hui par BrevoEmailProvider (couche Data). Marqueur explicite
 * de la hiérarchie de providers ; aucune méthode spécifique fournisseur.
 */
interface EmailProvider extends CommunicationProvider
{
}
