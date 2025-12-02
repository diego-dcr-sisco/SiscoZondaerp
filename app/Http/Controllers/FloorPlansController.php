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
            'Áreas de aplicación' => route('customer.show.sede.areas', ['id' => $floorplan->customer_id])
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
        $type = Storage::disk('public')->mimeType($url);

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
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:10000'
        ]);

        $file = $request->file('file');
        $url = $this->path . $customerId . '/' . time() . '_' . $file->getClientOriginalName();

        Storage::disk('public')->put($url, file_get_contents($file));

        $floorplan = new FloorPlans();
        $floorplan->fill($request->all());
        $floorplan->customer_id = $customerId;
        $floorplan->service_id = $request->input('service_id') != 0 ? $request->input('service_id') : null;
        $floorplan->path = $url;
        $floorplan->save();

        return back();
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

            $image = Image::make(Storage::disk('public')->get('/' . $floorplan->path));
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
            'print_data'
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
        $request->validate([
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        $floorplan->fill($request->except('file'));
        $floorplan->customer_id = $floorplan->customer->id;
        $floorplan->service_id = $request->input('service_id') != 0 ? $request->input('service_id') : null;

        if ($request->hasFile('file')) {
            // Si hay una imagen nueva, eliminar la imagen anterior si existe
            if ($floorplan->path && Storage::disk('local')->exists('public/floorplans/' . $floorplan->path)) {
                Storage::disk('local')->delete('public/floorplans/' . $floorplan->path);
            }

            $newFilename = $floorplan->customer->id . '/' . $floorplan->customer->id . '_' . time() . '.' . $request->file('file')->getClientOriginalExtension();
            $request->file('file')->storeAs('public/floorplans/', $newFilename, 'local');
            $floorplan->path = 'floorplans/' . $newFilename;
        }
        $floorplan->save();

        return back();
    }

    public function updateDevices(Request $request, string $id)
    {
        $pointsData = json_decode($request->input('points'));
        //dd($pointsData);
        $create_version = $request->input('create_version');
        $floorplan = FloorPlans::find($id);

        $version = FloorplanVersion::where('floorplan_id', $floorplan->id)->max('version');

        if ($version) {
            $latestVersionNumber = ++$version;
        } else {
            $latestVersionNumber = 1;
        }


        if ($create_version) {
            FloorplanVersion::insert([
                'floorplan_id' => $floorplan->id,
                'version' => $latestVersionNumber,
                'updated_at' => now(),
            ]);
        } else {
            FloorplanVersion::where('floorplan_id', $floorplan->id)->where('version', $version)->update(['updated_at' => $request->input('version_updated_at') . ' 00:00:00']);
        }

        $nplans = [];
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
            $nplans[] = $point->count;
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
}