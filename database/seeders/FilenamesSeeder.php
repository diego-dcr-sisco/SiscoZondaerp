<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Filenames;

class FilenamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileNames = [
            'customer' => [
                'Certificado RFC',
                'Comprobante domicilio fiscal',
                'Credencial INE',
                'Estatutos de incorporación',
                'Comprobante situación fiscal',
                'Manual del portal',
            ],
            'user' => [
                'INE',
                'CURP',
                'Constancia de situación fiscal (RFC)',
                'NSS',
                'Acta de nacimiento',
                'Comprobante de domicilio',
                'Licencia para conducir',
                'Foto',
                'Firma',
                'Examen medico general',
                'Examen de colinesterasa',
                'Certificado DC3 Alturas',
                'Certificado DC3 Espacios confinados'
            ],
            'product' => [
                'Ficha del responsable técnico (RP)',
                'Ficha técnica',
                'Especificaciones de seguridad',
                'Especificación de registro',
                'Registro sanitario'
            ],
        ];

        $data = [];

        foreach ($fileNames as $type => $names) {
            foreach ($names as $name) {
                $data[] = [
                    'name' => $name,
                    'type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Filenames::insert($data);
    }
}
