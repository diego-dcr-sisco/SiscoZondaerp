<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Properties;

class PropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $values = [
            'Sedes',
            'Referencias',
            'Zonas/Áreas',
            'Planos',
            'Archivos'
        ];
        for($i = 0; $i<count($values); $i++)
        {
            Properties::create([
                'name' =>  $values[$i],
            ]); 
        }
    }
}
