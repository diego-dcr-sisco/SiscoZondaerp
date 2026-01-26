<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FloorPlans>
 */
class FloorPlansFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'floortype_id' => fake()->numberBetween(1, 5),
            'customer_id' => fake()->numberBetween(1, 5),
            'path' => fake()->word(),
        ];
    }

}
