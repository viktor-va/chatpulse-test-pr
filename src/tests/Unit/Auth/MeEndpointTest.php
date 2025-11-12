<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use Modules\Auth\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_endpoint_returns_authenticated_user()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }
}
