<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Statuts d'une demande et transitions autorisées (logique pure, testable).
 *
 * Reflète la contrainte CHECK de la table enquiries et le workflow :
 * new → qualified → quote_draft → quote_sent → accepted/rejected/expired
 *     → mission → closed.
 *
 * Le back-office ne peut appliquer qu'une transition AUTORISÉE (écriture
 * contrôlée). Aucune génération/écriture côté navigateur.
 */
final class EnquiryStatus
{
    /** @var array<string,string> libellés FR */
    private const LABELS = [
        'new'         => 'Nouvelle',
        'qualified'   => 'Qualifiée',
        'quote_draft' => 'Devis (brouillon)',
        'quote_sent'  => 'Devis envoyé',
        'accepted'    => 'Acceptée',
        'rejected'    => 'Refusée',
        'expired'     => 'Expirée',
        'mission'     => 'Mission',
        'closed'      => 'Clôturée',
    ];

    /** @var array<string,string[]> transitions autorisées */
    private const TRANSITIONS = [
        'new'         => ['qualified', 'rejected', 'closed'],
        'qualified'   => ['quote_draft', 'rejected', 'closed'],
        'quote_draft' => ['quote_sent', 'rejected', 'closed'],
        'quote_sent'  => ['accepted', 'rejected', 'expired'],
        'accepted'    => ['mission', 'closed'],
        'mission'     => ['closed'],
        'rejected'    => [],
        'expired'     => ['qualified'],
        'closed'      => [],
    ];

    public static function isValid(string $status): bool
    {
        return isset(self::LABELS[$status]);
    }

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? $status;
    }

    /** @return array<string,string> */
    public static function all(): array
    {
        return self::LABELS;
    }

    public static function canTransition(string $from, string $to): bool
    {
        if (!self::isValid($from) || !self::isValid($to) || $from === $to) {
            return false;
        }
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /** @return string[] statuts vers lesquels on peut passer depuis $from */
    public static function nextStatuses(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }
}
