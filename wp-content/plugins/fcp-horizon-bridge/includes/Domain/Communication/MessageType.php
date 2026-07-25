<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Finalité d'un message. Distinction stricte : le transactionnel (lié à une
 * demande/prestation initiée par le client) ne requiert PAS de consentement
 * marketing ; le marketing exige un consentement distinct, explicite et prouvable.
 */
final class MessageType
{
    public const TRANSACTIONAL = 'transactional';
    public const MARKETING = 'marketing';

    public static function isValid(string $type): bool
    {
        return in_array($type, [self::TRANSACTIONAL, self::MARKETING], true);
    }
}
