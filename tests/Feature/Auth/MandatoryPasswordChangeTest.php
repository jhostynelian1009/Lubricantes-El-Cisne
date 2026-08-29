<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MandatoryPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserNeedingChange(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => true,
        ], $attributes));
    }

    public function test_user_with_must_change_password_is_redirected_to_change_form()
    {
        $user = $this->makeUserNeedingChange(['email' => 'redir@example.com', 'password' => bcrypt('secret')]);
        $this->actingAs($user);

        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('password.change'));
    }

    public function test_user_can_access_password_change_form()
    {
        $user = $this->makeUserNeedingChange(['email' => 'form@example.com', 'password' => bcrypt('secret')]);
        $this->actingAs($user);

        $response = $this->get(route('password.change'));

        $response->assertStatus(200);
    }

    public function test_user_can_change_password_and_flag_is_cleared()
    {
        $user = $this->makeUserNeedingChange(['email' => 'change@example.com', 'password' => bcrypt('oldpass123')]);
        $this->actingAs($user);

        $response = $this->post(route('password.update'), [
            'password' => 'newSecurePass1',
            'password_confirmation' => 'newSecurePass1',
        ]);

        $response->assertRedirect('/');
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(password_verify('newSecurePass1', $user->fresh()->password));
    }

    public function test_user_can_logout_without_redirect_loop()
    {
        $user = $this->makeUserNeedingChange(['email' => 'loop@example.com', 'password' => bcrypt('secret')]);
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_restores_access_to_normal_routes_after_password_change()
    {
        $user = $this->makeUserNeedingChange(['email' => 'restore@example.com', 'password' => bcrypt('oldpass123')]);
        $this->actingAs($user);

        $this->post(route('password.update'), [
            'password' => 'newSecurePass1',
            'password_confirmation' => 'newSecurePass1',
        ]);

        // After change, must_change_password = false, normal routes accessible
        $response = $this->get(route('profile.show'));
        $response->assertStatus(200);
    }
}
