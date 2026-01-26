<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductCatalogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo' => fake()->sentence(),
            'icon' => fake()->sentence(),
            'name' => fake()->word(),
            'status' => '1',
            'bussiness_name' => fake()->word(),
            'bar_code' => fake()->randomNumber(),
            'linebuss_id' => '1',
            'presentation' => fake()->sentence(),
            'obsolete' => fake()->randomDigit(),
            'description' => fake()->sentence(),
            'indications_execution' => fake()->sentence(),
            'application_method' => fake()->sentence(),
            'manufacturer' => fake()->sentence(),
            'register_number' => fake()->randomNumber(),
            'validity_date' => fake()->randomDigit(),
            'active_ingredient' => fake()->sentences(),
            'per_active_ingredient' => fake()->sentence(),
            'dosage' => fake()->randomNumber(),
            'safety_period' => fake()>randomNumber(),
            'residual_effect' => fake()->sentences(),
            'purpose_id' => '1',
            'biocidesgyt_id' => '1',
            'toxicity' => fake()->sentences(),
            'toxicity_categ' => '1',
            'agent_combat' => '1',
            'econimic_data_id' => '1'
        ];
    }
}
