<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Zone;

class ZonesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            'San Luis',            
            'Aguascalientes',
            'Rioverde',
            'Cd. Valles',
            'Jalisco',
            'Tecoman',
            'Culiacan',
            'Durango',
            'Guanajuato',
            'Monterrey',
            'Queretaro',
            'Veracruz',
            'Yucatan',
            'Matamoros'
        ];
            
        foreach ($zones as $zone) {
            Zone::create(['name' => $zone]);
        }
    }
}
