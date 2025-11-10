<?php

use App\Http\Middleware\GatewaySecretMiddleware;
use App\Http\Middleware\MetricsHttpMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: base_path(env('API_ROUTES', 'routes/api.php')),
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(MetricsHttpMiddleware::class);
        $middleware->append(RequestIdMiddleware::class);
        $middleware->append(GatewaySecretMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        Modules\Chat\Providers\ChatServiceProvider::class,
        Modules\Org\Providers\OrgServiceProvider::class,
        Modules\Auth\Providers\AuthModuleServiceProvider::class,
    ])
    ->create();
