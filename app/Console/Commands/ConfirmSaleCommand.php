<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConfirmSaleCommand extends Command
{
    protected $signature = 'test:confirm-sale {user_id} {sale_id}';
    protected $description = 'Herramienta de integración exclusivamente para pruebas de concurrencia de ventas.';
    protected $hidden = true;

    public function handle(SaleService $saleService): int
    {
        if (! app()->environment('testing') || DB::connection()->getDatabaseName() !== 'lubricantes_testing') {
            $this->error('Este comando está restringido exclusivamente al entorno de pruebas.');
            return 1;
        }

        $userId = $this->argument('user_id');
        $saleId = $this->argument('sale_id');

        $user = User::findOrFail($userId);
        $sale = Sale::findOrFail($saleId);

        try {
            $saleService->confirm($sale, $user);
            $this->info('Success');
            return 0;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
