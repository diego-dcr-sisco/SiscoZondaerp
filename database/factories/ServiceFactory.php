<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'method_id' => fake()->numberBetween(1, 5),
            'service_code' => fake()->numberBetween(19999, 50000),
            'service_description' => fake()->sentence(6, true),
            'observations' => fake()->sentence(6, true),
            'treated_areas' => fake()->sentence(6, true),
            'recommendations' => fake()->sentence(6, true),
        ];
    }
}
