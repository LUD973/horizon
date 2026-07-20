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

        $found = $this->findIdByEmail($email);
        if ($found !== null) {
            $this->applyMarketingConsent($found, $consentMarketing);
            return $found;
        }

        $row = $contactRow;
        $row['consent_marketing'] = $consentMarketing;
        if ($consentMarketing) {
            $row['consent_marketing_at'] = gmdate('c');
        }

        try {
            $created = $this->client->insert('contacts', $row);
            return (string) $created['id'];
        } catch (SupabaseException $e) {
            // Course concurrente : un autre appel a créé le contact entre le
            // SELECT et l'INSERT. La contrainte UNIQUE (lower(email)) a rejeté
            // l'insertion (HTTP 409). On relit le contact existant, sans erreur
            // utilisateur.
            if ($e->getCode() === 409) {
                $again = $this->findIdByEmail($email);
                if ($again !== null) {
                    $this->applyMarketingConsent($again, $consentMarketing);
                    return $again;
                }
            }
            throw $e;
        }
    }

    private function findIdByEmail(string $email): ?string
    {
        $rows = $this->client->select('contacts', [
            'email'  => 'eq.' . $email,
            'select' => 'id',
            'limit'  => '1',
        ]);
        return isset($rows[0]['id']) ? (string) $rows[0]['id'] : null;
    }

    private function applyMarketingConsent(string $id, bool $consentMarketing): void
    {
        if (!$consentMarketing) {
            return; // Jamais de rétrogradation silencieuse.
        }
        $this->client->update('contacts', ['id' => 'eq.' . $id], [
            'consent_marketing'    => true,
            'consent_marketing_at' => gmdate('c'),
        ]);
    }
}
