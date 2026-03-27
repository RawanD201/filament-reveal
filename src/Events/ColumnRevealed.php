<?php

namespace Rawand\FilamentReveal\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a column is successfully revealed
 */
class ColumnRevealed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public mixed $user,
        public string $modelClass,
        public mixed $recordId,
        public string $columnName,
        public ?string $ipAddress = null,
        public ?array $metadata = null
    ) {}
}
