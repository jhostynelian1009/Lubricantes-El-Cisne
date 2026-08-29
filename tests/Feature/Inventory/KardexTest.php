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

class KardexTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => UserRole::ADMIN, 'active' => true]);
    }

    private function createEmployeeWithPermission(string $permissionKey): User
    {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $permission = Permission::firstOrCreate(['key' => $permissionKey, 'name' => 'Permission ' . $permissionKey, 'assignable_to_employee' => true]);
        $user->permissions()->attach($permission);
        return $user;
    }

    public function test_inventory_view_es_obligatorio()
    {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $this->actingAs($employee);

        $response = $this->get(route('inventory.movements.index'));
        $response->assertStatus(403);

        $response = $this->get(route('inventory.kardex.form'));
        $response->assertStatus(403);

        $viewUser = $this->createEmployeeWithPermission('inventory.view');
        $this->actingAs($viewUser);
        $response = $this->get(route('inventory.movements.index'));
        $response->assertStatus(200);
    }

    public function test_filtros_y_paginacion_en_movimientos_conservan_parametros()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $p1 = Product::factory()->create(['active' => true]);
        $p2 = Product::factory()->create(['active' => true]);

        $adj = InventoryAdjustment::create([
            'product_id' => $p1->id,
            'type' => 'adjustment_in',
            'quantity' => '1.000',
            'reason' => 'Ajuste inicial',
            'created_by' => $admin->id,
        ]);

        for ($i = 0; $i < 25; $i++) {
            InventoryMovement::create([
                'product_id' => $p1->id,
                'type' => 'adjustment_in',
                'quantity_before' => (string) $i,
                'quantity_delta' => '1.000',
                'quantity_after' => (string) ($i + 1),
                'created_by' => $admin->id,
                'reference_type' => InventoryAdjustment::class,
                'reference_id' => $adj->id,
                'reason' => 'Ajuste test',
            ]);
        }

        // Movimiento de otro producto para comprobar aislamiento del filtro
        InventoryMovement::create([
            'product_id' => $p2->id,
            'type' => 'adjustment_in',
            'quantity_before' => '0.000',
            'quantity_delta' => '5.000',
            'quantity_after' => '5.000',
            'created_by' => $admin->id,
            'reason' => 'Otro producto',
        ]);

        $response = $this->get(route('inventory.movements.index', [
            'product_id' => $p1->id,
            'type' => 'adjustment_in',
            'created_by' => $admin->id,
            'reference_type' => InventoryAdjustment::class,
            'reference_id' => $adj->id,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('product_id=' . $p1->id, $content);
        $this->assertStringContainsString('type=adjustment_in', $content);
        $this->assertStringContainsString('reference_type=' . urlencode(InventoryAdjustment::class), $content);
        $this->assertStringContainsString('reference_id=' . $adj->id, $content);

        $movements = $response->viewData('movements');
        $this->assertCount(5, $movements); // Paginated 20 on page 1, 5 on page 2
        foreach ($movements as $m) {
            $this->assertEquals($p1->id, $m->product_id);
            $this->assertEquals(InventoryAdjustment::class, $m->reference_type);
            $this->assertEquals($adj->id, $m->reference_id);
        }
    }

    public function test_kardex_calcula_saldo_inicial_ordenamiento_y_coincidencia_con_current_stock()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = Product::factory()->create(['current_stock' => '25.000', 'active' => true]);

        // Movimientos anteriores a la fecha de inicio
        InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'initial_adjustment',
            'quantity_before' => '0.000',
            'quantity_delta' => '10.000',
            'quantity_after' => '10.000',
            'created_by' => $admin->id,
            'reason' => 'Carga Inicial',
            'created_at' => now()->subDays(10),
        ]);

        InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'adjustment_in',
            'quantity_before' => '10.000',
            'quantity_delta' => '5.000',
            'quantity_after' => '15.000',
            'created_by' => $admin->id,
            'reason' => 'Sobrante',
            'created_at' => now()->subDays(5),
        ]);

        // Movimientos dentro del rango con la misma fecha pero IDs secuenciales para validar ordenamiento estable
        $now = now();
        $m1 = InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'adjustment_out',
            'quantity_before' => '15.000',
            'quantity_delta' => '-2.000',
            'quantity_after' => '13.000',
            'created_by' => $admin->id,
            'reason' => 'Caducado',
            'created_at' => $now,
        ]);

        $m2 = InventoryMovement::create([
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity_before' => '13.000',
            'quantity_delta' => '12.000',
            'quantity_after' => '25.000',
            'created_by' => $admin->id,
            'reason' => 'Compra',
            'created_at' => $now,
        ]);

        $response = $this->get(route('inventory.kardex.report', [
            'product_id' => $product->id,
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);

        $initialBalance = $response->viewData('initialBalance');
        $this->assertEquals('15.000', $initialBalance);

        $movements = $response->viewData('movements');
        $this->assertCount(2, $movements);

        // Validar orden estable por created_at e id asc
        $this->assertEquals($m1->id, $movements[0]->id);
        $this->assertEquals('-2.000', (string) $movements[0]->quantity_delta);

        $this->assertEquals($m2->id, $movements[1]->id);
        $this->assertEquals('12.000', (string) $movements[1]->quantity_delta);

        // Calcular saldo final desde el saldo inicial sumando deltas del período
        $calculatedFinalBalance = (float) $initialBalance;
        foreach ($movements as $mov) {
            $calculatedFinalBalance += (float) $mov->quantity_delta;
        }

        $this->assertEquals((float) $product->fresh()->current_stock, $calculatedFinalBalance);
        $this->assertEquals(25.0, $calculatedFinalBalance);
    }
}
