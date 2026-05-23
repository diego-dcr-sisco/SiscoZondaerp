<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExecFrequency;

class ExecFrequencySeeder extends Seeder {
    
    public function run(): void {
        $frequency = [
            [
                'name' => 'Día',
            ],
            [
                'name' => 'Semanal',
            ],
            [
                'name' => 'Mensual',
            ],
            [
                'name' => 'Anual',
            ],    
            [
                'name' => 'Por periodo',
            ],        
        ];

        Execfrequency::insert($frequency);
    }
}
