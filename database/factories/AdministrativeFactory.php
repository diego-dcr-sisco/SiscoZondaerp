<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AdministrativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => '1',
            'userfile_id' => '1',
            'branch_id' => '1',
            'curp' => fake()->word(),
            'rfc' => fake()->word(),
            'nss' => fake()->randomNumber(),
            'address' => fake()->word(),
            'phone' => fake()->word(),
            'birthdate' => fake()->date(),
            'hire_date' => fake()->date(),
            'status' => fake()->randomNumber(),
            'salary' => fake()->randomFloat(),
            'signature' => fake()->word()
        ];
    }
}
