<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Modules\Org\Models\{Organization, Membership};
use Modules\Chat\Models\{Room, RoomMember, Message};

class ChatpulseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create();

        $demoUser = User::firstOrCreate(
            ['email' => 'dev@chatpulse.local'],
            [
                'name' => 'Dev User',
                'password' => Hash::make('secret'),
            ]
        );

        $mainOrg = Organization::firstOrCreate(
            ['slug' => 'acme'],
            ['name' => 'Acme Corporation']
        );

        Membership::firstOrCreate([
            'organization_id' => $mainOrg->id,
            'user_id'         => $demoUser->id,
            'role'            => 'owner',
        ]);

        $rooms = [
            ['name' => 'general',      'is_private' => false],
            ['name' => 'engineering',  'is_private' => false],
            ['name' => 'management',   'is_private' => true],
        ];

        foreach ($rooms as $r) {
            $room = Room::firstOrCreate([
                'organization_id' => $mainOrg->id,
                'name'            => $r['name'],
            ], [
                'type'       => 'group',
                'is_private' => $r['is_private'],
            ]);

            RoomMember::firstOrCreate([
                'room_id' => $room->id,
                'user_id' => $demoUser->id,
            ]);
        }

        $general = Room::where('name', 'general')
            ->where('organization_id', $mainOrg->id)
            ->first();

        if ($general) {
            Message::factory()->count(5)->create([
                'room_id' => $general->id,
                'user_id' => $demoUser->id,
            ]);
        }

        $extraUsers = collect();

        for ($i = 0; $i < 20; $i++) {
            $extraUsers->push(
                User::create([
                    'name'     => $faker->name(),
                    'email'    => $faker->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                ])
            );
        }

        $orgs = collect();

        $orgCount = rand(3, 4);

        for ($i = 0; $i < $orgCount; $i++) {
            $orgs->push(
                Organization::create([
                    'name' => $faker->company(),
                    'slug' => $faker->unique()->slug(),
                ])
            );
        }

        foreach ($orgs as $org) {
            $members = $extraUsers->random(rand(3, 8));

            foreach ($members as $u) {
                Membership::create([
                    'organization_id' => $org->id,
                    'user_id'         => $u->id,
                    'role'            => 'member',
                ]);
            }

            $roomNames = ['general', 'random', 'fun', 'team'];
            $roomsToCreate = collect($roomNames)->random(rand(2, 4));

            foreach ($roomsToCreate as $roomName) {
                $room = Room::create([
                    'organization_id' => $org->id,
                    'type'            => 'group',
                    'name'            => $roomName,
                    'is_private'      => false,
                ]);

                $roomMembers = $members->random(rand(2, 3));

                foreach ($roomMembers as $rm) {
                    RoomMember::create([
                        'room_id' => $room->id,
                        'user_id' => $rm->id,
                    ]);
                }


                Message::factory()->count(rand(5, 10))->create([
                    'room_id' => $room->id,
                    'user_id' => $roomMembers->random()->id,
                ]);
            }
        }

        $this->command?->info('Chatpulse demo seed completed successfully 🎉');
    }
}
