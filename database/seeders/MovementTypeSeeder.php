<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MovementType;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movement_type = [
            ['Devolucion', 'in'],
            ['Recepcion', 'in'],
            ['Transpaso entrada', 'in'],
            ['Regularizacion entrada', 'in'],
            ['Deterioro', 'out'],
            ['Robo', 'out'],
            ['Transpaso salida', 'out'],
            ['Consumo', 'out'],
            ['Regularizacion salida', 'out'],
            ['Devolucion a proveedor', 'out']
        ];

        foreach ($movement_type as $item) {
            MovementType::create([
                'name' => $item[0],  // Nombre del movimiento (ej: "Devolucion")
                'type' => $item[1],  // Tipo: 'in' o 'out'
            ]);
        }
    }
}
