<?php
declare(strict_types=1);

namespace FCP\Horizon\Data;

/**
 * Accès aux contacts. Rapprochement par e-mail (unicité en base).
 */
final class ContactRepository
{
    public function __construct(private SupabaseClient $client)
    {
    }

    /**
     * Rapproche par e-mail, sinon crée. Le consentement marketing n'est mis à
     * jour que s'il est explicitement donné (jamais rétrogradé silencieusement).
     *
     * @param array<string,mixed> $contactRow
     * @return string id du contact
     */
    public function matchOrCreate(array $contactRow, bool $consentMarketing): string
    {
        $email = (string) $contactRow['email'];
        $existing = $this->client->select('contacts', [
            'email'  => 'eq.' . $email,
            'select' => 'id',
            'limit'  => '1',
        ]);

        if (isset($existing[0]['id'])) {
            $id = (string) $existing[0]['id'];
            if ($consentMarketing) {
                $this->client->update('contacts', ['id' => 'eq.' . $id], [
                    'consent_marketing'    => true,
                    'consent_marketing_at' => gmdate('c'),
                ]);
            }
            return $id;
        }

        $row = $contactRow;
        $row['consent_marketing'] = $consentMarketing;
        if ($consentMarketing) {
            $row['consent_marketing_at'] = gmdate('c');
        }

        $created = $this->client->insert('contacts', $row);
        return (string) $created['id'];
    }
}
