<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\Room;
use Modules\Chat\Models\RoomMember;
use Modules\Org\Models\Membership;
use Modules\Org\Models\Organization;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'email' => 'dev@chatpulse.local',
            'password' => bcrypt('secret'),
        ]);

        $org = Organization::firstOrCreate(
            ['slug' => 'acme'],
            ['name' => 'Acme Inc.']
        );

        Membership::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $user->id],
            ['role' => 'owner']
        );

        $room = Room::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'general'],
            ['type' => 'group', 'is_private' => false]
        );

        RoomMember::firstOrCreate([
            'room_id' => $room->id,
            'user_id' => $user->id,
        ]);

        // seed a few messages if room is empty
        if (!Message::where('room_id', $room->id)->exists()) {
            Message::create(['room_id'=>$room->id,'user_id'=>$user->id,'body'=>'Welcome to ChatPulse!']);
            Message::create(['room_id'=>$room->id,'user_id'=>$user->id,'body'=>'This is a seeded message.']);
            Message::create(['room_id'=>$room->id,'user_id'=>$user->id,'body'=>'Broadcasting is live via Reverb.']);
        }

    }
}
