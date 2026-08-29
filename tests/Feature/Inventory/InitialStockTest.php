<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialStockTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => UserRole::ADMIN,
            'active' => true,
        ]);
    }

    private function createEmployeeWithPermissions(array $permissionKeys): User
    {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'active' => true,
        ]);

        foreach ($permissionKeys as $key) {
            $permission = Permission::firstOrCreate([
                'key' => $key,
                'name' => 'Permission ' . $key,
                'assignable_to_employee' => true,
            ]);
            $user->permissions()->attach($permission);
        }

        return $user;
    }

    public function test_initial_stock_requires_inventory_adjust_permission()
    {
        // Employee with products.manage but WITHOUT inventory.adjust
        $employee = $this->createEmployeeWithPermissions(['products.manage']);
        $this->actingAs($employee);

        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        $response = $this->post(route('products.initial-stock', $product), [
            'quantity' => '20.000',
            'unit_cost' => '10.00',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_inventory_adjust_permission_can_execute_initial_stock()
    {
        $employee = $this->createEmployeeWithPermissions(['inventory.adjust']);
        $this->actingAs($employee);

        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        $response = $this->post(route('products.initial-stock', $product), [
            'quantity' => '25.000',
            'unit_cost' => '12.50',
            'reason' => 'Carga inicial en bodegas de San Lorenzo',
        ]);

        $response->assertRedirect(route('products.show', $product));
        $this->assertEquals('25.000', (string)$product->fresh()->current_stock);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'initial_adjustment',
            'quantity_delta' => '25.000',
            'quantity_before' => '0.000',
            'quantity_after' => '25.000',
            'created_by' => $employee->id,
        ]);
    }

    public function test_initial_stock_can_only_be_executed_once()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        // First attempt - Success
        $response1 = $this->post(route('products.initial-stock', $product), [
            'quantity' => '10.000',
        ]);
        $response1->assertRedirect(route('products.show', $product));

        // Second attempt - Rejected
        $response2 = $this->post(route('products.initial-stock', $product), [
            'quantity' => '15.000',
        ]);

        $response2->assertSessionHasErrors(['quantity']);
        $this->assertEquals('10.000', (string)$product->fresh()->current_stock);
        $this->assertEquals(1, $product->inventoryMovements()->count());
    }

    public function test_sku_becomes_locked_after_initial_stock_movement()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create(['sku' => 'ORIGINAL-SKU', 'current_stock' => '0.000', 'active' => true]);

        // Execute initial stock
        $this->post(route('products.initial-stock', $product), ['quantity' => '5.000']);

        // Attempting to change SKU via update request
        $response = $this->put(route('products.update', $product), [
            'category_id' => $product->category_id,
            'sku' => 'MODIFIED-SKU',
            'name' => $product->name,
            'unit' => $product->unit,
            'minimum_stock' => $product->minimum_stock,
            'sale_price' => $product->sale_price,
        ]);

        $response->assertSessionHasErrors(['sku']);
        $this->assertEquals('ORIGINAL-SKU', $product->fresh()->sku);
    }

    public function test_initial_stock_rejects_zero_quantity()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        $response = $this->post(route('products.initial-stock', $product), [
            'quantity' => '0.000',
            'unit_cost' => '10.00',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertEquals('0.000', (string)$product->fresh()->current_stock);
        $this->assertEquals(0, \App\Models\InventoryMovement::count());
    }

    public function test_repeated_initial_stock_request_does_not_change_stock_or_duplicate_movement()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        // Primera carga válida
        $response1 = $this->post(route('products.initial-stock', $product), [
            'quantity' => '20.000',
            'unit_cost' => '15.00',
        ]);
        $response1->assertRedirect(route('products.show', $product));

        // Segunda carga rechazada
        $response2 = $this->post(route('products.initial-stock', $product), [
            'quantity' => '30.000',
            'unit_cost' => '18.00',
        ]);

        $response2->assertSessionHasErrors(['quantity']);
        $this->assertEquals('20.000', (string)$product->fresh()->current_stock);
        $this->assertEquals(1, \App\Models\InventoryMovement::count());
    }
}
