<?php

namespace Modules\Chat\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Org\Models\Organization;
use Modules\Chat\Models\{Room,RoomMember};

class RoomController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Organization $org)
    {
        $rooms = Room::query()
            ->where('organization_id', $org->id)
            ->join('room_members', 'room_members.room_id', '=', 'rooms.id')
            ->where('room_members.user_id', $request->user()->id)
            ->select('rooms.*')
            ->orderBy('rooms.name')
            ->get();

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_id' => ['required','string','exists:organizations,id'],
            'name'            => ['required','string','max:120'],
            'type'            => ['sometimes','in:group,dm'],
            'is_private'      => ['sometimes','boolean'],
        ]);

        $org = Organization::findOrFail($data['organization_id']);
        $this->authorize('create', [Room::class, $org]);

        $room = Room::create([
            'organization_id' => $org->id,
            'name'            => $data['name'],
            'type'            => $data['type'] ?? 'group',
            'is_private'      => (bool)($data['is_private'] ?? false),
        ]);

        RoomMember::firstOrCreate([
            'room_id' => $room->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($room, 201);
    }
}
