<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\InventoryAdjustment;
use App\Models\InventoryMovement;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => UserRole::ADMIN, 'active' => true]);
    }

    private function createEmployeeWithPermission(string $permissionKey): User
    {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $permission = Permission::firstOrCreate(['key' => $permissionKey, 'name' => 'Perm ISO', 'assignable_to_employee' => true]);
        $user->permissions()->attach($permission);
        return $user;
    }

    public function test_incremento_autorizado_crea_saldo_y_movimiento_exactos()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = Product::factory()->create(['current_stock' => '10.000', 'active' => true]);

        $response = $this->post(route('inventory.adjustments.store'), [
            'product_id' => $product->id,
            'type' => 'adjustment_in',
            'quantity' => '5.500',
            'reason' => 'Ajuste sobrante de inventario',
        ]);

        $response->assertRedirect(route('products.show', $product));

        $product->refresh();
        $this->assertEquals('15.500', (string) $product->current_stock);

        $adj = InventoryAdjustment::first();
        $this->assertEquals('adjustment_in', $adj->type);
        $this->assertEquals('5.500', (string) $adj->quantity);
        $this->assertEquals('Ajuste sobrante de inventario', $adj->reason);

        $mov = InventoryMovement::first();
        $this->assertEquals('10.000', (string) $mov->quantity_before);
        $this->assertEquals('5.500', (string) $mov->quantity_delta);
        $this->assertEquals('15.500', (string) $mov->quantity_after);
        $this->assertEquals($admin->id, $mov->created_by);
        $this->assertEquals(InventoryAdjustment::class, $mov->reference_type);
        $this->assertEquals($adj->id, $mov->reference_id);
    }

    public function test_disminucion_autorizada_crea_saldo_y_movimiento_exactos()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = Product::factory()->create(['current_stock' => '20.000', 'active' => true]);

        $this->post(route('inventory.adjustments.store'), [
            'product_id' => $product->id,
            'type' => 'adjustment_out',
            'quantity' => '5.000',
            'reason' => 'Producto caducado',
        ]);

        $product->refresh();
        $this->assertEquals('15.000', (string) $product->current_stock);

        $mov = InventoryMovement::first();
        $this->assertEquals('20.000', (string) $mov->quantity_before);
        $this->assertEquals('-5.000', (string) $mov->quantity_delta);
        $this->assertEquals('15.000', (string) $mov->quantity_after);
    }

    public function test_disminucion_que_produciria_stock_negativo_no_modifica_registros()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = Product::factory()->create(['current_stock' => '2.000', 'active' => true]);

        try {
            $this->post(route('inventory.adjustments.store'), [
                'product_id' => $product->id,
                'type' => 'adjustment_out',
                'quantity' => '5.000',
                'reason' => 'Cantidad superior al saldo',
            ]);
        } catch (\Throwable $e) {
        }

        $this->assertEquals('2.000', (string) $product->fresh()->current_stock);
        $this->assertEquals(0, InventoryAdjustment::count());
        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_motivo_vacio_y_producto_inactivo_son_rechazados()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $pActive = Product::factory()->create(['current_stock' => '20.000', 'active' => true]);
        $pInactive = Product::factory()->create(['current_stock' => '20.000', 'active' => false]);

        // Motivo vacío
        $response = $this->post(route('inventory.adjustments.store'), [
            'product_id' => $pActive->id,
            'type' => 'adjustment_out',
            'quantity' => '5.000',
            'reason' => '   ',
        ]);
        $response->assertSessionHasErrors(['reason']);

        // Producto inactivo
        try {
            $this->post(route('inventory.adjustments.store'), [
                'product_id' => $pInactive->id,
                'type' => 'adjustment_out',
                'quantity' => '5.000',
                'reason' => 'Motivo válido',
            ]);
        } catch (\Throwable $e) {
            $this->assertStringContainsString('inactivo', $e->getMessage());
        }
        $this->assertEquals('20.000', (string) $pInactive->fresh()->current_stock);
    }

    public function test_permisos_y_rutas_separadas()
    {
        $entriesUser = $this->createEmployeeWithPermission('inventory.entries.create');
        $this->actingAs($entriesUser);

        $response = $this->get(route('inventory.adjustments.create'));
        $response->assertStatus(403);

        $adjustUser = $this->createEmployeeWithPermission('inventory.adjust');
        $this->actingAs($adjustUser);

        $response = $this->get(route('inventory.adjustments.create'));
        $response->assertStatus(200);

        $response = $this->get(route('stock-entries.create'));
        $response->assertStatus(403);
    }

    public function test_manipular_saldo_resultante_se_ignora()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = Product::factory()->create(['current_stock' => '10.000', 'active' => true]);

        // Intento de inyectar saldos o deltas falsas en la petición
        $this->post(route('inventory.adjustments.store'), [
            'product_id' => $product->id,
            'type' => 'adjustment_in',
            'quantity' => '5.000',
            'reason' => 'Ajuste',
            'current_stock' => '999.000',
            'quantity_after' => '999.000',
            'resulting_balance' => '999.000',
        ]);

        $product->refresh();
        $this->assertEquals('15.000', (string) $product->current_stock);

        $movement = InventoryMovement::first();
        $this->assertEquals('10.000', (string) $movement->quantity_before);
        $this->assertEquals('5.000', (string) $movement->quantity_delta);
        $this->assertEquals('15.000', (string) $movement->quantity_after);
    }
}
