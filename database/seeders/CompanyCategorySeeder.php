<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompanyCategory;

class CompanyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company_categories = [
            [
                'category' => 'Mineria y Extracción de materiales',
            ],
            [
                'category' => 'Automotriz y Partes para automoviles',
            ],
            [
                'category' => 'Tecnología de la Información (TI)',
            ],
            [
                'category' => 'Salud y Cuidado Personal',
            ],
            [
                'category' => 'Energía y Medio Ambiente',
            ],
            [
                'category' => 'Educación y Formación',
            ],
            [
                'category' => 'Servicios Financieros',
            ],
            [
                'category' => 'Construcción y Arquitectura',
            ],
            [
                'category' => 'Moda y Belleza',
            ],
            [
                'category' => 'Entretenimiento y Medios',
            ],
            [
                'category' => 'Viajes y Turismo',
            ],
            [
                'category' => 'Agricultura',
            ],
            [
                'category' => 'Alimentos (Naturales y/o Procesados)',
            ],
            [
                'category' => 'Supermercado',
            ],
            [
                'category' => 'Cafeteria/Restaurante',
            ],
            [
                'category' => 'Comida Rapida',
            ],
            [
                'category' => 'Servicios de Consultoría',
            ],
            [
                'category' => 'Comercio Minorista',
            ],
            [
                'category' => 'Manufactura y Producción',
            ],
            [
                'category' => 'Transporte y Logística',
            ],
            [
                'category' => 'Bienes Raíces',
            ],
            [
                'category' => 'Deportes y Recreación',
            ],
            [
                'category' => 'Arte y Cultura',
            ],
            [
                'category' => 'Gobierno',
            ],
            [
                'category' => 'Organizaciones Sin Fines de Lucro',
            ],
            [
                'category' => 'Control de plagas',
            ],
        ];
        
        CompanyCategory::insert($company_categories);
    }
}
