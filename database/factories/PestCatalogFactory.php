<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PestCatalogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //'pest_code' => fake()->word(),
            'pest_category_id' => fake()->numberBetween(1, 5),
            'pest_code' => fake()->randomNumber(),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'image' => fake()->imageUrl(),
        ];
    }
}
