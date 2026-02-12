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
use Illuminate\Support\Facades\DB;

class GraphicController extends Controller
{
    private $colors = [
        'DeepSpaceBlue'     => '#012640',
        'DeepNavy'          => '#02265A',
        'TrueCobalt'        => '#0A2986',
        'IndigoVelvet'      => '#512A87',
        'VelvetPurple'      => '#773774',
        'DustyMauve'        => '#B74453',
        'FieryTerracotta'   => '#DE523B',
    ];

    // Colores estándar para tipos de servicio (Doméstico, Comercial, Industrial)
    private $service_colors = [
        'Domestico'  => '#0A2986',  // True Cobalt
        'Comercial'  => '#512A87',  // Indigo Velvet
        'Industrial' => '#DE523B',  // Fiery Terracotta
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

        // Estadisticas de clientes
        $anualCustomersChart  = $this->totalCustomersByYear($actualYear);
        $chart                = $this->newCustomers();                                       // Nuevos clientes por mes
        $categoryChart        = $this->customersByYear();                                    // Total de clientes por categoría
        $leadsChart           = $this->newLeadsByMonth($request, $actualYear, $actualMonth); // Leads captados en el mes
        $monthlyServicesChart = $this->monthlyServices();                                    // Tipos de servicios captados por mes
        $pestsDonutChart      = $this->pestsDonutChart();                                    // Plagas más presentadas

        // Estadisticas de calidad
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
            'pestsDonutChart',
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
            ->backgroundColor('rgba(10, 41, 134, 0.2)')
            ->color('#0A2986');

        $chart->dataset('Comerciales', 'line', $comercials)
            ->backgroundColor('rgba(81, 42, 135, 0.2)')
            ->color('#512A87');

        $chart->dataset('Industrial/Planta', 'line', $industrials)
            ->backgroundColor('rgba(222, 82, 59, 0.2)')
            ->color('#DE523B');

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
                            'color' => ['#0A2986', '#512A87', '#DE523B'],
                        ],
                    ],
                ],
            ]);
        }

        // For non-AJAX requests, return a chart object
        $chart = new SampleChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);
        $chart->dataset('Leads', 'bar', [$domestics, $comercials, $industrials])
            ->backgroundColor(['#0A2986', '#512A87', '#DE523B'])
            ->color(['#0A2986', '#512A87', '#DE523B']);

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
            ->backgroundColor(['#FFA000', '#0D47A1', '#D32F2F']) // Colores para cada barra
            ->color(['#FFA000', '#0D47A1', '#D32F2F']);          // Bordes para cada barra

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
            ->backgroundColor(['#0A2986', '#512A87', '#DE523B']) // Colores para cada barra
            ->color(['#0A2986', '#512A87', '#DE523B']);          // Bordes para cada barra

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
            ->backgroundColor('rgba(10, 41, 134, 0.2)')
            ->color('#0A2986');

        $chart->dataset('Comerciales', 'line', $comercials)
            ->backgroundColor('rgba(81, 42, 135, 0.2)')
            ->color('#512A87');

        $chart->dataset('Industrial/Planta', 'line', $industrials)
            ->backgroundColor('rgba(222, 82, 59, 0.2)')
            ->color('#DE523B');

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
            ->backgroundColor('rgba(10, 41, 134, 0.2)')
            ->color('#0A2986');

        $chart->dataset('Comerciales', 'line', $comercials)
            ->backgroundColor('rgba(81, 42, 135, 0.2)')
            ->color('#512A87');

        $chart->dataset('Industrial/Planta', 'line', $industrials)
            ->backgroundColor('rgba(222, 82, 59, 0.2)')
            ->color('#DE523B');

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
            ->backgroundColor(['rgba(10, 41, 134, 0.2), rgba(81, 42, 135, 0.2), rgba(222, 82, 59, 0.2)'])
            ->color(['#0A2986', '#512A87', '#DE523B']);

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
            ->backgroundColor(['rgba(10, 41, 134, 0.2), rgba(81, 42, 135, 0.2), rgba(222, 82, 59, 0.2)'])
            ->color(['#0A2986', '#512A87', '#DE523B']);

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
                            $colors = ['#FFA000', '#0D47A1', '#D32F2F'];
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

        $chart = new SampleChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);

        $chart->dataset('Servicios', 'doughnut', [$domestics, $comercials, $industrials])
            ->backgroundColor(['#0A2986', '#512A87', '#DE523B'])
            ->color(['#0A2986', '#512A87', '#DE523B']);

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

        $chart = new SampleChart;
        $chart->labels(['Domésticos', 'Comerciales', 'Industrial/Planta']);

        $chart->dataset('Servicios', 'doughnut', [$domestics, $comercials, $industrials])
            ->backgroundColor(['#0A2986', '#512A87', '#DE523B'])
            ->color(['#0A2986', '#512A87', '#DE523B']);

        return $chart->api();
    }

    ////////////////////////////// Servicios completados por mes

    public function servicesCompletedByMonth(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $monthlyServices = [];
        $monthLabels = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthLabels[] = Carbon::create()->month($month)->locale('es')->monthName;
            
            // Contar todas las órdenes generadas en el mes
            $servicesCount = Order::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();
            
            $monthlyServices[] = $servicesCount;
        }

        return response()->json([
            'labels' => $monthLabels,
            'data' => $monthlyServices,
        ]);
    }

    //////////////////////// Fin de Estadisticas de CLIENTES ////////////////////////////////////////
    // -------------------------------------------------------------------------------------- //

    ////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////// Estadisticas DE CALIDAD //////////////////////////////////////////

    ////////////////////////////// Plagas más presentadas por mes

    public function pestsDonutChart()
    {
        $labels = ['Plagas'];
        $api    = route('crm.chart.pestsDonut');
        $chart  = new SampleChart;
        $chart->labels($labels)->load($api);

        return $chart;
    }

    public function pestsDonutDataset()
    {
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;

        $pestsData = DB::table('device_pest')
            ->join('order', 'device_pest.order_id', '=', 'order.id')
            ->join('pest_catalog', 'device_pest.pest_id', '=', 'pest_catalog.id')
            ->select('pest_catalog.id', 'pest_catalog.name', DB::raw('SUM(device_pest.total) as total_count'))
            ->whereMonth('order.programmed_date', $month)
            ->whereYear('order.programmed_date', $year)
            ->groupBy('pest_catalog.id', 'pest_catalog.name')
            ->orderBy('total_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $pestsData->pluck('name')->toArray();
        $data = $pestsData->pluck('total_count')->toArray();

        $chart = new SampleChart;
        $chart->labels($labels);
        $chart->dataset('Plagas', 'doughnut', $data)
            ->backgroundColor($this->colors)
            ->color($this->colors);

        return $chart->api();
    }

    public function refreshPestsDonut(Request $request)
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        $pestsData = DB::table('device_pest')
            ->join('order', 'device_pest.order_id', '=', 'order.id')
            ->join('pest_catalog', 'device_pest.pest_id', '=', 'pest_catalog.id')
            ->select('pest_catalog.id', 'pest_catalog.name', DB::raw('SUM(device_pest.total) as total_count'))
            ->whereMonth('order.programmed_date', $month)
            ->whereYear('order.programmed_date', $year)
            ->groupBy('pest_catalog.id', 'pest_catalog.name')
            ->orderBy('total_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $pestsData->pluck('name')->toArray();
        $data = $pestsData->pluck('total_count')->toArray();

        $chart = new SampleChart;
        $chart->labels($labels);
        $chart->dataset('Plagas', 'doughnut', $data)
            ->backgroundColor($this->colors)
            ->color($this->colors);

        return $chart->api();
    }

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
            ->backgroundColor(['#B74453', '#0A2986', '#512A87'])
            ->color(['#B74453', '#0A2986', '#512A87']);

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
            ->backgroundColor(['#B74453', '#0A2986', '#512A87'])
            ->color(['#B74453', '#0A2986', '#512A87']);

        return $chart->api();
    }

    /////////////////////////////// Consumo por dispositivo en ordenes de servicio

    //////////////////////// Fin de Estadisticas de CALIDAD ////////////////////////////////////////
    // -------------------------------------------------------------------------------------- //

    ////////////////////////////////////////////////////////////////////////////////////////////
    // Estadisticas de ordenes o clientes agendados

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
    /////////////////////////////// Estadisticas Almacen //////////////////////////////////////////

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
    //........................... Estadisticas DEL AREA DE CALIDAD..............................
    //......................................................................................

    //////////////////////// MÉTODOS JSON PARA AJAX ////////////////////////////////

    /**
     * Devuelve datos de clientes por mes en formato JSON
     */
    public function customersByMonthJson(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $domestics = [];
        $comercials = [];
        $industrials = [];

        for ($month = 1; $month <= 12; $month++) {
            $domestics[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 1)
                /*->where(function($query) {
                    $query->whereNotNull('general_sedes')
                        ->where('general_sedes', '!=', 0);
                })*/
                ->count();

            $comercials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 2)
                ->where(function($query) {
                    $query->whereNotNull('general_sedes')
                        ->where('general_sedes', '!=', 0);
                })
                ->count();

            $industrials[] = Customer::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 3)
                ->where(function($query) {
                    $query->whereNotNull('general_sedes')
                        ->where('general_sedes', '!=', 0);
                })
                ->count();
        }

        return response()->json([
            'labels' => [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
            ],
            'domestics' => $domestics,
            'comercials' => $comercials,
            'industrials' => $industrials,
        ]);
    }

    /**
     * Devuelve datos de leads por mes en formato JSON
     */
    public function leadsByMonthJson(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $domestics = [];
        $comercials = [];
        $industrials = [];

        for ($month = 1; $month <= 12; $month++) {
            $domestics[] = Lead::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 1)
                ->count();

            $comercials[] = Lead::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 2)
                ->count();

            $industrials[] = Lead::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('service_type_id', 3)
                ->count();
        }

        return response()->json([
            'labels' => [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
            ],
            'domestics' => $domestics,
            'comercials' => $comercials,
            'industrials' => $industrials,
        ]);
    }

    /**
     * Devuelve datos de servicios por tipo de cliente en formato JSON
     */
    public function servicesByTypeJson(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

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
            'domestics' => $domestics,
            'comercials' => $comercials,
            'industrials' => $industrials,
        ]);
    }

    /**
     * Devuelve datos de servicios programados (órdenes por servicio) en formato JSON
     */
    public function servicesProgrammedJson(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Obtener todos los servicios y contar sus órdenes a través de OrderService
        $servicesData = DB::table('service')
            ->leftJoin('order_service', 'service.id', '=', 'order_service.service_id')
            ->leftJoin('order', 'order_service.order_id', '=', 'order.id')
            ->select('service.id', 'service.name', DB::raw('COUNT(DISTINCT order.id) as orders_count'))
            ->whereMonth('order.programmed_date', $month)
            ->whereYear('order.programmed_date', $year)
            ->groupBy('service.id', 'service.name')
            ->having('orders_count', '>', 0)
            ->orderBy('orders_count', 'desc')
            ->get();

        $labels = $servicesData->pluck('name')->toArray();
        $data = $servicesData->pluck('orders_count')->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Devuelve datos de seguimientos de clientes por mes en formato JSON
     */
    public function trackingsByMonthJson(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $trackings = [];

        for ($month = 1; $month <= 12; $month++) {
            $count = DB::table('tracking')
                ->whereMonth('next_date', $month)
                ->whereYear('next_date', $year)
                ->count();
            
            $trackings[] = $count;
        }

        return response()->json([
            'labels' => [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
            ],
            'data' => $trackings,
        ]);
    }

    /**
     * Devuelve datos de plagas más presentadas en formato JSON
     */
    public function pestsByCustomerJson(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Obtener las plagas más presentadas basadas en órdenes filtradas por mes/año
        $pestsData = DB::table('device_pest')
            ->join('order', 'device_pest.order_id', '=', 'order.id')
            ->join('pest_catalog', 'device_pest.pest_id', '=', 'pest_catalog.id')
            ->select('pest_catalog.id', 'pest_catalog.name', DB::raw('SUM(device_pest.total) as total_count'))
            ->whereMonth('order.programmed_date', $month)
            ->whereYear('order.programmed_date', $year)
            ->groupBy('pest_catalog.id', 'pest_catalog.name')
            ->orderBy('total_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $pestsData->pluck('name')->toArray();
        $data = $pestsData->pluck('total_count')->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Generar reporte PDF con gráficas
     */
}
