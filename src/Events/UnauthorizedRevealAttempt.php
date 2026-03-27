<?php

namespace Rawand\FilamentReveal\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when an unauthorized reveal attempt is detected
 * This is a security-critical event that should be monitored
 */
class UnauthorizedRevealAttempt
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
    ) {}
}
