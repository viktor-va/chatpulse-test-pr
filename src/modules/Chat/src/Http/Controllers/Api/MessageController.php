<?php

namespace Modules\Chat\Http\Controllers\Api;

use App\Support\Metrics;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Chat\Events\MessageCreated;
use Modules\Chat\Models\Message;
use Modules\Chat\Models\Room;

class MessageController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Room $room)
    {
        $this->authorize('view', $room);

        $messages = Message::where('room_id', $room->id)
            ->orderByDesc('created_at')
            ->cursorPaginate(20);

        return response()->json([
            'data' => $messages->items(),
            'next_cursor' => $messages->nextCursor()?->encode(),
        ]);
    }

    public function store(Request $request)
    {
        $t0 = microtime(true);

        $data = $request->validate([
            'room_id' => ['required','string','exists:rooms,id'],
            'body'    => ['required','string'],
        ]);

        $room = Room::findOrFail($data['room_id']);
        $this->authorize('post', $room);

        $msg = Message::create([
            'room_id' => $data['room_id'],
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ]);

        // broadcast
        event(new MessageCreated($msg));

        // metrics hook
        if (class_exists(Metrics::class)) {
            $reg = Metrics::registry();
            $ns  = env('PROM_NAMESPACE','chatpulse');
            $reg->getOrRegisterCounter($ns,'chat_messages_total','Total messages',['room_id'])->inc([$msg->room_id]);
            $reg->getOrRegisterHistogram($ns,'chat_publish_latency_seconds','Publish latency',['room_id'],[0.005,0.01,0.025,0.05,0.1,0.25,0.5,1])
                ->observe(microtime(true)-$t0,[$msg->room_id]);
        }

        return response()->json($msg, 201);
    }
}
