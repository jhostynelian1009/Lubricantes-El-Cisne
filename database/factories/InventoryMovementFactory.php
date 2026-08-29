<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => 'initial_adjustment',
            'quantity_delta' => '10.000',
            'quantity_before' => '0.000',
            'quantity_after' => '10.000',
            'unit_cost' => '15.00',
            'reference_type' => Product::class,
            'reference_id' => function (array $attributes) {
                return $attributes['product_id'];
            },
            'reason' => 'Carga inicial de inventario',
            'created_by' => User::factory(),
            'created_at' => now(),
        ];
    }
}
