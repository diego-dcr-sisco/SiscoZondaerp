<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerReference>
 */
class CustomerReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'tip_ref' => 1,
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'company_area' => fake()->jobTitle(),
            'address' => fake()->sentence(),
            'colony' => fake()->sentence(),
            'zip_code' => fake()->number(),
            'state' => fake()->sentence(),
            'city' => fake()->sentence(),
            'signature' => "Firma",
        ];
    }
}
