<?php

namespace Tests\Feature\Catalogs;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
        ]);
    }

    private function createEmployeeWithPermission(?string $permissionKey = 'customers.manage'): User
    {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'active' => true,
        ]);

        if ($permissionKey) {
            $permission = Permission::firstOrCreate([
                'key' => $permissionKey,
                'name' => 'Permission ' . $permissionKey,
                'assignable_to_employee' => true,
            ]);
            $user->permissions()->attach($permission);
        }

        return $user;
    }

    public function test_authorized_crud_for_customers()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // Index
        $response = $this->get(route('customers.index'));
        $response->assertStatus(200);

        // Store
        $response = $this->post(route('customers.store'), [
            'name' => '  Juan  Carlos  Pérez  ',
            'identification' => '0801234567',
            'phone' => '0991234567',
            'email' => 'JUAN.PEREZ@GMAIL.COM',
            'address' => 'Barrio San Martín, San Lorenzo',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Juan Carlos Pérez',
            'identification' => '0801234567',
            'phone' => '0991234567',
            'email' => 'juan.perez@gmail.com',
            'address' => 'Barrio San Martín, San Lorenzo',
            'active' => true,
        ]);

        $customer = Customer::where('identification', '0801234567')->first();

        // Show
        $response = $this->get(route('customers.show', $customer));
        $response->assertStatus(200);

        // Update
        $response = $this->put(route('customers.update', $customer), [
            'name' => 'Juan Carlos Pérez Andrade',
            'identification' => '0801234567',
            'phone' => '0997654321',
            'email' => 'juan.perez.new@gmail.com',
            'address' => 'Barrio Las Palmeras',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Juan Carlos Pérez Andrade',
            'phone' => '0997654321',
        ]);
    }

    public function test_only_name_is_required_for_customer()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('customers.store'), [
            'name' => 'Cliente Ocasional',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Cliente Ocasional',
            'identification' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
        ]);
    }

    public function test_identification_is_unique_when_provided()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Customer::factory()->create(['identification' => '0809876543']);

        $response = $this->post(route('customers.store'), [
            'name' => 'Cliente Duplicado',
            'identification' => '0809876543',
        ]);

        $response->assertSessionHasErrors(['identification']);
    }

    public function test_identifications_and_phones_preserve_leading_zeros()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('customers.store'), [
            'name' => 'María Caicedo',
            'identification' => '0800012345',
            'phone' => '0900054321',
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'María Caicedo',
            'identification' => '0800012345',
            'phone' => '0900054321',
        ]);
    }

    public function test_customer_activation_and_deactivation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $customer = Customer::factory()->create(['active' => true]);

        $this->post(route('customers.toggle-status', $customer));
        $this->assertFalse($customer->fresh()->active);

        $this->post(route('customers.toggle-status', $customer));
        $this->assertTrue($customer->fresh()->active);
    }

    public function test_physical_deletion_route_does_not_exist()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $customer = Customer::factory()->create();

        $response = $this->delete("/customers/{$customer->id}");
        $response->assertStatus(405);
    }

    public function test_customer_data_is_normalized()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('customers.store'), [
            'name' => '   Pedro   Pablo   Quintero   ',
            'identification' => '  0801122334  ',
            'phone' => '  0981122334  ',
            'email' => '  PEDRO.QUINTERO@CORREO.EC  ',
            'address' => '   ', // Empty string converted to null
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Pedro Pablo Quintero',
            'identification' => '0801122334', // leading zeros preserved
            'phone' => '0981122334', // leading zeros preserved
            'email' => 'pedro.quintero@correo.ec', // lowercased
            'address' => null, // empty string converted to null
        ]);
    }

    public function test_customer_search_works_across_name_identification_phone_and_email()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Customer::factory()->create(['name' => 'Carlos Mendoza', 'identification' => '0801112233']);
        Customer::factory()->create(['name' => 'Ana Lucía Torres', 'phone' => '0994455667']);
        Customer::factory()->create(['name' => 'Roberto Gómez', 'email' => 'roberto@gomez.ec']);

        // Search by identification
        $response = $this->get(route('customers.index', ['search' => '0801112233']));
        $response->assertSee('Carlos Mendoza');
        $response->assertDontSee('Ana Lucía Torres');

        // Search by phone
        $response = $this->get(route('customers.index', ['search' => '0994455667']));
        $response->assertSee('Ana Lucía Torres');

        // Search by email
        $response = $this->get(route('customers.index', ['search' => 'roberto@gomez.ec']));
        $response->assertSee('Roberto Gómez');
    }

    public function test_customer_status_filter_separates_active_and_inactive_records()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Customer::factory()->create(['name' => 'Cliente Activo Ramírez', 'active' => true]);
        Customer::factory()->create(['name' => 'Cliente Inactivo Ramírez', 'active' => false]);

        $response = $this->get(route('customers.index', ['search' => 'Ramírez', 'status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('Cliente Activo Ramírez');
        $response->assertDontSee('Cliente Inactivo Ramírez');

        $response = $this->get(route('customers.index', ['search' => 'Ramírez', 'status' => 'inactive']));
        $response->assertStatus(200);
        $response->assertSee('Cliente Inactivo Ramírez');
        $response->assertDontSee('Cliente Activo Ramírez');
    }

    public function test_customer_index_is_paginated_and_preserves_filters()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        for ($i = 1; $i <= 20; $i++) {
            Customer::factory()->create([
                'name' => "Cliente Frecuente {$i}",
                'active' => true,
            ]);
        }

        $response = $this->get(route('customers.index', [
            'search' => 'Cliente Frecuente',
            'status' => 'active',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $customers = $response->viewData('customers');
        $this->assertInstanceOf(LengthAwarePaginator::class, $customers);
        $this->assertEquals(2, $customers->currentPage());

        $content = $response->getContent();
        $this->assertTrue(str_contains($content, 'search=Cliente%20Frecuente') || str_contains($content, 'search=Cliente+Frecuente'));
        $this->assertStringContainsString('status=active', $content);
    }

    public function test_active_scope_excludes_inactive_customers()
    {
        Customer::factory()->create(['name' => 'Cliente Activo', 'active' => true]);
        Customer::factory()->create(['name' => 'Cliente Inactivo', 'active' => false]);

        $activeCustomers = Customer::active()->get();

        $this->assertCount(1, $activeCustomers);
        $this->assertEquals('Cliente Activo', $activeCustomers->first()->name);
    }

    public function test_multiple_customers_can_have_null_identification()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->post(route('customers.store'), ['name' => 'Cliente Sin ID 1', 'identification' => '']);
        $this->post(route('customers.store'), ['name' => 'Cliente Sin ID 2', 'identification' => null]);

        $this->assertDatabaseHas('customers', ['name' => 'Cliente Sin ID 1', 'identification' => null]);
        $this->assertDatabaseHas('customers', ['name' => 'Cliente Sin ID 2', 'identification' => null]);
    }

    public function test_customer_validation_failure_shows_errors_preserves_input_and_does_not_persist_invalid_record()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('customers.store'), [
            'name' => 'Cliente Seguro El Cisne',
            'email' => 'correo-invalido-sin-arroba',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasInput('name', 'Cliente Seguro El Cisne');
        $this->assertEquals(0, Customer::where('name', 'Cliente Seguro El Cisne')->count());
    }
}
