<?php

namespace Tests\Feature\Sales;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleReceiptTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(): User
    {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $permission = Permission::firstOrCreate(['key' => 'sales.create', 'name' => 'Ventas', 'assignable_to_employee' => true]);
        $user->permissions()->attach($permission);
        return $user;
    }

    public function test_37_draft_has_no_receipt()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $draft = Sale::factory()->create(['status' => SaleStatus::DRAFT, 'created_by' => $seller->id]);

        $response = $this->get(route('sales.receipt', $draft));
        $response->assertStatus(403);
    }

    public function test_38_39_40_41_42_43_confirmed_sale_has_valid_printable_receipt()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);

        $product = Product::factory()->create([
            'sku' => 'PROD-999',
            'name' => 'Filtro de Aceite Heavy Duty',
            'unit' => 'Unidad',
            'current_stock' => '20.000',
            'sale_price' => '12.50',
            'active' => true,
        ]);

        $saleService = app(SaleService::class);
        $draft = $saleService->createDraft(null, $seller);
        $saleService->replaceLines($draft, [
            ['product_id' => $product->id, 'quantity' => '2.000'],
        ], $seller);

        $confirmed = $saleService->confirm($draft, $seller);

        $response = $this->get(route('sales.receipt', $confirmed));
        $response->assertStatus(200);

        // Snapshots and exact totals
        $response->assertSee('PROD-999');
        $response->assertSee('Filtro de Aceite Heavy Duty');
        $response->assertSee('25.00');

        // Consumidor final when customer is null
        $response->assertSee('Consumidor final');

        // Exact mandatory notice required by prompt
        $response->assertSee('Comprobante interno — no constituye factura electrónica autorizada');

        // Must NOT contain SRI or authorization claims
        $response->assertDontSee('factura electrónica autorizada por el SRI');
        $response->assertDontSee('Clave de Acceso SRI');

        // Includes @media print CSS
        $response->assertSee('@media print');
    }
}
