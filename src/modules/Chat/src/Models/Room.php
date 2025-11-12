<?php

namespace Modules\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Room extends Model
{
    use HasUlids;

    protected $table = 'rooms';
    protected $fillable = ['organization_id','type','name','is_private'];

    public function members()
    {
        return $this->hasMany(RoomMember::class);
    }
}
