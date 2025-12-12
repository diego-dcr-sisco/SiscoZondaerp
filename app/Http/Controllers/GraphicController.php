<?php
namespace App\Http\Controllers;

use App\Charts\MonthlyLeadsChart;
use App\Charts\SampleChart;
use App\Charts\TotalCustomersChart;
use App\Models\Administrative;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\MovementProduct;
use App\Models\MovementType;
use App\Models\Order;
use App\Models\ProductCatalog;
use App\Models\Warehouse;
use App\Models\WarehouseLot;
use App\Models\WarehouseMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GraphicController extends Controller
{
    private $colors = [
        'PrussianBlue'   => '#264653',
        'Charcoal'       => '#2A9D8F',
        'Jasper'         => '#C3523E',
        'ResolutionBlue' => '#F4A261',
        'MarianBlue'     => '#E76F51',
        'PigmentGreen'   => '#4FA84D',
        'HarvestGold'    => '#F5AC23',
        'PersianRed'     => '#C63030',
    ];

    private $movement_colors = [
        "#33B5E5", // Devolucion (Azul-verde compuesto)
        "#6A3E98", // Recepcion (Morado-azul compuesto)
        "#00D1B2", // Transpaso entrada (Verde-azul compuesto)
        "#8B7B72", // Regularizacion entrada (Verde-morado compuesto)
        "#FF5733", // Deterioro (Naranja-rojo compuesto)
        "#FFB74D", // Robo (Amarillo-naranja compuesto)
        "#FF6F61", // Transpaso salida (Rojo-púrpura compuesto)
        "#A4C639", // Consumo (Amarillo-verde compuesto)
        "#FF8C00", // Regularizacion salida (Naranja-amarillo compuesto)
        "#FF1493", // Devolucion a proveedor (Rosa-rojo compuesto)
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
        'Diciembre',
    ];

    public function getMonths()
    {
        return $this->months;
    }

    public function index(Request $request)
    {
        $actualYear  = $request->input('year', Carbon::now()->year);
        $actualMonth = $request->input('month', Carbon::now()->month);

        // Graficas de clientes
        $anualCustomersChart  = $this->totalCustomersByYear($actualYear);
        $chart                = $this->newCustomers();                                       // Nuevos clientes por mes
        $categoryChart        = $this->customersByYear();                                    // Total de clientes por categoría
        $leadsChart           = $this->newLeadsByMonth($request, $actualYear, $actualMonth); // Leads captados en el mes
        $monthlyServicesChart = $this->monthlyServices();                                    // Tipos de servicios captados por mes

        // Graficas de calidad
        $adminUsers         = Administrative::all();
        $orderServicesChart = $this->serviceOrders(); // Ordenes de servicio por admin

        $navigation = [
            'Agenda'               => route('crm.agenda'),
            'Clientes'             => route('customer.index'),
            'Sedes'                => route('customer.index.sedes'),
            'Clientes potenciales' => Route('customer.index.leads'),
            'Estadisticas'         => route('crm.chart.dashboard'),
            'Ordenes de servicio'  => route('order.index'),
            'Facturacion'          => route('invoices.index'),
        ];

        return view('crm.charts.dashboard', compact(
            'chart',
            'categoryChart',
            'leadsChart',
            'monthlyServicesChart',
            'adminUsers',
            'orderServicesChart',
            'anualCustomersChart',
            'actualYear',
            'actualMonth',
            'navigation'
        ));
    }

    ////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// Grafica CLIENTES ///////////////////////////////////////////

    public function totalCustomersByYear($year = null)
    {
        $year          = $year ?? Carbon::now()->year; // Usa el año proporcionado o el año actual por defecto
        $monthlyTotals = [];

        for ($month = 1; $month <= 12; $month++) {
            $domestics[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 1)
                ->count();

            $comercials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 2)
                ->count();

            $industrials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 3)
                ->count();
        }

        $chart = new TotalCustomersChart;
        $chart->labels([
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
            'Diciembre',
        ]);

        $chart->dataset('Domésticos', 'line', $domestics)
            ->backgroundColor('rgba(3, 155, 229, 0.2)')
            ->color('#039BE5');

        $chart->dataset('Comerciales', 'line', $comercials)
            ->backgroundColor('rgba(26, 35, 126, 0.2)')
            ->color('#1A237E');

        $chart->dataset('Industrial/Planta', 'line', $industrials)
            ->backgroundColor('rgba(76, 175, 80, 0.2)')
            ->color('#4CAF50');

        return $chart;
    }

    public function newLeadsByMonth(Request $request, $year = null, $month = null)
    {
        $year  = $year ?? $request->input('year', Carbon::now()->year);    // Usa el año proporcionado o el año actual por defecto
        $month = $month ?? $request->input('month', Carbon::now()->month); // Usa el mes proporcionado o el mes actual por defecto

        $domestics = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 1)
            ->count();

        $comercials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 2)
            ->count();

        $industrials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 3)
            ->count();

        if ($request->ajax()) {
            return response()->json([
                'title'   => [
                    'text' => '',
                ],
                'tooltip' => [
                    'trigger' => 'axis',
                ],
                'xAxis'   => [
                    'type' => 'category',
                    'data' => ['Domésticos', 'Comerciales', 'Industrial/Planta'],
                ],
                'yAxis'   => [
                    'type' => 'value',
                ],
                'series'  => [
                    [
                        'name'      => 'Leads',
                        'type'      => 'bar',
                        'data'      => [$domestics, $comercials, $industrials],
                        'itemStyle' => [
                            'color' => ['red', 'blue', 'green'],
                        ],
                    ],
                ],
            ]);
        }

        // For non-AJAX requests, return a chart object
        $chart = new SampleChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);
        $chart->dataset('Leads', 'bar', [$domestics, $comercials, $industrials])
            ->backgroundColor(['red', 'blue', 'green'])
            ->color(['red', 'blue', 'green']);

        return $chart;
    }

    ////////////////////////////// Clientes por mes

    public function newCustomers()
    {
        $labels = ['Domesticos', 'Comerciales', 'Industrial/Planta'];
        $api    = route('crm.chart.customers');

        $chart = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function newCustomersDataset()
    {
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        // Filtrar los datos por mes y año
        $domestics = Customer::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 1)
            ->count();

        $comercials = Customer::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 2)
            ->count();

        $industrials = Customer::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 3)
            ->count();

        $counts = [$domestics, $comercials, $industrials];

        $chart = new SampleChart;
        $chart->dataset('Nuevos Clientes', 'bar', $counts)
            ->backgroundColor(['#039BE5', '#1A237E', '#4CAF50']) // Colores para cada barra
            ->color(['#039BE5', '#1A237E', '#4CAF50']);          // Bordes para cada barra

        return $chart->api();
    }

    public function refreshNewCustomers(Request $request)
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:' . Carbon::now()->year,
        ]);

        $domestics = Customer::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 1)
            ->count();

        $comercials = Customer::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 2)
            ->count();

        $industrials = Customer::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 3)
            ->count();

        $counts = [$domestics, $comercials, $industrials];

        $chart = new SampleChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);
        $chart->dataset('Nuevos Clientes', 'bar', $counts)
            ->backgroundColor(['#039BE5', '#1A237E', '#4CAF50']) // Colores para cada barra
            ->color(['#039BE5', '#1A237E', '#4CAF50']);          // Bordes para cada barra

        return $chart->api();
    }

    ////////////////////////////// Clientes por año

    public function customersByYear()
    {
        $labels = [
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
            'Diciembre',
        ];
        $api   = route('crm.chart.customersByYear');
        $chart = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function newCustomersByYear()
    {
        $year        = Carbon::now()->year;
        $domestics   = [];
        $comercials  = [];
        $industrials = [];

        for ($month = 1; $month <= 12; $month++) {
            $domestics[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 1)
                ->count();

            $comercials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 2)
                ->count();

            $industrials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 3)
                ->count();
        }

        $chart = new SampleChart;
        $chart->labels([
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
            'Diciembre',
        ]);

        $chart->dataset('Domésticos', 'line', $domestics)
            ->backgroundColor('rgba(3, 155, 229, 0.2)')
            ->color('#039BE5');

        $chart->dataset('Comerciales', 'line', $comercials)
            ->backgroundColor('rgba(26, 35, 126, 0.2)')
            ->color('#1A237E');

        $chart->dataset('Industrial/Planta', 'line', $industrials)
            ->backgroundColor('rgba(76, 175, 80, 0.2)')
            ->color('#4CAF50');

        return $chart->api();
    }

    public function refreshNewCustomersByYear(Request $request)
    {
        $year        = $request->input('year');
        $domestics   = [];
        $comercials  = [];
        $industrials = [];

        for ($month = 1; $month <= 12; $month++) {
            $domestics[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 1)
                ->count();

            $comercials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 2)
                ->count();

            $industrials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 3)
                ->count();
        }

        $chart = new SampleChart;
        $chart->labels([
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
            'Diciembre',
        ]);

        $chart->dataset('Domésticos', 'line', $domestics)
            ->backgroundColor('rgba(3, 155, 229, 0.2)')
            ->color('#039BE5');

        $chart->dataset('Comerciales', 'line', $comercials)
            ->backgroundColor('rgba(26, 35, 126, 0.2)')
            ->color('#1A237E');

        $chart->dataset('Industrial/Planta', 'line', $industrials)
            ->backgroundColor('rgba(76, 175, 80, 0.2)')
            ->color('#4CAF50');

        return $chart->api();
    }

    ////////////////////////////// Leads captados en el mes

    public function monthlyLeads()
    {
        $labels = ['Domesticos', 'Comerciales', 'Industrial/Planta'];
        $api    = route('crm.chart.monthlyLeads');
        $chart  = new MonthlyLeadsChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function leadsDataset()
    {
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        $domestics = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 1)
            ->count();

        $comercials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 2)
            ->count();

        $industrials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 3)
            ->count();

        $counts = [$domestics, $comercials, $industrials];

        $chart = new MonthlyLeadsChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);
        $chart->dataset('Leads', 'bar', $counts)
            ->backgroundColor(['rgba(3, 155, 229, 0.2), rgba(26, 35, 126, 0.2), rgba(76, 175, 80, 0.2)'])
            ->color(['#039BE5', '#1A237E', '#4CAF50']);

        return $chart->api();
    }

    public function refreshLeadsDataset(Request $request)
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        $domestics = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 1)
            ->count();

        $comercials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 2)
            ->count();

        $industrials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 3)
            ->count();

        $counts = [$domestics, $comercials, $industrials];

        $chart = new MonthlyLeadsChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);
        $chart->dataset('Leads', 'bar', $counts)
            ->backgroundColor(['rgba(3, 155, 229, 0.2), rgba(26, 35, 126, 0.2), rgba(76, 175, 80, 0.2)'])
            ->color(['#039BE5', '#1F237E', '#4CAF50']);

        return $chart->api();
    }

    public function leadsByServiceType(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year  = $request->input('year', Carbon::now()->year);

        $domestics = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 1)
            ->count();

        $comercials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 2)
            ->count();

        $industrials = Lead::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('service_type_id', 3)
            ->count();

        return response()->json([
            'title'   => [
                'text' => 'Leads por Tipo de Servicio',
            ],
            'tooltip' => [
                'trigger' => 'axis',
            ],
            'legend'  => [
                'data' => ['Domésticos', 'Comerciales', 'Industriales'],
            ],
            'xAxis'   => [
                'type' => 'category',
                'data' => ['Domésticos', 'Comerciales', 'Industriales'],
            ],
            'yAxis'   => [
                'type' => 'value',
            ],
            'series'  => [
                [
                    'name'      => 'Leads',
                    'type'      => 'bar',
                    'data'      => [$domestics, $comercials, $industrials],
                    'itemStyle' => [
                        'color' => function ($params) {
                            $colors = ['#039BE5', '#1A237E', '#4CAF50'];
                            return $colors[$params['dataIndex']];
                        },
                    ],
                ],
            ],
        ]);
    }

    ////////////////////////////// Servicios realizados en el mes

    public function monthlyServices()
    {
        $labels = ['Domesticos', 'Comerciales', 'Industrial/Planta'];
        $api    = route('crm.chart.monthlyServices');
        $chart  = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function monthlyServicesDataset()
    {
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        $domestics = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereHas('customer', function ($query) {
                $query->where('service_type_id', 1);
            })
            ->count();

        $comercials = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereHas('customer', function ($query) {
                $query->where('service_type_id', 2);
            })
            ->count();

        $industrials = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereHas('customer', function ($query) {
                $query->where('service_type_id', 3);
            })
            ->count();

        if (request()->ajax()) {
            return response()->json([
                'title'   => [
                    'text' => '',
                ],
                'tooltip' => [
                    'trigger' => 'axis',
                ],
                'xAxis'   => [
                    'type' => 'category',
                    'data' => ['Domésticos', 'Comerciales', 'Industrial/Planta'],
                ],
                'yAxis'   => [
                    'type' => 'value',
                ],
                'series'  => [
                    [
                        'name'      => 'Servicios',
                        'type'      => 'bar',
                        'data'      => [$domestics, $comercials, $industrials],
                        'itemStyle' => [
                            'color' => ['red', 'blue', 'green'],
                        ],
                    ],
                ],
            ]);
        }

        $chart = new SampleChart;
        $chart->labels(['Servicios']);

        $chart->dataset('Domésticos', 'bar', [$domestics])
            ->backgroundColor('red')
            ->color('red');

        $chart->dataset('Comerciales', 'bar', [$comercials])
            ->backgroundColor('blue')
            ->color('blue');

        $chart->dataset('Industrial/Planta', 'bar', [$industrials])
            ->backgroundColor('green')
            ->color('green');

        return $chart->api();
    }

    public function refreshMonthlyServices(Request $request)
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        $domestics = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereHas('customer', function ($query) {
                $query->where('service_type_id', 1);
            })
            ->count();

        $comercials = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereHas('customer', function ($query) {
                $query->where('service_type_id', 2);
            })
            ->count();

        $industrials = Order::whereMonth('programmed_date', $month)
            ->whereYear('programmed_date', $year)
            ->whereHas('customer', function ($query) {
                $query->where('service_type_id', 3);
            })
            ->count();

        return response()->json([
            'title'   => [
                'text' => '',
            ],
            'tooltip' => [
                'trigger' => 'axis',
            ],
            'xAxis'   => [
                'type' => 'category',
                'data' => ['Domésticos', 'Comerciales', 'Industrial/Planta'],
            ],
            'yAxis'   => [
                'type' => 'value',
            ],
            'series'  => [
                [
                    'name'      => 'Servicios',
                    'type'      => 'bar',
                    'data'      => [$domestics, $comercials, $industrials],
                    'itemStyle' => [
                        'color' => ['red', 'blue', 'green'],
                    ],
                ],
            ],
        ]);
    }
    //////////////////////// Fin de graficas de CLIENTES ////////////////////////////////////////
    // -------------------------------------------------------------------------------------- //

    ////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// GRAFICAS DE CALIDAD //////////////////////////////////////////

    ////////////////////////////// Gestión de órdenes de servicio por administrador

    public function serviceOrders()
    {
        $labels = ['Pendientes', 'Finalizadas', 'Aprovadas'];
        $api    = route('crm.chart.serviceOrders');
        $chart  = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function serviceOrdersDataset()
    {
        // ultimo mes
        $start = Carbon::now()->startOfMonth()->startOfDay();
        $end   = Carbon::now()->endOfMonth()->endOfDay();

        $admin_id = auth()->user()->simpleRole;

        $pending = Order::where('status_id', 1)
            ->whereBetween('created_at', [$start, $end])
            ->where('administrative_id', $admin_id)
            ->count();

        $finished = Order::where('status_id', 2)
            ->whereBetween('created_at', [$start, $end])
            ->where('administrative_id', $admin_id)
            ->count();

        $approved = Order::where('status_id', 3)
            ->whereBetween('created_at', [$start, $end])
            ->where('administrative_id', $admin_id)
            ->count();

        $counts = [$pending, $finished, $approved];
        $chart  = new SampleChart;
        // pendientes - amarillo(warning), finalizadas - azul(primary), aprovadas - verde(success)
        $chart->labels(['Pendientes', 'Finalizadas', 'Aprovadas']);
        $chart->dataset('Ordenes de Servicio', 'doughnut', $counts)
            ->backgroundColor(['#ffc107', '#0d6efd', '#198754'])
            ->color(['#ffc107', '#0d6efd', '#198754']);

        return $chart->api();
    }

    public function refreshServiceOrders(Request $request)
    {
        $admin_id  = $request->input('admin_user');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $pending = Order::where('status_id', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('administrative_id', $admin_id)
            ->count();

        $finished = Order::where('status_id', 2)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('administrative_id', $admin_id)
            ->count();

        $approved = Order::where('status_id', 3)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('administrative_id', $admin_id)
            ->count();

        $counts = [$pending, $finished, $approved];
        $chart  = new SampleChart;
        $chart->labels(['Pendientes', 'Finalizadas', 'Aprovadas']);
        $chart->dataset('Ordenes de Servicio', 'doughnut', $counts)
            ->backgroundColor(['#ffc107', '#0d6efd', '#198754'])
            ->color(['#ffc107', '#0d6efd', '#198754']);

        return $chart->api();
    }

    /////////////////////////////// Consumo por dispositivo en ordenes de servicio

    //////////////////////// Fin de graficas de CALIDAD ////////////////////////////////////////
    // -------------------------------------------------------------------------------------- //

    ////////////////////////////////////////////////////////////////////////////////////////////
    // Graficas de ordenes o clientes agendados

    public function orders()
    {
        $labels = ['Domesticos', 'Comerciales'];
        $api    = url(route('crm.chart.orders'));

        $chart = new SampleChart;
        $chart->labels($labels)->load($api);
        return $chart;
    }

    public function ordersDataset()
    {
        $month  = Carbon::now()->month;
        $counts = [0, 0];
        $orders = Order::whereMonth('programmed_date', $month)->get();
        foreach ($orders as $order) {
            if ($order->customer->service_type_id == 1) {
                $counts[0]++;
            }
            if ($order->customer->service_type_id == 2) {
                $counts[1]++;
            }
        }

        $chart = new SampleChart;
        $chart->dataset('Scheduled Orders', 'doughnut', $counts)->backgroundColor($this->colors)->color($this->colors);

        return $chart->api();
    }

    public function refreshOrders(Request $request)
    {
        $month  = $request->input('month');
        $counts = [];
        $orders = Order::whereMonth('programmed_date', $month)->get();
        foreach ($orders as $order) {
            if ($order->customer->service_type_id == 1) {
                $counts[0]++;
            }
            if ($order->customer->service_type_id == 2) {
                $counts[1]++;
            }
        }

        $chart = new SampleChart;
        $chart->dataset('Scheduled Orders', 'doughnut', $counts)->backgroundColor($this->colors)->color($this->colors);

        return $chart->api();
    }

    // Obtiene la diferencia de servicios agendados respecto de los clientes agregdaos
    // Si recibe 1 entonces es domestico, si recibe 2 es comercial
    public function orderTypes($service_type)
    {
        $labels = ['Agendados', 'Totales'];
        $api    = url(route('crm.chart.ordertypes', ['service_type' => $service_type]));
        $chart  = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function orderTypesDataset($service_type)
    {
        $month       = Carbon::now()->month;
        $counts      = [0, 0];
        $customerIds = [];

        $orders    = Order::whereMonth('programmed_date', $month)->get();
        $customers = Customer::whereMonth('created_at', $month)->where('service_type_id', $service_type)->where('general_sedes', 0)->count();

        foreach ($orders as $order) {
            if ($order->customer->service_type_id == $service_type) {
                $customerIds[] = $order->customer_id;
            }
        }

        $counts[0] = count(array_unique($customerIds));
        $counts[1] = $customers;

        $chart = new SampleChart;
        $chart->dataset('Clientes', 'bar', $counts)->backgroundColor($this->colors)->color($this->colors);

        return $chart->api();
    }

    public function refreshOrderTypes(Request $request, $service_type)
    {
        $month = $request->input('month');

        $counts      = [0, 0];
        $customerIds = [];

        $orders    = Order::whereMonth('programmed_date', $month)->get();
        $customers = Customer::whereMonth('created_at', $month)->where('service_type_id', $service_type)->where('general_sedes', 0)->count();

        foreach ($orders as $order) {
            if ($order->customer->service_type_id == $service_type) {
                $customerIds[] = $order->customer_id;
            }
        }

        $counts[0] = count(array_unique($customerIds));
        $counts[1] = $customers;

        $chart = new SampleChart;
        $chart->dataset('Clientes', 'bar', $counts)->backgroundColor($this->colors)->color($this->colors);

        return $chart->api();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// Graficas Almacen //////////////////////////////////////////

    // -------------------------------------------------------------------------------------- //
    //                              Uso de productos

    public function productUse()
    {
        // se usa el mes actual
        $labels = $this->months;
        $api    = url(route('stock.analytics.charts.productuse.dataset'));

        $chart = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function datasetProductUse()
    {
        // Obtener el producto seleccionado desde la request, por defecto el primero
        $productId = request()->get('product_id');

        if (! $productId) {
            $product   = ProductCatalog::orderBy('name')->first();
            $productId = $product ? $product->id : null;
        } else {
            $product = ProductCatalog::find($productId);
        }

        if (! $product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Inicializar arrays
        $inputs  = [];
        $outputs = [];

        $currentYear = Carbon::now()->year;

        // Procesar cada mes
        foreach ($this->months as $index => $month) {
            $monthNumber = $index + 1; // Los meses van de 1 a 12

            // Entradas (movimientos tipo 1-4)
            $inputSum = MovementProduct::join('warehouse_movements', 'movement_products.warehouse_movement_id', '=', 'warehouse_movements.id')
                ->where('movement_products.product_id', $productId)
                ->whereIn('warehouse_movements.movement_id', [1, 2, 3, 4]) // Tipos de entrada
                ->whereMonth('warehouse_movements.date', $monthNumber)
                ->whereYear('warehouse_movements.date', $currentYear)
                ->where('warehouse_movements.is_active', 1)
                ->sum('movement_products.amount');

            // Salidas (movimientos tipo 5-10)
            $outputSum = MovementProduct::join('warehouse_movements', 'movement_products.warehouse_movement_id', '=', 'warehouse_movements.id')
                ->where('movement_products.product_id', $productId)
                ->whereIn('warehouse_movements.movement_id', [5, 6, 7, 8, 9, 10]) // Tipos de salida
                ->whereMonth('warehouse_movements.date', $monthNumber)
                ->whereYear('warehouse_movements.date', $currentYear)
                ->where('warehouse_movements.is_active', 1)
                ->sum('movement_products.amount');

            $inputs[]  = (float) $inputSum;
            $outputs[] = (float) $outputSum;
        }

        $chart = new SampleChart;
        $chart->labels($this->months);

        // Dataset para entradas
        $chart->dataset('Entradas - ' . $product->name, 'bar', $inputs)
            ->backgroundColor($this->colors['PrussianBlue'])
            ->color($this->colors['PrussianBlue']);

        // Dataset para salidas
        $chart->dataset('Salidas - ' . $product->name, 'bar', $outputs)
            ->backgroundColor($this->colors['Jasper'])
            ->color($this->colors['Jasper']);

        return $chart->api();
    }

    // -------------------------------------------------------------------------------------- //
    //                              Movimientos de almacen

    public function stockMovements()
    {
        $labels = MovementType::all()->pluck('name');
        $api    = url(route('stock.analytics.charts.stockmovements.dataset'));

        $chart = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function datasetStockMovements()
    {
        $counts         = [];
        $warehouse      = Warehouse::find(1);
        $movement_types = MovementType::all();
        foreach ($movement_types as $movement_type) {
            $counts[] = WarehouseMovement::where('warehouse_id', 1)->where('movement_id', $movement_type->id)->count();
        }

        $chart = new SampleChart;
        $chart->dataset($warehouse->name, 'bar', $counts)->backgroundColor($this->colors['PrussianBlue'])->color($this->colors['PrussianBlue']);

        return $chart->api();
    }

    public function refreshStockMovements(Request $request)
    {
        $warehouseId = $request->get('warehouseId') ?? $request->get('warehouse_id');
        $warehouse   = Warehouse::find($warehouseId);

        if (! $warehouse) {
            return response()->json(['error' => 'Almacén no encontrado'], 404);
        }

        $movement_types = MovementType::all();
        $counts         = [];

        foreach ($movement_types as $movement_type) {
            $count = WarehouseMovement::where(function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->orWhere('destination_warehouse_id', $warehouseId);
            })
                ->where('movement_id', $movement_type->id)
                ->count();
            $counts[] = $count;
        }

        $chart = new SampleChart;
        $chart->dataset($warehouse->name, 'bar', $counts)
            ->backgroundColor($this->colors['PrussianBlue'])
            ->color($this->colors['PrussianBlue']);

        return $chart->api();
    }

    /**
     * Inventario por Almacén - Muestra stock actual por almacén
     */
    public function inventoryByWarehouse()
    {
        $labels = Warehouse::where('is_active', 1)->pluck('name');
        $api    = url(route('stock.analytics.charts.inventory.dataset'));

        $chart = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function datasetInventoryByWarehouse()
    {
        $warehouseId = request()->get('warehouse_id');

        if ($warehouseId) {
            // Inventario de un almacén específico: número de productos distintos en el almacén
            $warehouse = Warehouse::find($warehouseId);
            $labels    = [$warehouse ? $warehouse->name : 'Almacén'];
            $amounts   = [
                WarehouseLot::where('warehouse_id', $warehouseId)
                    ->where('current_amount', '>', 0)
                    ->distinct('product_id')
                    ->count('product_id'),
            ];

            $chart = new SampleChart;
            $chart->labels($labels);
            $chart->dataset('Número de productos distintos', 'bar', $amounts)
                ->backgroundColor($this->colors[0])
                ->color($this->colors[0]);
        } else {
            // Número de productos distintos por almacén
            $warehouses = Warehouse::where('is_active', 1)->get();
            $labels     = [];
            $amounts    = [];

            foreach ($warehouses as $warehouse) {
                $numProducts = WarehouseLot::where('warehouse_id', $warehouse->id)
                    ->where('current_amount', '>', 0)
                    ->distinct('product_id')
                    ->count('product_id');
                $labels[]  = $warehouse->name;
                $amounts[] = $numProducts;
            }

            $chart = new SampleChart;
            $chart->labels($labels);
            $chart->dataset('Número de productos distintos', 'bar', $amounts)
                ->backgroundColor($this->colors['PrussianBlue'])
                ->color($this->colors['PrussianBlue']);
        }

        return $chart->api();
    }

    /**
     * Productos Más Usados por Mes - Tendencias de consumo mensual
     */
    public function mostUsedProductsByMonth()
    {
        $labels = $this->months;
        $api    = url(route('stock.analytics.charts.mostused.dataset'));

        $chart = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function datasetMostUsedProductsByMonth()
    {
        $year        = request()->get('year', Carbon::now()->year);
        $limit       = request()->get('limit', 5); // Top 5 productos por defecto
        $warehouseId = request()->get('warehouse_id');

        // Obtener los productos más usados en el año
        $topProductsQuery = MovementProduct::join('warehouse_movements', 'movement_products.warehouse_movement_id', '=', 'warehouse_movements.id')
            ->join('product_catalog', 'movement_products.product_id', '=', 'product_catalog.id')
            ->whereIn('warehouse_movements.movement_id', [5, 6, 7, 8, 9, 10]) // Tipos de salida
            ->whereYear('warehouse_movements.date', $year)
            ->where('warehouse_movements.is_active', 1);

        if ($warehouseId) {
            $topProductsQuery->where('warehouse_movements.warehouse_id', $warehouseId);
        }

        $topProducts = $topProductsQuery
            ->selectRaw('movement_products.product_id, product_catalog.name, SUM(movement_products.amount) as total_used')
            ->groupBy('movement_products.product_id', 'product_catalog.name')
            ->orderByDesc('total_used')
            ->limit($limit)
            ->get();

        if ($topProducts->isEmpty()) {
            $chart = new SampleChart;
            $chart->labels($this->months);
            $chart->dataset('Sin datos', 'line', array_fill(0, 12, 0))
                ->backgroundColor($this->colors[1])
                ->color($this->colors[1]);
            return $chart->api();
        }

        $chart = new SampleChart;
        $chart->labels($this->months);

        // Crear dataset para cada producto
        foreach ($topProducts as $index => $product) {
            $monthlyData = [];

            // Obtener datos por mes para este producto
            foreach ($this->months as $monthIndex => $month) {
                $monthNumber = $monthIndex + 1;

                $monthlyUsage = MovementProduct::join('warehouse_movements', 'movement_products.warehouse_movement_id', '=', 'warehouse_movements.id')
                    ->where('movement_products.product_id', $product->product_id)
                    ->whereIn('warehouse_movements.movement_id', [5, 6, 7, 8, 9, 10])
                    ->whereMonth('warehouse_movements.date', $monthNumber)
                    ->whereYear('warehouse_movements.date', $year)
                    ->where('warehouse_movements.is_active', 1);

                if ($warehouseId) {
                    $monthlyUsage->where('warehouse_movements.warehouse_id', $warehouseId);
                }

                $monthlyData[] = (float) $monthlyUsage->sum('movement_products.amount');
            }

            // Usar colores diferentes para cada producto
            $color = $this->getProductColor($index);

            $chart->dataset($product->name, 'line', $monthlyData)
                ->backgroundColor('rgba(0,0,0,0)')
                ->color($color);
        }

        return $chart->api();
    }

    /**
     * Obtener color para el producto basado en el índice
     */
    private function getProductColor($index)
    {
        $colors = [
            '#264653', // Azul
            '#2A9D8F', // Amarillo
            '#E9C46A', // Verde azulado
            '#F4A261', // Púrpura
            '#E76F51', // Naranja
            '#FF6384', // Rosa (repetir si hay más de 6)
            '#C9CBCF', // Gris
            '#4BC0C0', // Verde azulado
            '#FF6384', // Rosa
            '#FF6495', // Naranja
        ];

        return $colors[$index % count($colors)];
    }

    //////////////////////// Fin de GRÁFICAS DE ALMACÉN ////////////////////////////////
    // -------------------------------------------------------------------------------------- //

    //......................................................................................
    //........................... GRAFICAS DEL AREA DE CALIDAD..............................
    //......................................................................................
}
