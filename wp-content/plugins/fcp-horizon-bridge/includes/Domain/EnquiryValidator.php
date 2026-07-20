<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Validation et normalisation CÔTÉ SERVEUR d'une demande (règle 6 de S2).
 *
 * Logique pure : aucune dépendance WordPress, entièrement testable. La
 * sanitisation WordPress (sanitize_text_field, etc.) reste appliquée en amont
 * par la couche présentation, mais la vérité de la validation est ici.
 *
 * Règle 8 : le consentement de traitement (nécessaire) est distinct du
 * consentement marketing (facultatif).
 */
final class EnquiryValidator
{
    private const PROFILES = ['individual', 'company'];
    private const CHANNELS = ['email', 'whatsapp', 'phone'];
    private const MAX_PASSENGERS = 60;

    public function validate(array $raw, ?\DateTimeImmutable $now = null): ValidationResult
    {
        $now ??= new \DateTimeImmutable('today');
        $errors = [];

        $profile = $this->str($raw['profile'] ?? 'individual');
        if (!in_array($profile, self::PROFILES, true)) {
            $profile = 'individual';
        }

        $organization = $this->str($raw['organization_name'] ?? '');
        if ($profile === 'company' && $organization === '') {
            $errors['organization_name'] = 'Le nom de la société est requis.';
        }

        $firstName = $this->str($raw['first_name'] ?? '');
        if (mb_strlen($firstName) < 2 || mb_strlen($firstName) > 80) {
            $errors['first_name'] = 'Prénom requis (2 à 80 caractères).';
        }

        $lastName = $this->str($raw['last_name'] ?? '');
        if (mb_strlen($lastName) < 2 || mb_strlen($lastName) > 80) {
            $errors['last_name'] = 'Nom requis (2 à 80 caractères).';
        }

        $email = strtolower($this->str($raw['email'] ?? ''));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }

        $phone = $this->normalizePhone($this->str($raw['phone'] ?? ''));
        if (strlen(preg_replace('/\D/', '', $phone)) < 8) {
            $errors['phone'] = 'Numéro de téléphone invalide.';
        }

        $channel = $this->str($raw['preferred_channel'] ?? 'email');
        if (!in_array($channel, self::CHANNELS, true)) {
            $channel = 'email';
        }

        $serviceDate = $this->str($raw['service_date'] ?? '');
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $serviceDate);
        if ($serviceDate === '' || $date === false) {
            $errors['service_date'] = 'Date de service invalide (format AAAA-MM-JJ).';
        } elseif ($date < $now) {
            $errors['service_date'] = 'La date doit être aujourd’hui ou ultérieure.';
        }

        $serviceTime = $this->str($raw['service_time'] ?? '');
        if ($serviceTime !== '' && \DateTimeImmutable::createFromFormat('!H:i', $serviceTime) === false) {
            $errors['service_time'] = 'Heure invalide (format HH:MM).';
        }

        $origin = $this->str($raw['origin'] ?? '');
        if (mb_strlen($origin) < 2 || mb_strlen($origin) > 160) {
            $errors['origin'] = 'Lieu de départ requis.';
        }

        $destination = $this->str($raw['destination'] ?? '');
        if (mb_strlen($destination) < 2 || mb_strlen($destination) > 160) {
            $errors['destination'] = 'Destination requise.';
        }

        $passengers = (int) ($raw['passengers'] ?? 0);
        if ($passengers < 1 || $passengers > self::MAX_PASSENGERS) {
            $errors['passengers'] = 'Nombre de passagers invalide (1 à ' . self::MAX_PASSENGERS . ').';
        }

        $luggage = isset($raw['luggage']) && $raw['luggage'] !== '' ? (int) $raw['luggage'] : null;
        if ($luggage !== null && $luggage < 0) {
            $errors['luggage'] = 'Nombre de bagages invalide.';
        }

        $consentProcessing = $this->bool($raw['consent_processing'] ?? false);
        if ($consentProcessing !== true) {
            $errors['consent_processing'] = 'Le consentement au traitement de la demande est requis.';
        }

        if ($errors !== []) {
            return ValidationResult::failed($errors);
        }

        return ValidationResult::ok(new EnquiryInput([
            'profile'           => $profile,
            'organization_name' => $organization,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'email'             => $email,
            'phone'             => $phone,
            'preferred_channel' => $channel,
            'locale'            => $this->str($raw['locale'] ?? 'fr') ?: 'fr',
            'category'          => $this->str($raw['category'] ?? 'mobility') ?: 'mobility',
            'subcategory'       => $this->str($raw['subcategory'] ?? ''),
            'service_date'      => $serviceDate,
            'service_time'      => $serviceTime,
            'origin'            => $origin,
            'destination'       => $destination,
            'passengers'        => $passengers,
            'luggage'           => $luggage,
            'flight_number'     => $this->str($raw['flight_number'] ?? ''),
            'train_number'      => $this->str($raw['train_number'] ?? ''),
            'range'             => $this->str($raw['range'] ?? ''),
            'notes'             => mb_substr($this->str($raw['message'] ?? ''), 0, 1500),
            'consent_marketing' => $this->bool($raw['consent_marketing'] ?? false),
        ]));
    }

    private function str(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        $value = strip_tags((string) $value);
        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private function bool(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    private function normalizePhone(string $value): string
    {
        $plus = str_starts_with(trim($value), '+') ? '+' : '';
        return $plus . preg_replace('/\D/', '', $value);
    }
}
