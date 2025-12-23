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
            foreach ($names as $name => $type) {
                Filenames::update(
                    [
                        'name' => $name,
                        'type' => $type,
                    ], 
                    [
                        'folder' => $type
                    ]
                );
            }
        }
    }
}
