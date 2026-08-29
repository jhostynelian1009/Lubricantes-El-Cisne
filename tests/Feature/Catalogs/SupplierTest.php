<?php

namespace Tests\Feature\Catalogs;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
        ]);
    }

    private function createEmployeeWithPermission(?string $permissionKey = 'suppliers.manage'): User
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

    public function test_authorized_crud_for_suppliers()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // Index
        $response = $this->get(route('suppliers.index'));
        $response->assertStatus(200);

        // Store
        $response = $this->post(route('suppliers.store'), [
            'name' => '  Distribuidora  Shell  Ecuador  ',
            'identification' => '1790012345001',
            'phone' => '022998877',
            'email' => 'VENTAS@SHELL.EC',
            'address' => 'Av. Amazonas N34-12',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Distribuidora Shell Ecuador',
            'identification' => '1790012345001',
            'phone' => '022998877',
            'email' => 'ventas@shell.ec',
            'address' => 'Av. Amazonas N34-12',
            'active' => true,
        ]);

        $supplier = Supplier::where('identification', '1790012345001')->first();

        // Show
        $response = $this->get(route('suppliers.show', $supplier));
        $response->assertStatus(200);

        // Update
        $response = $this->put(route('suppliers.update', $supplier), [
            'name' => 'Shell Ecuador S.A.',
            'identification' => '1790012345001',
            'phone' => '0998877665',
            'email' => 'contacto@shell.ec',
            'address' => 'Av. de los Granados E12-45',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Shell Ecuador S.A.',
            'phone' => '0998877665',
        ]);
    }

    public function test_optional_fields_can_be_null()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('suppliers.store'), [
            'name' => 'Proveedor Solo Nombre',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Proveedor Solo Nombre',
            'identification' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
        ]);
    }

    public function test_identification_is_unique_when_present()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Supplier::factory()->create(['identification' => '1791234567001']);

        $response = $this->post(route('suppliers.store'), [
            'name' => 'Otro Proveedor',
            'identification' => '1791234567001',
        ]);

        $response->assertSessionHasErrors(['identification']);
    }

    public function test_multiple_suppliers_can_have_null_identification()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->post(route('suppliers.store'), ['name' => 'Proveedor A', 'identification' => '']);
        $this->post(route('suppliers.store'), ['name' => 'Proveedor B', 'identification' => null]);

        $this->assertDatabaseHas('suppliers', ['name' => 'Proveedor A', 'identification' => null]);
        $this->assertDatabaseHas('suppliers', ['name' => 'Proveedor B', 'identification' => null]);
    }

    public function test_invalid_email_is_rejected()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('suppliers.store'), [
            'name' => 'Proveedor Email Inválido',
            'email' => 'correo-invalido-sin-arroba',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_normalization_lowercases_email_and_preserves_leading_zeros()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('suppliers.store'), [
            'name' => '  Importadora   Móvil  ',
            'identification' => '0102030405001',
            'phone' => '0987654321',
            'email' => '  INFO@MOVIL-EC.COM  ',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Importadora Móvil',
            'identification' => '0102030405001',
            'phone' => '0987654321',
            'email' => 'info@movil-ec.com',
        ]);
    }

    public function test_combined_search_works_across_name_identification_phone_and_email()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        Supplier::factory()->create(['name' => 'Distribuidora Castrol', 'identification' => '1791112223001']);
        Supplier::factory()->create(['name' => 'Lubricantes Havoline', 'phone' => '0991122334']);
        Supplier::factory()->create(['name' => 'Aceites Valvoline', 'email' => 'ventas@valvoline.ec']);

        // Search by identification
        $response = $this->get(route('suppliers.index', ['search' => '1791112223001']));
        $response->assertSee('Distribuidora Castrol');
        $response->assertDontSee('Lubricantes Havoline');

        // Search by phone
        $response = $this->get(route('suppliers.index', ['search' => '0991122334']));
        $response->assertSee('Lubricantes Havoline');

        // Search by email
        $response = $this->get(route('suppliers.index', ['search' => 'valvoline.ec']));
        $response->assertSee('Aceites Valvoline');
    }

    public function test_supplier_activation_and_deactivation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $supplier = Supplier::factory()->create(['active' => true]);

        $this->post(route('suppliers.toggle-status', $supplier));
        $this->assertFalse($supplier->fresh()->active);

        $this->post(route('suppliers.toggle-status', $supplier));
        $this->assertTrue($supplier->fresh()->active);
    }

    public function test_physical_deletion_route_does_not_exist()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $supplier = Supplier::factory()->create();

        $response = $this->delete("/suppliers/{$supplier->id}");
        $response->assertStatus(405);
    }

    public function test_supplier_status_filter_separates_active_and_inactive_records()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // Matching active supplier
        Supplier::factory()->create([
            'name' => 'Distribuidora Activa Shell',
            'identification' => '1799999999001',
            'active' => true,
        ]);

        // Matching inactive supplier
        Supplier::factory()->create([
            'name' => 'Distribuidora Inactiva Shell',
            'identification' => '1788888888001',
            'active' => false,
        ]);

        // Search with status=active
        $response = $this->get(route('suppliers.index', [
            'search' => 'Shell',
            'status' => 'active',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Distribuidora Activa Shell');
        $response->assertDontSee('Distribuidora Inactiva Shell');

        // Search with status=inactive
        $response = $this->get(route('suppliers.index', [
            'search' => 'Shell',
            'status' => 'inactive',
        ]));
        $response->assertStatus(200);
        $response->assertSee('Distribuidora Inactiva Shell');
        $response->assertDontSee('Distribuidora Activa Shell');
    }

    public function test_supplier_index_is_paginated_and_preserves_filters()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        for ($i = 1; $i <= 20; $i++) {
            Supplier::factory()->create([
                'name' => "Proveedor Movil {$i}",
                'active' => true,
            ]);
        }

        $response = $this->get(route('suppliers.index', [
            'search' => 'Proveedor Movil',
            'status' => 'active',
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $suppliers = $response->viewData('suppliers');
        $this->assertInstanceOf(LengthAwarePaginator::class, $suppliers);
        $this->assertEquals(2, $suppliers->currentPage());

        $content = $response->getContent();
        $this->assertTrue(str_contains($content, 'search=Proveedor%20Movil') || str_contains($content, 'search=Proveedor+Movil'));
        $this->assertStringContainsString('status=active', $content);
    }

    public function test_active_scope_excludes_inactive_suppliers()
    {
        Supplier::factory()->create(['name' => 'Proveedor Activo', 'active' => true]);
        Supplier::factory()->create(['name' => 'Proveedor Inactivo', 'active' => false]);

        $activeSuppliers = Supplier::active()->get();

        $this->assertCount(1, $activeSuppliers);
        $this->assertEquals('Proveedor Activo', $activeSuppliers->first()->name);
    }

    public function test_supplier_validation_failure_shows_errors_preserves_input_and_does_not_persist_invalid_record()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('suppliers.store'), [
            'name' => 'Proveedor Seguro El Cisne',
            'email' => 'correo-invalido-sin-arroba',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasInput('name', 'Proveedor Seguro El Cisne');
        $this->assertEquals(0, Supplier::where('name', 'Proveedor Seguro El Cisne')->count());
    }
}
