<?php

namespace Rawand\FilamentReveal\Entries;

use Closure;
use Filament\Infolists\Components\Entry;

/**
 * RevealEntry — secure infolist entry for sensitive data.
 *
 * Mirrors the RevealColumn API so the same model trait, token system,
 * endpoint, and authentication modal are reused without duplication.
 *
 * @example
 * RevealEntry::make('api_token')
 *     ->maskAsterisk()
 *     ->revealedColor('success')
 *     ->requiresAuthentication()
 */
class RevealEntry extends Entry
{
    protected string $view = 'filament-reveal::entries.reveal-entry';

    protected string $mask;

    protected string $revealedColor;

    protected bool|Closure $requiresAuthentication = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mask = $this->resolveMask(config('filament-reveal.default_mask', 'bullet'));
        $this->revealedColor = config('filament-reveal.revealed_color', 'primary');
        $this->requiresAuthentication = config('filament-reveal.require_authentication', false);
    }

    // ── Mask helpers ──────────────────────────────────────────────────────────

    public function mask(string $mask): static
    {
        $this->mask = $this->resolveMask($mask);

        return $this;
    }

    public function maskBullet(): static
    {
        return $this->mask('bullet');
    }

    public function maskAsterisk(): static
    {
        return $this->mask('asterisk');
    }

    public function maskHash(): static
    {
        return $this->mask('hash');
    }

    public function getMask(): string
    {
        return $this->mask;
    }

    // ── Revealed color ────────────────────────────────────────────────────────

    public function revealedColor(string $color): static
    {
        $this->revealedColor = $color;

        return $this;
    }

    public function getRevealedColor(): string
    {
        return $this->revealedColor;
    }

    // ── Authentication ────────────────────────────────────────────────────────

    public function requiresAuthentication(bool|Closure $required = true): static
    {
        $this->requiresAuthentication = $required;

        return $this;
    }

    public function getRequiresAuthentication(): bool
    {
        return (bool) $this->evaluate($this->requiresAuthentication);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function resolveMask(string $mask): string
    {
        return match ($mask) {
            'bullet' => '••••••••',
            'asterisk' => '********',
            'hash' => '########',
            default => $mask,
        };
    }
}
