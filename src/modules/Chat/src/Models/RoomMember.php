<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Modules\Auth\Models\User;

class RoomMember extends Model
{
    use HasUlids;

    protected $table = 'room_members';
    protected $fillable = ['room_id','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
