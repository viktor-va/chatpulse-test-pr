<?php

namespace Tests\Feature\Chat;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Modules\Auth\Models\User;
use Modules\Org\Models\{Organization, Membership};
use Modules\Chat\Models\{Room, RoomMember, Message};

class PostMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_post_message()
    {
        $user = User::factory()->create();
        //Passport::actingAs($user);
        Passport::actingAs($user, 'api');

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
        $user = User::factory()->create();
        //Passport::actingAs($user);
        Passport::actingAs($user, 'api');

        $org = Organization::create(['name' => 'Acme', 'slug' => 'acme']);
        // user is NOT a member of this room/org as admin/owner for manage, but we test member policy
        $room = Room::create(['organization_id' => $org->id, 'type' => 'group', 'name' => 'secret', 'is_private' => true]);

        $res = $this->postJson('/api/messages', ['room_id' => $room->id, 'body' => 'Should fail']);
        $res->assertForbidden(); // policy denies
    }
}
