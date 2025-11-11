<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pesticide_id' => '1',
            'service_id' => '1',
            'latitude' => fake()->randomNumber(),
            'longitude' => fake()->randomNumber(),
            'map_x' => fake()->randomNumber(),
            'map_y' => fake()->randomNumber()
        ];
    }
}
