<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCode;
use App\Models\Device;

class DeviceQrScannerCode extends Seeder
{
    public function run(): void
    {
        Device::chunk(1000, function ($devices) {
            foreach ($devices as $device) {
                try {
                    $device->update([
                        'qr' => QrCode::format('png')->size(200)->generate($device->code),
                    ]);
                } catch (\Exception $e) {
                    logger()->error("Error actualizando QR para Device ID: {$device->id} - {$e->getMessage()}");
                }
            }
        });
    }

}


