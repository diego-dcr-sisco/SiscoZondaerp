<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            'Pendiente',
            'Aceptada',
            'Finalizada',
            'Verificada',
            'Aprobada',
            'Cancelada',
        ];

        OrderStatus::insert(
            array_map(
                function ($name) {
                    return ['name' => $name];
                },
                $status
            )
        );
    }
}
