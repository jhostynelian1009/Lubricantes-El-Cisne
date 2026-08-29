<?php

namespace Tests\Feature\Inventory;

use App\Enums\UserRole;
use App\Models\InventoryAdjustment;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    use DatabaseTruncation;

    public function test_concurrency_prevents_negative_stock()
    {
        $this->assertSame(
            'lubricantes_testing',
            DB::connection()->getDatabaseName()
        );

        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('La concurrencia real de bloqueos requiere MySQL/MariaDB.');
        }

        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'active' => true]);
        $product = Product::factory()->create(['current_stock' => '5.000', 'active' => true]);

        $adminId = $admin->id;
        $productId = $product->id;
        $categoryId = $product->category_id;

        try {
            $defaultConnection = config('database.default');
            $dbConfig = config("database.connections.{$defaultConnection}");

            $env = [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => (string) $defaultConnection,
                'DB_HOST' => (string) ($dbConfig['host'] ?? '127.0.0.1'),
                'DB_PORT' => (string) ($dbConfig['port'] ?? '3306'),
                'DB_DATABASE' => (string) ($dbConfig['database'] ?? 'lubricantes_testing'),
                'DB_USERNAME' => (string) ($dbConfig['username'] ?? 'root'),
                'DB_PASSWORD' => (string) ($dbConfig['password'] ?? ''),
            ];

            $command = [
                PHP_BINARY,
                base_path('artisan'),
                'test:decrease-stock',
                (string) $adminId,
                (string) $productId,
                '3.000',
                '--env=testing',
            ];

            $pool = Process::pool(function ($pool) use ($command, $env) {
                $pool->path(base_path())->env($env)->command($command);
                $pool->path(base_path())->env($env)->command($command);
            })->start()->wait();

            $results = $pool->collect();

            $successCount = 0;
            $failCount = 0;

            foreach ($results as $result) {
                if ($result->successful()) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            $this->assertEquals(1, $successCount, 'Un proceso debió tener éxito.');
            $this->assertEquals(1, $failCount, 'Un proceso debió ser rechazado por falta de stock.');

            $product->refresh();
            $this->assertEquals('2.000', (string) $product->current_stock, 'El stock final debe ser exactamente 2.000.');

            $this->assertEquals(1, InventoryAdjustment::count(), 'Debe existir exactamente un ajuste de inventario.');
            $this->assertEquals(1, InventoryMovement::count(), 'Debe existir exactamente un movimiento de inventario.');

            $adjustment = InventoryAdjustment::first();
            $movement = InventoryMovement::first();

            $this->assertEquals('5.000', (string) $movement->quantity_before);
            $this->assertEquals('-3.000', (string) $movement->quantity_delta);
            $this->assertEquals('2.000', (string) $movement->quantity_after);
            $this->assertEquals($adminId, $movement->created_by);
            $this->assertEquals(InventoryAdjustment::class, $movement->reference_type);
            $this->assertEquals($adjustment->id, $movement->reference_id);
            $this->assertEquals('Prueba de concurrencia', $movement->reason);
        } finally {
            if (DB::connection()->getDatabaseName() !== 'lubricantes_testing') {
                throw new \RuntimeException('Base de datos no válida para limpieza de pruebas.');
            }

            DB::table('inventory_movements')->where('product_id', $productId)->delete();
            DB::table('inventory_adjustments')->where('product_id', $productId)->delete();
            DB::table('user_permissions')->where('user_id', $adminId)->delete();
            DB::table('products')->where('id', $productId)->delete();

            if ($categoryId) {
                DB::table('categories')->where('id', $categoryId)->delete();
            }

            DB::table('users')->where('id', $adminId)->delete();
        }
    }
}
