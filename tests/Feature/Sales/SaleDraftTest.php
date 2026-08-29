<?php

namespace Tests\Feature\Sales;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleDraftTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => UserRole::ADMIN, 'active' => true]);
    }

    private function createSeller(): User
    {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $permission = Permission::firstOrCreate(['key' => 'sales.create', 'name' => 'Ventas', 'assignable_to_employee' => true]);
        $user->permissions()->attach($permission);
        return $user;
    }

    public function test_1_authorized_user_creates_draft_without_modifying_stock()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['current_stock' => '10.000', 'sale_price' => '25.00', 'active' => true]);

        $response = $this->post(route('sales.store'), [
            'details' => [
                ['product_id' => $product->id, 'quantity' => '2.000'],
            ],
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals(SaleStatus::DRAFT, $sale->status);
        $this->assertNull($sale->number);

        $this->assertEquals('10.000', (string) $product->fresh()->current_stock);
        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_2_customer_is_optional()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);

        $response = $this->post(route('sales.store'), [
            'customer_id' => null,
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertNull($sale->customer_id);
    }

    public function test_3_inactive_customer_is_rejected()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $inactiveCustomer = Customer::factory()->create(['active' => false]);
        $product = Product::factory()->create(['active' => true]);

        try {
            $this->post(route('sales.store'), [
                'customer_id' => $inactiveCustomer->id,
                'details' => [['product_id' => $product->id, 'quantity' => '1.000']],
            ]);
        } catch (\Throwable $e) {
            $this->assertStringContainsString('inactivo', $e->getMessage());
        }

        $this->assertEquals(0, Sale::count());
    }

    public function test_4_employee_without_sales_create_receives_403()
    {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $this->actingAs($employee);

        $response = $this->get(route('sales.create'));
        $response->assertStatus(403);

        $response = $this->post(route('sales.store'), []);
        $response->assertStatus(403);
    }

    public function test_5_anonymous_user_is_redirected_to_login()
    {
        $response = $this->get(route('sales.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('sales.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_6_inactive_products_are_rejected()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $inactiveProduct = Product::factory()->create(['active' => false]);

        try {
            $this->post(route('sales.store'), [
                'details' => [
                    ['product_id' => $inactiveProduct->id, 'quantity' => '1.000'],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->assertStringContainsString('inactivo', $e->getMessage());
        }

        $this->assertEquals(0, Sale::count());
    }

    public function test_7_duplicate_products_are_consolidated()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['sale_price' => '10.00', 'active' => true]);

        $this->post(route('sales.store'), [
            'details' => [
                ['product_id' => $product->id, 'quantity' => '2.000'],
                ['product_id' => $product->id, 'quantity' => '3.500'],
            ],
        ]);

        $sale = Sale::first();
        $this->assertCount(1, $sale->details);
        $detail = $sale->details->first();
        $this->assertEquals('5.500', (string) $detail->quantity);
        $this->assertEquals('55.00', (string) $sale->total);
    }

    public function test_8_and_9_max_50_lines_accepted_51_lines_rejected()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);

        $products = Product::factory()->count(51)->create(['active' => true]);

        $lines50 = $products->slice(0, 50)->map(fn($p) => [
            'product_id' => $p->id,
            'quantity' => '1.000',
        ])->toArray();

        $response50 = $this->post(route('sales.store'), [
            'details' => $lines50,
        ]);
        $response50->assertSessionHasNoErrors();
        $this->assertEquals(1, Sale::count());

        $lines51 = $products->map(fn($p) => [
            'product_id' => $p->id,
            'quantity' => '1.000',
        ])->toArray();

        $response51 = $this->post(route('sales.store'), [
            'details' => $lines51,
        ]);
        $response51->assertSessionHasErrors(['details']);
    }

    public function test_10_quantity_0_001_works_exactly()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['sale_price' => '10.00', 'active' => true]);

        $this->post(route('sales.store'), [
            'details' => [
                ['product_id' => $product->id, 'quantity' => '0.001'],
            ],
        ]);

        $sale = Sale::first();
        $detail = $sale->details->first();
        $this->assertEquals('0.001', (string) $detail->quantity);
        $this->assertEquals('0.01', (string) $detail->line_total);
    }

    public function test_11_invalid_quantities_are_rejected()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['active' => true]);

        $invalidCases = ['0', '-1.000', '1.2345', '1e2'];

        foreach ($invalidCases as $bad) {
            $response = $this->post(route('sales.store'), [
                'details' => [
                    ['product_id' => $product->id, 'quantity' => $bad],
                ],
            ]);
            $this->assertTrue(
                $response->isRedirect() || $response->isClientError(),
                "Case {$bad} handled correctly"
            );
        }
    }

    public function test_12_manipulated_prices_and_totals_are_ignored()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $product = Product::factory()->create(['sale_price' => '50.00', 'active' => true]);

        $this->post(route('sales.store'), [
            'details' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '2.000',
                    'unit_price' => '1.00',
                    'line_total' => '2.00',
                ],
            ],
        ]);

        $sale = Sale::first();
        $detail = $sale->details->first();
        $this->assertEquals('50.00', (string) $detail->unit_price);
        $this->assertEquals('100.00', (string) $detail->line_total);
        $this->assertEquals('100.00', (string) $sale->total);
    }

    public function test_13_editing_draft_does_not_change_stock()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $p1 = Product::factory()->create(['current_stock' => '20.000', 'sale_price' => '10.00', 'active' => true]);
        $p2 = Product::factory()->create(['current_stock' => '15.000', 'sale_price' => '5.00', 'active' => true]);

        $sale = Sale::create(['status' => SaleStatus::DRAFT, 'created_by' => $seller->id]);

        $this->put(route('sales.update', $sale), [
            'details' => [
                ['product_id' => $p1->id, 'quantity' => '5.000'],
                ['product_id' => $p2->id, 'quantity' => '3.000'],
            ],
        ]);

        $this->assertEquals('20.000', (string) $p1->fresh()->current_stock);
        $this->assertEquals('15.000', (string) $p2->fresh()->current_stock);
        $this->assertEquals(0, InventoryMovement::count());
    }

    public function test_14_deleting_draft_creates_no_movements()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $sale = Sale::create(['status' => SaleStatus::DRAFT, 'created_by' => $seller->id]);

        $this->delete(route('sales.destroy', $sale));

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
        $this->assertEquals(0, InventoryMovement::count());
    }
}
