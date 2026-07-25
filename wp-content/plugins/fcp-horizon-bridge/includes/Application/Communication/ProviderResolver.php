<?php
declare(strict_types=1);

namespace FCP\Horizon\Application\Communication;

use FCP\Horizon\Domain\Communication\CommunicationProvider;

/** Résout le fournisseur d'un canal (permet d'injecter un double en test). */
interface ProviderResolver
{
    public function providerFor(string $channel): ?CommunicationProvider;
}
