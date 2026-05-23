<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceStatus;

class ServiceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            [
                'name' => 'Activo'
            ],
            [
                'name' => 'Pendiente'
            ],
            [
                'name' => 'Inactivo'
            ],
        ];
    
        ServiceStatus::insert($status);
    }
}
