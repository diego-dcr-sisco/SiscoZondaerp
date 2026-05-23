<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reference_type;

class ReferenceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        reference_type::create([
            'name' => 'Servicio',
        ]);

        reference_type::create([
            'name' => 'Compras',
        ]);

        reference_type::create([
            'name' => 'Ventas',
        ]);
    }
}
