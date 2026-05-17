<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'document' => (string) $this->faker->unique()->numberBetween(10000000000000, 99999999999999),
            'email' => $this->faker->unique()->safeEmail,
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }
}