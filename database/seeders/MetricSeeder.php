<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Metric;

class MetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Unidades
    // wt => Weight
    // vol => Volumen
    // len => Length
    // area => Area
    // unit => Units

    $metrics = [
        [
            'metric' => 'Miligramos (mg)',
            'type' => 'wt',
        ],
        [
            'metric'=> 'Gramos (g)',
            'type' => 'wt',
        ],
        [
            'metric'=> 'Kilogramos (kg)',
            'type' => 'wt',
        ],
        [
            'metric'=> 'Mililitros (ml)',
            'type' => 'vol',
        ],
        [
            'metric'=> 'Litros (L)',
            'type' => 'vol',
        ],
        [
            'metric'=> 'Metros cúbicos (m³)',
            'type' => 'vol',
        ],
        [
            'metric'=> 'Centímetros (cm)',
            'type' => 'len',
        ],
        [
            'metric'=> 'Metros (m)',
            'type' => 'len',
        ],
        [
            'metric'=> 'Kilómetros (km)',
            'type' => 'len',
        ],
        [
            'metric'=> 'Metros cuadrados (m²)',
            'type' => 'area',
        ],
        [
            'metric'=> 'Unidades (uds)',
            'type' => 'unit',
        ],
        [
            'metric'=> 'Gotas (gts)',
            'type' => 'unit',
        ],
    ];

    foreach ($metrics as $metric) {
        Metric::create([
            'value' => $metric['metric'],
            'type' => $metric['type'],
        ]);
    }
}

}
