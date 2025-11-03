<?php

namespace App\Events;

use Modules\Chat\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): Channel
    {
        return new Channel('chat.room.' . $this->message->room_id);
    }

    public function broadcastAs(): string
    {
        return 'MessageCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'      => $this->message->id,
            'user_id' => $this->message->user_id,
            'body'    => $this->message->body,
            'created' => $this->message->created_at?->toISOString(),
        ];
    }
}
