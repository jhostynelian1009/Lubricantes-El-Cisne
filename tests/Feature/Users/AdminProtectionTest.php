<?php

namespace Tests\Feature\Users;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    /** Helper to create an active admin user. */
    private function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::ADMIN,
            'active' => true,
        ], $attributes));
    }

    public function test_an_admin_cannot_self_deactivate()
    {
        $admin = $this->createAdmin(['email' => 'self@example.com']);
        $this->actingAs($admin);

        $response = $this->post(route('users.toggle-status', $admin), []);

        $response->assertSessionHas('error', 'No puede desactivar su propia cuenta.');
        $this->assertTrue($admin->fresh()->active);
    }

    public function test_cannot_deactivate_the_last_active_admin()
    {
        $targetAdmin = $this->createAdmin(['email' => 'lastadmin@example.com']);

        // Create an active employee and grant users.manage permission so they can act on users
        $employeeActor = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'active' => true,
        ]);
        $permission = Permission::firstOrCreate([
            'key' => 'users.manage',
            'name' => 'Administrar usuarios y permisos',
            'assignable_to_employee' => true,
        ]);
        $employeeActor->permissions()->attach($permission);

        $response = $this->actingAs($employeeActor)
            ->post(route('users.toggle-status', $targetAdmin), []);

        $response->assertSessionHas('error', 'No se puede desactivar al único administrador activo del sistema.');
        $this->assertTrue($targetAdmin->fresh()->active);
    }

    public function test_can_deactivate_one_of_two_active_admins()
    {
        $admin1 = $this->createAdmin(['email' => 'admin1@example.com']);
        $admin2 = $this->createAdmin(['email' => 'admin2@example.com']);
        $this->actingAs($admin1);

        $response = $this->post(route('users.toggle-status', $admin2), []);

        $response->assertSessionHas('success');
        $this->assertFalse($admin2->fresh()->active);
        $this->assertTrue($admin1->fresh()->active);
    }

    public function test_cannot_demote_the_last_active_admin()
    {
        $targetAdmin = $this->createAdmin(['email' => 'onlyadmin@example.com']);
        $this->actingAs($targetAdmin);

        // Self-demotion attempt when single active admin
        $response = $this->put(route('users.update', $targetAdmin), [
            'name'   => $targetAdmin->name,
            'email'  => $targetAdmin->email,
            'role'   => UserRole::EMPLOYEE->value,
            'active' => true,
        ]);

        $response->assertSessionHas('error', 'No se puede desactivar o degradar al único administrador activo del sistema.');
        $this->assertEquals(UserRole::ADMIN, $targetAdmin->fresh()->role);
    }

    public function test_can_demote_when_more_than_one_active_admin_exists()
    {
        $admin1 = $this->createAdmin(['email' => 'admin1d@example.com']);
        $admin2 = $this->createAdmin(['email' => 'admin2d@example.com']);
        $this->actingAs($admin1);

        $response = $this->put(route('users.update', $admin2), [
            'name'   => $admin2->name,
            'email'  => $admin2->email,
            'role'   => UserRole::EMPLOYEE->value,
            'active' => true,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(UserRole::EMPLOYEE, $admin2->fresh()->role);
    }

    public function test_operation_is_atomic_and_rolls_back_on_exception()
    {
        $admin = $this->createAdmin(['email' => 'atomic@example.com']);
        $this->actingAs($admin);

        // role omitted triggers validation error from UpdateUserRequest
        $response = $this->put(route('users.update', $admin), [
            'name'  => $admin->name,
            'email' => $admin->email,
        ]);

        $response->assertSessionHasErrors();
        $this->assertEquals(UserRole::ADMIN, $admin->fresh()->role);
        $this->assertTrue($admin->fresh()->active);
    }
}
