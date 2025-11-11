<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxRegime;

class TaxRegimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxs = [
            'General de Ley Personas Morales',
            'Personas Morales con Fines no Lucrativos',
            'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            'Arrendamiento',
            'Régimen de Enajenación o Adquisición de Bienes',
            'Demás ingresos',
            'Consolidación',
            'Residentes en el Extranjero sin Establecimiento Permanente en México',
            'Ingresos por Dividendos (socios y accionistas)',
            'Personas Físicas con Actividades Empresariales y Profesionales',
            'Ingresos por intereses',
            'Régimen de los ingresos por obtención de premios',
            'Sin obligaciones fiscales',
            'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
            'Incorporación Fiscal',
            'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            'Opcional para Grupos de Sociedades',
            'Coordinados',
            'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            'Régimen Simplificado de Confianza',
            'Hidrocarburos',
            'De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales',
            'Enajenación de acciones en bolsa de valores'
        ];

        foreach ($taxs as $tax) {
            TaxRegime::create([
                'name' => $tax,
            ]);
        }
    }
}
