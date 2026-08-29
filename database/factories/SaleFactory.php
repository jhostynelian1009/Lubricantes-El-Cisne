<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'number' => null,
            'customer_id' => null,
            'status' => SaleStatus::DRAFT,
            'subtotal' => '0.00',
            'total' => '0.00',
            'created_by' => User::factory(),
            'confirmed_at' => null,
            'confirmed_by' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'number' => 'V-2026-' . str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'status' => SaleStatus::CONFIRMED,
            'confirmed_at' => now(),
            'confirmed_by' => $attributes['created_by'] ?? User::factory(),
        ]);
    }
}
