<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pendiente',
            ],
            [
                'name' => 'Activo',
            ],
            [
                'name' => 'Eliminado',
            ],
            [
                'name' => 'En vacaciones',
            ],
            [
                'name' => 'Enfermo',
            ],
            [
                'name' => 'Incapacitado',
            ],
            [
                'name' => 'Permiso',
            ],
        ];

        foreach ($statuses as $status) {
            \App\Models\Status::create($status);
        }
    }
}
