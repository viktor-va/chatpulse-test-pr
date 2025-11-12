<?php

namespace Tests\Feature\Chat;

use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\User;
use Modules\Org\Models\{Organization, Membership};
use Modules\Chat\Models\{Room, RoomMember};

class PostMessageTest extends TestCase
{
    use RefreshDatabase;

//
//    protected function setUp(): void
//    {
//        // use chat-api routes for this class
//        putenv('API_ROUTES=routes/chat-api.php');
//        $_ENV['API_ROUTES']    = 'routes/chat-api.php';
//        $_SERVER['API_ROUTES'] = 'routes/chat-api.php';
//
//        parent::setUp(); // now RefreshDatabase will migrate with the right routes loaded
//    }
     function createApplication()
    {
        // Boot like chat-api service
        putenv('API_ROUTES=routes/chat-api.php');
        $_ENV['API_ROUTES'] = 'routes/chat-api.php';
        $_SERVER['API_ROUTES'] = 'routes/chat-api.php';

        $app = require __DIR__.'/../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    public function test_member_can_post_message()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $org = Organization::create(['name' => 'Acme', 'slug' => 'acme']);
        Membership::create(['organization_id' => $org->id, 'user_id' => $user->id, 'role' => 'owner']);

        $room = Room::create(['organization_id' => $org->id, 'type' => 'group', 'name' => 'general', 'is_private' => false]);
        RoomMember::create(['room_id' => $room->id, 'user_id' => $user->id]);

        $payload = ['room_id' => $room->id, 'body' => 'Hello from test'];

        $res = $this->postJson('/api/messages', $payload);
        $res->assertCreated()->assertJsonFragment(['body' => 'Hello from test']);

        $this->assertDatabaseHas('messages', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'body'    => 'Hello from test',
        ]);
    }

    public function test_non_member_cannot_post_message()
    {
        $stranger = User::factory()->create();
        Sanctum::actingAs($stranger);

        $org = Organization::create(['name' => 'Acme', 'slug' => 'acme']);
        $room = Room::create(['organization_id' => $org->id, 'type' => 'group', 'name' => 'secret', 'is_private' => true]);

        $res = $this->postJson('/api/messages', ['room_id' => $room->id, 'body' => 'Should fail']);
        $res->assertForbidden();
    }
}
