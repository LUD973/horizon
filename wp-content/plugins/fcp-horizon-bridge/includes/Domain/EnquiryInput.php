<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Données de demande VALIDÉES et NORMALISÉES (logique pure).
 *
 * Immuable : construite uniquement par EnquiryValidator après contrôle serveur.
 * Fournit les lignes prêtes à persister (contact / enquiry / details) sans
 * connaître Supabase (aucune dépendance à la couche Data).
 */
final class EnquiryInput
{
    /**
     * @param array<string,mixed> $data données déjà normalisées
     */
    public function __construct(private array $data)
    {
    }

    public function email(): string
    {
        return (string) $this->data['email'];
    }

    public function consentMarketing(): bool
    {
        return (bool) $this->data['consent_marketing'];
    }

    public function preferredChannel(): string
    {
        return (string) $this->data['preferred_channel'];
    }

    /** @return array<string,mixed> Ligne pour la table contacts. */
    public function contactRow(): array
    {
        return [
            'type'               => $this->data['profile'],
            'first_name'         => $this->data['first_name'],
            'last_name'          => $this->data['last_name'],
            'email'              => $this->data['email'],
            'phone'              => $this->data['phone'],
            'preferred_language' => $this->data['locale'],
            'preferred_channel'  => $this->data['preferred_channel'],
        ];
    }

    /**
     * @return array<string,mixed> Ligne pour la table enquiries.
     *   public_reference est volontairement absent : généré côté serveur (trigger).
     */
    public function enquiryRow(): array
    {
        return [
            'category'          => $this->data['category'],
            'subcategory'       => $this->data['subcategory'],
            'source'            => 'website',
            'preferred_channel' => $this->data['preferred_channel'],
            'locale'            => $this->data['locale'],
            'summary'           => $this->summary(),
        ];
    }

    /** @return array<string,mixed> Ligne pour la table enquiry_details. */
    public function detailsRow(): array
    {
        return [
            'service_date'  => $this->data['service_date'],
            'service_time'  => $this->data['service_time'],
            'origin'        => $this->data['origin'],
            'destination'   => $this->data['destination'],
            'passengers'    => $this->data['passengers'],
            'luggage'       => $this->data['luggage'],
            'flight_number' => $this->data['flight_number'],
            'train_number'  => $this->data['train_number'],
            'notes'         => $this->data['notes'],
            'flexible_json' => [
                'range'          => $this->data['range'],
                'organization'   => $this->data['organization_name'],
            ],
        ];
    }

    /** Résumé formel court (une ligne), sans donnée superflue. */
    public function summary(): string
    {
        $parts = [];
        if ($this->data['origin'] !== '' && $this->data['destination'] !== '') {
            $parts[] = $this->data['origin'] . ' → ' . $this->data['destination'];
        }
        if ($this->data['service_date'] !== '') {
            $parts[] = $this->data['service_date']
                . ($this->data['service_time'] !== '' ? ' ' . $this->data['service_time'] : '');
        }
        if ($this->data['passengers'] > 0) {
            $parts[] = $this->data['passengers'] . ' pax';
        }
        return implode(' · ', $parts);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->data;
    }
}
