<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Presentation;

class presentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presentations = [
            'Dispositivo',
            'Gas',
            'Liquido',
            'Gel',
            'Polvo',
            'Granulado',
            'Espuma',
            'Goma',
            'Otro',
        ];
        

        foreach ($presentations as $pres) {
            Presentation::create([
                'name' => $pres,
            ]);
        }
    }
}
