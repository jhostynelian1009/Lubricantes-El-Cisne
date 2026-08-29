<?php

namespace Tests\Feature\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveUserMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_deactivated_after_login_is_logged_out_on_next_request()
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => false,
            'email' => 'active@example.com',
        ]);

        $this->actingAs($user);

        // Deactivate the user server-side (e.g. by another admin)
        $user->active = false;
        $user->save();

        // The next authenticated request should be rejected by EnsureUserIsActive
        $response = $this->get(route('profile.show'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_deactivated_user_subsequent_request_is_redirected_to_login()
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => false,
            'email' => 'deact@example.com',
        ]);

        $this->actingAs($user);
        $user->active = false;
        $user->save();

        // First request triggers logout
        $this->get(route('profile.show'));

        // Second request should still see them as guest
        $secondResponse = $this->get(route('profile.show'));

        $this->assertGuest();
        $secondResponse->assertRedirect(route('login'));
    }

    public function test_session_is_no_longer_authenticated_after_deactivation()
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => false,
            'email' => 'session@example.com',
        ]);

        $this->actingAs($user);
        $user->active = false;
        $user->save();

        $this->get(route('profile.show'));

        $this->assertGuest();
    }
}
