<?php

namespace App\Http\Controllers;

use App\Models\OrderIncidents;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\FloorPlans;
use App\Models\ProductCatalog;
use App\Models\ControlPoint;
use App\Models\Device;
use App\Models\CustomerContract;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\ContractService;
use App\Models\Service;
use App\Models\ApplicationMethod;
use App\Models\ApplicationMethodService;
use App\Models\ApplicationArea;
use App\Models\ProductPest;
use App\Models\FloorplanVersion;
use App\Models\OrderInsidences;
use App\Models\Branch;
use App\Models\OrderName;
use App\Models\PestCatalog;
use App\Models\DevicePest;
use App\Models\Order;

use Illuminate\Support\Str;
use Carbon\Carbon;

use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCode;
use Intervention\Image\Facades\Image;

use Barryvdh\DomPDF\Facade\Pdf;
use App\PDF\QRDevice;
use App\Jobs\CleanTempFiles;


class FloorPlansController extends Controller
{
    private $path = 'floorplans/';
    private $size = 25;

    private $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];

    private function countControlPoints($nestedDevices)
    {
        $result = [];
        foreach ($nestedDevices as $devices) {
            foreach ($devices as $control_point_id) {
                if (!isset($result[$control_point_id])) {
                    $result[$control_point_id] = ['control_point_id' => $control_point_id, 'count' => 1];
                } else {
                    $result[$control_point_id]['count']++;
                }
            }
        }
        return array_values($result);
    }

    private function getNavigation(FloorPlans $floorplan)
    {
        $navigation = [
            'Plano' => route('floorplan.edit', ['id' => $floorplan->id]),
            'Dispositivos' => route('floorplan.devices', ['id' => $floorplan->id, 'version' => $floorplan->lastVersion() ?? '0']),
            'QRs' => route('floorplan.qr', ['id' => $floorplan->id]),
            'Geolocalización' => route('floorplan.geolocation', ['id' => $floorplan->id]),
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $floorplan->customer_id]),
            'Graficas' => route('floorplan.graphic.incidents', ['id' => $floorplan->id])
        ];

        return $navigation;
    }



    public function getImage(string $path)
    {
        $url = /*$this->path*/ '/' . $path;
        if (!Storage::disk('public')->exists($url)) {
            abort(404);
        }
        $file = Storage::disk('public')->get($url);
        $type = mime_content_type(Storage::disk('public')->path($url));
        return response($file, 200)->header('Content-Type', $type);
    }

    public function getDevicesVersion(Request $request, string $id)
    {
        $devices = null;
        $version = $request->input('version');

        if (empty($id) || empty($version)) {
            return response()->json('Faltan parámetros necesarios.');
        }

        $devices = Device::where('floorplan_id', $id)->where('version', $version)
            ->select('id', 'type_control_point_id', 'floorplan_id', 'application_area_id', 'nplan', 'latitude', 'itemnumber', 'longitude', 'map_x', 'map_y', 'color', 'code')
            ->get();
        return response()->json($devices);
    }

    public function index(string $id)
    {
        $error = $success = $warning = $contract = $status = null;
        $client = Customer::where('id', $id)->first();
        $floorplans = FloorPlans::where('customer_id', $id)->get();
        $contract = Contract::where('customer_id', $id)->first();

        if ($contract != null) {
            $status = true;
        } else {
            $warning = "El cliente no tiene un contrato activo, no podrás editar los puntos de control.";
            $status = false; //false;
        }

        return view('customer.show', compact('status', 'floorplans', 'client', 'error', 'success', 'warning'));
    }

    public function process(string $id)
    {
        $error = null;
        $success = null;
        $warning = null;
        $customerID = $id;
        $client = Customer::where('id', $id)->first();
        $floorplans = FloorPlans::where('customer_id', $id)->get();

        // Obtener los ID de contrato para un cliente específico
        $contractIds = Contract::where('customer_id', $id)->pluck('id')->toArray();

        // Si hay contratos, obtener los ID de servicio asociados
        $serviceIds = [];
        if (!empty($contractIds)) {
            $serviceIds = ContractService::whereIn('contract_id', $contractIds)->pluck('service_id')->toArray();
        }

        // Obtener los servicios correspondientes a los ID de servicio
        $services = Service::when($serviceIds, function ($query) use ($serviceIds) {
            return $query->whereIn('id', $serviceIds);
        })->get();

        return view('floorplan.create', compact('services', 'floorplans', 'client', 'error', 'success', 'warning'));
    }

    public function create(string $id)
    {
        $customer = Customer::find($id);
        return view('floorplan.create', compact('customer'));
    }

    public function print(string $id)
    {
        $floorplan = FloorPlans::findOrFail($id);
        $legend = [];

        if ($floorplan->service_id) {
            $last_version = session('last_updated_version') ?? $floorplan->lastVersion();
            $devices = Device::where('floorplan_id', $id)->where('version', $last_version)
                ->select('id', 'type_control_point_id', 'floorplan_id', 'application_area_id', 'product_id', 'nplan', 'latitude', 'itemnumber', 'longitude', 'map_x', 'map_y', 'img_tamx', 'img_tamy', 'color', 'code')
                ->get();
            $f_version = FloorplanVersion::where('floorplan_id', $floorplan->id)->where('version', $last_version)->first();

            foreach ($devices as $device) {
                $color = $device->color; // Usar color como clave de agrupación
                $filtered = array_filter($legend, function ($item) use ($color) {
                    return $item['color'] == $color;
                });

                if ($filtered) {
                    $key = key($filtered);
                    $legend[$key]['count']++;
                    $legend[$key]['numbers'][] = $device->nplan;
                } else {
                    $productName = ProductCatalog::where("id", $device->product_id)->value('name');
                    $legend[] = [
                        'type' => $device->type_control_point_id,
                        'label' => $device->controlPoint->name,
                        'code' => $device->controlPoint->code,
                        'color' => $color,
                        'count' => 1,
                        'numbers' => [$device->nplan],
                        'product' => $productName ? $productName : "No aplica",
                    ];
                }
            }

            Carbon::setLocale('es');
            setlocale(LC_TIME, 'es_ES.UTF-8');

            $image = Image::make(Storage::disk('public')->get('/' . $floorplan->path));
            $img_sizes = [$image->width(), $image->height()];

            $print_data = [
                'name' => $floorplan->filename,
                'floorplan_version' => $floorplan->versions()->latest('version')->value('version'),
                'date_version' => $f_version ? Carbon::parse($f_version->updated_at)->format('Y-m-d') : '',
                'customer' => $floorplan->customer->name,
                'service' => $floorplan->service->name,
                'count' => $devices->count(),
                'legend' => $legend
            ];
        } else {
            session()->flash('error', 'Impresion no permitida, sin servicio asociado.');
            return back();
        }

        $navigation = $this->getNavigation($floorplan);

        return view('floorplans.print', compact('floorplan', 'devices', 'print_data', 'img_sizes', 'navigation'))->with(['last_updated_version' => $last_version]);
    }

    public function printVersion(Request $request)
    {
        $data = $request->all();

        try {
            $jsonData = $request->input('pdf_json_data');
            $data = json_decode($jsonData, true);
            $legend_data = [];
            $groupedPoints = $data['groupedPoints'];

            foreach ($groupedPoints as $gp) {
                $c_point = ControlPoint::find($gp['type_control_point_id']);

                $legend_data[] = [
                    'label' => $c_point->name . ' (' . $c_point->code . ') - Puntos totales: ' . $gp['count'] . ' - Rango(s): ' . implode(', ', $gp['nplans']),
                    'color' => $gp['color']
                ];
            }

            $pdfData = [
                "imageBase64" => $data['image'],
                "customer" => $data['customer'],
                "filename" => $data['filename'],
                'service' => $data['service'],
                "date_version" => $data['date_version'],
                "device_count" => $data['device_count'],
                "font_family" => $data['font_family'] ?? "Arial",
                "font_color" => $data['font_color'] ?? "#000000",
                "legend" => $legend_data
            ];

            // Generar el PDF en landscape
            $pdf = Pdf::loadView('floorplans.pdf.file', $pdfData)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    //'dpi' => 150,
                    'defaultFont' => $data['font_family'] ?? 'Arial'
                ]);

            // Crear nombre de archivo seguro
            $fileName = Str::slug($data['filename'] ?? 'plano') .
                Str::slug($data['customer'] ?? 'cliente') . '_' .
                date('Y-m-d_H-i') . '.pdf';

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPrintLegend($floorplan_id, $version)
    {
        $legend = [];

        $devices = Device::with('controlPoint') // Cargar relación eager loading
            ->where('floorplan_id', $floorplan_id)
            ->where('version', $version)
            ->get();

        // Agrupar y contar
        $grouped = $devices->groupBy(['color', 'type_control_point_id'])
            ->map(function ($typeGroups, $color) {
                return $typeGroups->map(function ($devicesGroup, $typeId) use ($color) {
                    $firstDevice = $devicesGroup->first();
                    $nplans = $devicesGroup->pluck('nplan')->unique()->toArray();

                    return [
                        'color' => $color,
                        'type_control_point_id' => $typeId,
                        'type_control_point_name' => $firstDevice->controlPoint->name ?? 'Sin nombre',
                        'count' => $devicesGroup->count(),
                        'nplans' => $nplans
                    ];
                });
            });

        // Aplanar el array
        foreach ($grouped as $colorGroups) {
            foreach ($colorGroups as $item) {
                $legend[] = $item;
            }
        }

        return $legend;
    }

    public function store(Request $request, string $customerId)
    {
        $validated = $request->validate([
            'filename' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-_]+$/'
            ],
            'file' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120'
            ],
            'service_id' => 'nullable|exists:service,id',
            'customer_id' => 'required|exists:customer,id'
        ], [
            'filename.required' => 'El nombre del plano es obligatorio',
            'filename.min' => 'El nombre debe tener al menos 3 caracteres',
            'filename.max' => 'El nombre no puede exceder 100 caracteres',
            'filename.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos',
            'file.required' => 'Debe seleccionar un archivo',
            'file.image' => 'El archivo debe ser una imagen',
            'file.mimes' => 'Solo se permiten archivos JPG, JPEG o PNG',
            'file.max' => 'El archivo no debe exceder 5MB'
        ]);

        $sanitizedFilename = $this->sanitizeFilename($validated['filename']);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $safeFileName = time() . '_' . Str::slug($sanitizedFilename) . '.' . $extension;
        $url = $this->path . $customerId . '/' . $safeFileName;

        Storage::disk('public')->put($url, file_get_contents($file));

        $floorplan = new FloorPlans();
        $floorplan->filename = $sanitizedFilename;
        $floorplan->customer_id = $customerId;
        $floorplan->service_id = $request->input('service_id') ?: null;
        $floorplan->path = $url;
        $floorplan->save();

        return back()->with('success', 'Plano creado exitosamente');
    }

    public function edit(string $id)
    {
        $data = [];
        $devicesIds = [];

        $floorplan = FloorPlans::findOrFail($id);
        $services = Service::orderBy('name', 'asc')->get();

        $navigation = $this->getNavigation($floorplan);

        return view('floorplans.edit.form', compact('floorplan', 'services', 'navigation'));
    }

    public function editDevices(string $id, string $version)
    {
        $data = [];
        $devicesIds = [];

        $floorplan = FloorPlans::findOrFail($id);

        if ($floorplan) {
            $customer = Customer::find($floorplan->customer->id);
            $floorplanIds = $customer->floorplans()->get()->pluck('id');
            $nplan = $count = 0;

            //$version = $floorplan->versions()->latest('version')->value('version');
            $customer = Customer::findOrFail($floorplan->customer->id);
            $services = Service::orderBy('name', 'asc')->get();
            $applications_areas = ApplicationArea::where('customer_id', $floorplan->customer->id)->orderBy('name')->get();
            $devices = Device::where('floorplan_id', $id)->where('version', $version)
                ->select('id', 'type_control_point_id', 'floorplan_id', 'application_area_id', 'product_id', 'nplan', 'latitude', 'itemnumber', 'longitude', 'map_x', 'map_y', 'img_tamx', 'img_tamy', 'color', 'code', 'size')
                ->get();

            // Obtener las últimas 4 revisiones para cada dispositivo
            $reviews = [];
            foreach ($devices as $device) {
                $revisions = OrderIncidents::where('device_id', $device->id)
                    //->orderBy('updated_at', 'desc')
                    ->orderBy('updated_at', 'asc')
                    ->limit(4)
                    ->select('device_id', 'answer', 'updated_at')
                    ->get();
                $reviews[$device->itemnumber][$device->type_control_point_id] = $revisions;
            }

            $product_names = [];
            $ctrlPoints = ControlPoint::orderBy('name', 'asc')->get();
            $products = ProductCatalog::where('presentation_id', '!=', 1)->orderBy('name', 'asc')->get();
            $lastDevice = Device::whereIn('floorplan_id', $floorplanIds)->get()->last();
            $countDevices = !empty($lastDevice) ? $lastDevice->itemnumber : 0;
            $aux = -1;

            $floorplansByService = Floorplans::where('service_id', $floorplan->service_id)->where('customer_id', $floorplan->customer->id)->get();
            foreach ($floorplansByService as $floorplanByService) {
                //$version = $floorplanByService->lastVersion();
                $devicesIds[] = $floorplanByService->devices($version)->get()->pluck('id')->toArray();
            }

            $devicesIds = collect($devicesIds)->flatten(1)->toArray();
            $nplans = Device::whereIn("id", $devicesIds)->get()->pluck('nplan')->toArray();

            $image = Image::make(Storage::disk('public')->path($floorplan->path));
            $img_sizes = [$image->width(), $image->height()];

            $legend = $this->getPrintLegend($floorplan->id, $version);

            //$last_version = session('last_updated_version') ?? $floorplan->lastVersion();
            $f_version = FloorplanVersion::where('floorplan_id', $floorplan->id)->where('version', $version)->first();
            $print_data = [
                'name' => $floorplan->filename,
                'floorplan_version' => $floorplan->versions()->latest('version')->value('version'),
                'date_version' => $f_version ? Carbon::parse($f_version->updated_at)->format('Y-m-d') : '',
                'customer' => $floorplan->customer->name,
                'service' => $floorplan->service->name ?? '',
                'count' => $devices->count(),
                'legend' => $legend
            ];
        }

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $navigation = $this->getNavigation($floorplan);
        $f_version = FloorplanVersion::where('floorplan_id', $id)->where('version', $version)->first();
        $can_resize = $devices->whereNull('size')->isNotEmpty();

        return view('floorplans.edit.devices', compact(
            'ctrlPoints',
            'applications_areas',
            'services',
            'customer',
            'devices',
            'reviews',
            'floorplan',
            'products',
            'countDevices',
            'nplan',
            'nplans',
            'img_sizes',
            'navigation',
            'f_version',
            'legend',
            'logoBase64',
            'print_data',
            'can_resize'
        ));
    }

    public function searchDevices(Request $request, string $floorplanId)
    {
        try {
            $version = $request->input('version');
            $pointId = $request->input('point');
            $app_areaId = $request->input('app_area');

            $devices = Device::where('floorplan_id', $floorplanId)->where('version', $version);

            if ($pointId) {
                $devices = $devices->where('type_control_point_id', $pointId);
            }

            if ($app_areaId) {
                $devices = $devices->where('application_area_id', $app_areaId);
            }

            $devices = $devices->orderBy('nplan')->get();

            $data = [];
            foreach ($devices as $device) {
                $data[] = [
                    'device_id' => $device->id,
                    'nplan' => $device->nplan,
                    'color' => $device->color,
                    'code' => $device->code,
                    'type' => $device->controlPoint->name,
                    'app_area' => $device->applicationArea->name ?? '-',
                    'version' => $device->version
                ];
            }
            return response()->json([
                'data' => $data,
                'point' => $pointId
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while searching for devices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function searchDevicesbyVersion(Request $request, string $id)
    {
        $version = $request->input('version');
        $floorplan = FloorPlans::find($id);

        // Para peticiones AJAX, retornar la URL en JSON
        $redirectUrl = route('floorplan.devices', ['id' => $floorplan->id, 'version' => $version]);

        return response()->json([
            'redirect' => $redirectUrl
        ], 200);
    }

    public function searchPrint(Request $request, string $id)
    {
        try {
            $legend = [];
            $version = $request->version;
            $floorplan = FloorPlans::find($id);
            $devices = Device::where('floorplan_id', $floorplan->id)->where('version', $version)
                ->select('id', 'type_control_point_id', 'floorplan_id', 'application_area_id', 'product_id', 'nplan', 'latitude', 'itemnumber', 'longitude', 'map_x', 'map_y', 'img_tamx', 'img_tamy', 'color', 'code')
                ->get();

            $f_version = FloorplanVersion::where('floorplan_id', $floorplan->id)->where('version', $version)->first();

            foreach ($devices as $device) {
                $color = $device->color;
                $filtered = array_filter($legend, function ($item) use ($color) {
                    return $item['color'] == $color;
                });

                if ($filtered) {
                    $key = key($filtered);
                    $legend[$key]['count']++;
                    $legend[$key]['numbers'][] = $device->nplan;
                } else {
                    $productName = ProductCatalog::where("id", $device->product_id)->value('name');
                    $legend[] = [
                        'type' => $device->type_control_point_id,
                        'label' => $device->controlPoint->name,
                        'code' => $device->controlPoint->code,
                        'color' => $color,
                        'count' => 1,
                        'numbers' => [$device->nplan],
                        'product' => $productName ? $productName : "No aplica",
                    ];
                }
            }

            $print_data = [
                'name' => $floorplan->filename,
                'version' => $version,
                'date_version' => Carbon::parse($f_version->updated_at)->format('Y-m-d'),
                'customer' => $floorplan->customer->name,
                'service' => $floorplan->service->name,
                'count' => $devices->count(),
                'legend' => $legend
            ];
            return response()->json([
                'success' => true,
                'data' => $print_data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function searchQRs(Request $request, string $id)
    {
        // Obtener parámetros de ordenamiento
        $size = $request->input('size');
        $direction = $request->input('direction', 'DESC');

        $floorplan = FloorPlans::find($id);
        // Construir consulta base
        $devices = Device::where('floorplan_id', $floorplan->id)
            ->select('id', 'type_control_point_id', 'floorplan_id', 'application_area_id', 'product_id', 'nplan', 'latitude', 'itemnumber', 'longitude', 'map_x', 'map_y', 'img_tamx', 'img_tamy', 'color', 'code')
            ->get();

        $query = Device::where('floorplan_id', $floorplan->id);

        // Aplicar filtros (mantén tus filtros existentes)
        if ($request->filled('version')) {
            $query->where('version', $request->version);
        }

        if ($request->filled('point')) {
            $query->where('type_control_point_id', $request->point);

        }

        if ($request->filled('app_area')) {
            $query->where('application_area_id', $request->app_area);
        }

        // Aplicar ordenamiento después de los filtros
        $query->select('id', 'type_control_point_id', 'floorplan_id', 'application_area_id', 'product_id', 'nplan', 'latitude', 'itemnumber', 'longitude', 'map_x', 'map_y', 'img_tamx', 'img_tamy', 'color', 'code', 'version')->orderBy('nplan', $direction);
        $size = $size ?? $this->size;

        $control_points = ControlPoint::whereIn('id', $devices->pluck('type_control_point_id')->unique())->get();
        $application_areas = ApplicationArea::whereIn('id', $devices->pluck('application_area_id')->unique())->get();

        $devices = $query->paginate($size)
            ->appends($request->all());


        $navigation = $this->getNavigation($floorplan);

        return view(
            'floorplans.selectqrs',
            compact('devices', 'floorplan', 'control_points', 'application_areas', 'navigation')
        );
    }

    public function update(Request $request, string $id)
    {
        $floorplan = FloorPlans::find($id);
        
        $validated = $request->validate([
            'filename' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\-_]+$/'
            ],
            'file' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120'
            ],
            'service_id' => 'nullable|exists:service,id'
        ], [
            'filename.required' => 'El nombre del plano es obligatorio',
            'filename.min' => 'El nombre debe tener al menos 3 caracteres',
            'filename.max' => 'El nombre no puede exceder 100 caracteres',
            'filename.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y guiones bajos',
            'file.image' => 'El archivo debe ser una imagen',
            'file.mimes' => 'Solo se permiten archivos JPG, JPEG o PNG',
            'file.max' => 'El archivo no debe exceder 5MB'
        ]);

        $floorplan->filename = $this->sanitizeFilename($validated['filename']);
        $floorplan->service_id = $request->input('service_id') ?: null;

        if ($request->hasFile('file')) {
            if ($floorplan->path && Storage::disk('public')->exists($floorplan->path)) {
                Storage::disk('public')->delete($floorplan->path);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $safeFileName = Str::slug($floorplan->filename) . '_' . time() . '.' . $extension;
            $newPath = $this->path . $floorplan->customer_id . '/' . $safeFileName;
            
            Storage::disk('public')->put($newPath, file_get_contents($file));
            $floorplan->path = $newPath;
        }
        
        $floorplan->save();

        return back()->with('success', 'Plano actualizado exitosamente');
    }

    public function updateDevices(Request $request, string $id)
    {
        $pointsData = json_decode($request->input('points'));
        $create_version = $request->input('create_version');
        $floorplan = FloorPlans::find($id);

        $version = FloorplanVersion::where('floorplan_id', $floorplan->id)->max('version');

        if ($version) {
            $latestVersionNumber = $create_version ? $version + 1 : $version;
        } else {
            $latestVersionNumber = 1;
        }

        // Obtener dispositivos actuales de la versión antes de actualizar
        $currentDevices = Device::where('floorplan_id', $floorplan->id)
            ->where('version', $version)
            ->get();

        // Crear array de nplans que vienen en el request
        $newNplans = collect($pointsData)->pluck('count')->toArray();

        // Identificar dispositivos que se van a eliminar
        $devicesToDelete = $currentDevices->filter(function ($device) use ($newNplans) {
            return !in_array($device->nplan, $newNplans);
        });

        // Verificar si algún dispositivo eliminado tiene revisiones
        $forceNewVersion = false;
        foreach ($devicesToDelete as $device) {
            $hasReviews = OrderIncidents::where('device_id', $device->id)->exists();
            if ($hasReviews) {
                $forceNewVersion = true;
                break;
            }
        }

        // Si se detectó que hay dispositivos con revisiones que se van a eliminar, forzar nueva versión
        if ($forceNewVersion && !$create_version) {
            $create_version = true;
            $latestVersionNumber = $version + 1;
        }

        if ($create_version) {
            FloorplanVersion::insert([
                'floorplan_id' => $floorplan->id,
                'version' => $latestVersionNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $updateDate = Carbon::createFromFormat('Y-m-d', $request->input('version_updated_at'));
            $updateDate->startOfDay();

            FloorplanVersion::where('floorplan_id', $floorplan->id)
                ->where('version', $version)
                ->update(['updated_at' => $updateDate]);
        }

        foreach ($pointsData as $point) {
            $found_point = ControlPoint::find($point->point_id);
            $code = $point->code ?? ($found_point->code . '-' . $found_point->nplan);
            $device = Device::updateOrCreate(
                [
                    'floorplan_id' => $floorplan->id,
                    'nplan' => $point->count,
                    'version' => $latestVersionNumber ?? 1,
                ],
                [
                    'type_control_point_id' => $point->point_id,
                    'application_area_id' => $point->area_id,
                    'product_id' => $point->product_id > 0 ? $point->product_id : null,
                    'itemnumber' => $point->index,
                    'map_x' => $point->x,
                    'map_y' => $point->y,
                    'color' => $point->color,
                    'code' => $code,
                    'img_tamx' => $point->img_tamx,
                    'img_tamy' => $point->img_tamy,
                    'size' => $point->size
                ]
            );

            //$device->qr = QrCode::format('png')->size(200)->generate($code);
            //$device->save();

            if ($device->wasRecentlyCreated) {
                Device::where('floorplan_id', $device->floorplan_id)->where('nplan', $device->nplan)->where('version', $device->version)->whereNot('type_control_point_id', $device->type_control_point_id)->delete();
                $device->qr = QrCode::format('png')->size(200)->generate($code);
                $device->save();
            }
        }

        // Si NO se creó una nueva versión, eliminar los dispositivos que ya no están en el plano
        // Si se creó nueva versión, los dispositivos antiguos se mantienen en la versión anterior
        if (!$create_version) {
            foreach ($devicesToDelete as $device) {
                $device->delete();
            }
        }

        return redirect()->route('floorplan.devices', ['id' => $floorplan->id, 'version' => $latestVersionNumber]);
    }

    public function updateVersion(Request $request, string $id)
    {
        $f_version = FloorplanVersion::where('floorplan_id', $id)->where('version', $request->last_updated_version)->first();
        $f_version->update(['updated_at' => Carbon::parse($request->date_version)]);
        return back()->with(['last_updated_version' => $request->last_updated_version]);
    }

    public function delete($id)
    {
        $exist_report_name = OrderName::find($id);

        if ($exist_report_name && $exist_report_name->order_id) {
            // Existe un reporte asociado
            $client_id = $exist_report_name->client_id;
            $mensaje = "No se puede eliminar este plano, ya tiene una orden asociada.";
        } else {
            // No hay órdenes asociadas, se puede eliminar
            Device::where('floorplan_id', $id)->delete();
            $floorplan = FloorPlans::find($id);

            if ($floorplan) {
                $floorplan->delete();
                $mensaje = "Plano y dispositivos borrados exitosamente.";
            } else {
                $mensaje = "No se encontró el plano.";
            }

            // Si $exist_report_name no existe, utiliza el $customerID proporcionado en la ruta
        }

        // Redirigir a la ruta 'customer.edit' con los parámetros adecuados
        return back();
    }

    public function getQR(string $id)
    {
        $devices = $customer = null;
        $floorplan = FloorPlans::find($id);
        $version = $floorplan->versions()->latest('version')->value('version');

        $devices = Device::where('floorplan_id', $id)
            ->where('version', $version)
            ->orderBy('nplan')
            ->get();

        $control_points = ControlPoint::whereIn('id', $devices->pluck('type_control_point_id')->unique())->get();
        $application_areas = ApplicationArea::whereIn('id', $devices->pluck('application_area_id')->unique())->get();
        //dd($types);
        $customer = Customer::find($floorplan->customer_id);
        $type = $customer->service_type_id;

        //dd($floorplan->lastVersion());

        $navigation = $this->getNavigation($floorplan);

        return view('floorplans.selectqrs', compact('devices', 'floorplan', 'type', 'control_points', 'application_areas', 'navigation'));
    }

    public function getVersionQR(Request $request, string $id)
    {
        $devices = null;
        $floorplan = FloorPlans::find($id);
        $version = intval($request->input('version'));

        $devices = Device::where('floorplan_id', $id)
            ->where('version', $version)
            ->orderBy('type_control_point_id')
            ->orderBy('application_area_id')
            ->get();

        return response()->json([
            'devices' => $devices,
            'floorplan' => $floorplan,
        ]);
    }

    public function printQR(Request $request, string $id)
    {
        $devices_data = [];
        $selected_devices = json_decode($request->input('selected_devices'));

        $tempDir = storage_path('app/temp_qr/');

        $floorplan = FloorPlans::find($id);
        $qr_device = new QRDevice();

        foreach ($selected_devices as $device_id) {
            $qrd = $qr_device->device($device_id);
            $devices_data[] = $qrd;
        }

        $data['devices'] = $devices_data;

        $pdf = Pdf::loadView('floorplans.pdf.qr', $data);
        $pdf_name = 'QR_' . $floorplan->filename . '_' . $floorplan->customer->name;

        register_shutdown_function(function () use ($tempDir) {
            if (File::exists($tempDir)) {
                File::cleanDirectory($tempDir);
            }
        });

        return $pdf->stream($pdf_name);
    }

    private function sanitizeFilename(string $filename): string
    {
        $sanitized = preg_replace('/[<>:"\/\\|?*\x00-\x1f]/', '', $filename);
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = trim($sanitized);
        
        if (empty($sanitized)) {
            $sanitized = 'Plano_' . time();
        }
        
        return $sanitized;
    }

    /**
     * Vista principal de gráficas de incidentes del plano
     */
    public function graphicIncidents(Request $request, string $id)
    {
        $floorplan = FloorPlans::find($id);
        $version = intval($request->input('version'));
        $month = intval($request->input('month'));
        $year = intval($request->input('year'));
        $trend = $request->input('trend');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        if (empty($version)) {
            $version = $floorplan->versions()->latest('version')->value('version');
        }

        if (empty($month)) {
            $month = Carbon::now()->month;
        }

        if (empty($year)) {
            $year = Carbon::now()->year;
        }

        // Si la solicitud es AJAX, devolver JSON
        if ($request->ajax()) {
            $devices = Device::where('floorplan_id', $id)
                ->where('version', $version)
                ->orderBy('nplan')
                ->get();

            if ($trend) {
                // Obtener datos de tendencia para todos los meses del año
                $graph_per_months = $this->graphIncidentsByMonth($devices, $year);
                return response()->json([
                    'success' => true,
                    'trend' => $graph_per_months
                ]);
            }

            // Si se proporcionan fechas de rango, usarlas; de lo contrario, usar mes/año
            if ($startDate && $endDate) {
                $orders = Order::whereBetween('programmed_date', [$startDate, $endDate])
                    ->whereIn('id', DevicePest::whereIn('device_id', $devices->pluck('id'))->pluck('order_id')->unique())
                    ->get();
            } else {
                $orders = Order::whereMonth('programmed_date', $month)
                    ->whereYear('programmed_date', $year)
                    ->whereIn('id', DevicePest::whereIn('device_id', $devices->pluck('id'))->pluck('order_id')->unique())
                    ->get();
            }

            $graph_per_devices = $this->graphIncidentsByDevice($devices, $orders);
            $graph_per_pests = $this->graphIncidentsByPests($devices, $orders);

            return response()->json([
                'success' => true,
                'devices' => $graph_per_devices,
                'pests' => $graph_per_pests
            ]);
        }

        $navigation = $this->getNavigation($floorplan);

        $devices = Device::where('floorplan_id', $id)
            ->where('version', $version)
            ->orderBy('nplan')
            ->get();

        $orders = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereIn('id', DevicePest::whereIn('device_id', $devices->pluck('id'))->pluck('order_id')->unique())
            ->get();

        $graph_per_devices = $this->graphIncidentsByDevice($devices, $orders);
        $graph_per_pests = $this->graphIncidentsByPests($devices, $orders);
        $graph_per_months = $this->graphIncidentsByMonth($devices, $year);

        $months = $this->months;
        $years = $this->getYears();

        return view('floorplans.graphics.incidents', compact('devices', 'floorplan', 'version', 'navigation', 'months', 'years', 'graph_per_devices', 'graph_per_pests', 'graph_per_months'));
    }

    /**
     * Gráfica de incidentes por dispositivo
     */
    private function graphIncidentsByDevice($devices, $orders)
    {
        $labels = [];
        $data = [];

        foreach ($devices as $device) {
            $incident_per_device = DevicePest::where('device_id', $device->id)
                ->whereIn('order_id', $orders->pluck('id'))
                ->get();
            $count = $incident_per_device->sum('total');

            if ($count > 0) {
                $labels[] = $device->code;
                $data[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Gráfica de incidentes por plagas
     */
    private function graphIncidentsByPests($devices, $orders)
    {
        $labels = [];
        $data = [];

        $pests = PestCatalog::whereIn('id', DevicePest::whereIn('device_id', $devices->pluck('id'))->pluck('pest_id')->unique())->get();
        $labels = $pests->pluck('name');
        $pest_keys = $pests->select('id')->toArray();

        foreach ($pest_keys as $pk) {
            $pest_per_devices = DevicePest::whereIn('device_id', $devices->pluck('id'))
                ->where('pest_id', $pk['id'])
                ->whereIn('order_id', $orders->pluck('id'))
                ->get();
            $count = $pest_per_devices->sum('total');
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Obtener años disponibles
     */
    private function getYears()
    {
        $startYear = Order::selectRaw('YEAR(MIN(programmed_date)) as year')
            ->whereNotNull('programmed_date')
            ->value('year');

        $currentYear = Carbon::now()->year;

        // Si no hay datos, usar el año actual
        if (!$startYear || $startYear > $currentYear) {
            return [$currentYear];
        }

        $years = range($startYear, $currentYear);
        return $years;
    }

    /**
     * Gráfica de incidentes por mes (tendencia anual)
     */
    private function graphIncidentsByMonth($devices, $year)
    {
        $labels = [];
        $data = [];

        // Iterar sobre los 12 meses del año
        for ($month = 1; $month <= 12; $month++) {
            $labels[] = $this->months[$month];

            // Obtener órdenes del mes y año especificado
            $orders = Order::whereMonth('programmed_date', $month)
                ->whereYear('programmed_date', $year)
                ->whereIn('id', DevicePest::whereIn('device_id', $devices->pluck('id'))->pluck('order_id')->unique())
                ->get();

            // Sumar todas las incidencias de plagas en ese mes
            $total_incidents = DevicePest::whereIn('device_id', $devices->pluck('id'))
                ->whereIn('order_id', $orders->pluck('id'))
                ->sum('total');

            $data[] = $total_incidents;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Estadísticas por dispositivo individual (plagas y tendencia mensual)
     */
    public function deviceStats(Request $request, string $floorplanId, string $deviceId)
    {
        $floorplan = FloorPlans::findOrFail($floorplanId);
        $device = Device::findOrFail($deviceId);

        if ($device->floorplan_id != $floorplan->id) {
            abort(404);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $trend = $request->input('trend', false);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Órdenes relevantes - usar rango de fechas si están disponibles, sino mes/año
        if ($startDate && $endDate) {
            $orders = Order::whereBetween('programmed_date', [$startDate, $endDate])
                ->whereIn('id', DevicePest::where('device_id', $device->id)->pluck('order_id')->unique())
                ->get();
        } else {
            $orders = Order::whereMonth('programmed_date', $month)
                ->whereYear('programmed_date', $year)
                ->whereIn('id', DevicePest::where('device_id', $device->id)->pluck('order_id')->unique())
                ->get();
        }

        // Gráfica por plagas (labels y data)
        $pests = PestCatalog::whereIn('id', DevicePest::where('device_id', $device->id)->pluck('pest_id')->unique())->get();
        $labels_pests = $pests->pluck('name');
        $data_pests = [];
        foreach ($pests->pluck('id') as $pid) {
            $count = DevicePest::where('device_id', $device->id)
                ->where('pest_id', $pid)
                ->whereIn('order_id', $orders->pluck('id'))
                ->sum('total');
            $data_pests[] = $count;
        }

        // Gráfica por meses (tendencia) para el año seleccionado
        $labels_months = [];
        $data_months = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels_months[] = $this->months[$m];
            $orders_month = Order::whereMonth('programmed_date', $m)
                ->whereYear('programmed_date', $year)
                ->whereIn('id', DevicePest::where('device_id', $device->id)->pluck('order_id')->unique())
                ->get();

            $total_incidents = DevicePest::where('device_id', $device->id)
                ->whereIn('order_id', $orders_month->pluck('id'))
                ->sum('total');

            $data_months[] = $total_incidents;
        }

        $graph_per_pests = ['labels' => $labels_pests, 'data' => $data_pests];
        $graph_per_months = ['labels' => $labels_months, 'data' => $data_months];

        // Últimas 10 revisiones (order_incidents) para este dispositivo
        $reviews = OrderIncidents::where('device_id', $device->id)
            ->with(['question', 'order'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        if ($request->wantsJson() || $trend) {
            return response()->json([
                'success' => true,
                'pests' => $graph_per_pests,
                'trend' => $graph_per_months
            ]);
        }

        $navigation = $this->getNavigation($floorplan);
        $months = $this->months;
        $years = $this->getYears();

        return view('floorplans.graphics.device_stats', compact('device', 'floorplan', 'navigation', 'months', 'years', 'graph_per_pests', 'graph_per_months', 'reviews'));
    }
}