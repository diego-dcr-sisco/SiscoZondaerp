<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Purpose;

class PurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purposes = ['Derribo', 'Captura', 'Repeler'];
        
        foreach ($purposes as $purpose) {
            Purpose::create([
                'type' => $purpose,
            ]);
        }
    }
}
