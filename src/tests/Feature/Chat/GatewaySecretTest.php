<?php

namespace Tests\Feature\Chat;

use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class GatewaySecretTest extends TestCase
{
    public function createApplication()
    {
        // Boot like chat-api service
        putenv('API_ROUTES=routes/chat-api.php');
        $_ENV['API_ROUTES'] = 'routes/chat-api.php';
        $_SERVER['API_ROUTES'] = 'routes/chat-api.php';

        $app = require __DIR__.'/../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gateway.secret' => 'test-secret']);

        Route::get('/gateway-test', function () {
            return response()->json(['ok' => true]);
        });
    }

    public function test_requests_without_gateway_secret_are_forbidden()
    {
        $this->getJson('/gateway-test')
            ->assertStatus(403);
    }

    public function test_requests_with_correct_gateway_secret_are_allowed()
    {
        $this->getJson('/gateway-test', [
            'X-Internal-Gateway-Secret' => 'test-secret',
        ])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }
}
