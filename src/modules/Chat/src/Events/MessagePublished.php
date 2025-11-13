<?php

namespace Modules\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessagePublished implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public string $message) {}

    public function broadcastOn(): Channel
    {
        // public channel for now; we'll add auth channels later
        return new Channel('chat.public');
    }

    public function broadcastAs(): string
    {
        return 'MessagePublished';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message, 'at' => now()->toISOString()];
    }
}
