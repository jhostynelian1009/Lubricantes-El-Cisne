<?php

namespace Tests\Feature\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_active_admin_with_correct_attributes()
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Test Admin')
            ->expectsQuestion('Correo electrónico', 'newadmin@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'SecurePass1234')
            ->assertExitCode(0);

        $user = User::where('email', 'newadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::ADMIN, $user->role);
        $this->assertTrue($user->active);
        $this->assertFalse($user->must_change_password);
    }

    public function test_command_rejects_duplicate_email()
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'role' => UserRole::ADMIN,
        ]);

        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Another Admin')
            ->expectsQuestion('Correo electrónico', 'duplicate@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'SecurePass1234')
            ->assertExitCode(1);

        $this->assertEquals(1, User::where('email', 'duplicate@example.com')->count());
    }

    public function test_command_rejects_password_shorter_than_12_characters()
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Admin Short')
            ->expectsQuestion('Correo electrónico', 'shortpw@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'short')
            ->assertExitCode(1);

        $this->assertNull(User::where('email', 'shortpw@example.com')->first());
    }

    public function test_command_does_not_output_plain_password()
    {
        $output = $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Safe Admin')
            ->expectsQuestion('Correo electrónico', 'safe@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'MySuperSecret1234');

        // The command output must not contain the raw password
        $output->doesntExpectOutput('MySuperSecret1234');
        $output->assertExitCode(0);
    }

    public function test_created_admin_has_role_admin_active_true_and_must_change_false()
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Attr Admin')
            ->expectsQuestion('Correo electrónico', 'attr@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'StrongPassword99')
            ->assertExitCode(0);

        $user = User::where('email', 'attr@example.com')->firstOrFail();
        $this->assertEquals(UserRole::ADMIN, $user->role);
        $this->assertTrue($user->active);
        $this->assertFalse($user->must_change_password);
    }
}
