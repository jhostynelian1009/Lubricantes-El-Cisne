<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'identification' => fake()->unique()->numerify('17########'),
            'phone' => fake()->numerify('09########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'active' => true,
        ];
    }
}
