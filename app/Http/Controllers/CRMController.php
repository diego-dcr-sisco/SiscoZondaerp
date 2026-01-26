<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderService;
use App\Models\OrderStatus;
use App\Models\Quote;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Tracking;
use Carbon\Carbon;
use function Laravel\Prompts\select;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class CRMController extends Controller
{
    protected $navigation;
    protected $size = 100;
    private $contact_medium = [
        'whatsapp' => 'WhatsApp',
        'sms' => 'Mensaje SMS',
        'call' => 'Llamada telefónica',
        'email' => 'Correo electrónico',
        'flyer' => 'Volanteo físico',
    ];

    private $search_urls = [
        'history' => 'tracking.history',
        'customers' => 'tracking.table',
        'agenda' => 'crm.agenda.index',
    ];

    private function generateChartColors(int $count, string $type = 'random', float $opacity = 0.8): array
    {
        $colors = [];
        $baseColors = [
            'blue' => [
                'base' => [21, 67, 96],
                'variations' => 5,
            ],
            'red' => [
                'base' => [148, 49, 38],
                'variations' => 5,
            ],
            'yellow' => [
                'base' => [255, 206, 86],
                'variations' => 5,
            ],
            'green' => [
                'base' => [75, 192, 192],
                'variations' => 5,
            ],
            'purple' => [
                'base' => [153, 102, 255],
                'variations' => 5,
            ],
            'orange' => [
                'base' => [255, 159, 64],
                'variations' => 5,
            ],
            'random' => [
                'base' => null,
                'variations' => 0,
            ],
        ];

        // Si el tipo no existe, usar random
        if (!array_key_exists($type, $baseColors)) {
            $type = 'random';
        }

        for ($i = 0; $i < $count; $i++) {
            if ($type === 'random') {
                // Generar color completamente aleatorio
                $r = mt_rand(0, 255);
                $g = mt_rand(0, 255);
                $b = mt_rand(0, 255);
            } else {
                // Generar variaciones del color base
                $base = $baseColors[$type]['base'];
                $variationFactor = $i % $baseColors[$type]['variations'] * 5;

                $r = max(0, min(255, $base[0] + mt_rand(-30, 30) + $variationFactor * 10));
                $g = max(0, min(255, $base[1] + mt_rand(-30, 30) + $variationFactor * 15));
                $b = max(0, min(255, $base[2] + mt_rand(-30, 30) + $variationFactor * 20));
            }

            $colors[] = sprintf('rgba(%d, %d, %d, %.2f)', $r, $g, $b, $opacity);
        }

        return $colors;
    }

    private function makeAgenda($orders)
    {
        $calendar_data = [];

        foreach ($orders as $order) {
            $programmed_date = is_string($order->programmed_date)
                ? Carbon::parse($order->programmed_date)
                : $order->programmed_date;

            $calendar_data[] = [
                'type' => 'order',
                'id' => $order->id,
                'title' => 'Orden #' . $order->id . ' - ' . ($order->customer->name ?? 'Cliente no disponible'),
                'start' => $programmed_date->toIso8601String(),
                'end' => $programmed_date->copy()->addHours(2)->toIso8601String(),
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

        return $calendar_data;
    }

    public function __construct()
    {
        $this->navigation = [
            'Agenda' => route('crm.agenda'),
            'Clientes' => route('customer.index'),
            'Sedes' => route('customer.index.sedes'),
            'Clientes potenciales' => Route('customer.index.leads'),
            'Estadisticas' => route('crm.chart.dashboard'),
            'Ordenes de servicio' => route('order.index'),
        ];
    }

    // s1
    public function index()
    {
        $navigation = $this->navigation;
        return view('crm.index', compact('navigation'));
    }

    public function agenda(Request $request)
{
    $data = $request->all();

    // Verificar si hay filtros aplicados (excluyendo paginación/ordenación)
    $hasFilters = $this->hasAppliedFilters($request);

    $calendar_data = [];
    $navigation = $this->navigation;
    $initial_date = Carbon::today()->firstOfMonth()->format('Y-m-d');

    // Si no hay filtros aplicados, retornar vista vacía o con datos por defecto
    if (!$hasFilters) {
        return view('crm.agenda.calendar', [
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

    return view('crm.agenda.calendar', [
        'calendar_events' => json_encode($calendar_data),
        'order_status' => OrderStatus::all(),
        'navigation' => $navigation,
        'nav' => 'c',
        'hasFilters' => true, // Puedes usar esto en tu vista,
        'initial_date' => $initial_date,
    ]);
}

/**
 * Verifica si la request tiene filtros aplicados
 * Excluye campos de paginación y ordenación
 */
private function hasAppliedFilters(Request $request)
{
    $filterableFields = [
        'folio', 'customer', 'service', 'date_range', 
        'time', 'status', 'order_type', 'signature_status'
    ];
    
    foreach ($filterableFields as $field) {
        if ($request->filled($field)) {
            return true;
        }
    }
    
    return false;
}

    public function tracking(Request $request)
    {
        $tracking_query = Tracking::query();

        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        if ($request->filled('trackable')) {
            $searchTerm = '%' . $request->trackable . '%';

            $tracking_query->where(function ($query) use ($searchTerm) {
                $query->whereHasMorph('trackable', [Customer::class], function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                })
                    ->orWhereHasMorph('trackable', [Lead::class], function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Filtro por rango de fechas
        if ($request->filled('date-range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date-range')));

            $tracking_query->whereBetween('next_date', [$startDate, $endDate]);
        }

        // Filtro por servicio
        if ($request->filled('service')) {
            $tracking_query->whereHas('service', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->service . '%');
            });
        }

        $trackings = $tracking_query->orderByRaw("FIELD(status, '" . implode("','", ['active', 'completed', 'canceled']) . "')")
            ->paginate($this->size)
            ->appends($request->all());

        $navigation = $this->navigation;

        return view('crm.agenda.tracking', [
            'trackings' => $trackings,
            'order_status' => OrderStatus::all(),
            'navigation' => $navigation,
            'nav' => 't',
        ]);
    }

    public function quotation(Request $request)
    {
        $quotes_query = Quote::query();

        // Búsqueda por trackable (customer o lead)
        if ($request->filled('trackable')) {
            $searchTerm = '%' . $request->trackable . '%';

            $quotes_query->where(function ($query) use ($searchTerm) {
                $query->whereHasMorph('model', [Customer::class], function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                })
                    ->orWhereHasMorph('model', [Lead::class], function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Filtro por rango de fechas
        if ($request->filled('date-range')) {
            [$startDate, $endDate] = array_map(function ($d) {
                return Carbon::createFromFormat('d/m/Y', trim($d));
            }, explode(' - ', $request->input('date-range')));

            $quotes_query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filtro por servicio
        if ($request->filled('service')) {
            $quotes_query->whereHas('service', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->service . '%');
            });
        }

        $navigation = $this->navigation;

        $quotes = $quotes_query->orderBy('created_at', 'desc')
            ->paginate($this->size)
            ->appends($request->all());

        return view('crm.agenda.quotation', [
            'order_status' => OrderStatus::all(),
            'quotes' => $quotes,
            'navigation' => $navigation,
            'nav' => 'q',
        ]);
    }

    // Función auxiliar para determinar colores según estado
    private function getOrderColor($status)
    {
        $colors = [
            '#B71C1C',
            '#1B5E20',
            '#1A237E'
        ];

        return $colors[$status] ?? '#6B7280'; // Gris por defecto
    }

    // Funcion editada para quitar un error de routing
    // Se quito el tracking.create y se cambio por invoices.index
    // 2/08/2025

    public function servicesByCustomer(string $customerId)
    {
        $customer_id = $customerId;
        $data_charts = [];

        $customer = Customer::find($customer_id);
        $services = OrderService::whereHas('order', function ($query) use ($customer_id) {
            $query->where('customer_id', $customer_id);
        })
            ->with('service')
            ->get()
            ->groupBy('service_id')
            ->map(function ($group) {
                return [
                    'id' => $group->first()->service->id,
                    'name' => $group->first()->service->name,
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        $colors = $this->generateChartColors(count($services), 'blue');

        $data_charts['services'] = [
            'labels' => array_column($services, 'name'),
            'datasets' => [
                [
                    'label' => 'Servicios',
                    'data' => array_column($services, 'count'),
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
        ];

        $service_ids = array_column($services, 'id');
        $services_count = array_fill(0, count($service_ids), 0);
        foreach ($service_ids as $index => $service_id) {
            $services_count[$index] = Tracking::where('trackable_id', $customerId)->where('service_id', $service_id)->count();
        }

        $data_charts['trackingByServices'] = [
            'labels' => array_column($services, 'name'),
            'datasets' => [
                [
                    'label' => 'Trackings por mes',
                    'data' => $services_count,
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
        ];

        $trackings = Tracking::where('trackable_id', $customerId)->whereIn('service_id', $service_ids)->get();
        $monthly_counts = array_fill(0, 12, 0);
        foreach ($trackings as $tracking) {
            if ($tracking->next_date) {
                $month = Carbon::parse($tracking->next_date)->month - 1; // 0-11
                $monthly_counts[$month]++;
            }
        }

        $data_charts['trackingByMonths'] = [
            'labels' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            'datasets' => [
                [
                    'label' => 'Seguimientos por mes',
                    'data' => $monthly_counts,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.5,
                ],
            ],
        ];
        return view('dashboard.crm.tracking.services', compact('services', 'data_charts', 'trackings', 'customer'));
    }

    public function trackingByCustomer(Request $request)
    {
        $tracking_data = [];
        $tracking_urls = [];
        $customer_id = $request->input('customer_id');
        $service_ids = OrderService::whereHas('order', fn($q) => $q->where('customer_id', $customer_id))
            ->pluck('service_id')
            ->unique()
            ->values()
            ->toArray();

        foreach ($service_ids as $service_id) {
            $tracking_urls[$service_id] = Route('crm.tracking.create', ['customerId' => $customer_id, 'serviceId' => $service_id]);
            $trackings = Tracking::where('trackable_id', $customer_id)
                ->where('trackable_type', 'App\Models\Customer')
                ->where('service_id', $service_id)
                ->get();

            if (!$trackings->isEmpty()) {
                foreach ($trackings as $tracking) {
                    $tracking_data[$service_id]['data'][] = [
                        'id' => $tracking->id ?? null,
                        'title' => $tracking->title ?? null,
                        'description' => $tracking->description ?? null,
                        'next_date' => $tracking->next_date ?? null,
                        'range' => $tracking->range ?? null,
                        'status' => $tracking->status ?? null,
                        'updated_at' => $tracking->updated_at ?? null,
                    ];

                    if ($tracking->id) {
                        $tracking_data[$service_id][$tracking->id] = [
                            'url_auto' => Route('crm.tracking.auto', ['id' => $tracking->id]),
                            'url_complete' => Route('crm.tracking.complete', ['id' => $tracking->id]),
                            'url_edit' => Route('crm.tracking.edit', ['id' => $tracking->id]),
                            'url_destroy' => Route('crm.tracking.destroy', ['id' => $tracking->id]),
                        ];
                    }
                }
            } else {
                $tracking_data[$service_id] = [];
            }
        }

        $services = Service::whereIn('id', $service_ids)->select('id', 'name')->get();

        return response()->json([
            'customer' => Customer::find($customer_id),
            'services' => $services,
            'trackings' => $tracking_data,
            'urls' => $tracking_urls,
        ]);
    }

    public function createTracking(string $customerId, string $serviceId)
    {
        $orders_data = $service_ids = [];

        $customer = Customer::find($customerId);
        $service = Service::find($serviceId);
        $leads = Lead::select('id', 'name')->get();

        if ($customer) {
            $service_ids = OrderService::whereHas('order', fn($q) => $q->where('customer_id', $customer->id))
                ->pluck('service_id')
                ->unique()
                ->values()
                ->toArray();
        }
        $services = !empty($service_ids) ? Service::whereIn('id', $service_ids)->select('id', 'name')->get() : Service::select('id', 'name')->get();
        $customers = Customer::where('general_sedes', '!=', 0)->get();

        foreach ($service_ids as $service_id) {
            $orders = Order::where('customer_id', $customer->id)
                ->whereHas('services', fn($q) => $q->where('service_id', $service_id))
                ->with('status')
                ->orderBy('programmed_date', 'desc')
                ->get();

            foreach ($orders as $order) {
                $orders_data[$service_id][] = [
                    'id' => $order->id,
                    'status' => $order->status_id,
                    'date' => $order->programmed_date,
                ];
            }
        }

        return view('dashboard.crm.tracking.create', compact('customer', 'customers', 'service', 'services', 'orders_data', 'leads'));
    }

    public function createTrackingOrder(string $order_id)
    {

    }

    public function storeTracking(Request $request)
    {
        $tracking_type = $request->input('tracking_type');
        $trackable_type = $request->input('trackable_type'); // 'customer' o 'lead'
        $trackable_id = $request->input('trackable_id');

        $data = [
            'trackable_id' => $trackable_id,
            'trackable_type' => $trackable_type === 'customer'
                ? 'App\Models\Customer'
                : 'App\Models\Lead',
            'service_id' => $request->input('service_id'),
            'order_id' => $request->input('order_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'next_date' => $request->input('next_date'),
        ];

        if ($request->has('frequency_type')) {
            $range = [
                'frequency' => $request->input('frequency'),
                'frequency_type' => $request->input('frequency_type'),
            ];
            $data['range'] = json_encode($range);

            if ($tracking_type == 'year') {
                $mainTracking = Tracking::create($data);
                $this->generateFutureTrackingDates($mainTracking);
            } else {
                Tracking::create($data);
            }
        } else {
            Tracking::create($data);
        }

        return redirect()->route('crm.tracking.history')->with('success', 'Seguimiento creado exitosamente');
    }

    protected function generateFutureTrackingDates(Tracking $mainTracking)
    {
        $range = json_decode($mainTracking->range, true);
        $frequency = (int) $range['frequency'];
        $frequencyType = $range['frequency_type'];
        $startDate = Carbon::parse($mainTracking->next_date);
        $endDate = $startDate->copy()->addYear()->subDay();

        $currentDate = $startDate->copy();
        $datesToCreate = [];

        while ($currentDate->lte($endDate)) {
            switch ($frequencyType) {
                case 'days':
                    $currentDate->addDays($frequency);
                    break;
                case 'weeks':
                    $currentDate->addWeeks($frequency);
                    break;
                case 'months':
                    $currentDate->addMonths($frequency);
                    break;
            }

            if ($currentDate->lte($endDate)) {
                $datesToCreate[] = [
                    'trackable_id' => $mainTracking->trackable_id,
                    'trackable_type' => $mainTracking->trackable_type,
                    'service_id' => $mainTracking->service_id,
                    'order_id' => $mainTracking->order_id,
                    'title' => $mainTracking->title,
                    'description' => $mainTracking->description,
                    'next_date' => $currentDate->format('Y-m-d'),
                    'range' => $mainTracking->range,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($datesToCreate)) {
            Tracking::insert($datesToCreate);
        }
    }

    public function editTracking(string $trackingId)
    {
        $tracking = Tracking::find($trackingId);
        $order = Order::find($tracking->order_id);
        $services = Service::whereIn('id', $order->services->pluck('service_id'))->select('id', 'name')->get();

        return view('tracking.edit', compact('tracking', 'services'));
    }

    public function updateTracking(Request $request, string $id)
    {
        $tracking = Tracking::findOrFail($id);
        $tracking->update($request->all());

        return back();
    }

    public function searchTracking(Request $request)
    {
        $customers = $trackings = null;
        $view = $request->view ?? 'trackings'; // Valor por defecto
        $url = $this->search_urls[$view];

        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();
        $calendar_events = json_encode($this->makeAgenda($startDate, $endDate));

        //dd($request->all());

        if ($view == 'customers') {
            // Consulta directa para customers
            $query = Customer::where(function ($q) {
                $q->where('general_sedes', '!=', 0)
                    ->orWhere(function ($subQ) {
                        $subQ->where('general_sedes', 0)
                            ->where('service_type_id', 1);
                    });
            });

            // Filtros adicionales (name y service_type_id)
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('service_type_id')) {
                $query->where('service_type_id', $request->service_type_id);
            }

            if ($request->filled('date')) {
                try {
                    [$fs_date, $fe_date] = array_map(function ($d) {
                        return Carbon::createFromFormat('d/m/Y', trim($d));
                    }, explode(' - ', $request->date));

                    $trackings = Tracking::whereBetween('next_date', [
                        $fs_date->format('Y-m-d'),
                        $fe_date->format('Y-m-d'),
                    ]);
                    $query->whereIn('id', $trackings->pluck('trackable_id'));
                } catch (\Exception $e) {
                    return back()->with('error', 'Formato de fecha inválido');
                }
            }

            $customers = $query->orderBy('name')->paginate($this->size);
        } else {
            // Consulta para Trackings con filtro por tipo (Customer/Lead)
            $query = Tracking::with(['trackable'])
                ->where(function ($q) {
                    $q->where('trackable_type', Customer::class)
                        ->whereHasMorph('trackable', [Customer::class], function ($subQ) {
                            $subQ->where('general_sedes', '!=', 0);
                        });

                    $q->orWhere('trackable_type', Lead::class);
                });

            // Búsqueda por nombre (tanto para Customer como Lead)
            if ($request->filled('name')) {
                $query->where(function ($q) use ($request) {
                    $q->whereHasMorph('trackable', [Customer::class], function ($subQ) use ($request) {
                        $subQ->where('name', 'like', '%' . $request->name . '%');
                    })
                        ->orWhereHasMorph('trackable', [Lead::class], function ($subQ) use ($request) {
                            $subQ->where('name', 'like', '%' . $request->name . '%');
                        });
                });
            }

            // Búsqueda por service_type (solo aplica a Customers)
            if ($request->filled('service_type_id')) {
                $query->whereHasMorph('trackable', [Customer::class], function ($q) use ($request) {
                    $q->where('service_type_id', $request->service_type_id);
                });
            }

            // Filtro por fecha para trackings
            if ($request->filled('date')) {
                try {
                    [$startDate, $endDate] = array_map(function ($d) {
                        return Carbon::createFromFormat('d/m/Y', trim($d));
                    }, explode(' - ', $request->date));

                    $query->whereBetween('next_date', [
                        $startDate->format('Y-m-d'),
                        $endDate->format('Y-m-d'),
                    ]);
                } catch (\Exception $e) {
                    return back()->with('error', 'Formato de fecha inválido');
                }
            }

            $trackings = $query->orderBy('next_date', 'desc')->paginate($this->size);
        }

        // Datos para la vista
        $contact_medium = $this->contact_medium;
        $serviceTypes = ServiceType::all();

        return view($url, compact(
            'customers',
            'trackings',
            'contact_medium',
            'serviceTypes',
            'calendar_events',
            'view'
        ));
    }

    public function destroyTracking(string $id)
    {
        $tracking = Tracking::find($id);
        $tracking->delete();

        return back();
    }

    public function cancelTracking(string $id)
    {
        $tracking = Tracking::find($id);
        $tracking->update(['status' => 'canceled']);
        return back();
    }

    public function autoTracking(string $id)
    {
        $tracking = Tracking::find($id);
        $range = json_decode($tracking->range);
        $new_date = Carbon::parse($tracking->next_date)->add($range->frequency, $range->frequency_type);

        Tracking::create([
            'trackable_id' => $tracking->trackable_id,
            'trackable_type' => $tracking->trackable_type,
            'service_id' => $tracking->service_id,
            'order_id' => $tracking->order_id,
            'title' => null,
            'description' => null,
            'next_date' => $new_date,
            'range' => json_encode($range),
        ]);

        return back();
    }

    public function completeTracking(string $id)
    {
        $tracking = Tracking::find($id);
        $tracking->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('crm.tracking')
            ->with('success', 'El seguimiento ha sido actualizado exitosamente.');
    }

    public function historyTracking()
    {
        $trackings = Tracking::orderBy('next_date')->paginate($this->size);
        $service_types = ServiceType::all();
        return view('dashboard.crm.tracking.history', compact('trackings'));
    }

    public function getTrackings()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $trackings = Tracking::whereBetween('next_date', [$startOfWeek, $endOfWeek])
            ->where('status', '!=', 'canceled')
            ->orderBy('next_date')
            ->get();
        $data = [];

        foreach ($trackings as $tracking) {
            // Manejo seguro de la orden
            $orderInfo = null;
            if ($tracking->order_id && $tracking->order) {
                $orderInfo = [
                    'id' => $tracking->order_id,
                    'folio' => $tracking->order->folio ?? 'Sin folio',
                    'url' => route('order.edit', ['id' => $tracking->order_id]),
                ];
            }

            $data[] = [
                'id' => $tracking->id,
                'customer' => $tracking->trackable->name,
                'order' => $orderInfo, // Aquí usamos la estructura segura
                'service' => $tracking->service_id,
                'next_date' => $tracking->next_date,
                'title' => $tracking->title,
                'description' => $tracking->description,
                'status' => $tracking->status,
                'range' => $tracking->range,
                'auto_url' => route('crm.tracking.auto', ['id' => $tracking->id]),
                'edit_url' => route('crm.tracking.edit', ['id' => $tracking->id]),
                'cancel_url' => route('crm.tracking.cancel', ['id' => $tracking->id]),
                'destroy_url' => route('crm.tracking.destroy', ['id' => $tracking->id]),
            ];
        }

        return response()->json([
            'success' => true,
            'trackings' => $data,
            'count' => $trackings->count(),
        ]);
    }

    public function setTracking(Request $request)
    {
        $tracking_id = $request->input('tracking_id');
        $status = $request->input('status');

        $tracking = Tracking::find($tracking_id);

        if (!$tracking) {
        }

        $final_desc = 'Descripción: ' . $tracking->description . '\n' . 'Razon: ' . $request->input('reason');

        $tracking->update([
            'description' => $final_desc,
            'status' => $status,
        ]);

        return back();
    }

    // GRAFICA - Medio de contacto del customere
    public function contactMedium()
    {
        $data = $chartData = [];
        foreach ($this->contact_medium as $medium) {
            $data[] = Customer::where('contact_mediun', $medium)->count();
        }

        $colors = $this->generateChartColors(count($this->contact_medium), 'red');

        $chartData = [
            'labels' => $this->contact_medium,
            'datasets' => [
                [
                    'label' => 'Medio de contacto',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
        ];

        return view('crm.analytics');
    }
}
