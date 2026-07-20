<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain;

/**
 * Résultat de validation : soit une entrée normalisée, soit des erreurs
 * par champ. Logique pure, sans dépendance WordPress.
 */
final class ValidationResult
{
    /** @param array<string,string> $errors @param ?EnquiryInput $input */
    private function __construct(
        private array $errors,
        private ?EnquiryInput $input,
    ) {
    }

    /** @param array<string,string> $errors */
    public static function failed(array $errors): self
    {
        return new self($errors, null);
    }

    public static function ok(EnquiryInput $input): self
    {
        return new self([], $input);
    }

    public function isValid(): bool
    {
        return $this->errors === [] && $this->input !== null;
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function input(): ?EnquiryInput
    {
        return $this->input;
    }
}
