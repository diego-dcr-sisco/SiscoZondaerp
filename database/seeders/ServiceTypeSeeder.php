<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $service_types = [
            'Domestico',
            'Comercial',
            'Industrial/Planta',
        ];

        foreach ($service_types as $service_type) {
            \App\Models\ServiceType::create([
                'name' => $service_type,
            ]);
        }
    }
}
