<?php

namespace Tests\Feature\Sales;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class SaleConcurrencyTest extends TestCase
{
    public function test_concurrent_sales_prevent_negative_stock_and_duplicate_sequence_numbers()
    {
        // 1. Barrera previa obligatoria
        $this->assertSame(
            'lubricantes_testing',
            DB::connection()->getDatabaseName(),
            'Base de datos no válida para ejecución de pruebas concurrentes.'
        );

        $sellerId = null;
        $permissionId = null;
        $permissionCreatedByTest = false;
        $categoryId = null;
        $productId = null;
        $draft1Id = null;
        $draft2Id = null;
        $saleIds = [];
        $sequenceType = 'sale_' . date('Y');
        $sequenceExistedBefore = false;
        $preExistingSequenceValue = null;

        $existingSeq = DB::table('sequences')->where('type', $sequenceType)->first();
        if ($existingSeq) {
            $sequenceExistedBefore = true;
            $preExistingSequenceValue = $existingSeq->current_value;
        }

        try {
            // Permiso sales.create
            $perm = DB::table('permissions')->where('key', 'sales.create')->first();
            if ($perm) {
                $permissionId = $perm->id;
                $permissionCreatedByTest = false;
            } else {
                $permissionId = DB::table('permissions')->insertGetId([
                    'key' => 'sales.create',
                    'name' => 'Ventas',
                    'assignable_to_employee' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permissionCreatedByTest = true;
            }

            // Usuario vendedor
            $seller = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'active' => true,
            ]);
            $sellerId = $seller->id;

            DB::table('user_permissions')->insert([
                'user_id' => $sellerId,
                'permission_id' => $permissionId,
            ]);

            // Categoría y Producto
            $category = Category::factory()->create(['active' => true]);
            $categoryId = $category->id;

            $product = Product::factory()->create([
                'category_id' => $categoryId,
                'current_stock' => '10.000',
                'sale_price' => '15.00',
                'active' => true,
            ]);
            $productId = $product->id;

            // Servicio y borradores
            $saleService = app(SaleService::class);

            $draft1 = $saleService->createDraft(null, $seller);
            $draft1Id = $draft1->id;
            $saleService->replaceLines($draft1, [
                ['product_id' => $productId, 'quantity' => '8.000'],
            ], $seller);

            $draft2 = $saleService->createDraft(null, $seller);
            $draft2Id = $draft2->id;
            $saleService->replaceLines($draft2, [
                ['product_id' => $productId, 'quantity' => '8.000'],
            ], $seller);

            $saleIds = [$draft1Id, $draft2Id];

            // Entorno explícito para subprocesos
            $env = [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => config('database.default'),
                'DB_HOST' => config('database.connections.mysql.host', '127.0.0.1'),
                'DB_PORT' => config('database.connections.mysql.port', '3306'),
                'DB_DATABASE' => 'lubricantes_testing',
                'DB_USERNAME' => config('database.connections.mysql.username', 'root'),
                'DB_PASSWORD' => config('database.connections.mysql.password', ''),
                'PATH' => getenv('PATH') ?: '',
                'SystemRoot' => getenv('SystemRoot') ?: '',
                'WINDIR' => getenv('WINDIR') ?: '',
            ];

            $artisanPath = base_path('artisan');
            $cmd1 = [PHP_BINARY, $artisanPath, 'test:confirm-sale', (string) $sellerId, (string) $draft1Id, '--env=testing'];
            $cmd2 = [PHP_BINARY, $artisanPath, 'test:confirm-sale', (string) $sellerId, (string) $draft2Id, '--env=testing'];

            $process1 = new Process($cmd1, base_path(), $env);
            $process2 = new Process($cmd2, base_path(), $env);

            $process1->start();
            $process2->start();

            $process1->wait();
            $process2->wait();

            $code1 = $process1->getExitCode();
            $code2 = $process2->getExitCode();

            // Aserciones de finalización de proceso
            $successes = ($code1 === 0 ? 1 : 0) + ($code2 === 0 ? 1 : 0);
            $failures = ($code1 !== 0 ? 1 : 0) + ($code2 !== 0 ? 1 : 0);
            $this->assertEquals(1, $successes, 'Exactamente un proceso debe finalizar con éxito (código 0).');
            $this->assertEquals(1, $failures, 'Exactamente un proceso debe fallar (código distinto de 0).');

            // Stock final e integridad
            $productReloaded = Product::find($productId);
            $this->assertGreaterThanOrEqual('0.000', (string) $productReloaded->current_stock, 'El stock nunca debe ser negativo.');
            $this->assertEquals('2.000', (string) $productReloaded->current_stock, 'El stock final debe ser exactamente 2.000.');

            // Estado de ventas
            $salesInDb = DB::table('sales')->whereIn('id', $saleIds)->get();
            $confirmedSales = $salesInDb->where('status', 'confirmed');
            $draftSales = $salesInDb->where('status', 'draft');

            $this->assertCount(1, $confirmedSales, 'Exactamente una venta debe quedar confirmada.');
            $this->assertCount(1, $draftSales, 'Exactamente una venta debe permanecer en borrador.');

            $confirmedSaleRow = $confirmedSales->first();
            $rejectedSaleRow = $draftSales->first();

            // Venta rechazada
            $this->assertNull($rejectedSaleRow->number, 'La venta rechazada debe mantener number = null.');
            $rejectedMovementsCount = DB::table('inventory_movements')
                ->where('reference_type', Sale::class)
                ->where('reference_id', $rejectedSaleRow->id)
                ->count();
            $this->assertEquals(0, $rejectedMovementsCount, 'La venta rechazada no debe registrar ningún movimiento de inventario.');

            $rejectedDetailsCount = DB::table('sale_details')->where('sale_id', $rejectedSaleRow->id)->count();
            $this->assertEquals(1, $rejectedDetailsCount, 'La venta rechazada debe conservar sus detalles originales.');

            // Venta confirmada
            $this->assertNotNull($confirmedSaleRow->number, 'La venta confirmada debe poseer número.');
            $this->assertStringStartsWith('V-' . date('Y') . '-', $confirmedSaleRow->number);

            $confirmedMovements = DB::table('inventory_movements')
                ->where('reference_type', Sale::class)
                ->where('reference_id', $confirmedSaleRow->id)
                ->get();
            $this->assertCount(1, $confirmedMovements, 'La venta confirmada debe tener exactamente un movimiento de inventario.');

            $m = $confirmedMovements->first();
            $this->assertEquals('sale', $m->type);
            $this->assertEquals('10.000', (string) $m->quantity_before);
            $this->assertEquals('-8.000', (string) $m->quantity_delta);
            $this->assertEquals('2.000', (string) $m->quantity_after);
            $this->assertEquals($sellerId, $m->created_by);
            $this->assertEquals(Sale::class, $m->reference_type);
            $this->assertEquals($confirmedSaleRow->id, $m->reference_id);

        } finally {
            // Limpieza dirigida y segura con Query Builder
            if (DB::connection()->getDatabaseName() !== 'lubricantes_testing') {
                throw new \RuntimeException('Base de datos no válida para limpieza de pruebas.');
            }

            if (! empty($saleIds)) {
                DB::table('inventory_movements')
                    ->where('reference_type', Sale::class)
                    ->whereIn('reference_id', $saleIds)
                    ->delete();

                DB::table('sale_details')->whereIn('sale_id', $saleIds)->delete();
                DB::table('sales')->whereIn('id', $saleIds)->delete();
            }

            if ($sellerId) {
                DB::table('user_permissions')->where('user_id', $sellerId)->delete();
            }

            if ($productId) {
                DB::table('products')->where('id', $productId)->delete();
            }

            if ($categoryId) {
                DB::table('categories')->where('id', $categoryId)->delete();
            }

            if ($sellerId) {
                DB::table('users')->where('id', $sellerId)->delete();
            }

            if ($permissionCreatedByTest && $permissionId) {
                DB::table('permissions')->where('id', $permissionId)->delete();
            }

            if ($sequenceExistedBefore) {
                DB::table('sequences')
                    ->where('type', $sequenceType)
                    ->update(['current_value' => $preExistingSequenceValue]);
            } else {
                DB::table('sequences')
                    ->where('type', $sequenceType)
                    ->delete();
            }
        }
    }

