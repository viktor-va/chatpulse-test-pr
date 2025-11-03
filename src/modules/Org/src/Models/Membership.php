<?php

namespace Modules\Org\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Membership extends Model
{
    use HasUlids;

    protected $table = 'memberships';
    protected $fillable = ['organization_id','user_id','role'];
}
