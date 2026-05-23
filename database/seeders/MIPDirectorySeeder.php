<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Directory;

class MIPDirectorySeeder extends Seeder
{
    private $path = 'mip_directory/';
    private $mip_directories = [
        'MIP',
        'Contrato de servicio',
        'Justificación',
        'Datos de la empresa',
        'Certificación MIP',
        'Plano de ubicación de dispositivos',
        'Responsabilidades',
        'Plago objeto',
        'Calendarización de actividades',
        'Descripción de actividades POEs',
        'Métodos preventivos',
        'Métodos correctivos',
        'Información de plaguicidas',
        'Reportes',
        'Gráficas de tendencias',
        'Señaléticas',
        'Pago seguro'
    ];

    public function run(): void
    {
        foreach ($this->mip_directories as $i => $mip_name) {
            $folder_name = $this->path . $mip_name;

            if (!Storage::disk('public')->exists($folder_name)) {
                Storage::disk('public')->makeDirectory($folder_name);
            }
        }
    }
}
