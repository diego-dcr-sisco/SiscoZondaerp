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

    private $bootstrapColors = ['#ffc107', '#6610f2', '#0d6efd', '#0dcaf0', '#198754', '#dc3545'];

    private $size = 20;

    private $navigation;

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

    private function hasAppliedFilters(Request $request)
    {
        $filterableFields = [
            'folio',
            'customer',
            'service',
            'date_range',
            'time',
            'status',
            'order_type',
            'signature_status',
        ];

        foreach ($filterableFields as $field) {
            if ($request->filled($field)) {
                return true;
            }
        }

        return false;
    }

    private function getPlanningByTechnician($orders)
    {
        // Obtener todos los IDs de órdenes primero
        $order_ids = $orders->pluck('id');

        // Cargar todos los técnicos de una vez
        $all_technicians = OrderTechnician::whereIn('order_id', $order_ids)
            ->with('technician')
            ->get()
            ->groupBy('order_id');

        $data = [];
        $custom_color = []; // ← Movido fuera del loop de horas

        // Primero: recolectar todos los clientes únicos y asignar colores
        $all_customers = $orders->pluck('customer_id')->unique();
        foreach ($all_customers as $customer_id) {
            $custom_color[$customer_id] = $this->getRandomPastelColor();
        }

        foreach ($this->hrs_format as $format) {
            // Filtrar órdenes que empiecen con esta hora (ignorando segundos)
            $orders_for_hour = $orders->filter(function ($order) use ($format) {
                return substr($order->start_time, 0, 5) === $format;
            });

            $formatted_orders = $orders_for_hour->map(function ($order) use ($all_technicians, $custom_color) {
                $technicians = [];

                if (isset($all_technicians[$order->id])) {
                    $technicians = $all_technicians[$order->id]->map(function ($order_tech) {
                        return [
                            'id' => $order_tech->technician_id,
                            'name' => $order_tech->technician->name ?? 'Técnico no asignado',
                        ];
                    })->toArray();
                }

                $border_color = $this->bootstrapColors[$order->status_id - 1] ?? '#6c757d'; // Color por defecto

                // Usar el color ya asignado al cliente
                $bg_color = $custom_color[$order->customer_id] ?? $this->getRandomPastelColor();

                return [
                    'border_color' => $border_color,
                    'bg_color' => $bg_color,
                    'order' => $order,
                    'technicians' => $technicians
                ];
            });

            $data[$format] = [
                'orders' => $formatted_orders,
                'order_count' => $orders_for_hour->count(),
                'technician_count' => $formatted_orders->sum(function ($item) {
                    return count($item['technicians']);
                })
            ];
        }

        return $data;
    }

    private function getRandomPastelColor($format = 'hex')
    {
        // Colores pastel: valores entre 150-255 (más claros)
        $red = mt_rand(150, 255);
        $green = mt_rand(150, 255);
        $blue = mt_rand(150, 255);

        switch ($format) {
            case 'hex':
                return sprintf("#%02x%02x%02x", $red, $green, $blue);

            case 'rgb':
                return "rgb($red, $green, $blue)";

            case 'rgba':
                return "rgba($red, $green, $blue, 0.7)";

            case 'array':
                return ['r' => $red, 'g' => $green, 'b' => $blue];

            default:
                return sprintf("#%02x%02x%02x", $red, $green, $blue);
        }
    }

    public function __construct()
    {
        $this->navigation = [
            'Cronograma' => route('planning.schedule'),
            'Actividades' => route('planning.activities')
        ];
    }

    public function loadingERP()
    {
        session(['loading-erp' => true]);
        return view('loading-erp');
    }

    /*public function schedule(Request $request): View
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
    }*/

    public function schedule(Request $request)
    {
        $data = $request->all();

        // Verificar si hay filtros aplicados (excluyendo paginación/ordenación)
        $hasFilters = $this->hasAppliedFilters($request);

        $calendar_data = [];
        $navigation = $this->navigation;
        $initial_date = Carbon::today()->firstOfMonth()->format('Y-m-d');

        // Si no hay filtros aplicados, retornar vista vacía o con datos por defecto
        if (!$hasFilters) {
            return view('dashboard.planning.schedule', [
                'calendar_events' => json_encode($calendar_data),
                'order_status' => OrderStatus::all(),
                'navigation' => $navigation,
                'nav' => 'c',
                'hasFilters' => false, // Puedes usar esto en tu vista
                'initial_date' => $initial_date,
            ]);
        }

        // Obtener parámetros de ordenamiento
        $size = $request->input('size');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'DESC');

        // Construir consulta base
        $query = Order::query();

        // Aplicar filtros (mantén tus filtros existentes)
        if ($request->filled('folio')) {
            $query->where('folio', 'like', '%' . $request->input('folio') . '%');
        }

        if ($request->filled('customer')) {
            $searchTerm = '%' . $request->input('customer') . '%';
            $customerIds = Customer::where('name', 'LIKE', $searchTerm)->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        if ($request->filled('status')) {
            $query->where('status_id', $request->input('status'));
        }

        if ($request->filled('service')) {
            $serviceName = '%' . $request->input('service') . '%';
            $serviceIds = Service::where('name', 'LIKE', $serviceName)->pluck('id');
            $orderIds = OrderService::whereIn('service_id', $serviceIds)->pluck('order_id');
            $query->whereIn('id', $orderIds);
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query->whereBetween('programmed_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ]);

            $initial_date = Carbon::parse($startDate)->firstOfMonth()->format('Y-m-d');
        }

        if ($request->filled('time')) {
            $query->whereTime('start_time', $request->input('time'));
        }

        if ($request->filled('order_type')) {
            if ($request->input('order_type') == 'MIP') {
                $query->where('contract_id', '>', 0);
            } else {
                $query->whereNull('contract_id');
            }
        }

        if ($request->filled('signature_status')) {
            if ($request->input('signature_status') == 'signed') {
                $query->whereNotNull('customer_signature');
            } elseif ($request->input('signature_status') == 'unsigned') {
                $query->whereNull('customer_signature');
            }
        }

        // Aplicar ordenamiento después de los filtros
        $query->orderBy($sort, $direction);
        $size = $size ?? $this->size;

        // Paginar resultados
        $orders = $query->get();
        $calendar_data = $this->makeAgenda($orders);

        return view('dashboard.planning.schedule', [
            'calendar_events' => json_encode($calendar_data),
            'order_status' => OrderStatus::all(),
            'navigation' => $navigation,
            'nav' => 'c',
            'hasFilters' => true, // Puedes usar esto en tu vista,
            'initial_date' => $initial_date,
        ]);
    }

    private function makeAgenda($orders)
    {
        $calendar_data = [];

        foreach ($orders as $order) {
            $programmed_date = is_string($order->programmed_date)
                ? Carbon::parse($order->programmed_date)
                : $order->programmed_date;

            $programmed_date = substr($programmed_date, 0, 10);

            $calendar_data[] = [
                'type' => 'order',
                'id' => $order->id,
                'title' => 'Orden #' . $order->id . ' - ' . ($order->customer->name ?? 'Cliente no disponible'),
                'start' => $programmed_date . 'T' . $order->start_time,
                'end' => $programmed_date,
                'color' => $this->getOrderColor(($order->customer->service_type_id - 1)),
                'extendedProps' => [
                    'type' => 'order',
                    'customer' => $order->customer->name ?? 'Cliente no disponible',
                    'products' => $order->products->pluck('name')->implode(', ') ?? null,
                    'services' => $order->services->pluck('name')->implode(', ') ?? null,
                    'technicians' => $order->getNameTechnicians()->pluck('name')->implode(', ') ?? null,
                    'status' => $order->status->name ?? '-',
                    'date' => Carbon::parse($order->programmed_date)->format('d-m-y'),
                    'time' => Carbon::parse($order->start_time)->format('H:i') . ' - ' . Carbon::parse($order->end_time)->format('H:i'),
                    'edit_url' => route('order.edit', ['id' => $order->id]),
                    'report_url' => route('report.review', ['id' => $order->id]),
                ],
            ];
        }

        //dd($calendar_data);
        return $calendar_data;
    }

    private function getOrderColor($status)
    {
        $colors = [
            '#B71C1C',
            '#1B5E20',
            '#1A237E'
        ];
        return $colors[$status] ?? '#6B7280'; // Gris por defecto
    }

    public function activities(Request $request)
    {
        $data = $request->all();

        // Verificar si hay filtros aplicados (excluyendo paginación/ordenación)
        $hasFilters = $this->hasAppliedFilters($request);

        $planning_data = [];
        $navigation = $this->navigation;
        $initial_date = Carbon::today()->firstOfMonth()->format('Y-m-d');
        $order_status = OrderStatus::all();
        $branches = Branch::all();

        /*$technicians = Technician::all()->map(function ($tech) {
return [
'id' => $tech->id,
'name' => $tech->user->name,
];
});*/

        $technicians = Technician::query()->with('user');
        if ($request->filled('branch')) {
            $technicians->where('branch_id', $request->input('branch'));
        }
        $technicians = $technicians->get()->map(function ($tech) {
            return [
                'id' => $tech->id,
                'name' => $tech->user->name, // Ahora user está cargado eficientemente
            ];
        });

        $hours = $this->hrs_format;

        // Si no hay filtros aplicados, retornar vista vacía o con datos por defecto
        if (!$hasFilters) {
            return view('dashboard.planning.activities', [
                'planning_data' => $planning_data,
                'order_status' => $order_status,
                'navigation' => $navigation,
                'technicians' => $technicians,
                'hours' => $hours,
                'branches' => $branches,
            ]);
        }

        // Obtener parámetros de ordenamiento
        $size = $request->input('size');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'DESC');

        // Construir consulta base
        $query = Order::query();

        // Aplicar filtros (mantén tus filtros existentes)
        if ($request->filled('folio')) {
            $query->where('folio', 'like', '%' . $request->input('folio') . '%');
        }

        if ($request->filled('customer')) {
            $searchTerm = '%' . $request->input('customer') . '%';
            $customerIds = Customer::where('name', 'LIKE', $searchTerm)->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        if ($request->filled('status')) {
            $query->where('status_id', $request->input('status'));
        }

        if ($request->filled('service')) {
            $serviceName = '%' . $request->input('service') . '%';
            $serviceIds = Service::where('name', 'LIKE', $serviceName)->pluck('id');
            $orderIds = OrderService::whereIn('service_id', $serviceIds)->pluck('order_id');
            $query->whereIn('id', $orderIds);
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date_range')));

            $query->whereBetween('programmed_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ]);

            $initial_date = Carbon::parse($startDate)->firstOfMonth()->format('Y-m-d');
        }

        if ($request->filled('time')) {
            $query->whereTime('start_time', $request->input('time'));
        }

        if ($request->filled('order_type')) {
            if ($request->input('order_type') == 'MIP') {
                $query->where('contract_id', '>', 0);
            } else {
                $query->whereNull('contract_id');
            }
        }

        if ($request->filled('signature_status')) {
            if ($request->input('signature_status') == 'signed') {
                $query->whereNotNull('customer_signature');
            } elseif ($request->input('signature_status') == 'unsigned') {
                $query->whereNull('customer_signature');
            }
        }

        // Aplicar ordenamiento después de los filtros
        $query->orderBy($sort, $direction);
        $size = $size ?? $this->size;

        // Paginar resultados
        $orders = $query->whereIn('start_time', $this->hrs_format)
            ->with('techniciansScope')
            ->get();

        //dd($orders);
        $planning_data = $this->getPlanningByTechnician($orders);

        //dd($planning_data, $technicians, $hour_formats);

        return view(
            'dashboard.planning.activities',
            compact('navigation', 'order_status', 'planning_data', 'technicians', 'hours', 'branches')
        );
    }

    public function updateOrder(Request $request)
    {
        try {
            $order = Order::findOrFail($request->order_id);
            $tech_exist = OrderTechnician::where('order_id', $order->id)->where('technician_id', $request->technician_id)->exists();

            if ($tech_exist) {
                OrderTechnician::where('order_id', $order->id)->whereNot('technician_id', $request->technician_id)->delete();
            } else {
                OrderTechnician::where('order_id', $order->id)->delete();
                OrderTechnician::create(['order_id' => $order->id, 'technician_id' => $request->technician_id]);
            }

            $order->update([
                'start_time' => $request->hour,
                //'end_time' => $request->end_time,'
            ]);

            return response()->json(['success' => true, 'exist' => $tech_exist]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
                    'customer' => $tracking->trackable->name ?? '-',
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

            return view('dashboard.index', compact('trackings_data', 'services', 'count_trackings'));
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

    public function updateEventDate(Request $request)
    {
        try {
            $eventId = $request->input('event_id');
            $startDate = $request->input('start_date');
            //$startTime = $request->input('start_time');

            // Buscar y actualizar el evento en tu base de datos
            $order = Order::find($eventId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento no encontrado'
                ]);
            }

            // Actualizar fecha y hora
            $order->programmed_date = $startDate;
            //$event->scheduled_time = $startTime;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Evento actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateEventDuration(Request $request)
    {
        try {
            $eventId = $request->input('event_id');
            $startDate = $request->input('start_date');
            $startTime = $request->input('start_time');
            $endDate = $request->input('end_date');
            $endTime = $request->input('end_time');

            // Buscar y actualizar el evento
            $event = YourEventModel::find($eventId);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento no encontrado'
                ]);
            }

            // Actualizar fechas y horas
            $event->scheduled_date = $startDate;
            $event->scheduled_time = $startTime;
            $event->end_date = $endDate;
            $event->end_time = $endTime;
            $event->save();

            return response()->json([
                'success' => true,
                'message' => 'Duración actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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
