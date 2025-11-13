<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Modules\Chat\Database\Factories\MessageFactory;

class Message extends Model
{
    use HasUlids, HasFactory;

    protected $table = 'messages';

    protected $fillable = ['room_id','user_id','body','meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function newFactory(): MessageFactory
    {
        return MessageFactory::new();
    }
}
