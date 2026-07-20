<?php
declare(strict_types=1);

namespace FCP\Horizon\Application;

use FCP\Horizon\Data\AuditLogRepository;
use FCP\Horizon\Data\CommunicationRepository;
use FCP\Horizon\Data\ContactRepository;
use FCP\Horizon\Data\EnquiryRepository;
use FCP\Horizon\Domain\EnquiryInput;
use FCP\Horizon\Domain\WhatsAppMessage;

/**
 * Orchestration de la soumission d'une demande.
 *
 * Ordre impératif (règles S2 nos 2, 3, 4) :
 *   1. Rapprocher / créer le contact.
 *   2. Créer la demande (référence FCP attribuée par le serveur/trigger).
 *   3. Créer les détails.
 *   4. Journaliser (audit_logs) et préparer la communication WhatsApp (« prepared »).
 *   5. Construire le lien wa.me et le RETOURNER.
 *
 * Le lien WhatsApp n'est construit qu'après enregistrement réussi. En cas
 * d'échec, une exception est levée : le contrôleur ne renvoie aucune URL et le
 * navigateur n'ouvre pas WhatsApp.
 */
final class EnquiryService
{
    public function __construct(
        private ContactRepository $contacts,
        private EnquiryRepository $enquiries,
        private CommunicationRepository $communications,
        private AuditLogRepository $audit,
        private string $whatsappNumber,
    ) {
    }

    /**
     * @param array{ip?:?string, request_id?:?string} $context
     * @return array{reference:string, enquiry_id:string, whatsapp_url:string, summary:string}
     */
    public function submit(EnquiryInput $input, array $context = []): array
    {
        $contactId = $this->contacts->matchOrCreate($input->contactRow(), $input->consentMarketing());

        $enquiry = $this->enquiries->create($contactId, $input->enquiryRow(), $input->detailsRow());

        $this->audit->record(
            'enquiry.created',
            'enquiries',
            $enquiry['id'],
            ['public_reference' => $enquiry['public_reference'], 'summary' => $input->summary()],
            $context['ip'] ?? null,
            $context['request_id'] ?? null,
        );

        $this->communications->prepare(
            $enquiry['id'],
            'whatsapp',
            'enquiry_receipt',
            $this->whatsappNumber !== '' ? $this->whatsappNumber : null,
        );

        $whatsapp = new WhatsAppMessage($enquiry['public_reference'], $input);

        return [
            'reference'    => $enquiry['public_reference'],
            'enquiry_id'   => $enquiry['id'],
            'whatsapp_url' => $whatsapp->url($this->whatsappNumber),
            'summary'      => $input->summary(),
        ];
    }
}
