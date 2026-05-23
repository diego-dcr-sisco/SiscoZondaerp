<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\CfdiUsage;

class CfdiUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/CfdiUsageSeeder.php
public function run()
{
    $usages = [
        ['code' => 'G01', 'description' => 'Adquisición de mercancías', 'type' => 'G', 'applicable_to' => 'both'],
        ['code' => 'G02', 'description' => 'Devoluciones, descuentos o bonificaciones', 'type' => 'G', 'applicable_to' => 'both'],
        ['code' => 'G03', 'description' => 'Gastos en general', 'type' => 'G', 'applicable_to' => 'both'],
        ['code' => 'I01', 'description' => 'Construcciones', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I02', 'description' => 'Mobiliario y equipo de oficina por inversiones', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I03', 'description' => 'Equipo de transporte', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I04', 'description' => 'Equipo de computo y accesorios', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I05', 'description' => 'Dados, troqueles, moldes, matrices y herramental', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I06', 'description' => 'Comunicaciones telefónicas', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I07', 'description' => 'Comunicaciones satelitales', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'I08', 'description' => 'Otra maquinaria y equipo', 'type' => 'I', 'applicable_to' => 'both'],
        ['code' => 'D01', 'description' => 'Honorarios médicos, dentales y gastos hospitalarios', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D02', 'description' => 'Gastos médicos por incapacidad o discapacidad', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D03', 'description' => 'Gastos funerales', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D04', 'description' => 'Donativos', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D05', 'description' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D06', 'description' => 'Aportaciones voluntarias al SAR', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D07', 'description' => 'Primas por seguros de gastos médicos', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D08', 'description' => 'Gastos de transportación escolar obligatoria', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D09', 'description' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'D10', 'description' => 'Pagos por servicios educativos (colegiaturas)', 'type' => 'D', 'applicable_to' => 'physical'],
        ['code' => 'S01', 'description' => 'Sin efectos fiscales', 'type' => 'S', 'applicable_to' => 'both'],
        ['code' => 'CP01', 'description' => 'Pagos', 'type' => 'CP', 'applicable_to' => 'both'],
        ['code' => 'CN01', 'description' => 'Nómina', 'type' => 'CN', 'applicable_to' => 'both'],
    ];

    foreach ($usages as $usage) {
        CfdiUsage::create($usage);
    }
}
}
