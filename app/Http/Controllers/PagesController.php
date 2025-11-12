<?php

namespace App\Http\Controllers;

// Verificación de retorno

use App\Models\Administrative;
use App\Models\Branch;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Modelos
use App\Models\User;
use App\Models\Customer;
use App\Models\DatabaseLog;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\OrderStatus;
use App\Models\OrderTechnician;
use App\Models\Service;
use App\Models\Technician;
use App\Models\Lead;
use App\Models\LineBusiness;
use App\Models\OrderFrequency;
use App\Models\ServiceType;
use App\Models\UserFile;
use App\Models\Tracking;
use App\Models\Lot;

use Carbon\Carbon;

use function Laravel\Prompts\alert;

class PagesController extends Controller
{

    private $path = 'client_system/';
    private $mip_path = 'mip_directory/';

    private $hrs_format = [
        "00:00",
        "01:00",
        "02:00",
        "03:00",
        "04:00",
        "05:00",
        "06:00",
        "07:00",
        "08:00",
        "09:00",
        "10:00",
        "11:00",
        "12:00",
        "13:00",
        "14:00",
        "15:00",
        "16:00",
        "17:00",
        "18:00",
        "19:00",
        "20:00",
        "21:00",
        "22:00",
        "23:00"
    ];

    private $months = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
    ];

    private $size = 20;

    private function convertToUTC($date, $time)
    {
        $timezone = 'America/Mexico_City';
        $dateTimeLocal = $date . ' ' . $time;
        $carbonLocal = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeLocal, $timezone);
        return $carbonLocal->toDateTimeString();
    }

    private function getOrdersByTimeLapse($time_lapse, $orders)
    {
        if ($time_lapse == 1) {
            $orders->where('programmed_date', now()->toDateString());
        } elseif ($time_lapse == 2) {
            $orders->whereBetween('programmed_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($time_lapse == 3) {
            $orders->whereBetween('programmed_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }

        return $orders->get();
    }

    private function getPlanningData($start_date = null, $end_date = null)
    {
        $data = [];
        $timelapse = $this->hrs_format;


        if (!$start_date && !$end_date) {
            $start_date = now()->toDateString();
            $end_date = now()->toDateString();
        }

        foreach ($timelapse as $hrs) {
            $orders = Order::whereTime('start_time', $hrs)
                ->whereBetween('programmed_date', [$start_date, $end_date])
                ->get();

            foreach ($orders as $order) {
                $data[$hrs][] = [
                    'customer' => $order->customer->name,
                    'order_id' => $order->id,
                    'order_folio' => $order->folio,
                    'date' => $order->programmed_date,
                    'time' => $order->start_time,
                    'status' => $order->status->name,
                    'type' => $order->customer->serviceType->name,
                    'service' => $order->services()->first()->name,
                    'technicians' => $order->getNameTechnicians()->pluck('name')->toArray(),
                    'links' => [
                        'edit' => route('order.edit', ['id' => $order->id]),
                        'report' => route('report.review', ['id' => $order->id]),
                        'tracking' => route('tracking.create.order', ['id' => $order->id]),
                        'destroy' => route('order.destroy', ['id' => $order->id])
                    ],
                ];
            }
        }
        return $data;
    }

    private function getPlanningByTechnician($start_date = null, $end_date = null)
    {
        $timelapse = $this->hrs_format;

        if (!$start_date && !$end_date) {
            $start_date = now()->toDateString();
            $end_date = now()->toDateString();
        }

        // Obtener todos los técnicos con la relación user
        $allTechnicians = Technician::with('user')
            ->whereIn('user_id', Technician::pluck('user_id'))
            ->join('user', 'technician.user_id', '=', 'user.id')
            ->orderBy('user.name', 'ASC')
            ->select('technician.*')
            ->get()
            ->mapWithKeys(function ($tech) {
                return [$tech->id => $tech->user->name];
            })->toArray();

        // Inicializar la estructura de datos
        $data = [];
        $technicianOrders = [];

        foreach ($timelapse as $hrs) {
            $orders = Order::whereTime('start_time', $hrs)
                ->whereBetween('programmed_date', [$start_date, $end_date])
                ->with(['technicians.user', 'customer', 'status', 'customer.serviceType', 'services'])
                ->get();

            // Inicializar la fila para esta hora
            $data[$hrs] = [];

            // Para cada técnico, inicializar con array vacío
            foreach ($allTechnicians as $techId => $techName) {
                $data[$hrs][$techId] = [];
            }

            // Llenar con las órdenes correspondientes
            foreach ($orders as $order) {
                foreach ($order->technicians as $technician) {
                    $orderData = [
                        'customer' => $order->customer->name,
                        'order_id' => $order->id,
                        'order_folio' => $order->folio,
                        'date' => $order->programmed_date,
                        'time' => $order->start_time,
                        'status' => $order->status->name,
                        'type' => $order->customer->serviceType->name,
                        'service' => $order->services->first()->name ?? 'N/A',
                        'links' => [
                            'edit' => route('order.edit', ['id' => $order->id]),
                            'report' => route('report.review', ['id' => $order->id]),
                            'tracking' => route('crm.tracking.create.order', ['customerId' => $order->customer_id, 'orderId' => $order->id]),
                            'destroy' => route('order.destroy', ['id' => $order->id])
                        ],
                    ];

                    $data[$hrs][$technician->id][] = $orderData;

                    // También mantener un registro de todas las órdenes por técnico
                    if (!isset($technicianOrders[$technician->id])) {
                        $technicianOrders[$technician->id] = [
                            'name' => $technician->user->name,
                            'orders' => []
                        ];
                    }
                    $technicianOrders[$technician->id]['orders'][] = $orderData;
                }
            }
        }

        return [
            'timelapse' => $timelapse,
            'technicians' => $allTechnicians,
            'data' => $data,
            'technician_orders' => $technicianOrders
        ];
    }

    public function loadingERP()
    {
        session(['loading-erp' => true]);
        return view('loading-erp');
    }

    public function schedule(Request $request): View
    {
        $start_date = null;
        $end_date = null;

        if ($request->filled('date_range')) {
            [$start_date, $end_date] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));
        }

        $timelapse = $this->hrs_format;
        $schedule_data = $this->getPlanningData($start_date, $end_date);

        $navigation = [
            'Cronograma' => route('planning.schedule'),
            'Actividades' => route('planning.activities')
        ];

        return view(
            'dashboard.planning.schedule',
            compact('timelapse', 'schedule_data', 'navigation')
        );
    }

    public function activities()
    {
        $navigation = [
            'Cronograma' => route('planning.schedule'),
            'Actividades' => route('planning.activities')
        ];

        // Obtener los datos de planificación por técnico
        $planningData = $this->getPlanningByTechnician();

        // Extraer las variables necesarias para la vista
        $timelapse = $planningData['timelapse'];
        $technicians = $planningData['technicians'];
        $data = $planningData['data'];
        $technician_orders = $planningData['technician_orders'];

        return view(
            'dashboard.planning.activities',
            compact('navigation', 'timelapse', 'technicians', 'data', 'technician_orders')
        );
    }

    public function updateAssignments(Request $request)
    {
        try {
            $changes = $request->input('changes', []);

            foreach ($changes as $change) {
                $order = Order::find($change['order_id']);

                if ($order) {
                    // Actualizar la hora de la orden
                    $order->start_time = $change['to']['hour'];
                    $order->save();

                    // Actualizar los técnicos asignados (si es necesario)
                    // Esta parte depende de cómo manejes las relaciones entre órdenes y técnicos

                    $ot = OrderTechnician::updateOrCreate(
                        ['order_id' => $order->id],
                        ['technician_id' => $change['to']['technician']]
                    );

                    OrderTechnician::where('order_id', $order->id)->whereNot('id', $ot->id)->delete();
                }
            }

            return response()->json(['success' => true, 'message' => 'Asignaciones actualizadas correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function dashboard()
    {
        if (auth()->user()->type_id == 1) {
            $trackings_data = [];

            $startOfWeek = now()->startOfMonth();
            $endOfWeek = now()->endOfMonth();
            $services = Service::select('id', 'name')->orderBy('name')->get();

            $trackings = Tracking::whereBetween('next_date', [$startOfWeek, $endOfWeek])
                ->orderBy('next_date')
                ->get();

            $count_trackings = Tracking::whereBetween('next_date', [$startOfWeek, $endOfWeek])
                ->where('status', 'active')
                ->orderBy('next_date')
                ->count();

            foreach ($trackings as $tracking) {
                // Manejo seguro de la orden
                $orderInfo = null;
                if ($tracking->order_id && $tracking->order) {
                    $orderInfo = [
                        'id' => $tracking->order_id,
                        'folio' => $tracking->order->folio ?? 'Sin folio',
                    ];
                }

                $trackings_data[] = [
                    'id' => $tracking->id,
                    'customer' => $tracking->trackable->name,
                    'order' => $orderInfo,
                    'service' => $tracking->service_id,
                    'next_date' => $tracking->next_date,
                    'title' => $tracking->title,
                    'description' => $tracking->description,
                    'status' => $tracking->status,
                    'range' => $tracking->range,
                    'auto_url' => route('tracking.auto', ['id' => $tracking->id]),
                    'edit_url' => route('tracking.edit', ['id' => $tracking->id]),
                    'cancel_url' => route('tracking.cancel', ['id' => $tracking->id]),
                    'destroy_url' => route('tracking.destroy', ['id' => $tracking->id])
                ];
            }

            // Almacenar en sesión
            session(['trackings_data' => $trackings_data]);
            //session(['dashboard_services' => $services]);
            session(['count_trackings' => $count_trackings]);

            return view('dashboard.index', compact( 'services'));
        } else {
            $path = $this->path;
            $mip_path = $this->mip_path;
            return view('client.index', compact('path', 'mip_path'));
        }
    }

    public function crm()
    {
        /*
        $charts = [
            'customers' => (new GraphicController)->newCustomers(),
            'orders' => (new GraphicController)->orders(),
            'domestic' => (new GraphicController)->orderTypes(1),
            'comercial' => (new GraphicController)->orderTypes(2),
        ];

        $chartNames = [
            'Nuevos clientes',
            'Clientes agendados',
            'Clientes domesticos agendados',
            'Clientes comerciales agendados',
        ];

        $frecuencies = OrderFrequency::all();
        $leads = Lead::all();
        $months = $this->months;
        */
        return view('crm.index');
    }

    public function crmOrders(string $status)
    {
        $orders = Order::where('status_id', $status)->orderBy('id', 'desc');
        return view('crm.', compact('customers', 'order_status', 'type'));
    }


    public function rrhh(Request $request, $section)
    {
        $navigation = [
            'Crear usuario' => '/users/create',
            'Usuarios pendientes' => '/RRHH/1',
            'Documentos pendientes' => '/RRHH/2',
            'Documentos por vencer' => '/RRHH/3'
        ];

        $search = $request->input('search');
        $usersQuery = User::orderBy('name');

        if ($search) {
            $usersQuery->where('name', 'like', '%' . $search . '%');
        }

        $users = $usersQuery->paginate(20);

        foreach ($users as $user) {
            $user->pendingFiles = $this->pendingFiles($user->id);
        }

        $files = $section == 2
            ? UserFile::whereNull('path')->get()
            : UserFile::whereMonth('expirated_at', '<=', Carbon::now()->month)->get();

        return view('dashboard.rrhh.index', compact('users', 'files', 'section', 'navigation'));
    }

    public function pendingFiles($userId)
    {
        $files = UserFile::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereMonth('expirated_at', '<=', Carbon::now()->month)
                    ->orWhereNull('path');
            })
            ->get();
        return $files;
    }

    public function qualityOrders(string $status)
    {
        $user = auth()->user();
        $orders = Order::where('status_id', $status);

        if ($user->role_id == 1 && $user->work_department_id == 7) {
            $customerIds = Customer::where('administrative_id', $user->id)->get()->pluck('id');
            $orders = $orders->whereIn('customer_id', $customerIds);
        }

        $orders = $orders->paginate($this->size);

        return view(
            'dashboard.quality.orders',
            compact('orders', 'status')
        );
    }

    public function qualityGeneralByCustomer(string $customerId, string $section, string $status)
    {
        $customer = Customer::find($customerId);
        $zones = [];
        $floorplans = [];
        $deviceSummary = [];
        $orders = [];

        switch ($section) {
            case 1:
                $orders = Order::where('status_id', $status)->where('customer_id', $customerId)->paginate($this->size);

                break;
            case 2:
                $i = 0;
                foreach ($customer->floorplans as $floorplan) {
                    $devicesCount = $floorplan->devices($floorplan->versions->pluck('version')->first())->get()->count();
                    $floorplans[$i] = [
                        'id' => $floorplan->id,
                        'name' => $floorplan->filename,
                        'service' => $floorplan->service?->name,
                        'deviceCount' => $devicesCount,
                        'version' => $floorplan->versions->pluck('version')->first() ? $floorplan->versions->pluck('version')->first() : "Sin versión",
                    ];
                    $i++;
                }
                break;
            case 3:
                $i = 0;
                foreach ($customer->applicationAreas as $zone) {
                    $deviceByArea = 0;
                    foreach ($customer->floorplans as $floorplan) {
                        foreach ($floorplan->devices($floorplan->versions->pluck('version')->first())->get() as $device) {
                            if ($device->application_area_id == $zone->id) {
                                $deviceByArea++;
                            }
                        }
                    }
                    $zones[$i] = [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'zonetype' => $zone->zoneType?->name,
                        'm2' => $zone->m2,
                        'deviceCount' => $deviceByArea,
                    ];
                    $i++;
                }
                break;
            case 4:
                foreach ($customer->floorplans as $floorplan) {
                    foreach ($floorplan->devices($floorplan->versions->pluck('version')->first())->get() as $device) {
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

                        // Agrega los dispositivos que no se han agregado
                        if (!in_array($device->applicationArea->name, $deviceSummary[$deviceId]['zones'])) {
                            $deviceSummary[$deviceId]['zones'][] = $device->applicationArea->name;
                        }
                        // Agrega los planos que no se han agregado
                        if (!in_array($floorplan->filename, $deviceSummary[$deviceId]['floorplans'])) {
                            $deviceSummary[$deviceId]['floorplans'][] = $floorplan->filename;
                        }
                    }
                }
                break;
        }

        return view(
            'dashboard.quality.show.general',
            compact('orders', 'deviceSummary', 'floorplans', 'zones', 'status', 'customerId', 'section')
        );

    }


    public function qualityCustomers()
    {
        $user = auth()->user();

        $totalPages = 0;
        if ($user->role_id == 4) {
            $customers = Customer::where('general_sedes', '!=', 0)->where('service_type_id', 3)->get();
        } else {
            $customers = Customer::where('administrative_id', $user->id)->where('general_sedes', '!=', 0)->where('service_type_id', 3)->get();
        }

        return view(
            'dashboard.quality.customers',
            compact('customers')
        );
    }

    public function qualityControl()
    {
        $totalPages = 0;

        $customers = Customer::where('general_sedes', 0)->get();
        $users = User::where('work_department_id', 7)->get();

        return view(
            'dashboard.quality.control',
            compact('customers', 'users')
        );
    }

    public function qualityControlStore(Request $request)
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

    public function qualityControlDestroy(string $customerId)
    {

        $customer = Customer::find($customerId);
        if ($customer) {
            $customer->administrative_id = null;
            $customer->save();
            $sedes = Customer::where('general_sedes', $customerId)->get();
            foreach ($sedes as $sede) {
                $sede->administrative_id = null;
                $sede->save();
            }
        }

        return back();
    }



    public function filterPlanning(Request $request)
    {
        $daily_program = $technicians = $orders = [];

        try {
            $data = json_decode($request->input('data'), true);
            $date = json_decode($request->input('date'), true);
            if ($data) {
                $key = $data['key'];
                $values = $data['values'];

                [$startDate, $endDate] = array_map(function ($d) {
                    return Carbon::createFromFormat('d/m/Y', trim($d))->format('Y-m-d');
                }, explode(' - ', $date));

                switch ($key) {
                    case 'technician':
                        $orders = Order::whereIn(
                            'id',
                            OrderTechnician::whereIn('technician_id', $values)->get()->pluck('order_id')
                        )->whereBetween('programmed_date', [$startDate, $endDate])->get();
                        $technicians = Technician::whereIn('id', $values)->get();
                        break;

                    case 'business_line':
                        $orders = Order::whereIn(
                            'id',
                            OrderService::whereIn(
                                'service_id',
                                Service::whereIn('business_line_id', $values)->get()->pluck('id')
                            )->get()->pluck('order_id')
                        )->whereBetween('programmed_date', [$startDate, $endDate])->get();
                        $technicians = Technician::whereIn(
                            'id',
                            OrderTechnician::whereIn('order_id', $orders->pluck('id'))->get()->pluck('technician_id')
                        )->get();
                        break;

                    case 'branch':
                        $orders = Order::whereIn(
                            'customer_id',
                            Customer::whereIn('branch_id', $values)->get()->pluck('id')
                        )->whereBetween('programmed_date', [$startDate, $endDate])->get();
                        $technicians = Technician::whereIn('branch_id', $values)->get();
                        break;

                    case 'service_type':
                        $orders = Order::whereIn(
                            'id',
                            OrderService::whereIn(
                                'service_id',
                                Service::whereIn('service_type_id', $values)->get()->pluck('id')
                            )->get()->pluck('order_id')
                        )->whereBetween('programmed_date', [$startDate, $endDate])->get();
                        $technicians = Technician::whereIn(
                            'id',
                            OrderTechnician::whereIn('order_id', $orders->pluck('id'))->get()->pluck('technician_id')
                        )->get();
                        break;

                    default:
                        $orders = [];
                        $technicians = [];
                        break;
                }
            }
            if ($orders) {
                $daily_program = $this->getPlanningData($orders);
            }

            return response()->json([
                'daily_program' => $daily_program,
                'technicians' => $technicians->map(function ($technician) {
                    return [
                        'id' => $technician->id,
                        'name' => $technician->user->name,
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            // Capturar cualquier excepción y manejarla
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function clients()
    {
        return view('clients.index');
    }

    public function updateSchedule(Request $request)
    {
        try {
            $data = $request->all();

            /*if (isset($data['date']) && isset($data['orderId'])) {
            $dateTimeString = $data['date'];

            // Extraer la fecha y hora
            $dateString = substr($dateTimeString, 4, 11); // 'Jul 04 2024'
            $timeString = substr($dateTimeString, 16, 8); // '12:00:00'
            $date = Carbon::createFromFormat('M d Y H:i:s', $dateString . ' ' . $timeString);

            $programmed_date = $date->format('Y-m-d'); // Formato Y-m-d para almacenar en base de datos
            $start_time = $date->format('H:i:s');

            // Encontrar la orden por ID
            $order = Order::find($data['orderId']);

            if ($order) {
                // Actualizar los campos programados en la orden
                $order->programmed_date = $programmed_date;
                $order->start_time = $start_time;
                $order->save();

                return response()->json(['message' => 'Save'], 200);
            } else {
                return response()->json(['message' => 'Order not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Invalid data provided'], 400);
        }*/

            if (isset($data['technicianId']) && isset($data['orderId'])) {
                $order = Order::find($data['orderId']);
                $order->start_time = Carbon::createFromTime($data['hour'], 0, 0);
                $order->save();

                OrderTechnician::updateOrCreate(
                    ['order_id' => $order->id],
                    ['technician_id' => $data['technicianId']]
                );
            }

            return response()->json(200);
        } catch (\Exception $e) {
            // Capturar cualquier excepción y manejarla
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    public function getOrdersByCustomer(Request $request)
    {
        $orders = [];

        if (!empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $customerIDs = Customer::where('name', 'LIKE', $searchTerm)->pluck('id');
            $orders = Order::whereIn('customer_id', $customerIDs)->pluck('id');
        }

        return response()->json(['orders' => $orders]);
    }

    public function orders(string $va, $page)
    {
        $customers = Customer::all();
        $order_services = OrderService::all();
        $services = Service::all();
        $status = OrderStatus::all();
        if ($va == 1) {
            //ordenes de servicio terminadas
            $orders = Order::where('status_id', 3)->get();
        } elseif ($va == 2) {
            //ordenes de servicio canceladas
            $orders = Order::where('status_id', 6)->get();
        } elseif ($va == 3) {
            $orders = Order::whereNotIn('status_id', [3, 6])->get();
        }

        return view('dashboard.tables.order', compact('customers', 'services', 'orders', 'order_services', 'va'));
    }

    public function trackingIndex(string $va, $page)
    {
        $customers = null;
        $size = 20;
        //clientes registrados 6 u 1 año antes a la fecha actual
        $fechaActual = Carbon::now();
        $haceUnAnio = $fechaActual->copy()->subYear();
        $haceSeisMeses = $fechaActual->copy()->subMonths(6);
        if ($va == 1) {
            $primerDiaMesActual = now()->startOfMonth();
            $ultimoDiaMesActual = now()->endOfMonth();

            $cust_ids = Customer::whereBetween('created_at', [$primerDiaMesActual, $ultimoDiaMesActual])
                ->whereNotNull('general_sedes')
                ->where('general_sedes', '!=', 0)
                ->pluck('id')
                ->toArray();

            $customers_withcontract = Contract::pluck('customer_id')->toArray();

            $customers_ids = array_diff($cust_ids, $customers_withcontract);
            if ($customers_ids) {
                $customers = Customer::whereIn('id', $customers_ids)->get();
            }
        } elseif ($va == 2) {
            $customers = Customer::where(function ($query) {
                $campos = Schema::getColumnListing((new Customer())->getTable());
                foreach ($campos as $campo) {
                    $query->orWhereNull($campo);
                }
            })->where(function ($query) {
                $query->whereNotNull('general_sedes')
                    ->where('general_sedes', '!=', 0);
            })->get();
        } else {
            $primerDiaMesActual = now()->startOfMonth(); // Primer día del mes actual
            $ultimoDiaMesActual = now()->endOfMonth();
            $cust_ids = Lead::whereBetween('created_at', [$primerDiaMesActual, $ultimoDiaMesActual])
                ->pluck('id')
                ->toArray();

            $customers = Lead::whereIn('id', $cust_ids)->get();
        }
        return view('dashboard.tables.customer', compact('customers', 'va'));
    }

    public function stock()
    {
        return view('dashboard.stock..index');
    }

    public static function log($type, $change, $sql)
    {
        DatabaseLog::insert([
            'user_id' => auth()->user()->id,
            'changetype' => $type,
            'change' => is_array($change) ? json_encode($change) : $change,
            'sql_command' => $sql,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
