<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PestCategory;

class PestCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pestCategories = [
            'Cucarachas (Blatodeos)',
            'Polillas (Lepidópteros)',
            'Tijeretas (Dermápteros)',
            'Plagas de la madera (Xilófagos)',
            'Cochinillas (Osniscídeos)',
            'Libélulas y caballitos del diablo (Odonatos)',
            'Fitosanitarios (Plagas fitosanitarios)',
            'Reptiles y Anfibios (Reptilia y Amphibia)',
            'Roedores (Múridos)',
            'Avispas, abejas (Himenópteros)',
            'Microorganismos (Microorganismos)',
            'Pulgas, chinches (Ectoparásitos)',
            'Gorgojos, escarabajos (Coleópteros)',
            'Hormigas (Hormigas)',
            'Ácaros (Ácaros)',
            'Milpies (Miriápodos)',
            'Otras plagas (Otras plagas)',
            'Grillos, saltamontes (Ortópteros)',
            'Aves (Aves)',
            'Moscas y mosquitos (Dípteros)',
            'Arañas (Arácnidos)'
        ];

        foreach ($pestCategories as $pestCategory) {
            PestCategory::create([
                'category' => $pestCategory,
            ]);
        }
    }
}
