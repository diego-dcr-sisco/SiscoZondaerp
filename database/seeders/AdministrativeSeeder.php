<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Administrative;

class AdministrativeSeeder extends Seeder
{
    public function run(): void {
        
        Administrative::create(
            [
                'user_id' => 1,
                'contract_type_id' => 1,
                'branch_id' => 1,
                'company_id' => 1,
                'curp' => '',
                'rfc' => '',
                'nss' => '',
                'address' => fake()->address(),
                'phone' => fake()->phoneNumber(),
                'company_phone' => fake()->phoneNumber(),
                'city' => fake()->city(),   
                'colony' => fake()->streetName(),
                'zip_code' => fake()->postcode(),
                'state' => fake()->state(),
                'country' => fake()->country(),
                'birthdate' => fake()->date(), 
                'hiredate' => fake()->date(),
                'salary' => 10000,
                'clabe' => '',
                'signature' => 'SIGNATURE',
            ],
        );
    }
}
