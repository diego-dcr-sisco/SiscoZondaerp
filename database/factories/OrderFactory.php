<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => '1',
            'administrative_id' => '1',
            'technician_id' => '1',
            'customer_id' => '1',
            'paymentmethod_id' => '1',
            'start_time' => fake()->time(),
            'end_time' => fake()->time(),
            'date' => fake()->date(),
            'status' => '1',
        ];
    }
}
