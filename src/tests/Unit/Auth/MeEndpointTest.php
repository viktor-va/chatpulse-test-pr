<?php

namespace Tests\Unit\Auth;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Modules\Auth\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_endpoint_returns_authenticated_user()
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }
}
