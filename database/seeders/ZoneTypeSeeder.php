<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ZoneType;

class ZoneTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ZoneType::create([
            'name' => 'Blanca',
        ]);

        ZoneType::create([
            'name' =>  'Gris',
        ]);

        ZoneType::create([
            'name' => 'Negra',
        ]);
    }
}
