<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'address' =>fake()->sentence(),
            'colony' =>fake()->sentence(),
            'phone' => fake()->word(),
            'alt_phone' => fake()->word(),
            'email' => fake()->email(),
            'zip_code' => fake()->randomNumber(),
            'city' => fake()->word(),
            'state' => fake()->word(),
            'country' => fake()->word(),
            'license_number' => fake()->randomNumber()
        ];
    }
}
