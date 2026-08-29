<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('PROD-####-???')),
            'barcode' => $this->faker->optional(0.7)->numerify('786100#######'),
            'name' => 'Aceite ' . $this->faker->words(3, true),
            'description' => $this->faker->optional(0.5)->sentence(),
            'unit' => $this->faker->randomElement(['galon', 'litro', 'cuarto', 'caneca', 'unidad']),
            'current_stock' => '0.000',
            'minimum_stock' => '5.000',
            'last_cost' => $this->faker->randomFloat(2, 5, 50),
            'sale_price' => $this->faker->randomFloat(2, 10, 80),
            'active' => true,
        ];
    }
}
