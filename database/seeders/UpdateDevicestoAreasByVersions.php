<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;

class UpdateDevicestoAreasByVersions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer_id = 329;
        $floorplan_id = 49;
        $version_to_update = 1;
        $version = 2;

        $devices_to_update = Device::where('floorplan_id', $floorplan_id)
            ->where('version', $version_to_update)
            ->get();

        $fetch_devices = Device::where('floorplan_id', $floorplan_id)
            ->where('version', $version)
            ->get();

        $this->command->info("Devices a actualizar (versión {$version_to_update}): {$devices_to_update->count()}");
        $this->command->info("Devices fuente (versión {$version}): {$fetch_devices->count()}");

        $updated = 0;
        $skipped = 0;

        foreach ($devices_to_update as $device) {
            $matching_device = $fetch_devices
                ->where('type_control_point_id', $device->type_control_point_id)
                ->where('nplan', $device->nplan)
                ->first();

            if ($matching_device) {
                $device->update([
                    'application_area_id' => $matching_device->application_area_id,
                ]);
                $this->command->line("  [OK] Device ID {$device->id} (nplan: {$device->nplan}) → area_id: {$matching_device->application_area_id}");
                $updated++;
            } else {
                $this->command->warn("  [SIN MATCH] Device ID {$device->id} (nplan: {$device->nplan}, type_control_point_id: {$device->type_control_point_id})");
                $skipped++;
            }
        }

        $this->command->info("─────────────────────────────────────");
        $this->command->info("Actualizados: {$updated} | Sin match: {$skipped}");
    }
}
