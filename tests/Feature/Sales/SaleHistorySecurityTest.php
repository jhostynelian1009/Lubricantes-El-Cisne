<?php

namespace Tests\Feature\Sales;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleHistorySecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(): User
    {
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);
        $permission = Permission::firstOrCreate(['key' => 'sales.create', 'name' => 'Ventas', 'assignable_to_employee' => true]);
        $user->permissions()->attach($permission);
        return $user;
    }

    public function test_29_and_30_filters_and_pagination_preserve_query_strings()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);
        $customer = Customer::factory()->create();

        Sale::factory()->count(20)->create([
            'customer_id' => $customer->id,
            'created_by' => $seller->id,
            'status' => SaleStatus::DRAFT,
        ]);

        $response = $this->get(route('sales.index', [
            'customer_id' => $customer->id,
            'status' => 'draft',
        ]));

        $response->assertStatus(200);
        $response->assertSee('customer_id=' . $customer->id);
        $response->assertSee('status=draft');
    }

    public function test_31_eager_loading_prevents_n_plus_one()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);

        Sale::factory()->count(10)->create([
            'created_by' => $seller->id,
        ]);

        \DB::flushQueryLog();
        \DB::enableQueryLog();

        $response = $this->get(route('sales.index'));
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        $response->assertStatus(200);
        $this->assertLessThan(30, count($queries), 'El listado paginado no debe realizar N+1 consultas');
    }

    public function test_32_manipulating_ids_does_not_evade_policy()
    {
        $seller1 = $this->createSeller();
        $seller2 = User::factory()->create(['role' => UserRole::EMPLOYEE, 'active' => true]);

        $sale1 = Sale::factory()->create(['created_by' => $seller1->id, 'status' => SaleStatus::DRAFT]);

        $this->actingAs($seller2);

        $response = $this->get(route('sales.edit', $sale1));
        $response->assertStatus(403);
    }

    public function test_33_sales_create_permission_does_not_grant_sales_cancel()
    {
        $seller = $this->createSeller();
        $this->assertFalse($seller->hasPermissionTo('sales.cancel'));
        $this->assertTrue($seller->hasPermissionTo('sales.create'));
    }

    public function test_34_no_cancel_route_exists()
    {
        $seller = $this->createSeller();
        $this->actingAs($seller);

        $response = $this->post('/sales/1/cancel');
        $response->assertStatus(404);
    }

    public function test_35_and_36_codebase_security_verifications()
    {
        $appPath = app_path('Http/Controllers');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appPath));

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());

                if (str_contains($file->getFilename(), 'Controller.php')) {
                    $this->assertStringNotContainsString('->current_stock =', $content, "Direct stock mutation found in {$file->getFilename()}");
                }
            }
        }
    }
}
