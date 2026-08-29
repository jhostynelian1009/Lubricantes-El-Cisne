<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::ADMIN,
            'active' => true,
            'must_change_password' => false,
        ], $attributes));
    }

    /** Throttle key mirrors LoginController: email|ip */
    private function throttleKey(string $email): string
    {
        return strtolower($email) . '|127.0.0.1';
    }

    public function test_active_user_can_login_with_valid_credentials()
    {
        $user = $this->makeUser(['email' => 'valid@example.com', 'password' => bcrypt('secret1234')]);

        $response = $this->post(route('login'), ['email' => 'valid@example.com', 'password' => 'secret1234']);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_session_is_regenerated_on_successful_login()
    {
        $this->makeUser(['email' => 'regen@example.com', 'password' => bcrypt('secret1234')]);

        $sessionBefore = session()->getId();
        $this->post(route('login'), ['email' => 'regen@example.com', 'password' => 'secret1234']);
        $sessionAfter = session()->getId();

        // Laravel regenerates the session on login; the IDs must differ
        $this->assertNotEquals($sessionBefore, $sessionAfter);
    }

    public function test_last_login_at_is_updated_on_successful_login()
    {
        $user = $this->makeUser(['email' => 'ts@example.com', 'password' => bcrypt('secret1234')]);
        $this->assertNull($user->last_login_at);

        $this->post(route('login'), ['email' => 'ts@example.com', 'password' => 'secret1234']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_invalid_credentials_shows_generic_error()
    {
        $this->makeUser(['email' => 'err@example.com', 'password' => bcrypt('correct')]);

        $response = $this->post(route('login'), ['email' => 'err@example.com', 'password' => 'wrong']);

        $response->assertSessionHasErrors(['email' => 'Estas credenciales no coinciden con nuestros registros.']);
        $this->assertGuest();
    }

    public function test_nonexistent_email_shows_same_generic_error()
    {
        $response = $this->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'anything']);

        $response->assertSessionHasErrors(['email' => 'Estas credenciales no coinciden con nuestros registros.']);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login()
    {
        $this->makeUser(['email' => 'inactive@example.com', 'active' => false, 'password' => bcrypt('secret1234')]);

        $response = $this->post(route('login'), ['email' => 'inactive@example.com', 'password' => 'secret1234']);

        $response->assertSessionHasErrors(['email' => 'Su cuenta está desactivada.']);
        $this->assertGuest();
    }

    public function test_rate_limiter_blocks_after_five_failed_attempts()
    {
        $email = 'blocked@example.com';
        RateLimiter::clear($this->throttleKey($email));

        // Five failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), ['email' => $email, 'password' => 'wrong']);
        }

        // Sixth attempt must be rejected by rate limiter
        $response = $this->post(route('login'), ['email' => $email, 'password' => 'wrong']);

        $response->assertSessionHasErrors(['email' => 'Demasiados intentos. Por favor, espere unos minutos antes de volver a intentarlo.']);
        $this->assertGuest();
    }

    public function test_rate_limiter_is_cleared_after_successful_login()
    {
        $email = 'reset@example.com';
        $key = $this->throttleKey($email);
        RateLimiter::clear($key);

        $user = $this->makeUser(['email' => $email, 'password' => bcrypt('goodpass1234')]);

        // Four failed attempts (below limit)
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('login'), ['email' => $email, 'password' => 'wrong']);
        }

        // Successful login must clear the limiter
        $response = $this->post(route('login'), ['email' => $email, 'password' => 'goodpass1234']);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
    }

    public function test_logout_invalidates_session_and_redirects()
    {
        $user = $this->makeUser(['email' => 'bye@example.com', 'password' => bcrypt('secret1234')]);
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_logout_regenerates_csrf_token()
    {
        $user = $this->makeUser(['email' => 'csrf@example.com', 'password' => bcrypt('secret1234')]);
        $this->actingAs($user);

        $tokenBefore = csrf_token();
        $this->post(route('logout'));
        $tokenAfter = csrf_token();

        $this->assertNotEquals($tokenBefore, $tokenAfter);
    }

    public function test_register_get_route_returns_404()
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    public function test_register_post_route_returns_404()
    {
        $response = $this->post('/register', []);
        $response->assertStatus(404);
    }
}
