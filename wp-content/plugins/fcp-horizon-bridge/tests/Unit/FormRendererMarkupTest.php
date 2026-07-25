<?php
declare(strict_types=1);

namespace FCP\Horizon\Tests\Unit;

use FCP\Horizon\PublicSite\FormRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Vérifie la présence des repères d'accessibilité ajoutés au lot UX (C1/C2) :
 * cibles de focus programmatique après erreur et après succès.
 */
final class FormRendererMarkupTest extends TestCase
{
    private string $html;

    protected function setUp(): void
    {
        $this->html = (new FormRenderer())->render();
    }

    public function testErrorsBoxIsProgrammaticallyFocusable(): void
    {
        self::assertMatchesRegularExpression(
            '/id="fcp-form-errors"[^>]*tabindex="-1"/',
            $this->html,
            'Le bloc d’erreurs doit rester focusable via tabindex="-1" (C1).'
        );
        // Le rôle et l'annonce ARIA existants doivent être conservés.
        self::assertStringContainsString('role="alert"', $this->html);
        self::assertStringContainsString('aria-live="assertive"', $this->html);
    }

    public function testConfirmationIsProgrammaticallyFocusable(): void
    {
        self::assertMatchesRegularExpression(
            '/id="fcp-confirmation"[^>]*tabindex="-1"/',
            $this->html,
            'La confirmation doit rester focusable via tabindex="-1" (C2).'
        );
        self::assertStringContainsString('aria-live="polite"', $this->html);
    }
}
