<?php

namespace Modules\Chat\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Chat\Models\{Room, RoomMember};

class RoomMemberController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Room $room)
    {
        $this->authorize('manageMembers', $room);

        $data = $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
        ]);

        $member = RoomMember::firstOrCreate([
            'room_id' => $room->id,
            'user_id' => $data['user_id'],
        ]);

        return response()->json($member, 201);
    }

    public function destroy(Request $request, Room $room, User $user)
    {
        $this->authorize('manageMembers', $room);

        RoomMember::where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->noContent(); // 204
    }
}
