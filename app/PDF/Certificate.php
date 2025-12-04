<?php
namespace App\PDF;

use App\Models\EvidencePhoto;
use Carbon\Carbon;

use App\Models\Order;
use App\Models\User;
use App\Models\UserFile;
use App\Models\FloorPlans;
use App\Models\Device;
use App\Models\ControlPoint;
use App\Models\Question;
use App\Models\ControlPointQuestion;
use App\Models\DeviceProduct;
use App\Models\DevicePest;
use App\Models\OrderIncidents;
use App\Models\OrderRecommendation;
use App\Models\FloorplanVersion;

use Illuminate\Support\Facades\Storage;

//require_once 'vendor/autoload.php';

class Certificate
{
    private $file_answers_path = 'datas/json/answers.json';

    private $order_id;
    private $order;
    private $data;

    private function extractUnits($text)
    {
        if ($text == null) {
            return '';
        }

        $matches = [];
        if (preg_match('/\((.*?)\)/', $text, $matches)) {
            return $matches[1];
        }

        return $text;
    }

    function cleanBase64Prefix($base64String)
    {
        // Look for the pattern data:image/...;base64, and remove everything up to the comma
        if (preg_match('/^data:image\/[a-zA-Z]+;base64,/', $base64String)) {
            return preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $base64String);
        }