    public function test_concurrent_confirmations_receive_unique_sequence_numbers()
    {
        // 1. Barrera previa obligatoria
        $this->assertSame(
            'lubricantes_testing',
            DB::connection()->getDatabaseName(),
            'Base de datos no válida para ejecución de pruebas concurrentes.'
        );

        $sellerId = null;
        $permissionId = null;
        $permissionCreatedByTest = false;
        $categoryId = null;
        $productId = null;
        $draft1Id = null;
        $draft2Id = null;
        $saleIds = [];
        $sequenceType = 'sale_' . date('Y');
        $sequenceExistedBefore = false;
        $preExistingSequenceValue = null;

        $existingSeq = DB::table('sequences')->where('type', $sequenceType)->first();
        if ($existingSeq) {
            $sequenceExistedBefore = true;
            $preExistingSequenceValue = $existingSeq->current_value;
        }

        try {
            // Permiso sales.create
            $perm = DB::table('permissions')->where('key', 'sales.create')->first();
            if ($perm) {
                $permissionId = $perm->id;
                $permissionCreatedByTest = false;
            } else {
                $permissionId = DB::table('permissions')->insertGetId([
                    'key' => 'sales.create',
                    'name' => 'Ventas',
                    'assignable_to_employee' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permissionCreatedByTest = true;
            }

            // Vendedor
            $seller = User::factory()->create([
                'role' => UserRole::EMPLOYEE,
                'active' => true,
            ]);
            $sellerId = $seller->id;

            DB::table('user_permissions')->insert([
                'user_id' => $sellerId,
                'permission_id' => $permissionId,
            ]);

            // Categoría y Producto
            $category = Category::factory()->create(['active' => true]);
            $categoryId = $category->id;

            $product = Product::factory()->create([
                'category_id' => $categoryId,
                'current_stock' => '50.000',
                'sale_price' => '10.00',
                'active' => true,
            ]);
            $productId = $product->id;

            // Borradores
            $saleService = app(SaleService::class);

            $draft1 = $saleService->createDraft(null, $seller);
            $draft1Id = $draft1->id;
            $saleService->replaceLines($draft1, [
                ['product_id' => $productId, 'quantity' => '5.000'],
            ], $seller);

            $draft2 = $saleService->createDraft(null, $seller);
            $draft2Id = $draft2->id;
            $saleService->replaceLines($draft2, [
                ['product_id' => $productId, 'quantity' => '5.000'],
            ], $seller);

            $saleIds = [$draft1Id, $draft2Id];

            // Entorno explícito
            $env = [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => config('database.default'),
                'DB_HOST' => config('database.connections.mysql.host', '127.0.0.1'),
                'DB_PORT' => config('database.connections.mysql.port', '3306'),
                'DB_DATABASE' => 'lubricantes_testing',
                'DB_USERNAME' => config('database.connections.mysql.username', 'root'),
                'DB_PASSWORD' => config('database.connections.mysql.password', ''),
                'PATH' => getenv('PATH') ?: '',
                'SystemRoot' => getenv('SystemRoot') ?: '',
                'WINDIR' => getenv('WINDIR') ?: '',
            ];

            $artisanPath = base_path('artisan');
            $cmd1 = [PHP_BINARY, $artisanPath, 'test:confirm-sale', (string) $sellerId, (string) $draft1Id, '--env=testing'];
            $cmd2 = [PHP_BINARY, $artisanPath, 'test:confirm-sale', (string) $sellerId, (string) $draft2Id, '--env=testing'];

            $process1 = new Process($cmd1, base_path(), $env);
            $process2 = new Process($cmd2, base_path(), $env);

            $process1->start();
            $process2->start();

            $process1->wait();
            $process2->wait();

            $this->assertEquals(0, $process1->getExitCode(), 'El primer proceso debe finalizar con código 0.');
            $this->assertEquals(0, $process2->getExitCode(), 'El segundo proceso debe finalizar con código 0.');

            $salesInDb = DB::table('sales')->whereIn('id', $saleIds)->get();
            $this->assertCount(2, $salesInDb->where('status', 'confirmed'), 'Ambas ventas deben quedar en estado confirmed.');

            $s1Row = $salesInDb->firstWhere('id', $draft1Id);
            $s2Row = $salesInDb->firstWhere('id', $draft2Id);

            $this->assertNotNull($s1Row->number, 'La primera venta debe poseer número.');
            $this->assertNotNull($s2Row->number, 'La segunda venta debe poseer número.');
            $this->assertNotEquals($s1Row->number, $s2Row->number, 'Los números asignados a las ventas deben ser distintos.');

            $this->assertMatchesRegularExpression('/^V-\d{4}-\d{6}$/', $s1Row->number);
            $this->assertMatchesRegularExpression('/^V-\d{4}-\d{6}$/', $s2Row->number);

            $uniqueNumbersCount = DB::table('sales')->whereIn('id', $saleIds)->distinct()->count('number');
            $this->assertEquals(2, $uniqueNumbersCount, 'No debe existir duplicidad en sales.number.');

            $movements = DB::table('inventory_movements')
                ->where('reference_type', Sale::class)
                ->whereIn('reference_id', $saleIds)
                ->get();
            $this->assertCount(2, $movements, 'Deben existir exactamente dos movimientos de inventario.');

            $m1 = $movements->firstWhere('reference_id', $draft1Id);
            $m2 = $movements->firstWhere('reference_id', $draft2Id);
            $this->assertNotNull($m1, 'Debe existir un movimiento asociado a la primera venta.');
            $this->assertNotNull($m2, 'Debe existir un movimiento asociado a la segunda venta.');

            $productReloaded = Product::find($productId);
            $this->assertEquals('40.000', (string) $productReloaded->current_stock, 'El stock final debe ser exactamente 40.000.');

        } finally {
            // Limpieza dirigida y segura con Query Builder
            if (DB::connection()->getDatabaseName() !== 'lubricantes_testing') {
                throw new \RuntimeException('Base de datos no válida para limpieza de pruebas.');
            }

            if (! empty($saleIds)) {
                DB::table('inventory_movements')
                    ->where('reference_type', Sale::class)
                    ->whereIn('reference_id', $saleIds)
                    ->delete();

                DB::table('sale_details')->whereIn('sale_id', $saleIds)->delete();
                DB::table('sales')->whereIn('id', $saleIds)->delete();
            }

            if ($sellerId) {
                DB::table('user_permissions')->where('user_id', $sellerId)->delete();
            }

            if ($productId) {
                DB::table('products')->where('id', $productId)->delete();
            }

            if ($categoryId) {
                DB::table('categories')->where('id', $categoryId)->delete();
            }

            if ($sellerId) {
                DB::table('users')->where('id', $sellerId)->delete();
            }

            if ($permissionCreatedByTest && $permissionId) {
                DB::table('permissions')->where('id', $permissionId)->delete();
            }

            if ($sequenceExistedBefore) {
                DB::table('sequences')
                    ->where('type', $sequenceType)
                    ->update(['current_value' => $preExistingSequenceValue]);
            } else {
                DB::table('sequences')
                    ->where('type', $sequenceType)
                    ->delete();
            }
        }
    }
}
