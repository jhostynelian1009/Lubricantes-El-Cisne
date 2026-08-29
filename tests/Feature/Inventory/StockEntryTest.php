<?php

namespace Tests\Feature\Inventory;

use App\Enums\StockEntryStatus;
use App\Enums\UserRole;
use App\Models\InventoryMovement;
use App\Models\Permission;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockEntryDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockEntryTest extends TestCase
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

    public function test_crear_borrador_no_modifica_stock_y_puede_eliminarse()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        $response = $this->post(route('stock-entries.store'), [
            'entry_date' => now()->format('Y-m-d'),
            'details' => [
                ['product_id' => $product->id, 'quantity' => '10.000', 'unit_cost' => '5.00'],
            ],
        ]);

        $entry = StockEntry::first();
        $response->assertRedirect(route('stock-entries.show', $entry));

        $this->assertEquals(StockEntryStatus::DRAFT, $entry->status);
        $this->assertEquals('0.000', (string) $product->fresh()->current_stock);
        $this->assertEquals(0, InventoryMovement::count());

        $this->delete(route('stock-entries.destroy', $entry));
        $this->assertDatabaseMissing('stock_entries', ['id' => $entry->id]);
        $this->assertDatabaseMissing('stock_entry_details', ['stock_entry_id' => $entry->id]);
    }

    public function test_separacion_de_permisos_entre_entradas_y_ajustes()
    {
        $employeeWithoutPerm = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $this->actingAs($employeeWithoutPerm);

        $this->get(route('stock-entries.create'))->assertStatus(403);
        $this->get(route('inventory.adjustments.create'))->assertStatus(403);

        $adjustUser = $this->createEmployeeWithPermission('inventory.adjust');
        $this->actingAs($adjustUser);

        $this->get(route('inventory.adjustments.create'))->assertStatus(200);
        $this->get(route('stock-entries.create'))->assertStatus(403);

        $entriesUser = $this->createEmployeeWithPermission('inventory.entries.create');
        $this->actingAs($entriesUser);

        $this->get(route('stock-entries.create'))->assertStatus(200);
        $this->get(route('inventory.adjustments.create'))->assertStatus(403);
    }

    public function test_confirmar_entrada_multi_linea_incrementa_productos_genera_movimientos_y_actualiza_costos()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $p1 = Product::factory()->create(['current_stock' => '5.000', 'last_cost' => '10.00', 'active' => true]);
        $p2 = Product::factory()->create(['current_stock' => '0.000', 'last_cost' => '0.00', 'active' => true]);

        $entry = StockEntry::create(['entry_date' => now(), 'status' => StockEntryStatus::DRAFT, 'created_by' => $admin->id]);
        StockEntryDetail::create(['stock_entry_id' => $entry->id, 'product_id' => $p1->id, 'product_sku' => $p1->sku, 'product_name' => $p1->name, 'unit' => 'L', 'quantity' => '15.000', 'unit_cost' => '12.00', 'line_total' => '180.00']);
        StockEntryDetail::create(['stock_entry_id' => $entry->id, 'product_id' => $p2->id, 'product_sku' => $p2->sku, 'product_name' => $p2->name, 'unit' => 'L', 'quantity' => '20.000', 'unit_cost' => '8.00', 'line_total' => '160.00']);

        $response = $this->post(route('stock-entries.confirm', $entry));

        $entry->refresh();
        $this->assertEquals(StockEntryStatus::CONFIRMED, $entry->status);
        $this->assertNotNull($entry->number);
        $this->assertEquals($admin->id, $entry->confirmed_by);

        $p1->refresh();
        $this->assertEquals('20.000', (string) $p1->current_stock);
        $this->assertEquals('12.00', (string) $p1->last_cost);

        $p2->refresh();
        $this->assertEquals('20.000', (string) $p2->current_stock);
        $this->assertEquals('8.00', (string) $p2->last_cost);

        $this->assertEquals(2, InventoryMovement::count());
        $m1 = InventoryMovement::where('product_id', $p1->id)->first();
        $this->assertEquals('5.000', (string) $m1->quantity_before);
        $this->assertEquals('15.000', (string) $m1->quantity_delta);
        $this->assertEquals('20.000', (string) $m1->quantity_after);
        $this->assertEquals($admin->id, $m1->created_by);
        $this->assertEquals(StockEntry::class, $m1->reference_type);
        $this->assertEquals($entry->id, $m1->reference_id);
    }

    public function test_rollback_completo_de_entrada_multi_linea()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $p1 = Product::factory()->create(['current_stock' => '5.000', 'last_cost' => '10.00', 'active' => true]);
        $p2 = Product::factory()->create(['current_stock' => '0.000', 'last_cost' => '0.00', 'active' => true]);

        $entry = StockEntry::create(['entry_date' => now(), 'status' => StockEntryStatus::DRAFT, 'created_by' => $admin->id]);
        StockEntryDetail::create(['stock_entry_id' => $entry->id, 'product_id' => $p1->id, 'product_sku' => $p1->sku, 'product_name' => $p1->name, 'unit' => 'L', 'quantity' => '15.000', 'unit_cost' => '12.00', 'line_total' => '180.00']);
        StockEntryDetail::create(['stock_entry_id' => $entry->id, 'product_id' => $p2->id, 'product_sku' => $p2->sku, 'product_name' => $p2->name, 'unit' => 'L', 'quantity' => '20.000', 'unit_cost' => '8.00', 'line_total' => '160.00']);

        // Desactivamos el segundo producto para provocar la falla en la transacción durante la confirmación
        $p2->update(['active' => false]);

        try {
            $this->post(route('stock-entries.confirm', $entry));
        } catch (\Throwable $e) {
        }

        $entry->refresh();
        $p1->refresh();
        $p2->refresh();

        $this->assertEquals(StockEntryStatus::DRAFT, $entry->status);
        $this->assertNull($entry->number);
        $this->assertNull($entry->confirmed_by);
        $this->assertNull($entry->confirmed_at);

        $this->assertEquals('5.000', (string) $p1->current_stock);
        $this->assertEquals('10.00', (string) $p1->last_cost);

        $this->assertEquals('0.000', (string) $p2->current_stock);
        $this->assertEquals('0.00', (string) $p2->last_cost);

        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_confirmacion_repetida_rechazada()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $p1 = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);
        $entry = StockEntry::create(['entry_date' => now(), 'status' => StockEntryStatus::DRAFT, 'created_by' => $admin->id]);
        StockEntryDetail::create(['stock_entry_id' => $entry->id, 'product_id' => $p1->id, 'product_sku' => $p1->sku, 'product_name' => $p1->name, 'unit' => 'L', 'quantity' => '15.000', 'unit_cost' => '12.00', 'line_total' => '180.00']);

        $this->post(route('stock-entries.confirm', $entry));

        $response = $this->post(route('stock-entries.confirm', $entry));
        $response->assertStatus(409);

        $p1->refresh();
        $this->assertEquals('15.000', (string) $p1->current_stock);
        $this->assertEquals(1, InventoryMovement::count());
    }

    public function test_productos_duplicados_rechazados()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $p1 = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        $response = $this->post(route('stock-entries.store'), [
            'entry_date' => now()->format('Y-m-d'),
            'details' => [
                ['product_id' => $p1->id, 'quantity' => '10.000', 'unit_cost' => '5.00'],
                ['product_id' => $p1->id, 'quantity' => '20.000', 'unit_cost' => '5.00'],
            ],
        ]);
        $response->assertSessionHasErrors(['details.0.product_id']);
    }

    public function test_limite_de_200_lineas_aceptadas_y_201_rechazadas()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $products = Product::factory()->count(201)->create(['active' => true]);

        $details200 = $products->slice(0, 200)->map(fn($p) => [
            'product_id' => $p->id,
            'quantity' => '1.000',
            'unit_cost' => '2.00',
        ])->toArray();

        $response200 = $this->post(route('stock-entries.store'), [
            'entry_date' => now()->format('Y-m-d'),
            'details' => $details200,
        ]);
        $response200->assertSessionHasNoErrors();
        $this->assertEquals(1, StockEntry::count());

        $details201 = $products->map(fn($p) => [
            'product_id' => $p->id,
            'quantity' => '1.000',
            'unit_cost' => '2.00',
        ])->toArray();

        $response201 = $this->post(route('stock-entries.store'), [
            'entry_date' => now()->format('Y-m-d'),
            'details' => $details201,
        ]);
        $response201->assertSessionHasErrors(['details']);
        $this->assertEquals(1, StockEntry::count());
    }

    public function test_totales_manipulados_desde_el_navegador_son_ignorados()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = Product::factory()->create(['active' => true]);

        $this->post(route('stock-entries.store'), [
            'entry_date' => now()->format('Y-m-d'),
            'details' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '10.000',
                    'unit_cost' => '5.00',
                    'line_total' => '1.00', // Intento de manipulación
                ],
            ],
        ]);

        $entry = StockEntry::latest('id')->first();
        $detail = $entry->details->first();
        $this->assertEquals('50.00', (string) $detail->line_total);
    }

    public function test_entrada_confirmada_es_inmutable_y_conserva_instantaneas()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $p1 = Product::factory()->create(['name' => 'Aceite Original', 'sku' => 'SKU-ORIG', 'current_stock' => '0.000', 'active' => true]);

        $entry = StockEntry::create(['entry_date' => now(), 'status' => StockEntryStatus::DRAFT, 'created_by' => $admin->id]);
        StockEntryDetail::create([
            'stock_entry_id' => $entry->id,
            'product_id' => $p1->id,
            'product_sku' => $p1->sku,
            'product_name' => $p1->name,
            'unit' => 'L',
            'quantity' => '15.000',
            'unit_cost' => '12.00',
            'line_total' => '180.00',
        ]);
        $this->post(route('stock-entries.confirm', $entry));

        // Intento de edición en entrada confirmada debe fallar (409 Conflict)
        $responsePut = $this->put(route('stock-entries.update', $entry), [
            'entry_date' => now()->format('Y-m-d'),
            'details' => [['product_id' => $p1->id, 'quantity' => '20.000', 'unit_cost' => '12.00']],
        ]);
        $responsePut->assertStatus(409);

        // Intento de eliminación en entrada confirmada debe fallar (409 Conflict)
        $responseDel = $this->delete(route('stock-entries.destroy', $entry));
        $responseDel->assertStatus(409);

        // Cambiar atributos del producto posteriormente
        $p1->update(['name' => 'Aceite Modificado', 'sku' => 'SKU-MOD']);

        // El detalle del historial mantiene la instantánea
        $detail = StockEntryDetail::where('stock_entry_id', $entry->id)->first();
        $this->assertEquals('Aceite Original', $detail->product_name);
        $this->assertEquals('SKU-ORIG', $detail->product_sku);
    }
}
