<?php

namespace Modules\Chat\Policies;

use Modules\Auth\Models\User;
use Modules\Chat\Models\{Room,RoomMember};
use Modules\Org\Models\{Membership,Organization};

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        return RoomMember::where('room_id',$room->id)
            ->where('user_id',$user->id)->exists();
    }

    public function post(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function manageMembers(User $user, Room $room): bool
    {
        return Membership::where('organization_id',$room->organization_id)
            ->where('user_id',$user->id)
            ->whereIn('role',['owner','admin'])->exists();
    }

    public function create(User $user, Organization $org): bool
    {
        return Membership::where('organization_id',$org->id)
            ->where('user_id',$user->id)
            ->whereIn('role',['owner','admin'])->exists();
    }
}
