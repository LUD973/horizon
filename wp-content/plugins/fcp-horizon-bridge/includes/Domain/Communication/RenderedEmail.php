<?php
declare(strict_types=1);

namespace FCP\Horizon\Domain\Communication;

/** Résultat de rendu d'un e-mail (sujet + corps HTML + corps texte). */
final class RenderedEmail
{
    public function __construct(
        private string $subject,
        private string $html,
        private string $text,
    ) {
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function html(): string
    {
        return $this->html;
    }

    public function text(): string
    {
        return $this->text;
    }
}
