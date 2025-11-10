<?php

namespace Tests\Feature\Chat;

use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Org\Models\{Organization,Membership};
use Modules\Chat\Models\{Room,RoomMember,Message};

class RoomMessagesTest extends TestCase
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

    public function test_member_can_list_messages()
    {
        $user = User::factory()->create();

        $org = Organization::create([
            'name' => 'Acme',
            'slug' => 'acme',
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

        // Seed a couple of messages
        Message::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'body'    => 'First message',
        ]);

        Message::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'body'    => 'Second message',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->getJson("/api/rooms/{$room->id}/messages");

        $response->assertOk();
        $data = $response->json('data');

        $this->assertGreaterThanOrEqual(2, count($data));
        $this->assertTrue(collect($data)->pluck('body')->contains('First message'));
        $this->assertTrue(collect($data)->pluck('body')->contains('Second message'));
    }

    public function test_non_member_cannot_list_messages()
    {
        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $org = Organization::create([
            'name' => 'Acme',
            'slug' => 'acme-2',
        ]);

        Membership::create([
            'organization_id' => $org->id,
            'user_id'        => $member->id,
            'role'           => 'owner',
        ]);

        $room = Room::create([
            'organization_id' => $org->id,
            'type'            => 'group',
            'name'            => 'private',
            'is_private'      => true,
        ]);

        RoomMember::create([
            'room_id' => $room->id,
            'user_id' => $member->id,
        ]);

        Message::create([
            'room_id' => $room->id,
            'user_id' => $member->id,
            'body'    => 'Secret',
        ]);

        $this->actingAs($stranger, 'api');

        $response = $this->getJson("/api/rooms/{$room->id}/messages");

        $response->assertForbidden();
    }
}
