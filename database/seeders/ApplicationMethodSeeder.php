<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ApplicationMethod;

class ApplicationMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $application_methods = [
            'Adhesivo',
            'Aerosol',
            'Al boleo',
            'Aplicación rodenticida',
            'Aspersión manual',
            'Aspersión manual ambiental localizada',
            'Aspersión motorizada',
            'Bandeo',
            'Barrera zanja',
            'Bebederos',
            'Bioestación pared',
            'Bioestación suelo',
            'Bloque',
            'Bloque suspendido',
            'Brocheado',
            'Cañón lanzarredes',
            'Cebaderos',
            'Cebaderos alta seguridad',
            'Disolución en agua',
            'Disolución en agua del circuito',
            'Espolvoreo',
            'Estación',
            'Foco/Bulbo',
            'Fumigación',
            'Gel',
            'Granulado',
            'Inyección',
            'Inyección barrera',
            'Inyección madera',
            'Localizador/Dirigido(a)',
            'Material de soporte',
            'Nebulización',
            'Otros',
            'Pintura',
            'Producto fumígeno',
            'Puas exclusión',
            'Pulverización',
            'Punteo gel',
            'Red exclusión',
            'Rodillo',
            'Sin método de aplicación',
            'Suelta ejemplares',
            'Termonebulización',
            'Trampa adhesiva',
            'Trampa atrayente',
            'Trampa captura',
            'Trampa eléctrica',
            'Trampa feromonas',
            'ULV'
        ];

        foreach ($application_methods as $application_method) {
            ApplicationMethod::create([
                'name' => $application_method,
            ]);
        }
    }
}