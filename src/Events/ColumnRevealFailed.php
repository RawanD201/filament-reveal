<?php

namespace Rawand\FilamentReveal\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a column reveal attempt fails
 */
class ColumnRevealFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public mixed $user,
        public string $modelClass,
        public mixed $recordId,
        public string $columnName,
        public string $reason,
        public ?string $ipAddress = null,
        public ?array $metadata = null
    ) {
    }
}
