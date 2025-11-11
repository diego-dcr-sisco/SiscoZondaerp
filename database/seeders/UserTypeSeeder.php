<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserType::insert([
            [
                'name' => 'Empleado',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cliente',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
