<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Services\InventoryAdjustmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DecreaseStockCommand extends Command
{
    protected $signature = 'test:decrease-stock {user_id} {product_id} {quantity}';
    protected $description = 'Herramienta de integración exclusivamente para pruebas de concurrencia.';
    protected $hidden = true;

    public function handle(InventoryAdjustmentService $service): int
    {
        if (! app()->environment('testing') || DB::connection()->getDatabaseName() !== 'lubricantes_testing') {
            $this->error('Este comando está restringido exclusivamente al entorno de pruebas.');
            return 1;
        }

        $userId = $this->argument('user_id');
        $productId = $this->argument('product_id');
        $quantity = $this->argument('quantity');

        $user = User::findOrFail($userId);
        $product = Product::findOrFail($productId);

        try {
            $service->decrease($product, $quantity, 'Prueba de concurrencia', $user);
            $this->info('Success');
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}

