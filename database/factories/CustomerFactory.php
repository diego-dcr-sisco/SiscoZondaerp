<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_contract_id' => 1,
            'companycategory_id' => 1,
            'administrative_id' => 1,
            'service_id' => 1,
            'status' => fake()->randomNumber(),
            'starting_time' => fake()->randomtime(),
            'final_schedule' => fake()->randomtime(),
            'commercial_zone' => fake()->sentence(),
            'm2' => fake()->randomNumber(),
            'm3' => fake()->randomNumber(),
            'blueprints' => fake()->randomNumber(),
            'name' => fake()->word(),
            'url' => fake()->sentence(),
            'print_doc' => fake()->randomNumber(),
            'validate_certificate' => fake()->randomNumber(),
            'businessname' => fake()->sentence(),
            'address' => fake()->word(),
            'tax_regime' => fake()->sentence(),
            'rfc' => fake()->sentence(),
            'zip_code' => fake()->randomNumber(),
            'city' => fake()->sentence(),
            'state' => fake()->sentence(),
            'tel' => fake()->randomNumber(),
            'email' => fake()->email(),
        ];
    }
}
