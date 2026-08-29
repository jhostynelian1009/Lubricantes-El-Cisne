<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use BadMethodCallException;
use Illuminate\Foundation\Testing\RefreshDatabase;

use InvalidArgumentException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    public function test_stock_service_generates_movement_and_updates_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => '0.000',
            'active' => true,
        ]);

        $movement = $this->stockService->applyMovement(
            product: $product,
            type: 'initial_adjustment',
            quantityDelta: '50.000',
            user: $user,
            reason: 'Carga inicial de inventario',
            unitCost: '15.50',
            reference: $product
        );

        $this->assertInstanceOf(InventoryMovement::class, $movement);
        $this->assertEquals('50.000', (string)$movement->quantity_delta);
        $this->assertEquals('0.000', (string)$movement->quantity_before);
        $this->assertEquals('50.000', (string)$movement->quantity_after);
        $this->assertEquals('15.50', (string)$movement->unit_cost);
        $this->assertEquals($user->id, $movement->created_by);
        $this->assertEquals(Product::class, $movement->reference_type);
        $this->assertEquals($product->id, $movement->reference_id);

        $this->assertEquals('50.000', (string)$product->fresh()->current_stock);
        $this->assertEquals('15.50', (string)$product->fresh()->last_cost);
    }

    public function test_negative_final_stock_is_rejected()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => '5.000',
            'active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->applyMovement(
            product: $product,
            type: 'adjustment_out',
            quantityDelta: '-10.000',
            user: $user
        );
    }

    public function test_zero_delta_is_rejected()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => '10.000',
            'active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->applyMovement(
            product: $product,
            type: 'adjustment_in',
            quantityDelta: '0.000',
            user: $user
        );
    }

    public function test_inactive_product_is_rejected()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => '0.000',
            'active' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->applyMovement(
            product: $product,
            type: 'initial_adjustment',
            quantityDelta: '10.000',
            user: $user
        );
    }

    public function test_movements_cannot_be_updated_or_deleted()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['current_stock' => '0.000', 'active' => true]);

        $movement = $this->stockService->applyMovement(
            product: $product,
            type: 'initial_adjustment',
            quantityDelta: '10.000',
            user: $user
        );

        // Attempting to update Eloquent model
        try {
            $movement->update(['reason' => 'Intento alteración']);
            $this->fail('Se esperaba BadMethodCallException al intentar actualizar un movimiento.');
        } catch (BadMethodCallException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }

        // Attempting to delete Eloquent model
        try {
            $movement->delete();
            $this->fail('Se esperaba BadMethodCallException al intentar eliminar un movimiento.');
        } catch (BadMethodCallException $e) {
            $this->assertStringContainsString('inmutables', $e->getMessage());
        }
    }

    public function test_stock_change_rolls_back_product_and_movement_when_transaction_fails()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => '0.000',
            'active' => true,
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $this->stockService->applyMovement(
                product: $product,
                type: 'initial_adjustment',
                quantityDelta: '100.000',
                user: $user,
                reason: 'Carga con error provocado'
            );

            throw new \Exception('Error provocado para probar rollback.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
        }

        $this->assertEquals('0.000', (string)$product->fresh()->current_stock);
        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_movement_records_exact_balances_actor_reference_type_and_reason()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => '10.000',
            'active' => true,
        ]);

        $movement = $this->stockService->applyMovement(
            product: $product,
            type: 'entry',
            quantityDelta: '15.500',
            user: $user,
            reason: 'Compra según factura 001-001-12345',
            unitCost: '12.00',
            reference: $product
        );

        $this->assertEquals('10.000', (string)$movement->quantity_before);
        $this->assertEquals('15.500', (string)$movement->quantity_delta);
        $this->assertEquals('25.500', (string)$movement->quantity_after);
        $this->assertEquals($user->id, $movement->created_by);
        $this->assertEquals(Product::class, $movement->reference_type);
        $this->assertEquals($product->id, $movement->reference_id);
        $this->assertEquals('entry', $movement->type);
        $this->assertEquals('Compra según factura 001-001-12345', $movement->reason);
    }
}
