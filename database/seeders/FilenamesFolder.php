<?php

namespace Database\Seeders;

use App\Models\Filenames;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilenamesFolder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileNames = [
            'customer' => [
                'Certificado RFC' => 'rfc',
                'Comprobante domicilio fiscal' => 'tax_address',
                'Credencial INE' => 'ine',
                'Estatutos de incorporación' => 'statute',
                'Comprobante situación fiscal' => 'situation_fiscal',
                'Manual del portal' => 'portal',
            ],
            'user' => [
                'INE' => 'ine',
                'CURP' => 'curp',
                'Constancia de situación fiscal (RFC)' => 'rfc',
                'NSS' => 'nss',
                'Acta de nacimiento' => 'birth_certificate',
                'Comprobante de domicilio' => 'address_certificate',
                'Licencia para conducir' => 'license',
                'Foto' => 'photo',
                'Firma' => 'signatures',
                'Examen medico general' => 'medical_test',
                'Examen de colinesterasa' => 'colesterol_test',
                'Certificado DC3 Alturas' => 'height_certificate',
                'Certificado DC3 Espacios confinados' => 'confines_certificate'
            ],
            'product' => [
                'Ficha del responsable técnico (RP)' => 'rp',
                'Ficha técnica' => 'technical_sheet',
                'Especificaciones de seguridad' => 'security_specifications',
                'Especificación de registro' => 'registration_specification',
                'Registro sanitario' => 'sanitary_registration'
            ],
        ];

        foreach ($fileNames as $type => $names) {
            foreach ($names as $name => $folder) {
                // Actualizamos los registros que coincidan con el nombre y el tipo
                Filenames::where('name', $name)
                    ->where('type', $type)
                    ->update(['folder' => $folder]);
            }
        }
    }
}