<?php

namespace Modules\Org\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Organization extends Model
{
    use HasUlids;

    protected $table = 'organizations';
    protected $fillable = ['name','slug'];
}
