<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/** Canaux de communication (indépendant de tout fournisseur). */
final class Channel
{
    public const EMAIL = 'email';
    public const WHATSAPP = 'whatsapp';
    public const SMS = 'sms';
    public const PUSH = 'push';

    public static function isValid(string $channel): bool
    {
        return in_array($channel, [self::EMAIL, self::WHATSAPP, self::SMS, self::PUSH], true);
    }
}
