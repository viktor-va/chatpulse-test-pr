<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // In tests, use session auth for the `api` guard to avoid Passport keys.
        config([
            'auth.defaults.guard' => 'api',
            'auth.guards.api.driver' => 'session',
        ]);
    }
}
