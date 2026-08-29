<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleDetailFactory extends Factory
{
    protected $model = SaleDetail::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $qty = '2.000';
        $price = '10.00';
        $total = '20.00';

        return [
            'sale_id' => Sale::factory(),
            'product_id' => $product->id,
            'product_sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'quantity' => $qty,
            'unit_price' => $price,
            'line_total' => $total,
        ];
    }
}
