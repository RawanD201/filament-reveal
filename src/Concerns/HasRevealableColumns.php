<?php

namespace Rawand\FilamentReveal\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Trait for models with revealable columns
 *
 * Add this trait to your model to define which columns can be revealed
 * and implement custom authorization logic.
 */
trait HasRevealableColumns
{
    /**
     * Get the columns that can be revealed
     */
    public function getRevealableColumns(): array
    {
        return $this->revealableColumns ?? [];
    }

    /**
     * Check if a column is revealable
     */
    public function isColumnRevealable(string $columnName): bool
    {
        return in_array($columnName, $this->getRevealableColumns());
    }

    /**
     * Authorize access to reveal a column
     *
     * Override this method in your model to implement custom authorization
     */
    public function authorizeRevealColumn(string $columnName, ?Authenticatable $user = null): bool
    {
        // Default: allow if column is revealable and user is authenticated
        return $this->isColumnRevealable($columnName) && $user !== null;
    }

    /**
     * Get the value of a revealable column
     */
    public function getRevealableColumnValue(string $columnName): mixed
    {
        if (! $this->isColumnRevealable($columnName)) {
            throw new \InvalidArgumentException("Column '{$columnName}' is not revealable.");
        }

        return $this->getAttribute($columnName);
    }
}
