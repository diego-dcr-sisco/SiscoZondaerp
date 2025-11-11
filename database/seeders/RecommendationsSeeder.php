<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Recommendations;

class RecommendationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $descriptions = [
            'Mantener puertas, accesos cerrados, cuando la operación no lo requiera, para evitar el ingreso de organismos al interior.',
            'Limpieza constante, respetar programas existentes, para evitar acumulamientos de residuos que sean atrayentes de insectos rastreros, voladores y roedores.',
            'No mantener aguas superficiales o retenidas, encharcamientos que sean atrayentes o sirven para generación de plagas.',
            'Mantener áreas verdes, jardines y/o vegetación con el mantenimiento adecuado, evitar maleza alta que sea refugio de insectos.',
            'Sellar huecos, hendiduras y/o grietas en piso y paredes, que sirvan de refugio para insectos rastreros y/o roedores.',
            'Realizar mantenimiento a puertas, consiguiendo un sello hermético, evitando el ingreso de organismos (guardapolvos en condiciones, empalme de puertas).',
            'Colocación de malla mosquitera en ventanas, extractores, o zonas de ventilación, para evitar el ingreso de organismos rastreros, voladores, roedores y/o aves.',
            'Inspección de materia prima, materiales (tarimas, cajas), unidades de transporte, antes de ingresar a áreas de producción, evitando el arribo de plagas, omitiendo los controles establecidos.',
            'No realizar comportamientos y hábitos higiénicos que generen atrayentes de organismos, tales como ingerir alimentos en áreas inadecuadas, restos de comida, etc.',
        ];

        for($i = 0; $i<count($descriptions); $i++){
            Recommendations::create([
                'description' => $descriptions[$i]
            ]);
        }
    }
}
