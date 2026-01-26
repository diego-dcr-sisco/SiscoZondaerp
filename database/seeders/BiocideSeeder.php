<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Biocide;

class BiocideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $biocides = [
            [
                'type' => 'Biocidas para la higiene humana',
                'group' => 'Desinfectantes'
            ],
            [
                'type' => 'Desinfectantes y alguicidas no destinados a la aplicación directa a personas o animales',
                'group' => 'Desinfectantes'
            ],
            [
                'type' => 'Biocidas para la higiene veterinaria',
                'group' => 'Desinfectantes'
            ],
            [
                'type' => 'Desinfectantes para los equipos, recipientes, utensilios y superficies que están en contacto con los alimentos y piensos',
                'group' => 'Desinfectantes'
            ],
            [
                'type' => 'Desinfectantes empleados en la desinfección del agua potable',
                'group' => 'Desinfectantes'
            ],
            [
                'type' => 'Conservantes para los productos durante su almacenamiento',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Conservantes para películas',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Protectores para maderas',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Protectores de fibras, cuero, caucho y materiales polimerizados',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Protectores de líquidos utilizados en sistemas de refrigeración y procesos industriales',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Conservantes de materiales para construcción',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Productos antimoho',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Protectores de líquidos empleados para trabajar o cortar materiales',
                'group' => 'Conservantes'
            ],
            [
                'type' => 'Rodenticidas',
                'group' => 'Plaguicidas'
            ],
            [
                'type' => 'Avicidas',
                'group' => 'Plaguicidas'
            ],
            [
                'type' => 'Piscicidas',
                'group' => 'Plaguicidas'
            ],
            [
                'type' => 'Molusquicidas, vermicidas y productos para controlar otros invertebrados',
                'group' => 'Plaguicidas'
            ],
            [
                'type' => 'Insecticidas, acaricidas y productos para controlar otros artrópodos',
                'group' => 'Plaguicidas'
            ],
            [
                'type' => 'Repelentes y atrayentes',
                'group' => 'Plaguicidas'
            ],
            [
                'type' => 'Desinfectantes y alguicidas no destinados a la aplicación directa a personas o animales',
                'group' => 'Otros'
            ],
            [
                'type' => 'Conservantes para alimentos o piensos',
                'group' => 'Otros'
            ],
            [
                'type' => 'Productos antiincrustantes',
                'group' => 'Otros'
            ],
            [
                'type' => 'Líquidos para embalsamiento o taxidermia',
                'group' => 'Otros'
            ],
            [
                'type' => 'Control de otros animales vertebrados',
                'group' => 'Otros'
            ],
        ];

        foreach ($biocides as $biocide) {
            Biocide::create([
                'type' => $biocide['type'],
                'group'=> $biocide['group'],
            ]);
        }
    }
}
