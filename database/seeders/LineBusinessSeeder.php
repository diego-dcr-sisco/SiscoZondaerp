<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LineBusiness;

class LineBusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    { 
        $linesbuss = [
            'Control de aves',
            'Control de plagas',
            'Desinfección',
        ];
    
        foreach ($linesbuss as $linebuss) {
            LineBusiness::create([
                'name' => $linebuss,
            ]);
        }
    }
}