        // If it doesn't have the expected format, return the original string
        return $base64String;
    }

    private function ensureTempSignatureDir()
    {
        $tempDir = storage_path('app/temp/signatures');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return $tempDir;
    }

    private function createSignatureImage($base64Signature)
    {
        // Limpiar el base64 y decodificar
        $cleanBase64 = $this->cleanBase64Prefix($base64Signature);
        $signatureData = base64_decode($cleanBase64);

        if (!$signatureData) {
            return null;
        }

        // Asegurar que el directorio existe
        $tempDir = $this->ensureTempSignatureDir();

        // Crear nombre de archivo único
        $filename = uniqid('signature_', true) . '.png';
        $tempImage = $tempDir . '/' . $filename;

        // Guardar la imagen decodificada
        if (file_put_contents($tempImage, $signatureData) !== false) {
            return $tempImage;
        }

        return null;
    }

    private function getOptions($id, $answers)
    {
        foreach ($answers as $answer) {
            if ($answer['id'] == $id) {
                return $answer['options'];
            }
        }
        return [];
    }

    public function __construct(int $orderId)
    {
        $this->order_id = $orderId;
        $this->order = Order::find($orderId);

        $order_no = explode('-', $this->order->folio);
        $services_names = $this->order->services->pluck('name')->toArray();
        $services_str = !empty($services_names) ? implode('_', $services_names) : 'Sin_servicio';

        $pdf_name = $this->cleanFileName(
            'Certificado ' . $order_no[1] .
            ' ' . $this->order->customer->name .
            ' Fecha ' . $this->order->programmed_date .
            ' Servicio ' . $services_str
        ) . '.pdf';


        $this->data = [
            'title' => 'Certificado de Servicio ' . $order_no[1],
            'filename' => $pdf_name,
            'order' => [],
            'branch' => [],
            'customer' => [],
            'technician' => [],
            'services' => [],
            'products' => [],
            'reviews' => [],
            'notes' => [],
            'recommendations' => [],
            'photo_evidences' => []
        ];
    }

    private function cleanFileName($string)
    {
        $string = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $string = preg_replace("/[^a-zA-Z0-9_\-\s\.]/", "", $string);
        $string = preg_replace('/\s+/', '_', $string);

        return substr($string, 0, 100);
    }

    public function order()
    {
        $this->data['order'] = [
            'programmed_date' => Carbon::parse($this->order->programmed_date)->format('d-m-Y'),
            'start' => Carbon::parse($this->order->programmed_date)->format('d-m-Y') . ' - ' . Carbon::parse($this->order->start_time)->format('H:i'),
            'end' => Carbon::parse($this->order->completed_date)->format('d-m-Y') . ' - ' . Carbon::parse($this->order->end_time)->format('H:i'),
            'notes' => $this->order->notes,
        ];
    }

    public function branch()
    {
        $this->data['branch'] = [
            'name' => 'SISCOPLAGAS',
            'sede' => $this->order->customer->branch->name,
            'address' => $this->order->customer->branch->address,
            'email' => $this->order->customer->branch->email,
            'phone' => $this->order->customer->branch->phone,
            'no_license' => $this->order->customer->branch->license_number
        ];
    }

    public function customer()
    {
        $signaturePath = null;

        // Crear imagen temporal si existe firma
        if ($this->order->customer_signature) {
            $signaturePath = $this->createSignatureImage($this->order->customer_signature);
        }

        $this->data['customer'] = [
            'name' => $this->order->customer->name ?? '-',
            'address' => $this->order->customer->address ?? '-',
            'email' => $this->order->customer->email ?? '-',
            'phone' => $this->order->customer->phone ?? '-',
            'social_reason' => $this->order->customer->tax_name ?? $this->order->customer->matrix->name ?? '-',
            'city' => $this->order->customer->city ?? '-',
            'state' => $this->order->customer->state ?? '-',
            'rfc' => $this->order->customer->rfc ?? '-',
            'signed_by' => $this->order->signature_name ?? '-',
            'signature' => $signaturePath ? 'file://' . $signaturePath : '', // Guardar también la ruta relativa
            'signature_base64' => $this->order->customer_signature // Mantener original
        ];
    }

    public function technician()
    {
        $user_id = null;
        $signature_base64 = null;

        if ($this->order->closed_by != null) {
            $user_id = $this->order->closed_by;
        } else {
            $user_id = $this->order->technicians()?->first()?->user_id ?? null;
        }

        $user = User::find($user_id);

        $userfile = UserFile::where('user_id', $user_id)
            ->where('filename_id', 15)
            ->first();

        if ($userfile && $userfile->path) {
            $signature_img = Storage::disk('public')->get(ltrim($userfile->path, '/'));
            $signature_base64 = base64_encode($signature_img);
        }

        $this->data['technician'] = [
            'name' => $user->name ?? '-',
            'rfc' => $user->roleData->rfc ?? '-',
            'signature' => $signature_base64
        ];

        $this->data['technician'];
    }

    public function services()
    {
        $services_data = [];
        foreach ($this->order->services()->get() as $service) {
            $services_data[] = [
                'name' => $service->name,
                'text' => $this->order->propagateByService($service->id)->text ?? '',
            ];
        }

        $this->data['services'] = $services_data;
    }

    public function products()
    {
        $products_data = [];

        foreach ($this->order->products()->get() as $order_product) {
            $products_data[] = [
                'name' => $order_product->product->name,
                'active_ingredient' => $order_product->product->active_ingredient ?? '-',
                'no_register' => $order_product->product->register_number ?? '-',
                'safety_period' => $order_product->product->safety_period ?? '-',
                'application_method' => $order_product->appMethod->name ?? '-',
                'dosage' => $order_product->dosage ?? $order_product->product->dosage ?? '-',
                'amount' => $order_product->amount,
                'lot' => $order_product->lot->registration_number ?? $order_product->possible_lot ?? '-',
                'metric' => $this->extractUnits($order_product->metric->value ?? $order_product->product->metric->value) ?? '-'
            ];
        }

        $this->data['products'] = [
            'headers' => ['Nombre comercial', 'Materia activa', 'No Registro', 'Plazo seguridad', 'Método de aplicación', 'Dosificación', 'Consumo', 'Lote'],
            'data' => $products_data,
        ];
    }

    public function devices()
    {
        $_reviews = [];
        $devices_1 = [];
        $devices_2 = [];
        $review_devices = [];

        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);
        $services = $this->order->services;

        $devices_1 = Device::whereIn('id', OrderIncidents::where('order_id', $this->order->id)->pluck('device_id'))
            ->pluck('id')
            ->toArray();

        $floorplans = FloorPlans::where('customer_id', $this->order->customer_id)
            ->whereIn('service_id', $this->order->services()->pluck('service.id'))
            ->get();

        if ($floorplans->isNotEmpty()) {
            $versions = FloorplanVersion::whereIn('floorplan_id', $floorplans->pluck('id'))->get();
            if ($versions->isNotEmpty()) {
                $version = $versions->where('updated_at', '<=', $this->order->programmed_date)->last();

                if (!$version) {
                    $version = $versions->last();
                }

                $devices_2 = Device::whereIn('floorplan_id', $floorplans->pluck('id'))
                    ->where('version', $version->version)
                    ->pluck('id')
                    ->toArray();
            }
        }

        $device_ids = array_unique(array_merge($devices_1, $devices_2));
        $devices_query = Device::whereIn('id', $device_ids);

        $devices = $devices_query->get();
        $floorplans = FloorPlans::whereIn('id', $devices->pluck('floorplan_id'))->get();

        foreach ($floorplans as $floorplan) {
            $control_point_data = [];
            $fds = $devices_query->where('floorplan_id', $floorplan->id)->get();
            $control_points = ControlPoint::whereIn('id', $fds->pluck('type_control_point_id')
                ->unique())
                ->get();

            foreach ($control_points as $control_point) {
                $questions = Question::whereIn(
                    'id',
                    ControlPointQuestion::where('control_point_id', $control_point->id)
                        ->pluck('question_id')
                        ->unique()
                )->get();
                $question_headers = $questions->pluck('question')->toArray();

                $headers = array_merge(['Zona', 'Código', 'Producto y consumo', 'Valor revisión'], $question_headers);

                $found_devices = $devices_query->where('type_control_point_id', $control_point->id)
                    ->where('floorplan_id', $floorplan->id)
                    ->orderBy('nplan')->get();

                foreach ($found_devices as $found_device) {
                    $device_products = DeviceProduct::where('order_id', $this->order->id)->where('device_id', $found_device->id)->get();
                    $device_pests = DevicePest::where('order_id', $this->order->id)->where('device_id', $found_device->id)->get();

                    $question_data = [];
                    foreach ($questions as $question) {
                        $incident = OrderIncidents::where('order_id', $this->order->id)
                            ->where('device_id', $found_device->id)
                            ->where('question_id', $question->id)
                            ->first();

                        $question_data[] = [
                            'question' => $question->question,
                            'answer' => $incident->answer ?? '',
                        ];
                    }

                    $device_state = $found_device->states($this->order->id);
                    $observation = $device_state->observations ?? null;

                    if (!$observation) {
                        $observation = OrderIncidents::where('order_id', $this->order->id)
                            ->where('device_id', $found_device->id)
                            ->whereIn('question_id', [33, 34, 35])
                            ->first()
                            ->answer ?? null;
                    }

                    $devices_data[] = [
                        'zone' => $found_device->applicationArea->name ?? '-',
                        'code' => $found_device->code,
                        'intake' => $device_products->map(function ($device_product) {
                            return $device_product
                                ? $device_product->product->name . ' (' . $device_product->quantity . ' ' . $this->extractUnits($device_product->product->metric->value) . ')'
                                : '-';
                        })->implode(', '),
                        'pests' => $device_pests->map(function ($device_pest) {
                            return '(' . $device_pest->total . ') ' . $device_pest->pest->name;
                        })->implode(', '),
                        'questions' => $question_data,
                        'observations' => $observation
                    ];
                }

                $control_point_data[] = [
                    'name' => $control_point->name,
                    'headers' => $headers,
                    'devices' => $devices_data,
                ];
            }

            $_reviews[] = [
                'sede' => $floorplan->customer->name,
                'floorplan' => $floorplan->filename,
                'control_points' => $control_point_data
            ];
        }

        $this->data['reviews'] = $_reviews;
    }

    public function notes()
    {
        $this->data['notes'] = !empty($this->order->notes) && trim($this->order->notes) != '<br>'
            ? $this->order->notes
            : 'Sin notas';
    }

    public function recommendations()
    {
        $this->data['recommendations'] = ''; // Inicializar
        $services = $this->order->services()->get();

        foreach ($services as $service) {
            $recs = OrderRecommendation::where('order_id', $this->order->id)->where('service_id', $service->id)->get();

            foreach ($recs as $index => $rec) {
                if ($rec->recommendation_id) {
                    $this->data['recommendations'] .= (($index + 1 . ') ' . $rec->recommendation->description) ?? '') . "<br>";
                } else {
                    $this->data['recommendations'] .= $rec->recommendation_text ?? '' . "<br>";
                }
            }
        }
    }

    public function photoEvidences()
    {
        $photo_evidences = [];
        $evidences = EvidencePhoto::where('order_id', $this->order_id)->get();

        foreach ($evidences as $evidence) {
            $area = $evidence->area;

            // Inicializar el array del área si no existe
            if (!isset($photo_evidences[$area])) {
                $photo_evidences[$area] = [];
            }

            // Agregar la evidencia con solo imagen y descripción
            $photo_evidences[$area][] = [
                'image' => $evidence->evidence_data['image'] ?? '',
                'description' => $evidence->description
            ];
        }

        $this->data['photo_evidences'] = $photo_evidences;
    }

    public function getData(): array
    {
        return $this->data;
    }

}