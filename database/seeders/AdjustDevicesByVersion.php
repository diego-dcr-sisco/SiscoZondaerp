<?php

namespace Database\Seeders;

use App\Models\FloorplanVersion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;

class AdjustDevicesByVersion extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
                //Codigo para ajustar de una version v2 a una v1 => (puede ser v3 a v2)

        $floorplan_id = 76;
        $version = 2;
        $found_version = 1;


        $devicesV2 = Device::where('floorplan_id', $floorplan_id)->where('version', $version)->get();

        echo "Iniciando proceso de actualización...\n";
        echo "Se encontraron " . $devicesV2->count() . " dispositivos en la versión $version\n\n";

        foreach($devicesV2 as $deviceV2) {
            echo "Procesando dispositivo V2 - ID: $deviceV2->id, NPlan: $deviceV2->nplan, Item: $deviceV2->itemnumber\n";

            $deviceV1 = Device::where('floorplan_id', $floorplan_id)
                            ->where('version', $found_version)
                            ->where('nplan', $deviceV2->nplan)
                            ->where('itemnumber', $deviceV2->itemnumber)
                            ->where('type_control_point_id', $deviceV2->type_control_point_id)
                            ->first();

            if($deviceV1) {
                echo "  → Dispositivo V1 encontrado (ID: $deviceV1->id)\n";
                echo "  → Valores ANTES de actualizar:\n";
                echo "    - application_area_id: $deviceV1->application_area_id\n";

                $deviceV1->update([
                    'application_area_id' => $deviceV2->application_area_id
                ]);

                // Recargar el modelo para obtener los valores actualizados
                $deviceV1->refresh();

                echo "  → Valores DESPUÉS de actualizar:\n";
                echo "    - application_area_id: $deviceV1->application_area_id\n";
            } else {
                echo "  → No se encontró dispositivo equivalente en V1\n";
            }
            echo "----------------------------------------\n";
        }

        echo "\nProceso de actualización completado.\n";*/

        // Codigo para Copiar dispositivos en el mismo plano pero con una nueva version con condiciones de accion.
        $floorplan_id = 77;
        $devicesV1 = Device::where('floorplan_id', $floorplan_id)->where('version', 1)->get();
        $non_nplans = [144, 145, 146, 147];
        $replace_nplans = [
            148 => 144,
            149 => 145,
            150 => 146,
            151 => 147,
            152 => 148,
            153 => 149,
            154 => 150
        ];
        $count = 0;
        $new_version = 2;

        // Crear nueva versión del floorplan
        $new_fp = FloorplanVersion::create([
            'floorplan_id' => $floorplan_id,
            'version' => $new_version
        ]);

        echo "Iniciando clonación de dispositivos a versión $new_version\n";
        echo "Se encontraron " . $devicesV1->count() . " dispositivos en versión 1\n";
        echo "Excluyendo nplans: " . implode(', ', $non_nplans) . "\n";
        echo "Reemplazando nplans: " . implode(', ', array_keys($replace_nplans)) . " por " . implode(', ', array_values($replace_nplans)) . "\n\n";

        foreach ($devicesV1 as $deviceV1) {
            // Verificar si el nplan está en la lista de excluidos
            if (in_array($deviceV1->nplan, $non_nplans)) {
                echo "❌ Dispositivo excluido (nplan {$deviceV1->nplan}): ID V1 = $deviceV1->id\n";
                continue;
            }

            // Clonar el dispositivo
            $newDevice = $deviceV1->replicate();
            $newDevice->version = $new_version;

            // Verificar si el nplan debe ser reemplazado
            if (array_key_exists($deviceV1->nplan, $replace_nplans)) {
                $new_nplan = $replace_nplans[$deviceV1->nplan];
                $newDevice->nplan = $new_nplan;
                echo "🔄 Reemplazando nplan: {$deviceV1->nplan} -> $new_nplan para dispositivo ID V1 = $deviceV1->id\n";
            }

            // Guardar el nuevo dispositivo
            $newDevice->save();
            $count++;
            echo "✅ Dispositivo clonado: ID V1 = $deviceV1->id, Nuevo ID = $newDevice->id, nplan = $newDevice->nplan\n";
        }

        echo "\nTotal de dispositivos clonados: $count\n";
    }
}