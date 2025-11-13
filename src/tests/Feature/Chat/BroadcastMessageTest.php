<?php

namespace Tests\Feature\Chat;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\User;
use Modules\Chat\Events\MessageCreated;
use Modules\Chat\Models\{Message, Room, RoomMember};
use Modules\Org\Models\{Membership, Organization};
use Tests\TestCase;

class BroadcastMessageTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        // Boot like chat-api service
        putenv('API_ROUTES=routes/chat-api.php');
        $_ENV['API_ROUTES'] = 'routes/chat-api.php';
        $_SERVER['API_ROUTES'] = 'routes/chat-api.php';

        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function test_message_created_event_is_dispatched_on_post()
    {
        Event::fake([MessageCreated::class]);

        $user = User::factory()->create();

        $org = Organization::create([
            'name' => 'Acme',
            'slug' => 'acme-broadcast',
        ]);

        Membership::create([
            'organization_id' => $org->id,
            'user_id'        => $user->id,
            'role'           => 'owner',
        ]);

        $room = Room::create([
            'organization_id' => $org->id,
            'type'            => 'group',
            'name'            => 'general',
            'is_private'      => false,
        ]);

        RoomMember::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
        ]);

        $payload = [
            'room_id' => $room->id,
            'body'    => 'Broadcast smoke test',
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/messages', $payload);

        $response->assertCreated();

        Event::assertDispatched(MessageCreated::class, function ($event) use ($room, $user) {
            $this->assertInstanceOf(Message::class, $event->message);
            return $event->message->room_id === $room->id
                && $event->message->user_id === $user->id
                && $event->message->body === 'Broadcast smoke test';
        });
    }
}
