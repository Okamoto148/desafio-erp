<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    /**
     * 
     *
     * @var string
     */
    protected $model = Contract::class;

    /**
     *
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(), 
            'start_date'  => $this->faker->date(),
            'end_date'    => $this->faker->optional()->date(),
            'status'      => 'active',
        ];
    }
}