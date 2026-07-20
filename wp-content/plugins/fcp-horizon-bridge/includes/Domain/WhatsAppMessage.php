<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Construit le récapitulatif formel et le lien wa.me.
 *
 * IMPORTANT (règle 2) : ouvrir ce lien ne prouve PAS l'envoi d'un message.
 * La communication correspondante reste au statut « opened », jamais « sent ».
 *
 * Garde-fou de longueur : le texte est plafonné pour éviter une URL wa.me
 * excessive sur mobile ; le récapitulatif complet reste dans Horizon.
 */
final class WhatsAppMessage
{
    private const MAX_TEXT_LENGTH = 1200;

    public function __construct(
        private string $reference,
        private EnquiryInput $input,
    ) {
    }

    /** Texte formel, ton Maison, sans donnée superflue. */
    public function text(): string
    {
        $d = $this->input->toArray();

        $lines = [];
        $lines[] = 'French Class Prestige — Demande ' . $this->reference;
        $lines[] = '';
        $lines[] = 'Bonjour,';
        $lines[] = 'Je souhaite confirmer ma demande :';

        if ($d['origin'] !== '' && $d['destination'] !== '') {
            $lines[] = '• Trajet : ' . $d['origin'] . ' → ' . $d['destination'];
        }
        if ($d['service_date'] !== '') {
            $when = $d['service_date'] . ($d['service_time'] !== '' ? ' à ' . $d['service_time'] : '');
            $lines[] = '• Date : ' . $when;
        }
        if ($d['passengers'] > 0) {
            $lines[] = '• Passagers : ' . $d['passengers'];
        }
        if ($d['flight_number'] !== '') {
            $lines[] = '• Vol : ' . $d['flight_number'];
        }
        if ($d['train_number'] !== '') {
            $lines[] = '• Train : ' . $d['train_number'];
        }

        $lines[] = '';
        $lines[] = 'Référence : ' . $this->reference;

        $text = implode("\n", $lines);

        if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_TEXT_LENGTH - 1) . '…';
        }

        return $text;
    }

    /**
     * URL wa.me. Le numéro est réduit aux chiffres (format wa.me sans « + »).
     * Retourne '' si aucun numéro de destination n'est configuré.
     */
    public function url(string $destinationNumber): string
    {
        $digits = preg_replace('/\D/', '', $destinationNumber);
        if ($digits === '' || $digits === null) {
            return '';
        }
        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($this->text());
    }
}
