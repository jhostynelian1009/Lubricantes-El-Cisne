<?php

namespace Tests\Feature\Sales;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\InventoryMovement;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleConfirmTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(): User
    {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $permission = Permission::firstOrCreate(['key' => 'sales.create', 'name' => 'Ventas', 'assignable_to_employee' => true]);
        $user->permissions()->attach($permission);
        return $user;
    }

    public function test_15_valid_sale_confirmation_updates_stock_movements_and_snapshots()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create([
            'sku' => 'MOT-10W40',
            'name' => 'Aceite 10W40',
            'unit' => 'Galón',
            'current_stock' => '50.000',
            'sale_price' => '30.00',
            'active' => true,
        ]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $product->id, 'quantity' => '5.000'],
        ], $seller);

        $response = $this->post(route('sales.confirm', $draft));
        $response->assertRedirect();

        $confirmed = $draft->fresh(['details']);
        $this->assertEquals(SaleStatus::CONFIRMED, $confirmed->status);
        $this->assertNotNull($confirmed->number);
        $this->assertStringStartsWith('V-' . date('Y') . '-', $confirmed->number);

        // Deducts stock
        $this->assertEquals('45.000', (string) $product->fresh()->current_stock);

        // Creates exactly 1 movement
        $this->assertEquals(1, InventoryMovement::count());
        $movement = InventoryMovement::first();
        $this->assertEquals('sale', $movement->type);
        $this->assertEquals('-5.000', (string) $movement->quantity_delta);
        $this->assertEquals('50.000', (string) $movement->quantity_before);
        $this->assertEquals('45.000', (string) $movement->quantity_after);
        $this->assertEquals(Sale::class, $movement->reference_type);
        $this->assertEquals($confirmed->id, $movement->reference_id);

        // Check snapshots
        $detail = $confirmed->details->first();
        $this->assertEquals('MOT-10W40', $detail->product_sku);
        $this->assertEquals('Aceite 10W40', $detail->product_name);
        $this->assertEquals('Galón', $detail->unit);
        $this->assertEquals('150.00', (string) $confirmed->total);
    }

    public function test_16_sale_without_lines_cannot_be_confirmed()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);

        $this->expectException(\InvalidArgumentException::class);
        $saleService->confirm($draft, $seller);
    }

    public function test_17_insufficient_stock_on_any_line_rolls_back_entire_sale()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $p1 = Product::factory()->create(['current_stock' => '10.000', 'sale_price' => '10.00', 'active' => true]);
        $p2 = Product::factory()->create(['current_stock' => '2.000', 'sale_price' => '10.00', 'active' => true]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $p1->id, 'quantity' => '5.000'],
            ['product_id' => $p2->id, 'quantity' => '3.000'], // Insuficiente (2 < 3)
        ], $seller);

        try {
            $saleService->confirm($draft, $seller);
        } catch (\Throwable $e) {
            $this->assertStringContainsString('insuficiente', strtolower($e->getMessage()));
        }

        // Entire transaction rolled back
        $draftReloaded = $draft->fresh();
        $this->assertEquals(SaleStatus::DRAFT, $draftReloaded->status);
        $this->assertNull($draftReloaded->number);
        $this->assertEquals('10.000', (string) $p1->fresh()->current_stock);
        $this->assertEquals('2.000', (string) $p2->fresh()->current_stock);
        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_18_inactive_product_before_confirm_blocks_sale()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['current_stock' => '10.000', 'sale_price' => '10.00', 'active' => true]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $product->id, 'quantity' => '2.000'],
        ], $seller);

        // Desactivar el producto antes de confirmar
        $product->update(['active' => false]);

        try {
            $saleService->confirm($draft, $seller);
        } catch (\Throwable $e) {
            $this->assertStringContainsString('inactivo', strtolower($e->getMessage()));
        }

        $this->assertEquals(SaleStatus::DRAFT, $draft->fresh()->status);
        $this->assertEquals('10.000', (string) $product->fresh()->current_stock);
    }

    public function test_19_price_change_before_confirm_produces_conflict_and_does_not_reduce_stock()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['current_stock' => '10.000', 'sale_price' => '10.00', 'active' => true]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $product->id, 'quantity' => '2.000'],
        ], $seller);

        // Cambiar el precio del producto en el servidor
        $product->update(['sale_price' => '12.00']);

        try {
            $saleService->confirm($draft, $seller);
            $this->fail('Should have thrown price conflict');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('ha cambiado', $e->getMessage());
        }

        $this->assertEquals(SaleStatus::DRAFT, $draft->fresh()->status);
        $this->assertEquals('10.000', (string) $product->fresh()->current_stock);
    }

    public function test_20_repeated_confirmation_does_not_duplicate_movements_or_discounts()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['current_stock' => '20.000', 'sale_price' => '10.00', 'active' => true]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $product->id, 'quantity' => '5.000'],
        ], $seller);

        $confirmed = $saleService->confirm($draft, $seller);
        $this->assertEquals('15.000', (string) $product->fresh()->current_stock);

        // Intentar segunda confirmación
        try {
            $saleService->confirm($confirmed, $seller);
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }

        $this->assertEquals('15.000', (string) $product->fresh()->current_stock);
        $this->assertEquals(1, InventoryMovement::count());
    }

    public function test_21_confirmed_sale_cannot_be_edited_or_deleted()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['current_stock' => '20.000', 'sale_price' => '10.00', 'active' => true]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $product->id, 'quantity' => '5.000'],
        ], $seller);

        $confirmed = $saleService->confirm($draft, $seller);

        $responseEdit = $this->put(route('sales.update', $confirmed), [
            'details' => [['product_id' => $product->id, 'quantity' => '1.000']],
        ]);
        $responseEdit->assertStatus(409);

        $responseDelete = $this->delete(route('sales.destroy', $confirmed));
        $responseDelete->assertStatus(409);
    }
}
