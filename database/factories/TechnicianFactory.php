<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TechnicianFactory extends Factory
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
            'phone' => fake()->word(),
            'address' => fake()->word(),
            'city' => fake()->word(),
            'state' => fake()->word(),
            'birthdate' => fake()->date(),
            'hiredate' => fake()->date(),
            'status' => fake()->randomDigit(),
            'salary' => fake()->randomFloat(min: 0, max: 100),
            'signature' => 'Firma',    
        ];
    }
}
