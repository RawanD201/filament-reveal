<?php

namespace Rawand\FilamentReveal\Columns;

use Closure;
use Filament\Tables\Columns\TextColumn;

/**
 * RevealColumn - Secure column for sensitive data
 * 
 * Features:
 * - Data masked by default with configurable mask character
 * - Toggle visibility with eye icon
 * - Optional password authentication to reveal data
 * - Click revealed text to copy
 * - Search disabled by default for security
 * 
 * @example
 * RevealColumn::make('password')
 *     ->requiresAuthentication()
 *     ->maskAsterisk()
 *     ->revealedColor('danger');
 */
class RevealColumn extends TextColumn
{
    protected string $view = 'filament-reveal::columns.reveal-column';

    protected string $mask;

    protected string $revealedColor;

    protected bool | Closure $requiresAuthentication = false;


    protected function setUp(): void
    {
        parent::setUp();

        // Load defaults from config
        $defaultMask = config('filament-reveal.default_mask', 'bullet');
        $this->mask = $this->resolveMaskType($defaultMask);
        $this->revealedColor = config('filament-reveal.revealed_color', 'primary');
        $this->requiresAuthentication = config('filament-reveal.require_authentication', false);


        // Disable search by default for security
        $this->searchable(false);
    }

    /**
     * Resolve mask type to character string
     */
    protected function resolveMaskType(string $type): string
    {
        return match ($type) {
            'bullet' => '••••••••',
            'asterisk' => '********',
            'hash' => '########',
            default => $type,
        };
    }

    /**
     * Set custom mask
     */
    public function mask(string $mask): static
    {
        $this->mask = $this->resolveMaskType($mask);
        return $this;
    }

    /**
     * Use bullet mask (••••••••)
     */
    public function maskBullet(): static
    {
        $this->mask = '••••••••';
        return $this;
    }

    /**
     * Use asterisk mask (********)
     */
    public function maskAsterisk(): static
    {
        $this->mask = '********';
        return $this;
    }

    /**
     * Use hash mask (########)
     */
    public function maskHash(): static
    {
        $this->mask = '########';
        return $this;
    }

    /**
     * Get current mask
     */
    public function getMask(): string
    {
        return $this->mask;
    }

    /**
     * Set color for revealed text
     */
    public function revealedColor(string $color): static
    {
        $this->revealedColor = $color;
        return $this;
    }

    /**
     * Get revealed text color
     */
    public function getRevealedColor(): string
    {
        return $this->revealedColor;
    }

    /**
     * Require password authentication before revealing
     */
    public function requiresAuthentication(bool | Closure $condition = true): static
    {
        $this->requiresAuthentication = $condition;
        return $this;
    }

    /**
     * Check if authentication is required
     */
    public function getRequiresAuthentication(): bool
    {
        return $this->evaluate($this->requiresAuthentication);
    }

    /**
     * Override getState to NEVER expose sensitive data in HTML
     * Data is always fetched via AJAX after authentication
     */
    public function getState(): mixed
    {
        // Always return null - data will be fetched via AJAX
        return null;
    }
}
