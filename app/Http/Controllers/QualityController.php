<?php

namespace App\Http\Controllers;

use App\Models\ApplicationArea;
use App\Models\Customer;
use App\Models\FloorPlans;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\OrderStatus;
use App\Models\OrderIncidents;
use App\Models\Service;
use App\Models\User;
use App\Models\Contract;
use App\Models\ZoneType;
use App\Models\OrderTechnician;
use App\Models\Technician;
use App\Models\OpportunityArea;
use App\Models\Device;
use App\Models\Report;
use App\Services\QualityAnalyticsService;
use App\Models\RotationPlan;
use App\Models\ProductCatalog;
use App\Models\Filenames;
use App\Models\ServiceType;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PhpParser\ErrorHandler\Collecting;
use View;
use Illuminate\Validation\ValidationException;
use Carbon\CarbonPeriod;

class QualityController extends Controller
{
    private $size = 50;
    private QualityAnalyticsService $analyticsService;

    public function __construct(QualityAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function customers()
    {
        $size = $this->size;
        $customers = Customer::where('general_sedes', '!=', 0)->orderBy('name', 'asc')->paginate($this->size);
        $matrix = Customer::where('general_sedes', 0)->get();
        $navigation = [
            'Clientes' => route('quality.customers'),
        ];

        if (auth()->user()->hasAnyRole(['SupervisorCalidad', 'AdministradorDireccion'])) {
        //$navigation['Relaciones'] = route('quality.tracing');
    }

        return View(
            'dashboard.quality.customers',
            compact('customers', 'matrix', 'size', 'navigation')
        );
    }

    public function tracing()
    {
        $quality_users = User::where('work_department_id', 7)->get();
        $matrix = Customer::where('general_sedes', 0)->orderBy('name', 'ASC')->get();
        $control_customers = Customer::whereIn('administrative_id', $quality_users->pluck('id'))->where('general_sedes', 0)->paginate($this->size);
        $size = $this->size;
        $navigation = [
            'Clientes' => route('quality.customers'),
            //'Relaciones' => route('quality.tracing'),
        ];

        return View(
            'dashboard.quality.tracing',
            compact('quality_users', 'matrix', 'control_customers', 'size', 'navigation')
        );
    }

    public function storePermission(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $administrative_id = $request->input('user_id');
        $customer = Customer::find($customer_id);

        if ($customer) {
            $customer->administrative_id = $administrative_id;
            $customer->save();

            $sedes = Customer::where('general_sedes', $customer_id)->get();
            foreach ($sedes as $sede) {
                $sede->administrative_id = $administrative_id;
                $sede->save();
            }
        }

        return back();
    }

    public function destroyRelation(string $id, string $matrixId)
    {
        $customer = Customer::find($matrixId);
        if ($customer) {
            $customer->administrative_id = null;
            $customer->save();

            $sedes = Customer::where('general_sedes', $customer->id)->get();
            foreach ($sedes as $sede) {
                $sede->administrative_id = null;
                $sede->save();
            }
        }
              
        return back();
    }

    public function search(Request $request)
    {
        $size = $this->size;
        $matrix = Customer::where('general_sedes', 0)->get();

        $search = $request->input('search_customer');
        $searchTerm = '%' . $search . '%';
        $customers = Customer::where('general_sedes', '!=', 0);
        $customers = $customers->where('name', 'LIKE', $searchTerm);

        $matrix_id = $request->input('search_matrix');
        if ($matrix_id) {
            $customers = $customers->where('general_sedes', $matrix_id);
        }

        $customers = $customers->orderBy('name', 'asc')->paginate($this->size);
        $navigation = [
            'Clientes' => route('quality.customers'),
            //'Relaciones' => route('quality.tracing'),
        ];

        return View(
            'dashboard.quality.customers',
            compact('customers', 'matrix', 'size', 'navigation')
        );
    }

    public function customer(Request $request, string $id)
    {
        $pendings = [];
        $count_devices = 0;
        $customer = Customer::find($id);
        $orders = $customer->orders()->where('status_id', 1)->get();
        $opportunity_areas = OpportunityArea::where('customer_id', $customer->id)->get();
        $floorplans = $customer->floorplans;
        $rotation_plans = RotationPlan::where('customer_id', $customer->id)->get();
        $customer_ranges = Customer::where('general_sedes', '!=', 0)->orWhere('service_type_id', 1)->orderBy('name')->get();
        $order_status = OrderStatus::all();
        
        foreach ($floorplans as $floorplan) {
            $last_version = $floorplan->versions()->latest('version')->value('version');
            $count_devices += $floorplan->devices($last_version)->count();
        }

        foreach ($customer->contracts as $contract) {
            $endDate = Carbon::parse($contract->enddate);
            if ($endDate->isBetween(Carbon::now(), Carbon::now()->addDays(31))) {
                $pendings[] = [
                    'id' => $contract->id,
                    'content' => 'El contrato con id "' . $contract->id . '" esta apunto de expirar.',
                    'date' => $contract->enddate,
                    'type' => 'contract'
                ];
            }
        }

        foreach ($orders as $order) {
            $programmed_date = Carbon::parse($order->programmed_date);
            if ($programmed_date <= Carbon::now()) {
                $pendings[] = [
                    'id' => $order->id,
                    'content' => 'La orden de servicio con id "' . $order->id . '" con los servicios "' . implode(', ', $order->services->pluck('name')->toArray()) . '", esta programada para esta semana.',
                    'date' => $order->programmed_date,
                    'type' => 'order'
                ];
            }
        }

        foreach ($customer->files as $file) {
            $expirated_date = Carbon::parse($file->expirated_at);
            if ($expirated_date->isBetween(Carbon::now(), Carbon::now()->addDays(31))) {
                $pendings[] = [
                    'id' => $customer->id,
                    'content' => 'El Documento "' . $file->filename->name . '" esta apunto de expirar.',
                    'date' => $file->expirated_at,
                    'type' => 'file'
                ];
            }
        }

        // $size = count($pendings);
        $start = count($pendings) - 20;
        $pendings = array_slice($pendings, $start, 20);
        $techniciansAll = Technician::with('user')->get();

        $technicians = $techniciansAll->map(function ($techniciansAll) {
            return [
                'id' => $techniciansAll->id,
                'name' => $techniciansAll->user->name,
            ];
        });

        //consulta
       
        $query    = Order::where('customer_id', $customer->id);

        // Parámetros de paginación/orden
        $size      = $request->input('size',25);
        $sort = $request->input('sort', 'programmed_date');
        //$sort      = $request->input('sort', 'id');
        $direction = $request->input('direction', 'ASC');

        // Filtros opcionales
        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }

        if ($request->filled('service')) {
            $serviceIds = Service::where('name', 'LIKE', '%'.$request->service.'%')->pluck('id');
            $orderIds   = OrderService::whereIn('service_id', $serviceIds)->pluck('order_id');
            $query->whereIn('id', $orderIds);
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d))->format('Y-m-d');
            }, explode(' - ', $request->date_range));
            
            $query->whereBetween('programmed_date', [$startDate, $endDate]);
        }



        if ($request->filled('time')) {
            $query->whereTime('start_time', $request->time);
        }

        if ($request->filled('order_type')) {
            if ($request->order_type === 'MIP') {
                $query->whereNotNull('contract_id');
            } else {
                $query->whereNull('contract_id');
            }
        }

        if ($request->filled('signature_status')) {
            if ($request->signature_status === 'signed') {
                $query->whereNotNull('signature_name');
            } elseif ($request->signature_status === 'unsigned') {
                $query->whereNull('signature_name');
            }
        }

        // Orden y paginación
        $query->orderBy($sort, $direction);
        $orders = $query->paginate($size)
                        ->appends($request->only([
                            'size','sort','direction',
                            'status','service','date_range',
                            'time','order_type','signature_status'
                        ]));

        
        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),

        ];
        return view(
            'dashboard.quality.show.customer',
            compact('customer', 'count_devices', 'pendings', 'technicians', 'opportunity_areas', 'rotation_plans', 'navigation', 'orders', 'customer_ranges', 'order_status', 'size')
        );
    }

    public function showFiles(string $id)
    {
        $filenames = Filenames::where('type', 'customer')->get();
        $customer = Customer::find($id);
        $service_types = ServiceType::all();
        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),
        ];
        
        return view('dashboard.quality.files.index', compact('customer', 'filenames', 'navigation'));
    }

    public function rotationPlans(Request $request, string $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        
        // Obtener los contratos del cliente
        $contracts = $customer->contracts()->with('rotationPlans')->get();
        
        // Obtener todos los planes de rotación del cliente (a través de sus contratos)
        $rotationPlans = RotationPlan::whereIn('contract_id', $contracts->pluck('id'))
            ->orderBy('authorizated_at', 'desc')
            ->paginate($request->input('size', 25));

        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),

        ];
        return view('dashboard.quality.rotation-plan.index', compact('customer','rotationPlans','contracts','navigation'));
    }

    public function createRotationPlan(string $customerId)
    {
        
        $contracts = Contract::where('customer_id', $customerId)->get();
        $products = ProductCatalog::orderBy('name', 'asc')->get();
        $customer = Customer::find($customerId);
        $months = $this->getMonthsBetweenDates($contracts->first()->startdate, $contracts->first()->enddate);

        return view('dashboard.quality.rotation-plan.create', compact('contracts', 'products', 'customer', 'months'));
    }

    private function getMonthsBetweenDates($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, '1 month', $end);
        $months = [];
        foreach ($period as $date) {
            $months[] = [
                'index' => $date->month,
                'name' => $date->translatedFormat('F')
            ];
        }
        return $months;
    }

    public function searchRotationPlans(Request $request, string $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $query = RotationPlan::where('customer_id', $customerId);

        // Filtrar por nombre
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filtrar por rango de fechas
        if ($request->filled('date_range')) {
            [$startDate, $endDate] = explode(' - ', $request->input('date_range'));
            
            $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate))->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate))->endOfDay();
            
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $rotationPlans = $query->paginate($this->size);
        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),

        ];
        return view('dashboard.quality.rotation-plan.index', compact('customer', 'rotationPlans', 'navigation'));
    }

    public function searchOrders(Request $request, string $id)
    {
        $customer = $request->input('search_customer');
        $date = $request->input('date');
        $service = $request->input('service');
        $status = $request->input('status');

        // Parámetros de ordenamiento
        $sortBy = $request->input('sort_by', 'programmed_date');
        $sortDirection = $request->input('sort_direction', 'desc');

        $orders = Order::where('customer_id', $id);

        if ($date) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $date));
            $startDate = $startDate->format('Y-m-d');
            $endDate = $endDate->format('Y-m-d');
            $orders = $orders->whereBetween('programmed_date', [$startDate, $endDate]);
        }

        if ($service) {
            $serviceName = '%' . $service . '%';
            $orders = $orders->whereHas('services', function ($query) use ($serviceName) {
                $query->where('name', 'LIKE', $serviceName);
            });
        }

        if ($status) {
            $orders = $orders->where('status_id', $status);
        }

        // Aplicar ordenamiento
        $orders = $orders->orderBy($sortBy, $sortDirection);

        $size = $this->size;
        $order_status = OrderStatus::all();
        $customer = Customer::find($id);

        $orders = $orders->paginate($size)->appends([
            'search_customer' => $customer,
            'date' => $date,
            'service' => $service,
            'status' => $status,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
        ]);

        return view(
            'dashboard.quality.order.index',
            compact('orders', 'order_status', 'size', 'customer')
        );
    }

    public function updateOrderTechnicians(Request $request, string $id)
    {
        $order_id = $request->input('order_id');
        $technician_ids = $request->input('technician_ids');

        OrderTechnician::where('order_id', $order_id)->delete();

        foreach ($technician_ids as $technician_id) {
            $order_technician = new OrderTechnician();
            $order_technician->order_id = $order_id;
            $order_technician->technician_id = $technician_id;
            $order_technician->save();
        }

        return back()->with('success', 'Técnicos actualizados correctamente');
    }

    public function contracts(string $id)
    {
        $customer = Customer::find($id);
        $contracts = Contract::where('customer_id', $customer->id)->orderBy('enddate', 'desc')->paginate($this->size);
        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),

        ];

        return view(
            'dashboard.quality.contract.index',
            compact('contracts', 'customer', 'navigation')
        );
    }

    public function opportunityAreas(string $id)
    {
        $customer = Customer::find($id);
        $opportunity_areas = OpportunityArea::where('customer_id', $customer->id)->paginate($this->size);

        return view('dashboard.quality.show.opportunity-area', compact('opportunity_areas', 'customer'));
    }

    public function updateTechnician(Request $request, string $id)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:order,id',
            'technician_ids' => 'required|array',
            'technician_ids.*' => 'exists:technician,id'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                OrderTechnician::where('order_id', $validated['order_id'])->delete();

                foreach ($validated['technician_ids'] as $technician_id) {
                    OrderTechnician::create([
                        'order_id' => $validated['order_id'],
                        'technician_id' => $technician_id
                    ]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Técnicos actualizados correctamente']);
        } catch (\Exception $e) {
            \Log::error("Error updating technicians: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar técnicos'], 500);
        }
    }

    public function getTechniciansByDate(Request $request)
    {
        $date = $request->input('date_range');
        $technicians = [];
        if ($date) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $date));
            $startDate = $startDate->format('Y-m-d');
            $endDate = $endDate->format('Y-m-d');
            $orders = Order::whereBetween('programmed_date', [$startDate, $endDate])->get();
            $order_technicias = OrderTechnician::whereIn('order_id', $orders->pluck('id'))->get();
            $technicians = User::whereIn('id', Technician::whereIn('id', $order_technicias->pluck('technician_id'))->get()->pluck('user_id'))->get();
        }

        $data = [
            'technicians' => $technicians,
            'all_technicians' => User::where('work_department_id', 8)->get()
        ];

        return response()->json(['data' => $data]);
    }


    public function zones(string $id)
    {
        $zone_types = ZoneType::all();
        $customer = Customer::find($id);
        $zones = ApplicationArea::where('customer_id', $customer->id)->paginate($this->size);
        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),

        ];
        return view(
            'dashboard.quality.zone.index',
            compact('zones', 'customer', 'zone_types', 'navigation')
        );
    }

    public function editZone(string $id)
    {
        $zone = ApplicationArea::find($id);
        $customer = Customer::find($zone->customer_id);
        $type = $customer->type;
        $zone_types = ZoneType::all();
        return view('customer.edit.modals.area-edit', compact('zone', 'zone_types', 'customer', 'type'));
    }

    public function updateZone(Request $request, string $id)
    {
        $zone = ApplicationArea::find($id);
        $zone->name = $request->input('name');
        $zone->zone_type_id = $request->input('zone_type_id');
        $zone->m2 = $request->input('m2');
        $zone->save();
        return redirect()->back()->with('success', 'Zona actualizada correctamente');
    }

    public function devices(string $id, Request $request)
    {
        $customer = Customer::find($id);
        $floorplans = FloorPlans::where('customer_id', $customer->id)->get();
        $filterName = $request->input('name');
        $filterCode = $request->input('code');

        $deviceSummary = [];
        foreach ($floorplans as $floorplan) {
            $last_version = $floorplan->versions()->latest('version')->value('version');
            $devices = $floorplan->devices($last_version)->get();
            foreach ($devices as $device) {
                // filtros
            $matchesName = empty($filterName) || stripos($device->controlPoint->name, $filterName) !== false;
            $matchesCode = empty($filterCode) || stripos($device->controlPoint->code, $filterCode) !== false;
            
            if (!$matchesName || !$matchesCode) {
                continue; // Saltar dispositivo si no cumple con los filtros
            }
                $deviceId = $device->controlPoint->id;
                if (!isset($deviceSummary[$deviceId])) {
                    $deviceSummary[$deviceId] = [
                        'id' => $deviceId,
                        'name' => $device->controlPoint->name,
                        'count' => 0,
                        'code' => $device->controlPoint->code,
                        'floorplans' => [],
                        'zones' => [],
                    ];
                }
                $deviceSummary[$deviceId]['count']++;

                if (!in_array($device->applicationArea->name, $deviceSummary[$deviceId]['zones'])) {
                    $deviceSummary[$deviceId]['zones'][] = $device->applicationArea->name;
                }

                if (!in_array($floorplan->filename, $deviceSummary[$deviceId]['floorplans'])) {
                    $deviceSummary[$deviceId]['floorplans'][] = $floorplan->filename;
                }
            }
        }
        $navigation = [
            'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
            //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
            'Contrato' => route('quality.contracts', ['id' => $customer->id]),
            'Planos' => route('quality.floorplans', ['id' => $customer->id]),
            'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
            'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
            'Archivos' => route('quality.files', ['id' => $customer->id]),
            'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),
        ];

        return view(
            'dashboard.quality.device.index',
            compact('deviceSummary', 'customer', 'navigation')
        );
    }

    // AJAX search methods
    public function getOrdersByCustomer(Request $request)
    {
        $orders = [];

        if (!empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $customerIDs = Customer::where('name', 'LIKE', $searchTerm)->pluck('id');
            $orders = Order::whereIn('customer_id', $customerIDs)->pluck('id');
            // orWhere()
        }

        return response()->json(['orders' => $orders]);
    }

    public function searchOrdersTechnician(Request $request, string $id)
    {
        $orders = Order::where('customer_id', $id)->where('status_id', 1);
        $technicianSelected = [];
        $date = $request->input('date');

        if ($date) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $date));
            $startDate = $startDate->format('Y-m-d');
            $endDate = $endDate->format('Y-m-d');
            $orders = $orders->whereBetween('programmed_date', [$startDate, $endDate]);
        } else {
            $orders = [];
            return response()->json(['orders' => $orders, 'technicianSelected' => $technicianSelected]);
        }
        $orders = $orders->get();

        foreach ($orders as $order) {
            foreach ($order->technicians as $technician) {
                $id_technician = $technician->id;
                if (!isset($technicianSelected[$id_technician])) {
                    $technicianSelected[$id_technician] = $id_technician;
                }
            }
        }
        $technicianSelected = array_values($technicianSelected);
        return response()->json(['orders' => $orders, 'technicianSelected' => $technicianSelected]);
    }

    public function replaceTechnicians(Request $request, string $id)
    {
        $customer = Customer::find($id);
        $technicians = $request->input('technicians');
        $id_orders = $request->input('id_orders');

        if (is_string($id_orders)) {
            $id_orders = json_decode($id_orders, true);
        }

        if (is_string($technicians)) {
            $technicians = json_decode($technicians, true);
        }

        if (!$technicians || !$id_orders)
            return redirect()->back();

        //dd("aaa");
        foreach ($id_orders as $id_order) {
            $order = Order::find($id_order);
            if (!$order) {
                continue; // Si no existe la orden, pasa a la siguiente
            }

            // Obtener los técnicos relacionados actualmente con la orden
            $currentTechnicians = OrderTechnician::where('order_id', $id_order)
                ->pluck('technician_id')
                ->toArray();

            // Determinar las relaciones que se deben agregar y eliminar
            $techniciansToAdd = array_diff($technicians, $currentTechnicians); // Técnicos nuevos
            $techniciansToRemove = array_diff($currentTechnicians, $technicians); // Técnicos extra

            // Agregar las nuevas relaciones
            foreach ($techniciansToAdd as $technicianId) {
                OrderTechnician::create([
                    'order_id' => $id_order,
                    'technician_id' => $technicianId,
                ]);
            }

            // Eliminar las relaciones extra en la tabla intermedia
            foreach ($techniciansToRemove as $technicianId) {
                OrderTechnician::where('order_id', $id_order)
                    ->where('technician_id', $technicianId)
                    ->delete();
            }

        }

        return redirect()->back();

    }

    public function getOrdersByTime(Request $request)
    {
        $orders = [];
        if (!empty($request->start_time)) {
            $time_request = Carbon::parse($request->start_time)->format('H:i');
            $orders = Order::whereTime('start_time', '=', $time_request)->pluck('id')->toArray();
        }

        return response()->json(['orders' => $orders]);
    }

    public function getOrdersByDate(Request $request)
    {
        $orders = [];

        if (!empty($request->programmed_date)) {
            $date_request = Carbon::parse($request->programmed_date)->format('Y-m-d');
            $orders = Order::whereDate('programmed_date', $date_request)->pluck('id')->toArray();
        }

        return response()->json(['orders' => $orders]);
    }

    public function getOrdersByService(Request $request)
    {
        $orders = [];

        if (!empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            // $order->services->pluck('name')->toArray()
            $servicesId = Service::where('name', 'LIKE', $searchTerm)->pluck('id');
            // $orders = Order::services->whereIn('customer_id', $servicesId)->pluck('id');
            $orders = Order::whereHas('services', function ($query) use ($servicesId) {
                $query->whereIn('service_id', $servicesId); // Filtrar servicios por ID
            })->pluck('id'); // Obtener solo los IDs de las órdenes
        }

        return response()->json(['orders' => $orders]);
    }

    public function getOrdersByStatus(Request $request)
    {
        $orders = [];

        if (!empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $statusId = OrderStatus::where('name', 'LIKE', $searchTerm)->pluck('id');
            $orders = Order::whereIn('status_id', $statusId)->pluck('id');
        }

        return response()->json(['orders' => $orders]);
    }

    ///////////////////////////////// GRAFICAS DE CALIDAD ////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Display analytics dashboard for a customer
     * 
     * @param string $id Customer ID
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function analytics(Request $request, string $id)
    {
        $this->validateCustomerId($id);

        $data = $this->analyticsService->getAnalyticsData((int) $id);
        // Datos para la vista hija (con valores por defecto)
        $consumptionRequest = $request->has('reportType') ? $request : new Request([
            'reportType' => 'weekly',
            'start_date' => now()->subMonth()->format('Y-m-d')
        ]);

        $customer = Customer::findOrFail($id);
        
        $consumptionData = $this->deviceConsumptionPrueba($consumptionRequest,(string) $customer->id);

        $navigation = [
                'Ordenes de servicio' => route('quality.customer', ['id' => $customer->id]),
                //'Analíticas' => route('quality.analytics', ['id' => $customer->id]),
                'Contrato' => route('quality.contracts', ['id' => $customer->id]),
                'Planos' => route('quality.floorplans', ['id' => $customer->id]),
                'Áreas de aplicación' => route('quality.application-areas', ['id' => $customer->id]),
                'Dispositivos' => route('quality.devices', ['id' => $customer->id]),
                'Archivos' => route('quality.files', ['id' => $customer->id]),
                'Planes de rotación' => route('quality.rotation-plan.index', ['id' => $customer->id]),
        ];

        return view('dashboard.quality.analytics.index', array_merge($data, [
            'consumptionData' => $consumptionData,
            'chartData' => json_encode([
            'devices' => $consumptionData['table'],
            'timeKeys' => $consumptionData['timeKeys']
        ]),
            'customer' => $customer,
            'navigation' => $navigation
        ]));
    }

    /**
     * Get device consumption table data via AJAX or direct request
     * 
     * @param Request $request
     * @param string $customerId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function deviceConsumptionTable(Request $request, string $customerId)
    {
        $this->validateCustomerId($customerId);
        
        // Validate request parameters
        $validated = $request->validate([
            'date_range' => 'nullable|string',
            'service_id' => 'nullable|integer|exists:service,id'
        ]);

        // $data = $this->analyticsService->getDeviceConsumptionTableData(
        //     (int) $customerId,
        //     $validated['date_range'] ?? null,
        //     $validated['service_id'] ?? null
        // );
        
        // if ($request->ajax()) {
        //     return response()->json($data);
        // }
        
        // return view('dashboard.quality.analytics.device_consumption_table', $data);
        
        try {
            $data = $this->analyticsService->getDeviceConsumptionTableData(
                (int) $customerId,
                $validated['date_range'] ?? null,
                $validated['service_id'] ?? null
            );
            //dd($data);
            
            if ($request->ajax()) {
                return response()->json($data);
            }
            
            return view('dashboard.quality.analytics.device_consumption_table', $data);
            
        } catch (\Exception $e) {
            \Log::error("Error in deviceConsumptionTable method: " . $e->getMessage(), [
                'customer_id' => $customerId,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Error al cargar los datos de consumo'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error al cargar los datos de consumo']);
        }
    }

    /**
     * Validate customer ID exists
     * 
     * @param string $customerId
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function validateCustomerId(string $customerId): void
    {
        if (!is_numeric($customerId) || $customerId <= 0) {
            throw new \InvalidArgumentException('ID de cliente inválido');
        }
        
        // This will throw ModelNotFoundException if not found
        Customer::findOrFail($customerId);
    }




    public function deviceConsumptionPrueba( Request $request, string $id)
    { 
        
        // Valores por defecto para la carga inicial
        $defaultStartDate = now()->subMonth()->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');
        $defaultWeekDay = Carbon::FRIDAY;
        $timeKeys = [];
        $table = [];
        $reportType = 'weekly';
        $serviceId = null;

        $customer = Customer::findOrFail($id);

        // Validación: debe haber parámetros de filtro
        if ($request->hasAny(['date_range', 'service_id', 'week_day'])) {
            $validated = $request->validate([
                'date_range' => ['required', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4} - \d{2}\/\d{2}\/\d{4}$/'],
                'service_id' => ['required', 'integer', 'exists:service,id'], // ← AHORA REQUERIDO
                'week_day' => ['nullable', 'integer', 'between:0,6'],
                'report_type' => ['required', 'string', 'in:daily,weekly'],
            ]);
            
            // Procesar el rango de fechas
            $dates = explode(' - ', $validated['date_range']);
            $start_date = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $end_date = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
            $serviceId = $validated['service_id']; // ← AHORA SIEMPRE TIENE VALOR
            $weekDay = $validated['week_day'] ?? $defaultWeekDay;
            $reportType = $validated['report_type'];
        } else {
            // En carga inicial: solo retorna servicios disponibles, SIN tabla de datos
            $start_date = Carbon::parse($defaultStartDate)->startOfDay();
            $end_date = Carbon::parse($defaultEndDate)->endOfDay();
            $weekDay = $defaultWeekDay;
            $reportType = 'weekly';
        }

        // Obtenemos los IDs de servicios que aparecen en órdenes del cliente
        // Esto se obtiene SIN dependencia de dates inicialmente para mostrar en select
        $serviceIdsInOrders = DB::table('order_service')
            ->join('order', 'order_service.order_id', '=', 'order.id')
            ->where('order.customer_id', $id)
            ->pluck('order_service.service_id')
            ->unique();

        // Obtenemos solo esos servicios con prefix=1
        $allServices = Service::where('prefix', 1)
            ->whereIn('id', $serviceIdsInOrders)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Si no hay filtro completo, retorna vista sin datos (solo para cargar select)
        if (!$serviceId) {
            return view('dashboard.quality.device.consumptionTable', [
                'customer'      => $customer,
                'table'         => [],
                'start_date'    => $start_date->format('d/m/Y'),  
                'end_date'      => $end_date->format('d/m/Y'), 
                'selectedService' => null,
                'allServices'   => $allServices,
                'timeKeys'      => $timeKeys,
                'reportType'    => $reportType,
            ]);
        }

        // Validar que el servicio seleccionado exista en los servicios del cliente
        if (!$allServices->pluck('id')->contains($serviceId)) {
            return view('dashboard.quality.device.consumptionTable', [
                'customer'      => $customer,
                'table'         => [],
                'start_date'    => $start_date->format('d/m/Y'),  
                'end_date'      => $end_date->format('d/m/Y'), 
                'selectedService' => null,
                'allServices'   => $allServices,
                'timeKeys'      => $timeKeys,
                'reportType'    => $reportType,
                'error'         => 'El servicio seleccionado no existe en las órdenes de este cliente.'
            ]);
        }

        // Órdenes del cliente, en rango, con el servicio específico (que ya tiene prefix=1)
        $orders = Order::query()
            ->where('customer_id', $id)
            ->whereBetween('programmed_date', [$start_date, $end_date])
            ->whereHas('orderServices.service', function ($q) use ($serviceId) {
                $q->where('service.id', $serviceId);
            })
            ->get(['id', 'programmed_date']);
        
        // Ids de órdenes para usarlos al buscar incidentes
        $orderIds = $orders->pluck('id');

        // Servicio seleccionado en el filtro
        $selectedService = Service::find($serviceId);

        if ($orders->isEmpty()) {
            return view('dashboard.quality.device.consumptionTable', [
                'customer'      => $customer,
                'table'         => [],
                'start_date'    => $start_date->format('d/m/Y'),  
                'end_date'      => $end_date->format('d/m/Y'), 
                'selectedService' => $selectedService,
                'allServices'   => $allServices,
                'timeKeys'      => $timeKeys,
                'reportType'    => $reportType,
                'message'       => 'No hay órdenes registradas para este servicio en el rango de fechas seleccionado.'
            ]);
        }

        // Incidentes de esos orderIds órdenes, únicamente question_id = 13
        $incidents = OrderIncidents::query()
            ->with(['order:id,programmed_date'])
            ->whereIn('order_id', $orderIds)
            ->where('question_id', 13)
            ->whereNotNull('device_id')
            ->get(['order_id', 'device_id', 'answer']);
        // Convertir respuestas para usar Carbon
        $weekDayMap = [
            0 => Carbon::SUNDAY,
            1 => Carbon::MONDAY,
            2 => Carbon::TUESDAY,
            3 => Carbon::WEDNESDAY,
            4 => Carbon::THURSDAY,
            5 => $defaultWeekDay,
            6 => Carbon::SATURDAY
        ];

    
        $weekDayConstant = $weekDayMap[$weekDay] ?? $defaultWeekDay;

        // Calcular el primer día del rango semanal
        $firstDayOfWeek = $start_date->copy()->dayOfWeek === $weekDayConstant
            ? $start_date->copy()
            : $start_date->copy()->next($weekDayConstant);
        // Calcular el último día del rango semanal
        $lastDayOfWeek = $end_date->copy()->dayOfWeek === $weekDayConstant
            ? $end_date->copy()
            : $end_date->copy()->previous($weekDayConstant);
        // Generar las claves de las semanas en el rango
        $weekKeys = [];
        if ($firstDayOfWeek->lte($lastDayOfWeek)) {
            $currentDayOfWeek = $firstDayOfWeek->copy();
            while ($currentDayOfWeek->lte($lastDayOfWeek)) {
                $weekKeys[] = $currentDayOfWeek->formatLocalized('%d-%b-%y');
                $currentDayOfWeek->addWeek();
            }
        }
        // Mapeo de respuestas -> valor decimal
        $mappings = [
            'Nulo' => 0, 'Bajo' => 0.25, 'Medio' => 0.5, 'Alto' => 0.75, 'Consumo Total' => 1,
        ];


        // Dispositivos para obtener información
        $deviceIds = $incidents->pluck('device_id')->unique()->values();
        $devices = Device::whereIn('id', $deviceIds)->get(['id', 'code']);
        
        // Mapa id -> etiqueta visible (CE-1, etc.)
        $deviceLabelOf = function ($id) use ($devices) {
            $dev = $devices->firstWhere('id', $id);
            return $dev->code ?? $dev->name ?? ('Device '.$id);
        };

        
        if ($reportType === 'daily') {
            // Generar reporte diario
            $timeKeys = [];
            $currentDay = $start_date->copy();
            while ($currentDay->lte($end_date)) {
                $timeKeys[] = $currentDay->format('d-M-y');
                $currentDay->addDay();
            }

            $table = [];
            foreach ($devices as $d) {
                $label = $deviceLabelOf($d->id);
                $table[$label] = array_fill_keys($timeKeys, 0);
                $table[$label]['TOTAL'] = 0;
            }

            foreach ($incidents as $inc) {
                $orderDate = Carbon::parse($inc->order->programmed_date);
                $dayKey = $orderDate->format('d-M-y');
                $label = $deviceLabelOf($inc->device_id);
                $value = $mappings[$inc->answer] ?? 0;

                if (isset($table[$label][$dayKey])) {
                    $table[$label][$dayKey] += $value;
                    $table[$label]['TOTAL'] += $value;
                }
            }

            
        } else {
            // Generar reporte semanal
            $weekDayConstant = $weekDayMap[$weekDay] ?? $defaultWeekDay;
            // Recalcular primer día del rango semanal
            $firstDayOfWeek = $start_date->copy()->dayOfWeek === $weekDayConstant
                ? $start_date->copy()
                : $start_date->copy()->next($weekDayConstant);
            // Recalcular último día del rango semanal
            $lastDayOfWeek = $end_date->copy()->dayOfWeek === $weekDayConstant
                ? $end_date->copy()
                : $end_date->copy()->previous($weekDayConstant);
        
            $timeKeys = [];
        
            if ($firstDayOfWeek->lte($lastDayOfWeek)) {
                $currentDayOfWeek = $firstDayOfWeek->copy();
                while ($currentDayOfWeek->lte($lastDayOfWeek)) {
                    $timeKeys[] = $currentDayOfWeek->formatLocalized('%d-%b-%y');
                    $currentDayOfWeek->addWeek();
                }
            }

            $table = [];
            foreach ($devices as $d) {
                $label = $deviceLabelOf($d->id);
                $table[$label] = array_fill_keys($timeKeys, 0);
                $table[$label]['TOTAL'] = 0;
            }

            foreach ($incidents as $inc) {
                $orderDate = Carbon::parse($inc->order->programmed_date);
                $bucketDay = $orderDate->copy();
                if ($bucketDay->dayOfWeek !== $weekDayConstant) {
                    $bucketDay->next($weekDayConstant);
                }

                if (!empty($timeKeys) && $bucketDay->gte($firstDayOfWeek) && $bucketDay->lte($lastDayOfWeek)) {
                    $weekKey = $bucketDay->format('d-M-y');
                    $label = $deviceLabelOf($inc->device_id);
                    $value = $mappings[$inc->answer] ?? 0;

                    $table[$label][$weekKey] += $value;
                    $table[$label]['TOTAL'] += $value;
                }
            }
        
        }
        // Ordenar la tabla por clave (CE-1, CE-2, etc.)
        ksort($table, SORT_NATURAL);
        return [
            'customer' => $customer,
            'table' => $table,
            'start_date' => $start_date->format('d/m/Y'),
            'end_date' => $end_date->format('d/m/Y'),
            'allServices' => $allServices,
            'selectedService' => $selectedService,
            'timeKeys' => $timeKeys,
            'reportType' => $reportType,
        ];

        ///////////////////////////////// FIN DE GRAFICAS DE CALIDAD ////////////////////////////////////////
    }
}
