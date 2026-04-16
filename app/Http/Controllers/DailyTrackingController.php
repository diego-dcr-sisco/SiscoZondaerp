<?php

namespace App\Http\Controllers;

use App\Exports\DailyTrackingReportExport;
use App\Enums\DailyTrackingClosed;
use App\Enums\DailyTrackingContactMethod;
use App\Enums\DailyTrackingCustomerType;
use App\Enums\DailyTrackingInvoice;
use App\Enums\DailyTrackingPaymentMethod;
use App\Enums\DailyTrackingQuoted;
use App\Enums\DailyTrackingServiceType;
use App\Enums\DailyTrackingStatus;
use App\Http\Requests\StoreDailyTrackingRequest;
use App\Http\Requests\UpdateDailyTrackingRequest;
use App\Http\Requests\ImportExcelRequest;
use App\Models\DailyTracking;
use App\Models\Service;
use App\Services\ExcelImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DailyTrackingController extends Controller
{
    public function index(Request $request)
    {
        $navigation = $this->navigation();
        $perPage = (int) $request->integer('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $sortableColumns = ['service_date', 'customer_name', 'status', 'service_type', 'created_at'];
        $sort = $request->get('sort', 'created_at');
        if (! in_array($sort, $sortableColumns, true)) {
            $sort = 'created_at';
        }

        $direction = strtoupper($request->get('direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $query = $this->buildFilteredQuery($request)->with('service:id,name');

        $dailyTrackings = $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('crm.daily-tracking.index', array_merge($this->formData(), [
            'navigation' => $navigation,
            'dailyTrackings' => $dailyTrackings,
            'statusOptions' => DailyTrackingStatus::cases(),
            'serviceTypeOptions' => DailyTrackingServiceType::cases(),
            'nav' => 'd',
        ]));
    }

    public function charts(Request $request)
    {
        $chartDateRange = $this->parseDateRange((string) $request->input('date_range', ''));
        $chartWhereRaw = $this->buildChartWhereRaw($request);

        $contactMethodChart = new LaravelChart([
            'chart_title' => 'Medio de contacto con mayor cantidad',
            'report_type' => 'group_by_string',
            'model' => DailyTracking::class,
            'group_by_field' => 'contact_method',
            'aggregate_function' => 'count',
            'chart_type' => 'bar',
            'where_raw' => $chartWhereRaw,
            'chart_color' => '10, 41, 134',
            'labels' => [
                'google' => 'Google',
                'pagina' => 'Pagina web',
                'llamada' => 'Llamada',
                'cambaceo' => 'Cambaceo',
            ],
        ]);

        $amountsChartOptions = [
            'chart_title' => 'Montos facturados ($) por periodo',
            'report_type' => 'group_by_date',
            'model' => DailyTracking::class,
            'group_by_field' => 'created_at',
            'group_by_period' => 'month',
            'aggregate_function' => 'sum',
            'aggregate_field' => 'billed_amount',
            'chart_type' => 'bar',
            'where_raw' => $chartWhereRaw,
            'chart_color' => '183, 68, 83',
            'date_format' => 'Y-m',
            'continuous_time' => true,
        ];

        if ($chartDateRange !== null) {
            $amountsChartOptions['filter_field'] = 'created_at';
            $amountsChartOptions['range_date_start'] = $chartDateRange['start'];
            $amountsChartOptions['range_date_end'] = $chartDateRange['end'];
        } else {
            $amountsChartOptions['filter_field'] = 'created_at';
            $amountsChartOptions['filter_period'] = 'year';
        }

        $amountsChart = new LaravelChart($amountsChartOptions);

        $clientsPeriodChartOptions = [
            'chart_title' => 'Clientes ingresados por semana (anio actual)',
            'report_type' => 'group_by_date',
            'model' => DailyTracking::class,
            'group_by_field' => 'created_at',
            'group_by_period' => 'week',
            'aggregate_function' => 'count',
            'chart_type' => 'line',
            'where_raw' => $chartWhereRaw,
            'chart_color' => '81, 42, 135',
            'date_format' => 'o-\\WW',
            'continuous_time' => true,
        ];

        if ($chartDateRange !== null) {
            $clientsPeriodChartOptions['filter_field'] = 'created_at';
            $clientsPeriodChartOptions['range_date_start'] = $chartDateRange['start'];
            $clientsPeriodChartOptions['range_date_end'] = $chartDateRange['end'];
        } else {
            $clientsPeriodChartOptions['filter_field'] = 'created_at';
            $clientsPeriodChartOptions['filter_period'] = 'year';
        }

        $clientsPeriodChart = new LaravelChart($clientsPeriodChartOptions);

        $conversionRows = $this->buildFilteredQuery($request)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period")
            ->selectRaw("SUM(CASE WHEN quoted = 'yes' THEN 1 ELSE 0 END) as quoted_count")
            ->selectRaw("SUM(CASE WHEN closed = 'yes' THEN 1 ELSE 0 END) as closed_count")
            ->whereNotNull('created_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $conversionLabels = [];
        $conversionData = [];
        foreach ($conversionRows as $row) {
            $quotedCount = (int) $row->quoted_count;
            $closedCount = (int) $row->closed_count;
            $conversionRate = $quotedCount > 0 ? round(($closedCount / $quotedCount) * 100, 2) : 0;

            $conversionLabels[] = (string) $row->period;
            $conversionData[] = $conversionRate;
        }

        return view('crm.daily-tracking.charts', array_merge($this->formData(), [
            'contactMethodChart' => $contactMethodChart,
            'amountsChart' => $amountsChart,
            'clientsPeriodChart' => $clientsPeriodChart,
            'conversionLabels' => $conversionLabels,
            'conversionData' => $conversionData,
            'statusOptions' => DailyTrackingStatus::cases(),
        ]));
    }

    public function export(Request $request)
    {
        $contactMethodConfig = $this->contactMethodExportConfig();
        $allowedContactMethods = array_keys($contactMethodConfig);

        $request->validate([
            'group_by' => ['required', 'in:day,week,month,year'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'date_range' => ['nullable', 'string'],
            'contact_methods' => ['nullable', 'array'],
            'contact_methods.*' => ['in:' . implode(',', $allowedContactMethods)],
        ]);

        $selectedContactMethods = array_values(array_filter(
            (array) $request->input('contact_methods', []),
            fn ($method) => in_array($method, $allowedContactMethods, true)
        ));

        if (empty($selectedContactMethods)) {
            $selectedContactMethods = $allowedContactMethods;
        }

        $groupBy = (string) $request->get('group_by', 'month');
        $groupFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%x-W%v',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m',
        };

        $reportRows = $this->buildFilteredQuery($request)
            ->select(DB::raw("DATE_FORMAT(service_date, '{$groupFormat}') as period"))
            ->selectRaw('COUNT(*) as total_clients')
            ->selectRaw("SUM(CASE WHEN responded = 1 THEN 1 ELSE 0 END) as total_responded")
            ->selectRaw("SUM(CASE WHEN quoted = 'yes' THEN 1 ELSE 0 END) as total_quoted")
            ->selectRaw("SUM(CASE WHEN has_coverage = 0 THEN 1 ELSE 0 END) as no_coverage")
            ->selectRaw("SUM(CASE WHEN closed = 'yes' THEN 1 ELSE 0 END) as total_closed")
            ->selectRaw('SUM(COALESCE(quoted_amount, 0)) as total_quoted_amount')
            ->selectRaw("SUM(CASE WHEN closed = 'yes' THEN COALESCE(billed_amount, 0) ELSE 0 END) as total_closed_amount")
            ->selectRaw('SUM(COALESCE(billed_amount, 0)) as total_billed_amount')
            ->selectRaw("SUM(CASE WHEN customer_type = 'domestico' AND closed = 'yes' THEN COALESCE(billed_amount, 0) ELSE 0 END) as domestic_closed_amount")
            ->selectRaw("SUM(CASE WHEN customer_type = 'domestico' THEN 1 ELSE 0 END) as domestic")
            ->selectRaw("SUM(CASE WHEN customer_type = 'comercial' THEN 1 ELSE 0 END) as commercial")
            ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' THEN 1 ELSE 0 END) as industrial")
            ->selectRaw("SUM(CASE WHEN customer_type = 'comercial' THEN 1 ELSE 0 END) as new_commercial_clients")
            ->selectRaw("SUM(CASE WHEN service_type = 'comercial' THEN 1 ELSE 0 END) as commercial_services")
            ->selectRaw("SUM(CASE WHEN service_type = 'industrial' THEN 1 ELSE 0 END) as industrial_services")
            ->selectRaw("SUM(CASE WHEN invoice = 'yes' THEN 1 ELSE 0 END) as total_invoiced")
            ->groupBy('period')
            ->orderBy('period')
            ->tap(function ($query) use ($contactMethodConfig) {
                foreach ($contactMethodConfig as $methodValue => $meta) {
                    $alias = $meta['alias'];
                    $query->selectRaw("SUM(CASE WHEN contact_method = '{$methodValue}' THEN 1 ELSE 0 END) as {$alias}");
                }
            })
            ->get();

        $baseHeadings = [
            'Periodo',
            'Total clientes',
            'Total contestaron',
            'Total cotizados',
            'SIN COBERTURA',
            'Total cerrados',
            '% Contacto',
            '% Cotizacion',
            '% Conversion',
            '% Facturacion',
            'Monto total cotizado',
            'Monto total cerrado',
            'Monto total facturado',
            'Monto cerrado domestico',
            'Ticket promedio',
            'Domestico',
            'Comercial',
            'Industrial',
            'Clientes comerciales nuevos',
            'Servicios comerciales',
            'Servicios industriales',
        ];

        $methodHeadings = array_map(
            fn ($method) => $contactMethodConfig[$method]['label'],
            $selectedContactMethods
        );

        $headings = array_merge($baseHeadings, $methodHeadings, ['Total facturados']);

        $rows = $reportRows->map(function ($row) use ($selectedContactMethods, $contactMethodConfig) {
            $totalClients = (int) $row->total_clients;
            $totalResponded = (int) $row->total_responded;
            $totalQuoted = (int) $row->total_quoted;
            $totalClosed = (int) $row->total_closed;
            $totalInvoiced = (int) $row->total_invoiced;
            $totalClosedAmount = (float) $row->total_closed_amount;

            $averageTicket = $totalClosed > 0 ? ($totalClosedAmount / $totalClosed) : 0;

            $baseData = [
                (string) $row->period,
                $totalClients,
                $totalResponded,
                $totalQuoted,
                (int) $row->no_coverage,
                $totalClosed,
                $this->percentage($totalResponded, $totalClients),
                $this->percentage($totalQuoted, $totalResponded),
                $this->percentage($totalClosed, $totalQuoted),
                $this->percentage($totalInvoiced, $totalClosed),
                round((float) $row->total_quoted_amount, 2),
                round($totalClosedAmount, 2),
                round((float) $row->total_billed_amount, 2),
                round((float) $row->domestic_closed_amount, 2),
                round($averageTicket, 2),
                (int) $row->domestic,
                (int) $row->commercial,
                (int) $row->industrial,
                (int) $row->new_commercial_clients,
                (int) $row->commercial_services,
                (int) $row->industrial_services,
            ];

            $methodData = array_map(function ($method) use ($row, $contactMethodConfig) {
                $alias = $contactMethodConfig[$method]['alias'];
                return (int) ($row->{$alias} ?? 0);
            }, $selectedContactMethods);

            return array_merge($baseData, $methodData, [$totalInvoiced]);
        })->toArray();

        $filename = 'daily_tracking_report_' . now()->format('Ymd_His') . '.xlsx';

        $export = new DailyTrackingReportExport($rows, $headings);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($export->headings() as $index => $heading) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $heading);
        }

        foreach ($export->rows() as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValue($column . ($rowIndex + 2), $value);
            }
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function create()
    {
        return view('crm.daily-tracking.create', $this->formData());
    }

    public function store(StoreDailyTrackingRequest $request)
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $data['status_updated_at'] = now();
            $data['status_updated_by'] = Auth::id();
        }

        DailyTracking::create($data);

        return redirect()
            ->route('crm.daily-tracking.index')
            ->with('success', 'Registro diario creado correctamente.');
    }

    public function show(DailyTracking $dailyTracking)
    {
        $dailyTracking->load([
            'service:id,name',
            'logs' => fn ($query) => $query->latest(),
        ]);

        return view('crm.daily-tracking.show', [
            'navigation' => $this->navigation(),
            'dailyTracking' => $dailyTracking,
        ]);
    }

    public function edit(DailyTracking $dailyTracking)
    {
        return view('crm.daily-tracking.edit', array_merge(
            ['dailyTracking' => $dailyTracking],
            $this->formData()
        ));
    }

    public function update(UpdateDailyTrackingRequest $request, DailyTracking $dailyTracking)
    {
        $dailyTracking->update($request->validated());

        return redirect()
            ->route('crm.daily-tracking.index')
            ->with('success', 'Registro diario actualizado correctamente.');
    }

    public function destroy(DailyTracking $dailyTracking)
    {
        $dailyTracking->delete();

        return redirect()
            ->route('crm.daily-tracking.index')
            ->with('success', 'Registro diario eliminado correctamente.');
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        $query = DailyTracking::query();

        if ($request->filled('customer')) {
            $query->where('customer_name', 'like', '%' . trim((string) $request->customer) . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', (string) $request->service_type);
        }

        if ($request->filled('contact_methods')) {
            $contactMethods = array_values(array_filter((array) $request->input('contact_methods')));
            if (! empty($contactMethods)) {
                $query->whereIn('contact_method', $contactMethods);
            }
        }

        if ($request->filled('date_range')) {
            $range = explode(' - ', (string) $request->date_range);
            if (count($range) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($range[0]))->toDateString();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($range[1]))->toDateString();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // Ignore invalid date range and keep remaining filters.
                }
            }
        } else {
            if ($request->filled('from')) {
                $query->whereDate('created_at', '>=', (string) $request->from);
            }
            if ($request->filled('to')) {
                $query->whereDate('created_at', '<=', (string) $request->to);
            }
            if ($request->filled('service_date')) {
                $query->whereDate('created_at', (string) $request->service_date);
            }
        }

        return $query;
    }

    private function percentage(int|float $numerator, int|float $denominator): string
    {
        if ((float) $denominator <= 0.0) {
            return '0.00%';
        }

        return number_format(((float) $numerator / (float) $denominator) * 100, 2) . '%';
    }

    private function contactMethodExportConfig(): array
    {
        return [
            DailyTrackingContactMethod::GOOGLE->value => [
                'alias' => 'google',
                'label' => 'Google',
            ],
            DailyTrackingContactMethod::PAGINA->value => [
                'alias' => 'pagina_web',
                'label' => 'Pagina web',
            ],
            DailyTrackingContactMethod::LLAMADA->value => [
                'alias' => 'llamada',
                'label' => 'Llamada',
            ],
            DailyTrackingContactMethod::CAMBACEO->value => [
                'alias' => 'cambaceo',
                'label' => 'Cambaceo',
            ],
        ];
    }

    private function parseDateRange(string $dateRange): ?array
    {
        if (trim($dateRange) === '') {
            return null;
        }

        $parts = explode(' - ', $dateRange);
        if (count($parts) !== 2) {
            return null;
        }

        try {
            return [
                'start' => Carbon::createFromFormat('d/m/Y', trim($parts[0]))->toDateString(),
                'end' => Carbon::createFromFormat('d/m/Y', trim($parts[1]))->toDateString(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function buildChartWhereRaw(Request $request): string
    {
        $conditions = ['1 = 1'];

        if ($request->filled('customer')) {
            $customer = str_replace("'", "''", trim((string) $request->input('customer')));
            $conditions[] = "customer_name LIKE '%{$customer}%'";
        }

        if ($request->filled('status')) {
            $status = str_replace("'", "''", (string) $request->input('status'));
            $conditions[] = "status = '{$status}'";
        }

        if ($request->filled('service_type')) {
            $serviceType = str_replace("'", "''", (string) $request->input('service_type'));
            $conditions[] = "service_type = '{$serviceType}'";
        }

        $dateRange = $this->parseDateRange((string) $request->input('date_range', ''));
        if ($dateRange !== null) {
            $conditions[] = "created_at BETWEEN '{$dateRange['start']}' AND '{$dateRange['end']}'";
        }

        return implode(' AND ', $conditions);
    }

    private function navigation(): array
    {
        return [
            'Agenda' => route('crm.agenda'),
            'Clientes' => route('customer.index'),
            'Sedes' => route('customer.index.sedes'),
            'Clientes potenciales' => route('customer.index.leads'),
            'Estadisticas' => route('crm.chart.dashboard'),
            'Ordenes de servicio' => route('order.index'),
            'Actividades diarias' => route('crm.daily-tracking.index'),
        ];
    }

    private function formData(): array
    {
        return [
            'navigation' => $this->navigation(),
            'services' => Service::query()->select('id', 'name')->orderBy('name')->get(),
            'statusOptions' => DailyTrackingStatus::cases(),
            'contactMethodOptions' => DailyTrackingContactMethod::cases(),
            'serviceTypeOptions' => DailyTrackingServiceType::cases(),
            'customerTypeOptions' => DailyTrackingCustomerType::cases(),
            'quotedOptions' => DailyTrackingQuoted::cases(),
            'closedOptions' => DailyTrackingClosed::cases(),
            'invoiceOptions' => DailyTrackingInvoice::cases(),
            'paymentMethodOptions' => DailyTrackingPaymentMethod::cases(),
        ];
    }

    public function exportCharts(Request $request)
    {
        // Generate the same charts as in the index method
        $chartDateRange = $this->parseDateRange((string) $request->input('date_range', ''));
        $chartWhereRaw = $this->buildChartWhereRaw($request);

        // Chart 1: Contact Methods - obtener datos crudos (como texto plano sin hydration)
        $contactMethodsRaw = DB::table('daily_trackings')
            ->whereRaw($chartWhereRaw)
            ->selectRaw('contact_method, COUNT(*) as count')
            ->groupBy('contact_method')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        $contactMethodLabels = [
            'google' => 'Google',
            'pagina' => 'Pagina web',
            'llamada' => 'Llamada',
            'cambaceo' => 'Cambaceo',
        ];

        // Convertir a formato esperado
        $contactMethodsData = $contactMethodsRaw->map(function ($item) {
            return (object) [
                'contact_method' => $item->contact_method,
                'count' => $item->count,
            ];
        });

        // Chart 2: Amounts by period - obtener datos crudos
        $amountsData = DailyTracking::whereRaw($chartWhereRaw)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, SUM(billed_amount) as total")
            ->whereNotNull('billed_amount')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Chart 3: Clients by week - obtener datos crudos
        $clientsData = DailyTracking::whereRaw($chartWhereRaw)
            ->selectRaw("DATE_FORMAT(created_at, '%Y') as year, WEEK(created_at) as week, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y'), WEEK(created_at)")
            ->orderByRaw("DATE_FORMAT(created_at, '%Y'), WEEK(created_at)")
            ->get();

        // Chart 4: Conversion rate
        $conversionRows = $this->buildFilteredQuery($request)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period")
            ->selectRaw("SUM(CASE WHEN quoted = 'yes' THEN 1 ELSE 0 END) as quoted_count")
            ->selectRaw("SUM(CASE WHEN closed = 'yes' THEN 1 ELSE 0 END) as closed_count")
            ->whereNotNull('created_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $conversionLabels = [];
        $conversionData = [];
        $conversionQuotedCounts = [];
        $conversionClosedCounts = [];
        foreach ($conversionRows as $row) {
            $quotedCount = (int) $row->quoted_count;
            $closedCount = (int) $row->closed_count;
            $conversionRate = $quotedCount > 0 ? round(($closedCount / $quotedCount) * 100, 2) : 0;

            $conversionLabels[] = (string) $row->period;
            $conversionData[] = $conversionRate;
            $conversionQuotedCounts[] = $quotedCount;
            $conversionClosedCounts[] = $closedCount;
        }

        $contactChartLabels = $contactMethodsData
            ->map(fn ($item) => $contactMethodLabels[$item->contact_method] ?? (string) $item->contact_method)
            ->values()
            ->toArray();
        $contactChartValues = $contactMethodsData
            ->map(fn ($item) => (int) $item->count)
            ->values()
            ->toArray();

        $amountChartLabels = $amountsData
            ->pluck('period')
            ->map(fn ($period) => (string) $period)
            ->values()
            ->toArray();
        $amountChartValues = $amountsData
            ->pluck('total')
            ->map(fn ($value) => round((float) $value, 2))
            ->values()
            ->toArray();

        $clientsChartLabels = $clientsData
            ->map(fn ($item) => sprintf('%s-W%02d', (string) $item->year, (int) $item->week))
            ->values()
            ->toArray();
        $clientsChartValues = $clientsData
            ->pluck('count')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->toArray();

        $contactChartImage = $this->generateQuickChartImage([
            'type' => 'bar',
            'data' => [
                'labels' => $contactChartLabels,
                'datasets' => [[
                    'label' => 'Cantidad',
                    'data' => $contactChartValues,
                    'backgroundColor' => 'rgba(10,41,134,0.62)',
                    'borderColor' => '#0A2986',
                    'borderWidth' => 1,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ]);

        $amountChartImage = $this->generateQuickChartImage([
            'type' => 'bar',
            'data' => [
                'labels' => $amountChartLabels,
                'datasets' => [[
                    'label' => 'Monto facturado',
                    'data' => $amountChartValues,
                    'backgroundColor' => 'rgba(183,68,83,0.58)',
                    'borderColor' => '#B74453',
                    'borderWidth' => 1,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ]);

        $clientsChartImage = $this->generateQuickChartImage([
            'type' => 'line',
            'data' => [
                'labels' => $clientsChartLabels,
                'datasets' => [[
                    'label' => 'Clientes',
                    'data' => $clientsChartValues,
                    'borderColor' => '#512A87',
                    'backgroundColor' => 'rgba(81,42,135,0.20)',
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ]);

        $conversionChartImage = $this->generateQuickChartImage([
            'type' => 'line',
            'data' => [
                'labels' => $conversionLabels,
                'datasets' => [[
                    'label' => 'Tasa de conversion (%)',
                    'data' => $conversionData,
                    'borderColor' => '#DD513A',
                    'backgroundColor' => 'rgba(221,81,58,0.22)',
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        $pdfData = [
            'contactMethodsData' => $contactMethodsData,
            'contactMethodLabels' => $contactMethodLabels,
            'amountsData' => $amountsData,
            'clientsData' => $clientsData,
            'conversionLabels' => $conversionLabels,
            'conversionData' => $conversionData,
            'conversionQuotedCounts' => $conversionQuotedCounts,
            'conversionClosedCounts' => $conversionClosedCounts,
            'dateRange' => $chartDateRange,
            'contactChartImage' => $contactChartImage,
            'amountChartImage' => $amountChartImage,
            'clientsChartImage' => $clientsChartImage,
            'conversionChartImage' => $conversionChartImage,
        ];

        $pdf = Pdf::loadView('crm.daily-tracking.charts-pdf', $pdfData)->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('graficas-analisis-' . now()->format('Y-m-d-His') . '.pdf');
    }

    private function generateQuickChartImage(array $config): string
    {
        $chartConfig = json_encode($config);
        $url = 'https://quickchart.io/chart?c=' . urlencode((string) $chartConfig) . '&width=900&height=430&devicePixelRatio=2';

        try {
            $imageData = file_get_contents($url);

            if ($imageData === false) {
                throw new \RuntimeException('No se pudo generar la imagen de grafica.');
            }

            return 'data:image/png;base64,' . base64_encode($imageData);
        } catch (\Throwable $e) {
            // Imagen de respaldo de 1x1 para evitar romper el PDF
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        }
    }

    /**
     * Muestra el formulario para importar Excel
     */
    public function showImportForm()
    {
        return view('crm.daily-tracking.import-excel', [
            'navigation' => $this->navigation(),
        ]);
    }

    /**
     * Procesa la importación del archivo Excel
     */
    public function importFromExcel(ImportExcelRequest $request, ExcelImportService $importService)
    {
        try {
            $file = $request->file('excel_file');
            $filePath = $file->store('imports', 'local');

            $result = $importService->importFile(storage_path('app/' . $filePath));

            // Limpiar archivo después de procesar
            \Illuminate\Support\Facades\Storage::delete($filePath);

            if ($result['success']) {
                return redirect()
                    ->route('crm.daily-tracking.index')
                    ->with('success', $result['message'])
                    ->with('import_result', $result['data'])
                    ->with('import_time', $result['import_time']);
            } else {
                return back()
                    ->with('error', $result['message'])
                    ->with('import_result', $result['data']);
            }

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage())
                ->withInput();
        }
    }
}
