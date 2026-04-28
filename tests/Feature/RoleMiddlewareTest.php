<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_user_routes(): void
    {
        $response = $this->get('/user/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_user_role_can_access_user_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/user/dashboard');

        $response->assertOk();
    }

    public function test_non_user_role_is_forbidden_from_user_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/user/dashboard');

        $response->assertForbidden();
    }
}
