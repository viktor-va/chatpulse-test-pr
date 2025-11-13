<?php

namespace Modules\Chat\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Chat\Models\Room;
use Modules\Chat\Policies\RoomPolicy;
//use Modules\Chat\Models\Message;
//use Modules\Chat\Policies\MessagePolicy;

class ChatServiceProvider extends ServiceProvider
{
    protected $policies = [
        Room::class    => RoomPolicy::class,
        //Message::class => MessagePolicy::class,
    ];
    public function register(): void {}
    public function boot(): void {
        $this->registerPolicies();
    }
}
