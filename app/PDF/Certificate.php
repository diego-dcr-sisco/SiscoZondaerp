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
use App\Models\OrderProduct;
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

    function addBase64Prefix($base64String)
    {
        $prefix = 'data:image/png;base64,';
        if ($base64String === null || trim($base64String) === '') {
            return null;
        }
        if (trim($base64String) === $prefix) {
            return null;
        }
        if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64String)) {
            return $prefix . $base64String;
        }
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
        $pdf_name = '';
        $this->order_id = $orderId;
        $this->order = Order::find($orderId);

        if ($this->order->folio) {
            $order_no = explode('-', $this->order->folio);
            $folio = $order_no[1];
        } else {
            $folio = '';
        }

        $order_no = explode('-', $this->order->folio);
        $services_names = $this->order->services->pluck('name')->toArray();
        $services_str = !empty($services_names) ? implode('_', $services_names) : 'Sin_servicio';


        $pdf_name = $this->cleanFileName(
            'Certificado' . $folio .
            '_' . $this->order->customer->name .
            '_Fecha ' . $this->order->programmed_date .
            '_Servicio ' . $services_str
        ) . '.pdf';


        $this->data = [
            'title' => 'Certificado de Servicio ' . $folio,
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

    private function normalizeHtmlForPdf($html)
    {
        if (trim($html) === '') {
            return '';
        }
        
        $html = str_replace('&nbsp;', '||NBSP_PRESERVE||', $html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = str_replace('||NBSP_PRESERVE||', '&nbsp;', $html);
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        $html = preg_replace(
            '/[\x{200B}-\x{200F}\x{FEFF}]/u',  // Removido: \x{00A0}
            ' ',
            $html
        );

        return trim($html);
    }

    public function order()
    {
        $this->data['order'] = [
            'programmed_date' => Carbon::parse($this->order->programmed_date)->format('d-m-Y'),
            'start' => Carbon::parse($this->order->programmed_date)->format('d-m-Y') . ' - ' . Carbon::parse($this->order->start_time)->format('H:i'),
            'end' => Carbon::parse($this->order->completed_date)->format('d-m-Y') . ' - ' . Carbon::parse($this->order->end_time)->format('H:i'),
            'notes' => $this->order->notes . $this->order->comments . $this->order->recommendations,
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
            'signature_base64' => $this->addBase64Prefix($this->order->customer_signature ?? '') // Mantener original
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
            $signature_base64 = 'data:image/png;base64,' . base64_encode($signature_img);
        }

        $this->data['technician'] = [
            'name' => $user->name ?? '-',
            'rfc' => $user->roleData->rfc ?? '-',
            'signature_base64' => $signature_base64
        ];
    }

    public function services()
    {
        $services_data = [];
        foreach ($this->order->services()->get() as $service) {
            $services_data[] = [
                'name' => $service->name,
                'text' => $this->normalizeHtmlForPdf($this->order->propagateByService($service->id)->text ?? ''),
                //'text' =>  $this->order->propagateByService($service->id)->text ?? ''
            ];
        }

        $this->data['services'] = $services_data;
    }

    public function products()
    {
        $products_data = [];
        /*$devices_products = DeviceProduct::where('order_id', $this->order->id)->get();

        $order_products = OrderProduct::where('order_id', $this->order->id)->get();


        if ($devices_products->isNotEmpty() && $order_products->isEmpty()) {
            // Agrupar DeviceProduct por product_id y lot_id
            $grouped_devices = $devices_products->groupBy(function ($item) {
                return $item->product_id . '_' . ($item->lot_id ?? 'null');
            });

            foreach ($grouped_devices as $group_key => $group_items) {
                // Tomar el primer item del grupo para obtener los datos comunes
                $first_item = $group_items->first();

                // Sumar todas las cantidades del grupo
                $total_quantity = $group_items->sum('quantity');

                // Buscar si ya existe un OrderProduct con esta combinación
                $existing_order_product = OrderProduct::where('order_id', $this->order->id)
                    ->where('product_id', $first_item->product_id)
                    ->where('lot_id', $first_item->lot_id)
                    ->first();

                if ($existing_order_product) {
                    // Actualizar existente - suma la nueva cantidad total
                    $existing_order_product->update([
                        'amount' => $existing_order_product->amount + $total_quantity,
                        'service_id' => $first_item->service_id,
                        'metric_id' => $first_item->metric_id,
                        'application_method_id' => $first_item->application_method_id,
                        'dosage' => $first_item->dosage ?? null,
                    ]);
                } else {
                    // Crear nuevo
                    OrderProduct::create([
                        'order_id' => $this->order->id,
                        'product_id' => $first_item->product_id,
                        'lot_id' => $first_item->lot_id ?? null,
                        'service_id' => $first_item->service_id,
                        'metric_id' => $first_item->metric_id,
                        'application_method_id' => $first_item->application_method_id,
                        'amount' => $total_quantity,
                        'dosage' => $first_item->dosage ?? null,
                    ]);
                }
            }
        }*/


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

    private function getDevicesByVersion($order_id, $version = null)
    {
        $found_devices = [];
        $f_versions = [];
        $order = Order::find($order_id);
        $floorplans = FloorPlans::where('customer_id', $order->customer_id)
            ->whereIn('service_id', $order->services()->pluck('service.id'))
            ->get();

        if ($floorplans->isNotEmpty()) {
            foreach ($floorplans as $floorplan) {
                $versions = FloorplanVersion::where('floorplan_id', $floorplan->id)->get();
                $version = $versions->where('updated_at', '<=', $order->programmed_date)->last();
                if ($version) {
                    $f_versions[] = [
                        'floorplan_id' => $floorplan->id,
                        'version' => $version->version,
                    ];
                } else {
                    $f_versions[] = [
                        'floorplan_id' => $floorplan->id,
                        'version' => $versions->last()?->version,
                    ];
                }
            }

            $found_devices = [];
            foreach ($f_versions as $fv) {
                $devices = Device::where('floorplan_id', $fv['floorplan_id'])
                    ->where('version', $fv['version'])
                    ->pluck('id')
                    ->toArray();
                $found_devices = array_merge($found_devices, $devices);
            }
        }

        return $found_devices;
    }


    public function devices()
    {
        $_reviews = [];

        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);
        $services = $this->order->services;
        $incidents = OrderIncidents::where('order_id', $this->order->id)->get();

        // Obtener dispositivos con version correcta
        $found_device_ids = $this->getDevicesByVersion($this->order_id);

        // Obtener todos los dispositivos necesarios con sus relaciones
        $devices = Device::whereIn('id', $found_device_ids)
            ->with([
                'applicationArea',
                'controlPoint',
                'floorplan.customer',
                'deviceProducts' => function ($query) {
                    $query->where('order_id', $this->order->id)
                        ->with('product.metric');
                },
                'devicePests' => function ($query) {
                    $query->where('order_id', $this->order->id)
                        ->with('pest');
                },
                'incidents' => function ($query) {
                    $query->where('order_id', $this->order->id);
                },
                'deviceStates' => function ($query) {
                    $query->where('order_id', $this->order->id);
                }
            ])
            ->orderBy('nplan', 'ASC')
            ->get();

        // Agrupar dispositivos por floorplan_id
        $devicesByFloorplan = $devices->groupBy('floorplan_id');

        foreach ($devicesByFloorplan as $floorplan_id => $floorplanDevices) {
            // Obtener el floorplan desde el primer dispositivo
            $floorplan = $floorplanDevices->first()->floorplan;
            $control_point_data = [];

            // Agrupar dispositivos por control point dentro de este floorplan
            $devicesByControlPoint = $floorplanDevices->groupBy('type_control_point_id');

            foreach ($devicesByControlPoint as $control_point_id => $controlPointDevices) {
                $control_point = ControlPoint::find($control_point_id);

                // Obtener preguntas para este control point
                $questions = Question::whereIn(
                    'id',
                    ControlPointQuestion::where('control_point_id', $control_point_id)
                        ->pluck('question_id')
                        ->unique()
                )->get();

                $question_headers = $questions->pluck('question')->toArray();
                $headers = array_merge(['Zona', 'Código', 'Producto y consumo', 'Valor revisión'], $question_headers);

                // Inicializar array para dispositivos de este control point
                $devices_data = [];

                foreach ($controlPointDevices->sortBy('nplan') as $device) {
                    // Obtener productos y plagas desde las relaciones cargadas
                    $device_products = $device->deviceProducts;
                    $device_pests = $device->devicePests;

                    // Preparar datos de preguntas
                    $question_data = [];
                    foreach ($questions as $question) {
                        $incident = $device->incidents
                            ->where('question_id', $question->id)
                            ->first();

                        $question_data[] = [
                            'question' => $question->question,
                            'answer' => $incident->answer ?? '',
                        ];
                    }

                    // Obtener observaciones
                    $device_state = $device->states($this->order_id)->first();
                    $observation = $device_state->observations ?? null;

                    if (!$observation) {
                        $observation = $device->incidents
                            ->whereIn('question_id', [33, 34, 35])
                            ->first()
                            ->answer ?? null;
                    }

                    // Preparar string de productos
                    $intake_string = $device_products->map(function ($device_product) {
                        if ($device_product && $device_product->product) {
                            $unit = $this->extractUnits($device_product->product->metric->value ?? '');
                            return $device_product->product->name . ' (' . $device_product->quantity . ' ' . $unit . ')';
                        }
                        return '-';
                    })->implode(', ');

                    // Preparar string de plagas
                    $pests_string = $device_pests->map(function ($device_pest) {
                        if ($device_pest && $device_pest->pest) {
                            return '(' . $device_pest->total . ') ' . $device_pest->pest->name;
                        }
                        return '';
                    })->filter()->implode(', ');

                    $devices_data[] = [
                        'zone' => $device->applicationArea->name ?? '-',
                        'code' => $device->code,
                        'intake' => $intake_string ?: 'No aplica',
                        'pests' => $pests_string ?: 'Sin registro',
                        'questions' => $question_data,
                        'observations' => $observation
                    ];
                }

                $control_point_data[] = [
                    'name' => $control_point->name ?? 'Sin nombre',
                    'headers' => $headers,
                    'devices' => $devices_data,
                ];
            }

            $_reviews[] = [
                'sede' => $floorplan->customer->name ?? 'Sin sede',
                'floorplan' => $floorplan->filename ?? 'Sin archivo',
                'control_points' => $control_point_data
            ];
        }

        $this->data['reviews'] = $_reviews;
    }
    public function notes()
    {
        $this->data['notes'] = $this->normalizeHtmlForPdf(!empty($this->order->notes) && trim($this->order->notes) != '<br>'
            ? $this->order->notes
            : 'Sin notas');
    }

    private function isValidRecommendation($data)
    {
        // Verifica si el array está vacío
        if (empty($data)) {
            return false;
        }

        // Si es un array multidimensional (como en el ejemplo)
        if (isset($data[0])) {
            foreach ($data as $item) {
                if (!isset($item['recommendation_text'])) {
                    continue;
                }

                $text = $item['recommendation_text'];

                // Verifica si es null, string vacío o solo whitespace
                if ($text === null || trim($text) === '') {
                    return false;
                }
            }
            return true;
        }

        // Si es un array simple con la clave recommendation_text
        if (isset($data['recommendation_text'])) {
            $text = $data['recommendation_text'];
            return $text !== null && trim($text) !== '';
        }

        return false;
    }

    public function recommendations()
    {
        $this->data['recommendations'] = ''; // Inicializar
        $services = $this->order->services()->get();

        foreach ($services as $service) {
            $recs = OrderRecommendation::where('order_id', $this->order_id)->where('service_id', $service->id)->get();

            if ($this->isValidRecommendation($recs)) {
                foreach ($recs as $rec) {
                    $this->data['recommendations'] .= $rec->recommendation_text ?? '' . "<br>";
                }
            } else {
                $this->data['recommendations'] = 'Sin recomendaciones';
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