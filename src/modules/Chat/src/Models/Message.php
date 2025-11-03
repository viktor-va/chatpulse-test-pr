<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Message extends Model
{
    use HasUlids;

    protected $table = 'messages';

    protected $fillable = ['room_id','user_id','body','meta'];

    protected $casts = [
        'meta' => 'array',
    ];
}
