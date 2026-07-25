<?php
declare(strict_types=1);

namespace FCP\Horizon\Application\Communication;

use FCP\Horizon\Data\MessageRepository;
use FCP\Horizon\Data\SupabaseException;
use FCP\Horizon\Domain\Communication\Channel;
use FCP\Horizon\Domain\Communication\EmailTemplateRegistry;
use FCP\Horizon\Domain\Communication\MessageType;
use FCP\Horizon\Domain\EnquiryInput;
use FCP\Horizon\Support\Logger;

/**
 * Intentions de communication métier (accusé client, alerte équipe).
 *
 * Écrit uniquement des INTENTIONS dans l'outbox (statut pending) ; l'envoi réel
 * est asynchrone (OutboxProcessor). Ne lève JAMAIS d'exception : un problème de
 * communication ne doit jamais empêcher ni perdre une demande Horizon.
 */
final class NotificationService
{
    private const CATEGORY_LABELS = [
        'mobility'    => 'Mobilité',
        'events'      => 'Événements',
        'experiences' => 'Expériences',
    ];

    public function __construct(
        private MessageRepository $messages,
        private string $internalRecipient,
        private string $responseDelay = 'sous 24 heures ouvrées',
    ) {
    }

    /**
     * @param array{id:string, public_reference:string} $enquiry
     */
    public function enqueueEnquiryAcknowledgements(array $enquiry, EnquiryInput $input, string $ficheBaseUrl = ''): void
    {
        $data = $input->toArray();
        $reference = (string) $enquiry['public_reference'];
        $label = self::CATEGORY_LABELS[(string) ($data['category'] ?? '')] ?? 'Demande sur mesure';
        $ficheUrl = $ficheBaseUrl !== '' ? $ficheBaseUrl . rawurlencode($reference) : '';

        // 1) Accusé de réception client (transactionnel, toujours).
        $this->safeEnqueue([
            'enquiry_id'   => $enquiry['id'],
            'channel'      => Channel::EMAIL,
            'direction'    => 'outbound',
            'message_type' => MessageType::TRANSACTIONAL,
            'recipient'    => $input->email(),
            'template_id'  => EmailTemplateRegistry::CLIENT_ACK,
            'locale'       => (string) ($data['locale'] ?? 'fr'),
            'payload'      => ['params' => [
                'reference'      => $reference,
                'first_name'     => (string) ($data['first_name'] ?? ''),
                'service_label'  => $label,
                'service_date'   => (string) ($data['service_date'] ?? ''),
                'trajet'         => $input->summary(),
                'response_delay' => $this->responseDelay,
            ]],
        ]);

        // 2) Notification interne équipe FCP (si destinataire configuré).
        if ($this->internalRecipient !== '') {
            $this->safeEnqueue([
                'enquiry_id'   => $enquiry['id'],
                'channel'      => Channel::EMAIL,
                'direction'    => 'outbound',
                'message_type' => MessageType::TRANSACTIONAL,
                'recipient'    => $this->internalRecipient,
                'template_id'  => EmailTemplateRegistry::INTERNAL_ALERT,
                'locale'       => 'fr',
                'payload'      => ['params' => [
                    'reference'         => $reference,
                    'service_label'     => $label,
                    'summary'           => $input->summary(),
                    'preferred_channel' => $input->preferredChannel(),
                    'fiche_url'         => $ficheUrl,
                ]],
            ]);
        }
    }

    /** @param array<string,mixed> $row */
    private function safeEnqueue(array $row): void
    {
        try {
            $this->messages->enqueue($row);
        } catch (SupabaseException $e) {
            // On n'interrompt jamais le flux de la demande.
            Logger::error('Échec mise en file d’un message', ['code' => $e->getCode()]);
        }
    }
}
