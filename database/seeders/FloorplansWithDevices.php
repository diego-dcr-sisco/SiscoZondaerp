<?php

namespace Database\Seeders;

use App\Models\ControlPoint;
use App\Models\FloorPlans;
use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FloorplansWithDevices extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $floorplan_id = 42;
        
        echo "=== Iniciando seeder FloorplansWithDevices ===\n";
        echo "Buscando dispositivos para floorplan_id: {$floorplan_id}\n";
        
        $devices = Device::where('floorplan_id', $floorplan_id)
                        ->where('type_control_point_id', 9)
                        ->get();
        
        echo "Dispositivos encontrados: ".count($devices)."\n";
        
        foreach ($devices as $index => $device) {
            echo "\nProcesando dispositivo #".($index+1)." (ID: {$device->id})\n";
            
            $control_point = ControlPoint::find(9);
            
            if (!$control_point) {
                echo "¡Advertencia! ControlPoint no encontrado para ID: {$device->control_point_id}\n";
                continue;
            }
            
            $new_code = $control_point->code . '-' . $device->nplan;
            echo "Código actual: {$device->code} | Nuevo código: {$new_code}\n";
            
            $result = $device->update([
                'code' => $new_code,
            ]);
            
            echo $result ? "✅ Actualizado correctamente\n" : "❌ Error al actualizar\n";
        }
        
        echo "\n=== Proceso completado ===\n";
        echo "Total dispositivos procesados: ".count($devices)."\n";
    }
}
