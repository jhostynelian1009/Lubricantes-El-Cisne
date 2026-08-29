<?php

namespace Tests\Feature\Users;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
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

    private function makeEmployee(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::EMPLOYEE,
            'active' => true,
            'must_change_password' => false,
        ], $attributes));
    }

    public function test_employee_without_permission_receives_403()
    {
        $employee = $this->makeEmployee(['email' => 'emp@example.com']);
        $this->actingAs($employee);

        $response = $this->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_active_admin_has_access_to_users_index()
    {
        $admin = $this->makeAdmin(['email' => 'admin@example.com']);
        $this->actingAs($admin);

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_inactive_admin_is_not_bypassed_by_gate_before()
    {
        $admin = $this->makeAdmin(['email' => 'inactiveadmin@example.com', 'active' => false]);
        $this->actingAs($admin);

        // Middleware should disconnect; Gate::before must not grant access to inactive admin
        $response = $this->get(route('users.index'));

        // Either redirected to login (middleware) or 403 (gate) — must not be 200
        $this->assertNotEquals(200, $response->getStatusCode());
    }

    public function test_manipulated_request_cannot_grant_users_manage_to_employee()
    {
        $admin = $this->makeAdmin(['email' => 'mgr@example.com']);
        $employee = $this->makeEmployee(['email' => 'target@example.com']);
        $this->actingAs($admin);

        // Attempt to assign non-assignable permission via update
        $this->put(route('users.update', $employee), [
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => UserRole::EMPLOYEE->value,
            'active' => true,
            'permissions' => ['users.manage'],
        ]);

        $employee->load('permissions');
        $this->assertFalse($employee->permissions->contains('key', 'users.manage'));
    }

    public function test_manipulated_request_cannot_grant_audit_view_to_employee()
    {
        $admin = $this->makeAdmin(['email' => 'mgr2@example.com']);
        $employee = $this->makeEmployee(['email' => 'target2@example.com']);
        $this->actingAs($admin);

        $this->put(route('users.update', $employee), [
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => UserRole::EMPLOYEE->value,
            'active' => true,
            'permissions' => ['audit.view'],
        ]);

        $employee->load('permissions');
        $this->assertFalse($employee->permissions->contains('key', 'audit.view'));
    }

    public function test_employee_cannot_manage_other_user_by_changing_id_in_url()
    {
        $employee = $this->makeEmployee(['email' => 'sneaky@example.com']);
        $target = $this->makeAdmin(['email' => 'victim@example.com']);
        $this->actingAs($employee);

        $response = $this->get(route('users.edit', $target));

        $response->assertStatus(403);
    }

    public function test_permission_seeder_is_idempotent()
    {
        $this->seed(PermissionSeeder::class);
        $countFirst = \App\Models\Permission::count();

        $this->seed(PermissionSeeder::class);
        $countSecond = \App\Models\Permission::count();

        $this->assertEquals($countFirst, $countSecond);
    }

    public function test_permission_seeder_creates_exactly_13_keys()
    {
        $this->seed(PermissionSeeder::class);

        $this->assertEquals(13, \App\Models\Permission::count());
    }
}
