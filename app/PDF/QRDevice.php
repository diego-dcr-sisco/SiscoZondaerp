<?php
namespace App\PDF;

use App\Models\Device;
use Illuminate\Support\Facades\Storage;

class QRDevice
{
    private $floorplan_id;

    public function __construct()
    {
    }

    public function device($device_id)
    {
        $device = Device::find($device_id);

        $tempDir = storage_path('app/temp_qr/');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $qrBinary = $device->qr;

        // Generar nombre único para el archivo
        $filename = 'qr_' . $device->id . '_' . time() . '.png';
        $filePath = $tempDir . $filename;

        // Guardar como archivo PNG
        if (is_resource($qrBinary) && get_resource_type($qrBinary) === 'gd') {
            imagepng($qrBinary, $filePath);
        } else if (is_string($qrBinary)) {
            file_put_contents($filePath, $qrBinary);
        }

        $data = [
            'customer' => $device->floorplan->customer->name ?? '-',
            'floorplan' => $device->floorplan->filename ?? '-',
            'name' => $device->controlPoint->name . ' #' . $device->nplan,
            'code' => $device->code,
            'qr' => $filePath,
        ];

        return $data;
    }
}
