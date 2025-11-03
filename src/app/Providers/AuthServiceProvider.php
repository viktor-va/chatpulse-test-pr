<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Chat\Models\Room;
use Modules\Chat\Policies\RoomPolicy;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Room::class => RoomPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('create', [RoomPolicy::class, 'create']);
    }
}
