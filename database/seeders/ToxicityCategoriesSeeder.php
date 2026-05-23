<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ToxicityCategories;

class ToxicityCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $toxicity1 = ToxicityCategories::create([
            'name' => 'Cancerígenas, mutagénas o tóxicas para la reproducción',
        ]);
        $toxicity2 = ToxicityCategories::create([
            'name' => 'Sensibilización respiratoria, Categoria y SubCategorias 1A y 1B',
        ]);
        $toxicity3 = ToxicityCategories::create([
            'name' => 'Toxicidad aguda',
        ]);
        $toxicity4 = ToxicityCategories::create([
            'name' => 'Toxicidad específica exposición repetida',
        ]);
        $toxicity5 = ToxicityCategories::create([
            'name' => 'Toxicidad específica exposicíon única',
        ]);
    }
}
