<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/**
 * Rendu des e-mails transactionnels (logique pure, testable).
 *
 * i18n : le rendu dépend de la locale ; le français est livré, les autres
 * langues retombent sur le français en attendant. Aucune duplication de la
 * logique métier par langue — seuls les textes varient.
 *
 * ADN : ton sobre et premium ; ne JAMAIS promettre une réservation avant
 * validation effective.
 */
final class EmailTemplateRegistry
{
    public const CLIENT_ACK = 'enquiry_received_client';
    public const INTERNAL_ALERT = 'enquiry_internal_alert';

    public static function isKnown(string $templateKey): bool
    {
        return in_array($templateKey, [self::CLIENT_ACK, self::INTERNAL_ALERT], true);
    }

    /**
     * @param array<string,scalar> $params
     */
    public function render(string $templateKey, string $locale, array $params): RenderedEmail
    {
        return match ($templateKey) {
            self::CLIENT_ACK      => $this->clientAck($params),
            self::INTERNAL_ALERT  => $this->internalAlert($params),
            default               => throw new \InvalidArgumentException('Template inconnu : ' . $templateKey),
        };
    }

    /** @param array<string,scalar> $p */
    private function clientAck(array $p): RenderedEmail
    {
        $ref     = $this->e($p['reference'] ?? '');
        $first   = $this->e($p['first_name'] ?? '');
        $type    = $this->e($p['service_label'] ?? 'votre demande');
        $date    = $this->e($p['service_date'] ?? '');
        $delay   = $this->e($p['response_delay'] ?? 'sous 24 heures ouvrées');
        $trajet  = $this->e($p['trajet'] ?? '');

        $subject = 'Votre demande ' . $ref . ' — French Class Prestige';

        $lines = [
            $first !== '' ? 'Bonjour ' . $first . ',' : 'Bonjour,',
            '',
            'Nous accusons réception de votre demande auprès de French Class Prestige, Maison française de mobilité et de services premium.',
            '',
            'Référence : ' . $ref,
            'Objet : ' . $type,
        ];
        if ($trajet !== '') {
            $lines[] = 'Trajet : ' . $trajet;
        }
        if ($date !== '') {
            $lines[] = 'Date souhaitée : ' . $date;
        }
        $lines[] = '';
        $lines[] = 'Nos équipes reviennent vers vous ' . $delay . '.';
        $lines[] = '';
        $lines[] = 'Cette demande n’est pas encore une réservation confirmée : elle le deviendra après validation par nos équipes.';
        $lines[] = '';
        $lines[] = 'Nous restons à votre entière disposition.';
        $lines[] = 'French Class Prestige';

        return $this->fromLines($subject, $lines);
    }

    /** @param array<string,scalar> $p */
    private function internalAlert(array $p): RenderedEmail
    {
        $ref     = $this->e($p['reference'] ?? '');
        $type    = $this->e($p['service_label'] ?? '');
        $summary = $this->e($p['summary'] ?? '');
        $channel = $this->e($p['preferred_channel'] ?? '');
        $fiche   = $this->e($p['fiche_url'] ?? '');

        $subject = '[Horizon] Nouvelle demande ' . $ref;

        $lines = [
            'Nouvelle demande reçue.',
            '',
            'Référence : ' . $ref,
            'Type : ' . ($type !== '' ? $type : '—'),
            'Résumé : ' . ($summary !== '' ? $summary : '—'),
            'Canal préféré du client : ' . ($channel !== '' ? $channel : '—'),
        ];
        if ($fiche !== '') {
            $lines[] = '';
            $lines[] = 'Fiche Horizon : ' . $fiche;
        }

        return $this->fromLines($subject, $lines);
    }

    /** @param string[] $lines */
    private function fromLines(string $subject, array $lines): RenderedEmail
    {
        $text = implode("\n", $lines);
        $html = '<div style="font-family:Georgia,serif;color:#17202A;line-height:1.6">'
            . implode('', array_map(
                static fn (string $l): string => $l === '' ? '<br>' : '<p style="margin:0 0 .6em">' . $l . '</p>',
                $lines
            ))
            . '</div>';
        return new RenderedEmail($subject, $html, $text);
    }

    private function e(mixed $value): string
    {
        return htmlspecialchars((string) (is_scalar($value) ? $value : ''), ENT_QUOTES, 'UTF-8');
    }
}
