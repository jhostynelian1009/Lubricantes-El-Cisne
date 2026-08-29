<?php

namespace Tests\Feature\Profile;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => false,
        ], $attributes));
    }

    public function test_user_can_update_name_and_email()
    {
        $user = $this->makeAdmin(['email' => 'profile@example.com', 'name' => 'Original']);
        $this->actingAs($user);

        $response = $this->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('profile.show'));
        $this->assertEquals('Updated Name', $user->fresh()->name);
        $this->assertEquals('updated@example.com', $user->fresh()->email);
    }

    public function test_user_cannot_use_duplicate_email()
    {
        $other = $this->makeAdmin(['email' => 'taken@example.com']);
        $user = $this->makeAdmin(['email' => 'mine@example.com']);
        $this->actingAs($user);

        $response = $this->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('mine@example.com', $user->fresh()->email);
    }

    public function test_user_can_change_password_with_correct_current_password()
    {
        $user = $this->makeAdmin(['email' => 'pwchange@example.com', 'password' => bcrypt('currentPass1')]);
        $this->actingAs($user);

        $response = $this->put(route('profile.password'), [
            'current_password' => 'currentPass1',
            'password' => 'newSecurePass1',
            'password_confirmation' => 'newSecurePass1',
        ]);

        $response->assertRedirect(route('profile.show'));
        $this->assertTrue(password_verify('newSecurePass1', $user->fresh()->password));
    }

    public function test_password_change_fails_with_incorrect_current_password()
    {
        $user = $this->makeAdmin(['email' => 'wrongpw@example.com', 'password' => bcrypt('realPass123')]);
        $this->actingAs($user);

        $response = $this->put(route('profile.password'), [
            'current_password' => 'wrongPassword',
            'password' => 'newSecurePass1',
            'password_confirmation' => 'newSecurePass1',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(password_verify('realPass123', $user->fresh()->password));
    }

    public function test_profile_update_cannot_change_role_active_or_permissions()
    {
        $user = $this->makeAdmin(['email' => 'locked@example.com', 'role' => UserRole::EMPLOYEE, 'active' => true]);
        $this->actingAs($user);

        $this->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::ADMIN->value,
            'active' => false,
            'permissions' => ['users.manage'],
        ]);

        $updated = $user->fresh();
        $this->assertEquals(UserRole::EMPLOYEE, $updated->role);
        $this->assertTrue($updated->active);
        $this->assertCount(0, $updated->permissions);
    }
}
