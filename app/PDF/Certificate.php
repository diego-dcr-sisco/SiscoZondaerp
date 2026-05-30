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

        $base64String = trim($base64String);
        if ($base64String === $prefix || $base64String === 'data:image/png;base64,' || $base64String === 'data:image/jpeg;base64,') {
            return null;
        }

        if (strpos($base64String, 'data:image') === 0) {
            return $base64String;
        }
        return $prefix . $base64String;
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
            'photo_evidences' => [],
            'health_officer_signature' => 'data:image/png;base64,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',
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

        // DOMPDF renderiza mejor el ancho de imagen cuando existe atributo width en px.
        $html = preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
            $imgTag = $matches[0];

            if (!preg_match('/style\s*=\s*(["\'])(.*?)\1/i', $imgTag, $styleMatch)) {
                return $imgTag;
            }

            $styleValue = $styleMatch[2] ?? '';
            if (!preg_match('/width\s*:\s*([0-9]+(?:\.[0-9]+)?)\s*(px|%)/i', $styleValue, $widthMatch)) {
                return $imgTag;
            }

            $widthValue = (int) round((float) $widthMatch[1]);
            $unit = strtolower($widthMatch[2]);

            if ($unit !== 'px' || $widthValue <= 0) {
                return $imgTag;
            }

            if (preg_match('/\swidth\s*=\s*["\'][^"\']*["\']/i', $imgTag)) {
                return preg_replace(
                    '/(\swidth\s*=\s*["\'])[^"\']*(["\'])/i',
                    '$1' . $widthValue . '$2',
                    $imgTag
                );
            }

            return preg_replace('/<img/i', '<img width="' . $widthValue . '"', $imgTag, 1);
        }, $html);

        return trim($html);
    }

    public function order()
    {
        $this->data['order'] = [
            'programmed_date' => Carbon::parse($this->order->programmed_date)->format('d-m-Y'),
            'start' => Carbon::parse($this->order->programmed_date)->format('d-m-Y') . ' - ' . Carbon::parse($this->order->start_time)->format('H:i'),
            'end' => Carbon::parse($this->order->completed_date)->format('d-m-Y') . ' - ' . Carbon::parse($this->order->end_time)->format('H:i'),
            'notes' => $this->order->notes ?? $this->order->technical_observations . '<br>' . $this->order->comments,
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
                'no_register' => $order_product->product->report_register_number ?? '-',
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

        // Filtrar solo dispositivos revisados
        // Incluir dispositivos que tengan is_checked, productos, plagas o respuestas
        // (estos son los dispositivos que estaban revisados al momento de aprobar o en proceso)
        $devices = $devices->filter(function ($device) {
            $device_state = $device->deviceStates->first();
            $is_checked = $device_state ? ($device_state->is_checked || $device_state->is_scanned) : false;
            $has_products = $device->deviceProducts->count() > 0;
            $has_pests = $device->devicePests->count() > 0;
            $has_answers = $device->incidents->filter(function ($incident) {
                return !empty($incident->answer);
            })->count() > 0;

            return $is_checked || $has_products || $has_pests || $has_answers;
        });

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

                // Si el reporte está aprobado, filtrar solo preguntas con al menos una respuesta
                if ($this->order->status_id == 5) {
                    $device_ids = $controlPointDevices->pluck('id')->toArray();
                    $answered_question_ids = OrderIncidents::where('order_id', $this->order->id)
                        ->whereIn('device_id', $device_ids)
                        ->whereNotNull('answer')
                        ->where('answer', '!=', '')
                        ->pluck('question_id')
                        ->unique()
                        ->toArray();

                    $questions = $questions->whereIn('id', $answered_question_ids);
                }

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

                        $answer = $incident ? $incident->answer : null;
                        $answer = ($answer !== null && trim($answer) !== '') ? $answer : '-';

                        $question_data[] = [
                            'question' => $question->question,
                            'answer' => $answer,
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
        $temp_notes = $this->order->notes ?? $this->order->technical_observations . '<br>' . $this->order->comments;
        $this->data['notes'] = $this->normalizeHtmlForPdf(!empty($temp_notes) && trim($temp_notes) != '<br>'
            ? $temp_notes
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

            if ($service->prefix == 2) {
                $this->data['recommendations'] .=
                    '<p><strong>ANTES DE LA APLICACIÓN QUÍMICA</strong></p>
                <ol>
                    <li>Identificar la plaga a controlar.</li>
                    <li>No debe encontrarse personal en el área.</li>
                    <li>No debe de haber materia prima expuesta.</li>
                    <li>Asegurar que la aplicación no afecte el proceso, producción o a terceros.</li>
                </ol>
                <br>
                <p><strong>DURANTE DE LA APLICACIÓN QUÍMICA</strong></p>
                <ol>
                    <li>En el área solo debe de encontrarse el técnico aplicador</li>
                </ol>
                <br>
                <p><strong>DESPUÉS DE LA APLICACIÓN QUÍMICA</strong></p>
                <ol>
                    <li>Respetar el tiempo de reentrada conforme a la etiqueta del producto a utilizar.</li>
                    <li>Realizar recolección de plaga o limpieza necesaria al tipo de área.</li>
                </ol>';
            } else {
                if ($this->isValidRecommendation($recs)) {
                    foreach ($recs as $rec) {
                        $this->data['recommendations'] .= $rec->recommendation_text ?? '' . "<br>";
                    }
                } else {
                    $this->data['recommendations'] = 'Sin recomendaciones';
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
