<?php

namespace Tests\Feature\Catalogs;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossCatalogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createEmployeeWithPermission(string $permissionKey): User
    {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'active' => true,
        ]);

        $permission = Permission::firstOrCreate([
            'key' => $permissionKey,
            'name' => 'Permission ' . $permissionKey,
            'assignable_to_employee' => true,
        ]);
        $user->permissions()->attach($permission);

        return $user;
    }

    public function test_categories_manage_permission_does_not_grant_suppliers_or_customers()
    {
        $user = $this->createEmployeeWithPermission('categories.manage');
        $this->actingAs($user);

        // Can access categories
        $this->get(route('categories.index'))->assertStatus(200);

        // Cannot access suppliers or customers
        $this->get(route('suppliers.index'))->assertStatus(403);
        $this->get(route('customers.index'))->assertStatus(403);
    }

    public function test_suppliers_manage_permission_does_not_grant_categories_or_customers()
    {
        $user = $this->createEmployeeWithPermission('suppliers.manage');
        $this->actingAs($user);

        // Can access suppliers
        $this->get(route('suppliers.index'))->assertStatus(200);

        // Cannot access categories or customers
        $this->get(route('categories.index'))->assertStatus(403);
        $this->get(route('customers.index'))->assertStatus(403);
    }

    public function test_customers_manage_permission_does_not_grant_categories_or_suppliers()
    {
        $user = $this->createEmployeeWithPermission('customers.manage');
        $this->actingAs($user);

        // Can access customers
        $this->get(route('customers.index'))->assertStatus(200);

        // Cannot access categories or suppliers
        $this->get(route('categories.index'))->assertStatus(403);
        $this->get(route('suppliers.index'))->assertStatus(403);
    }

    public function test_manipulating_ids_in_url_does_not_bypass_policy()
    {
        $categoryUser = $this->createEmployeeWithPermission('categories.manage');
        $supplier = Supplier::factory()->create();
        $customer = Customer::factory()->create();

        $this->actingAs($categoryUser);

        $this->get(route('suppliers.show', $supplier))->assertStatus(403);
        $this->get(route('suppliers.edit', $supplier))->assertStatus(403);
        $this->get(route('customers.show', $customer))->assertStatus(403);
        $this->get(route('customers.edit', $customer))->assertStatus(403);
    }

    public function test_inactive_user_cannot_access_any_catalog()
    {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'active' => false,
        ]);

        $permission = Permission::firstOrCreate([
            'key' => 'categories.manage',
            'name' => 'Manage Categories',
            'assignable_to_employee' => true,
        ]);
        $user->permissions()->attach($permission);

        $this->actingAs($user);

        $this->get(route('categories.index'))->assertRedirect(route('login'));
    }
}
