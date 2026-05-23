<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PesticideFile>
 */
class PesticideFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku_id' => fake()->numberBetween(1, 5),
            'rp_specification' => fake()->word(),
            'techical_specification' => fake()->word(),
            'segurity_specification' => fake()->word(),
            'register_specification' => fake()->word(),
            'sanitary_register' => fake()->word(),
        ];
    }
}
