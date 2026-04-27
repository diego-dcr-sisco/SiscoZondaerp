<?php

namespace App\Http\Controllers;

use App\Exports\DailyTrackingReportExport;
use App\Enums\DailyTrackingClosed;
use App\Enums\DailyTrackingContactMethod;
use App\Enums\DailyTrackingCustomerType;
use App\Enums\DailyTrackingInvoice;
use App\Enums\DailyTrackingPaymentMethod;
use App\Enums\DailyTrackingQuoted;
use App\Enums\DailyTrackingStatus;
use App\Http\Requests\StoreDailyTrackingRequest;
use App\Http\Requests\UpdateDailyTrackingRequest;
use App\Http\Requests\ImportExcelRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DailyTracking;
use App\Models\Service;
use App\Models\ServiceType;
use App\Services\ExcelImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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

        $sortableColumns = ['service_date', 'customer_name', 'status', 'created_at'];
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
            'nav' => 'd',
        ]));
    }

    public function charts(Request $request)
    {
        $chartDateRange = $this->parseDateRange((string) $request->input('date_range', ''));
        $chartType = $this->resolveChartType($request);
        $chartView = $this->resolveChartView($request);
        $periodDivision = $this->resolvePeriodDivision($request, $chartDateRange);
        $periodConfig = $this->chartPeriodConfig($periodDivision);

        // Contact methods grouped by period × contact method
        $contactRows = $this->buildFilteredQuery($request)
            ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
            ->selectRaw("SUM(CASE WHEN contact_method = 'google'   THEN 1 ELSE 0 END) as google")
            ->selectRaw("SUM(CASE WHEN contact_method = 'pagina'   THEN 1 ELSE 0 END) as pagina")
            ->selectRaw("SUM(CASE WHEN contact_method = 'llamada'  THEN 1 ELSE 0 END) as llamada")
            ->selectRaw("SUM(CASE WHEN contact_method = 'cambaceo' THEN 1 ELSE 0 END) as cambaceo")
            ->whereNotNull('created_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $contactPeriods = [];
        $contactDatasets = ['google' => [], 'pagina' => [], 'llamada' => [], 'cambaceo' => []];
        foreach ($contactRows as $row) {
            $contactPeriods[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
            $contactDatasets['google'][]   = (int) $row->google;
            $contactDatasets['pagina'][]   = (int) $row->pagina;
            $contactDatasets['llamada'][]  = (int) $row->llamada;
            $contactDatasets['cambaceo'][] = (int) $row->cambaceo;
        }

        // Billed amounts grouped by period × customer type
        $amountsRows = $this->buildFilteredQuery($request)
            ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
            ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  THEN COALESCE(billed_amount, 0) ELSE 0 END) as domestico")
            ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  THEN COALESCE(billed_amount, 0) ELSE 0 END) as comercial")
            ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' THEN COALESCE(billed_amount, 0) ELSE 0 END) as industrial")
            ->whereNotNull('created_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $amountsPeriods = [];
        $amountsDatasets = ['domestico' => [], 'comercial' => [], 'industrial' => []];
        foreach ($amountsRows as $row) {
            $amountsPeriods[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
            $amountsDatasets['domestico'][]  = round((float) $row->domestico, 2);
            $amountsDatasets['comercial'][]  = round((float) $row->comercial, 2);
            $amountsDatasets['industrial'][] = round((float) $row->industrial, 2);
        }

        // Clients counted grouped by period × customer type
        $clientsRows = $this->buildFilteredQuery($request)
            ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
            ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  THEN 1 ELSE 0 END) as domestico")
            ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  THEN 1 ELSE 0 END) as comercial")
            ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' THEN 1 ELSE 0 END) as industrial")
            ->whereNotNull('created_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $clientsPeriods = [];
        $clientsDatasets = ['domestico' => [], 'comercial' => [], 'industrial' => []];
        foreach ($clientsRows as $row) {
            $clientsPeriods[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
            $clientsDatasets['domestico'][]  = (int) $row->domestico;
            $clientsDatasets['comercial'][]  = (int) $row->comercial;
            $clientsDatasets['industrial'][] = (int) $row->industrial;
        }

        // Top 10 services grouped by period
        $topServiceIds = $this->buildFilteredQuery($request)
            ->whereNotNull('service_id')
            ->selectRaw('service_id, COUNT(*) as total')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('service_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $topServicesPeriods = [];
        $topServicesDatasets = [];

        if (! empty($topServiceIds)) {
            $topServiceRows = $this->buildFilteredQuery($request)
                ->whereIn('service_id', $topServiceIds)
                ->whereNotNull('created_at')
                ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
                ->selectRaw('service_id, COUNT(*) as total')
                ->groupBy('period', 'service_id')
                ->orderBy('period')
                ->get();

            $serviceNames = Service::query()
                ->whereIn('id', $topServiceIds)
                ->pluck('name', 'id');

            $periodSet = [];
            $servicePeriodCountMap = [];

            foreach ($topServiceRows as $row) {
                $period = (string) $row->period;
                $serviceId = (int) $row->service_id;
                $periodSet[$period] = true;
                $servicePeriodCountMap[$serviceId][$period] = (int) $row->total;
            }

            $topServicesPeriods = array_values(array_keys($periodSet));
            $periodIndexes = array_flip($topServicesPeriods);

            foreach ($topServiceIds as $serviceId) {
                $series = array_fill(0, count($topServicesPeriods), 0);
                $countsByPeriod = $servicePeriodCountMap[$serviceId] ?? [];

                foreach ($countsByPeriod as $period => $count) {
                    if (array_key_exists($period, $periodIndexes)) {
                        $series[$periodIndexes[$period]] = (int) $count;
                    }
                }

                $topServicesDatasets[] = [
                    'label' => (string) ($serviceNames[$serviceId] ?? ('Servicio #' . $serviceId)),
                    'data' => $series,
                ];
            }

            $topServicesPeriods = array_map(
                fn (string $period) => $this->formatChartPeriodLabel($period, $periodDivision),
                $topServicesPeriods
            );
        }

        // Conversion rate grouped by period × customer type
        $conversionRows = $this->buildFilteredQuery($request)
            ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
            ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  AND quoted = 'yes' THEN 1 ELSE 0 END) as domestico_quoted")
            ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  AND closed = 'yes' THEN 1 ELSE 0 END) as domestico_closed")
            ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  AND quoted = 'yes' THEN 1 ELSE 0 END) as comercial_quoted")
            ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  AND closed = 'yes' THEN 1 ELSE 0 END) as comercial_closed")
            ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' AND quoted = 'yes' THEN 1 ELSE 0 END) as industrial_quoted")
            ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' AND closed = 'yes' THEN 1 ELSE 0 END) as industrial_closed")
            ->whereNotNull('created_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $conversionPeriods = [];
        $conversionDatasets = ['domestico' => [], 'comercial' => [], 'industrial' => []];
        foreach ($conversionRows as $row) {
            $conversionPeriods[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
            
            // Domestico
            $domesticoQuoted = (int) $row->domestico_quoted;
            $domesticoClosed = (int) $row->domestico_closed;
            $domesticoRate = $domesticoQuoted > 0 ? round(($domesticoClosed / $domesticoQuoted) * 100, 2) : 0;
            $conversionDatasets['domestico'][] = $domesticoRate;
            
            // Comercial
            $comercialQuoted = (int) $row->comercial_quoted;
            $comercialClosed = (int) $row->comercial_closed;
            $comercialRate = $comercialQuoted > 0 ? round(($comercialClosed / $comercialQuoted) * 100, 2) : 0;
            $conversionDatasets['comercial'][] = $comercialRate;
            
            // Industrial
            $industrialQuoted = (int) $row->industrial_quoted;
            $industrialClosed = (int) $row->industrial_closed;
            $industrialRate = $industrialQuoted > 0 ? round(($industrialClosed / $industrialQuoted) * 100, 2) : 0;
            $conversionDatasets['industrial'][] = $industrialRate;
        }

        return view('crm.daily-tracking.charts', array_merge($this->formData(), [
            'contactPeriods'  => $contactPeriods,
            'contactDatasets' => $contactDatasets,
            'amountsPeriods'  => $amountsPeriods,
            'amountsDatasets' => $amountsDatasets,
            'clientsPeriods'  => $clientsPeriods,
            'clientsDatasets' => $clientsDatasets,
            'topServicesPeriods' => $topServicesPeriods,
            'topServicesDatasets' => $topServicesDatasets,
            'conversionPeriods'  => $conversionPeriods,
            'conversionDatasets' => $conversionDatasets,
            'chartType'            => $chartType,
            'chartView'            => $chartView,
            'periodDivision'       => $periodDivision,
            'periodDivisionLabel'  => $periodConfig['label'],
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
            ->select(DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"))
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

    public function storeCustomerFromTracking(Request $request, DailyTracking $dailyTracking)
    {
        $contactMediumOptions = $this->customerContactMediumOptions();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'service_type_id' => ['nullable', 'integer', Rule::exists('service_type', 'id')],
            'branch_id' => ['required', 'integer', Rule::exists('branch', 'id')],
            'contact_medium' => ['required', Rule::in(array_keys($contactMediumOptions))],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        // Derive service_type_id from the tracking's customer_type if not supplied
        if (empty($validated['service_type_id'])) {
            $customerTypeValue = $dailyTracking->customer_type instanceof \BackedEnum
                ? $dailyTracking->customer_type->value
                : (string) $dailyTracking->customer_type;

            $serviceType = \App\Models\ServiceType::where(
                \Illuminate\Support\Facades\DB::raw('LOWER(name)'),
                'LIKE',
                strtolower($customerTypeValue) . '%'
            )->first();

            $validated['service_type_id'] = $serviceType?->id;
        }

        abort_if(empty($validated['service_type_id']), 422, 'No se pudo determinar el tipo de servicio para el cliente.');

        $customer = new Customer();
        $serviceTypeId = (int) $validated['service_type_id'];

        $customer->blueprints = $serviceTypeId === 3 ? 1 : 0;
        $customer->print_doc = $serviceTypeId === 3 ? 1 : 0;
        $customer->validate_certificate = $serviceTypeId === 3 ? 1 : 0;
        $customer->code = $this->generateCustomerCode((string) $validated['name']);

        $customer->fill([
            'name' => $validated['name'],
            'service_type_id' => $serviceTypeId,
            'branch_id' => (int) $validated['branch_id'],
            'contact_medium' => $validated['contact_medium'],
            'state' => $validated['state'] ?: null,
            'city' => $validated['city'] ?: null,
            'address' => $validated['address'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'email' => $validated['email'] ?: null,
            'administrative_id' => Auth::id(),
            'general_sedes' => 0,
        ]);

        $customer->status = 1;
        $customer->save();

        return redirect()
            ->route('crm.daily-tracking.index')
            ->with('success', 'Cliente creado correctamente desde el registro diario #' . $dailyTracking->id . '.');
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

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->input('service_id'));
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
                $query->whereDate('service_date', (string) $request->service_date);
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

        if ($request->filled('service_id')) {
            $serviceId = (int) $request->input('service_id');
            $conditions[] = "service_id = {$serviceId}";
        }

        $dateRange = $this->parseDateRange((string) $request->input('date_range', ''));
        if ($dateRange !== null) {
            $conditions[] = "created_at BETWEEN '{$dateRange['start']}' AND '{$dateRange['end']}'";
        }

        return implode(' AND ', $conditions);
    }

    private function resolveChartType(Request $request, string $inputName = 'chart_type', string $default = 'bar'): string
    {
        $chartType = strtolower((string) $request->input($inputName, $default));
        $allowedTypes = ['bar', 'line'];

        return in_array($chartType, $allowedTypes, true) ? $chartType : $default;
    }

    private function resolveChartView(Request $request): string
    {
        $chartView = strtolower((string) $request->input('chart_view', 'contact'));
        $allowedViews = ['contact', 'amounts', 'clients', 'services', 'conversion'];

        return in_array($chartView, $allowedViews, true) ? $chartView : 'contact';
    }

    private function resolvePeriodDivision(Request $request, ?array $chartDateRange): string
    {
        $requestedDivision = strtolower((string) $request->input('period_division', 'auto'));
        $allowedDivisions = ['auto', 'week', 'month', 'year'];

        if (! in_array($requestedDivision, $allowedDivisions, true)) {
            $requestedDivision = 'auto';
        }

        if ($requestedDivision !== 'auto') {
            return $requestedDivision;
        }

        return $this->inferDivisionFromDateRange($chartDateRange);
    }

    private function inferDivisionFromDateRange(?array $chartDateRange): string
    {
        if ($chartDateRange === null) {
            return 'month';
        }

        try {
            $start = Carbon::parse($chartDateRange['start'])->startOfDay();
            $end = Carbon::parse($chartDateRange['end'])->endOfDay();
            $days = $start->diffInDays($end) + 1;

            if ($days <= 62) {
                return 'week';
            }

            if ($days <= 730) {
                return 'month';
            }

            return 'year';
        } catch (\Exception $e) {
            return 'month';
        }
    }

    private function chartPeriodConfig(string $periodDivision): array
    {
        return match ($periodDivision) {
            'week' => [
                'group_by_period' => 'week',
                'date_format' => 'o-\\WW',
                'sql_format' => '%x-W%v',
                'label' => 'semana',
            ],
            'year' => [
                'group_by_period' => 'year',
                'date_format' => 'Y',
                'sql_format' => '%Y',
                'label' => 'anio',
            ],
            default => [
                'group_by_period' => 'month',
                'date_format' => 'Y-m',
                'sql_format' => '%Y-%m',
                'label' => 'mes',
            ],
        };
    }

    private function formatChartPeriodLabel(string $period, string $periodDivision): string
    {
        if ($periodDivision !== 'week') {
            return $period;
        }

        if (! preg_match('/^(\d{4})-W(\d{1,2})$/', $period, $matches)) {
            return $period;
        }

        try {
            $year = (int) $matches[1];
            $week = (int) $matches[2];
            $weekStart = Carbon::now()->setISODate($year, $week)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

            return $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y');
        } catch (\Exception $e) {
            return $period;
        }
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
        $statesPath = public_path('datas/json/Mexico_states.json');
        $citiesPath = public_path('datas/json/Mexico_cities.json');

        $states = is_file($statesPath)
            ? json_decode((string) file_get_contents($statesPath), true)
            : [];

        $cities = is_file($citiesPath)
            ? json_decode((string) file_get_contents($citiesPath), true)
            : [];

        return [
            'navigation' => $this->navigation(),
            'services' => Service::query()->select('id', 'name')->orderBy('name')->get(),
            'statusOptions' => DailyTrackingStatus::cases(),
            'contactMethodOptions' => DailyTrackingContactMethod::cases(),
            'customerTypeOptions' => DailyTrackingCustomerType::cases(),
            'quotedOptions' => DailyTrackingQuoted::cases(),
            'closedOptions' => DailyTrackingClosed::cases(),
            'invoiceOptions' => DailyTrackingInvoice::cases(),
            'paymentMethodOptions' => DailyTrackingPaymentMethod::cases(),
            'customerBranches' => Branch::query()->select('id', 'name')->orderBy('name')->get(),
            'customerServiceTypes' => ServiceType::query()->select('id', 'name')->orderBy('id')->get(),
            'customerContactMediumOptions' => $this->customerContactMediumOptions(),
            'states' => is_array($states) ? $states : [],
            'cities' => is_array($cities) ? $cities : [],
        ];
    }

    private function customerContactMediumOptions(): array
    {
        return [
            'whatsapp' => 'WhatsApp',
            'sms' => 'Mensaje SMS',
            'call' => 'Llamada telefonica',
            'email' => 'Correo electronico',
            'flyer' => 'Volanteo fisico',
        ];
    }

    private function generateCustomerCode(string $name): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z]/', '', substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3)));
        if ($prefix === '') {
            $prefix = 'CUS';
        }

        do {
            $code = $prefix . random_int(1000, 9999);
        } while (Customer::where('code', $code)->exists());

        return $code;
    }

    public function exportCharts(Request $request)
    {
        $chartDateRange = $this->parseDateRange((string) $request->input('date_range', ''));
        $chartType = $this->resolveChartType($request);
        $chartView = $this->resolveChartView($request);
        $periodDivision = $this->resolvePeriodDivision($request, $chartDateRange);
        $periodConfig = $this->chartPeriodConfig($periodDivision);

        $seriesColors = [
            'domestico' => ['border' => '#00BCD4', 'backgroundBar' => 'rgba(0,188,212,0.8)', 'backgroundLine' => 'rgba(0,188,212,0.2)'],
            'comercial' => ['border' => '#B74453', 'backgroundBar' => 'rgba(183,68,83,0.8)', 'backgroundLine' => 'rgba(183,68,83,0.2)'],
            'industrial' => ['border' => '#512A87', 'backgroundBar' => 'rgba(81,42,135,0.8)', 'backgroundLine' => 'rgba(81,42,135,0.2)'],
        ];

        $chartTitle = '';
        $chartSubtitle = '';
        $chartConfig = [];
        $analytics = [];

        if ($chartView === 'contact') {
            $rows = $this->buildFilteredQuery($request)
                ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
                ->selectRaw("SUM(CASE WHEN contact_method = 'google'   THEN 1 ELSE 0 END) as google")
                ->selectRaw("SUM(CASE WHEN contact_method = 'pagina'   THEN 1 ELSE 0 END) as pagina")
                ->selectRaw("SUM(CASE WHEN contact_method = 'llamada'  THEN 1 ELSE 0 END) as llamada")
                ->selectRaw("SUM(CASE WHEN contact_method = 'cambaceo' THEN 1 ELSE 0 END) as cambaceo")
                ->whereNotNull('created_at')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $labels = [];
            $google = [];
            $pagina = [];
            $llamada = [];
            $cambaceo = [];

            foreach ($rows as $row) {
                $labels[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
                $google[] = (int) $row->google;
                $pagina[] = (int) $row->pagina;
                $llamada[] = (int) $row->llamada;
                $cambaceo[] = (int) $row->cambaceo;
            }

            $methodTotals = [
                'Google' => array_sum($google),
                'Pagina web' => array_sum($pagina),
                'Llamada' => array_sum($llamada),
                'Cambaceo' => array_sum($cambaceo),
            ];
            arsort($methodTotals);
            $topMethod = array_key_first($methodTotals);
            $topMethodValue = $topMethod !== null ? $methodTotals[$topMethod] : 0;

            $chartTitle = 'Medios de contacto por periodo';
            $chartSubtitle = 'Analisis de origen de prospectos por division temporal.';
            $chartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Google', 'data' => $google, 'backgroundColor' => 'rgba(66,133,244,0.8)', 'borderColor' => '#4285F4', 'borderWidth' => 1],
                        ['label' => 'Pagina web', 'data' => $pagina, 'backgroundColor' => 'rgba(52,168,83,0.8)', 'borderColor' => '#34A853', 'borderWidth' => 1],
                        ['label' => 'Llamada', 'data' => $llamada, 'backgroundColor' => 'rgba(251,188,5,0.85)', 'borderColor' => '#FBBC05', 'borderWidth' => 1],
                        ['label' => 'Cambaceo', 'data' => $cambaceo, 'backgroundColor' => 'rgba(234,67,53,0.8)', 'borderColor' => '#EA4335', 'borderWidth' => 1],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => true]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ];

            $analytics = [
                ['label' => 'Periodos analizados', 'value' => (string) count($labels)],
                ['label' => 'Registros totales', 'value' => (string) array_sum($methodTotals)],
                ['label' => 'Medio principal', 'value' => ($topMethod ?? 'N/A') . ' (' . $topMethodValue . ')'],
            ];
        } elseif ($chartView === 'amounts') {
            $rows = $this->buildFilteredQuery($request)
                ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
                ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  THEN COALESCE(billed_amount, 0) ELSE 0 END) as domestico")
                ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  THEN COALESCE(billed_amount, 0) ELSE 0 END) as comercial")
                ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' THEN COALESCE(billed_amount, 0) ELSE 0 END) as industrial")
                ->whereNotNull('created_at')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $labels = [];
            $domestico = [];
            $comercial = [];
            $industrial = [];
            $totalsByPeriod = [];

            foreach ($rows as $row) {
                $labels[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
                $d = round((float) $row->domestico, 2);
                $c = round((float) $row->comercial, 2);
                $i = round((float) $row->industrial, 2);
                $domestico[] = $d;
                $comercial[] = $c;
                $industrial[] = $i;
                $totalsByPeriod[] = $d + $c + $i;
            }

            $chartKind = $chartType === 'line' ? 'line' : 'bar';
            $maxPeriodAmount = empty($totalsByPeriod) ? 0 : max($totalsByPeriod);
            $maxPeriodIndex = empty($totalsByPeriod) ? null : array_search($maxPeriodAmount, $totalsByPeriod, true);

            $chartTitle = 'Montos facturados por periodo y tipo de cliente';
            $chartSubtitle = 'Comparativo de facturacion por segmento de cliente.';
            $chartConfig = [
                'type' => $chartKind,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Domestico', 'data' => $domestico, 'backgroundColor' => $chartKind === 'line' ? $seriesColors['domestico']['backgroundLine'] : $seriesColors['domestico']['backgroundBar'], 'borderColor' => $seriesColors['domestico']['border'], 'borderWidth' => 2, 'fill' => $chartKind === 'line', 'tension' => 0.3],
                        ['label' => 'Comercial', 'data' => $comercial, 'backgroundColor' => $chartKind === 'line' ? $seriesColors['comercial']['backgroundLine'] : $seriesColors['comercial']['backgroundBar'], 'borderColor' => $seriesColors['comercial']['border'], 'borderWidth' => 2, 'fill' => $chartKind === 'line', 'tension' => 0.3],
                        ['label' => 'Industrial', 'data' => $industrial, 'backgroundColor' => $chartKind === 'line' ? $seriesColors['industrial']['backgroundLine'] : $seriesColors['industrial']['backgroundBar'], 'borderColor' => $seriesColors['industrial']['border'], 'borderWidth' => 2, 'fill' => $chartKind === 'line', 'tension' => 0.3],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => true]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ];

            $analytics = [
                ['label' => 'Periodos analizados', 'value' => (string) count($labels)],
                ['label' => 'Monto total facturado', 'value' => '$' . number_format(array_sum($totalsByPeriod), 2)],
                ['label' => 'Periodo con mayor facturacion', 'value' => $maxPeriodIndex !== null ? (($labels[$maxPeriodIndex] ?? 'N/A') . ' ($' . number_format($maxPeriodAmount, 2) . ')') : 'N/A'],
            ];
        } elseif ($chartView === 'clients') {
            $rows = $this->buildFilteredQuery($request)
                ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
                ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  THEN 1 ELSE 0 END) as domestico")
                ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  THEN 1 ELSE 0 END) as comercial")
                ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' THEN 1 ELSE 0 END) as industrial")
                ->whereNotNull('created_at')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $labels = [];
            $domestico = [];
            $comercial = [];
            $industrial = [];
            $totalsByPeriod = [];

            foreach ($rows as $row) {
                $labels[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);
                $d = (int) $row->domestico;
                $c = (int) $row->comercial;
                $i = (int) $row->industrial;
                $domestico[] = $d;
                $comercial[] = $c;
                $industrial[] = $i;
                $totalsByPeriod[] = $d + $c + $i;
            }

            $maxPeriodClients = empty($totalsByPeriod) ? 0 : max($totalsByPeriod);
            $maxPeriodIndex = empty($totalsByPeriod) ? null : array_search($maxPeriodClients, $totalsByPeriod, true);

            $chartTitle = 'Clientes ingresados por periodo y tipo';
            $chartSubtitle = 'Comportamiento de captacion por segmento de cliente.';
            $chartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Domestico', 'data' => $domestico, 'backgroundColor' => $seriesColors['domestico']['backgroundBar'], 'borderColor' => $seriesColors['domestico']['border'], 'borderWidth' => 1],
                        ['label' => 'Comercial', 'data' => $comercial, 'backgroundColor' => $seriesColors['comercial']['backgroundBar'], 'borderColor' => $seriesColors['comercial']['border'], 'borderWidth' => 1],
                        ['label' => 'Industrial', 'data' => $industrial, 'backgroundColor' => $seriesColors['industrial']['backgroundBar'], 'borderColor' => $seriesColors['industrial']['border'], 'borderWidth' => 1],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => true]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ];

            $analytics = [
                ['label' => 'Periodos analizados', 'value' => (string) count($labels)],
                ['label' => 'Clientes totales', 'value' => (string) array_sum($totalsByPeriod)],
                ['label' => 'Periodo con mayor captacion', 'value' => $maxPeriodIndex !== null ? (($labels[$maxPeriodIndex] ?? 'N/A') . ' (' . $maxPeriodClients . ')') : 'N/A'],
            ];
        } elseif ($chartView === 'services') {
            $topServiceIds = $this->buildFilteredQuery($request)
                ->whereNotNull('service_id')
                ->selectRaw('service_id, COUNT(*) as total')
                ->groupBy('service_id')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('service_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $periodLabels = [];
            $datasets = [];
            $serviceTotals = [];

            if (! empty($topServiceIds)) {
                $rows = $this->buildFilteredQuery($request)
                    ->whereIn('service_id', $topServiceIds)
                    ->whereNotNull('created_at')
                    ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
                    ->selectRaw('service_id, COUNT(*) as total')
                    ->groupBy('period', 'service_id')
                    ->orderBy('period')
                    ->get();

                $serviceNames = Service::query()->whereIn('id', $topServiceIds)->pluck('name', 'id');
                $periodSet = [];
                $servicePeriodCountMap = [];

                foreach ($rows as $row) {
                    $period = (string) $row->period;
                    $serviceId = (int) $row->service_id;
                    $periodSet[$period] = true;
                    $servicePeriodCountMap[$serviceId][$period] = (int) $row->total;
                    $serviceTotals[$serviceId] = ($serviceTotals[$serviceId] ?? 0) + (int) $row->total;
                }

                $rawPeriods = array_values(array_keys($periodSet));
                $periodIndexes = array_flip($rawPeriods);
                $periodLabels = array_map(fn (string $period) => $this->formatChartPeriodLabel($period, $periodDivision), $rawPeriods);

                $baseColors = [
                    '#2563EB', '#16A34A', '#DC2626', '#D97706', '#7C3AED',
                    '#0891B2', '#DB2777', '#4F46E5', '#65A30D', '#EA580C',
                ];
                $chartKind = $chartType === 'line' ? 'line' : 'bar';

                foreach (array_values($topServiceIds) as $index => $serviceId) {
                    $series = array_fill(0, count($rawPeriods), 0);
                    $countsByPeriod = $servicePeriodCountMap[$serviceId] ?? [];

                    foreach ($countsByPeriod as $period => $count) {
                        if (array_key_exists($period, $periodIndexes)) {
                            $series[$periodIndexes[$period]] = (int) $count;
                        }
                    }

                    $color = $baseColors[$index % count($baseColors)];
                    $datasets[] = [
                        'label' => (string) ($serviceNames[$serviceId] ?? ('Servicio #' . $serviceId)),
                        'data' => $series,
                        'borderColor' => $color,
                        'backgroundColor' => $chartKind === 'line' ? $color . '33' : $color . 'CC',
                        'borderWidth' => 2,
                        'fill' => $chartKind === 'line',
                        'tension' => 0.25,
                    ];
                }

                $chartConfig = [
                    'type' => $chartKind,
                    'data' => [
                        'labels' => $periodLabels,
                        'datasets' => $datasets,
                    ],
                    'options' => [
                        'plugins' => ['legend' => ['display' => true]],
                        'scales' => ['y' => ['beginAtZero' => true]],
                    ],
                ];
            }

            arsort($serviceTotals);
            $topServiceId = array_key_first($serviceTotals);
            $topServiceName = $topServiceId !== null
                ? (string) (Service::query()->where('id', (int) $topServiceId)->value('name') ?? ('Servicio #' . $topServiceId))
                : 'N/A';

            $chartTitle = 'Top 10 servicios por periodo';
            $chartSubtitle = 'Servicios con mayor actividad en el rango filtrado.';
            if (empty($chartConfig)) {
                $chartConfig = [
                    'type' => 'bar',
                    'data' => ['labels' => ['Sin datos'], 'datasets' => [['label' => 'Sin datos', 'data' => [0]]]],
                    'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true]]],
                ];
            }

            $analytics = [
                ['label' => 'Servicios en top', 'value' => (string) count($serviceTotals)],
                ['label' => 'Interacciones totales (top 10)', 'value' => (string) array_sum($serviceTotals)],
                ['label' => 'Servicio lider', 'value' => $topServiceName . ' (' . ($topServiceId !== null ? ($serviceTotals[$topServiceId] ?? 0) : 0) . ')'],
            ];
        } else {
            $rows = $this->buildFilteredQuery($request)
                ->selectRaw("DATE_FORMAT(created_at, '{$periodConfig['sql_format']}') as period")
                ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  AND quoted = 'yes' THEN 1 ELSE 0 END) as domestico_quoted")
                ->selectRaw("SUM(CASE WHEN customer_type = 'domestico'  AND closed = 'yes' THEN 1 ELSE 0 END) as domestico_closed")
                ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  AND quoted = 'yes' THEN 1 ELSE 0 END) as comercial_quoted")
                ->selectRaw("SUM(CASE WHEN customer_type = 'comercial'  AND closed = 'yes' THEN 1 ELSE 0 END) as comercial_closed")
                ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' AND quoted = 'yes' THEN 1 ELSE 0 END) as industrial_quoted")
                ->selectRaw("SUM(CASE WHEN customer_type = 'industrial' AND closed = 'yes' THEN 1 ELSE 0 END) as industrial_closed")
                ->whereNotNull('created_at')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $labels = [];
            $domestico = [];
            $comercial = [];
            $industrial = [];
            $promByPeriod = [];

            foreach ($rows as $row) {
                $labels[] = $this->formatChartPeriodLabel((string) $row->period, $periodDivision);

                $dq = (int) $row->domestico_quoted;
                $dc = (int) $row->domestico_closed;
                $cq = (int) $row->comercial_quoted;
                $cc = (int) $row->comercial_closed;
                $iq = (int) $row->industrial_quoted;
                $ic = (int) $row->industrial_closed;

                $dr = $dq > 0 ? round(($dc / $dq) * 100, 2) : 0;
                $cr = $cq > 0 ? round(($cc / $cq) * 100, 2) : 0;
                $ir = $iq > 0 ? round(($ic / $iq) * 100, 2) : 0;

                $domestico[] = $dr;
                $comercial[] = $cr;
                $industrial[] = $ir;
                $promByPeriod[] = round(($dr + $cr + $ir) / 3, 2);
            }

            $chartKind = $chartType === 'line' ? 'line' : 'bar';
            $maxProm = empty($promByPeriod) ? 0 : max($promByPeriod);
            $maxPromIndex = empty($promByPeriod) ? null : array_search($maxProm, $promByPeriod, true);

            $chartTitle = 'Tasa de conversion por periodo y tipo de cliente';
            $chartSubtitle = 'Porcentaje de cierres sobre cotizaciones generadas.';
            $chartConfig = [
                'type' => $chartKind,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Domestico (%)', 'data' => $domestico, 'backgroundColor' => $chartKind === 'line' ? $seriesColors['domestico']['backgroundLine'] : $seriesColors['domestico']['backgroundBar'], 'borderColor' => $seriesColors['domestico']['border'], 'borderWidth' => 2, 'fill' => $chartKind === 'line', 'tension' => 0.3],
                        ['label' => 'Comercial (%)', 'data' => $comercial, 'backgroundColor' => $chartKind === 'line' ? $seriesColors['comercial']['backgroundLine'] : $seriesColors['comercial']['backgroundBar'], 'borderColor' => $seriesColors['comercial']['border'], 'borderWidth' => 2, 'fill' => $chartKind === 'line', 'tension' => 0.3],
                        ['label' => 'Industrial (%)', 'data' => $industrial, 'backgroundColor' => $chartKind === 'line' ? $seriesColors['industrial']['backgroundLine'] : $seriesColors['industrial']['backgroundBar'], 'borderColor' => $seriesColors['industrial']['border'], 'borderWidth' => 2, 'fill' => $chartKind === 'line', 'tension' => 0.3],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => true]],
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'max' => 100,
                        ],
                    ],
                ],
            ];

            $analytics = [
                ['label' => 'Periodos analizados', 'value' => (string) count($labels)],
                ['label' => 'Promedio de conversion', 'value' => number_format(empty($promByPeriod) ? 0 : (array_sum($promByPeriod) / count($promByPeriod)), 2) . '%'],
                ['label' => 'Mejor periodo (promedio)', 'value' => $maxPromIndex !== null ? (($labels[$maxPromIndex] ?? 'N/A') . ' (' . number_format($maxProm, 2) . '%)') : 'N/A'],
            ];
        }

        $viewLabels = [
            'contact' => 'Medio de contacto',
            'amounts' => 'Montos facturados',
            'clients' => 'Clientes por periodo',
            'services' => 'Top 10 servicios',
            'conversion' => 'Tasa de conversion',
        ];

        $divisionLabels = [
            'week' => 'Semanal',
            'month' => 'Mensual',
            'year' => 'Anual',
        ];

        $pdfData = [
            'generatedAt' => now()->format('d/m/Y H:i'),
            'chartTitle' => $chartTitle,
            'chartSubtitle' => $chartSubtitle,
            'chartImage' => $this->generateQuickChartImage($chartConfig, 820, 280),
            'analytics' => $analytics,
            'analysisType' => $viewLabels[$chartView] ?? $chartView,
            'divisionLabel' => $divisionLabels[$periodDivision] ?? $periodDivision,
            'chartTypeLabel' => $chartType === 'line' ? 'Lineal' : 'Barras',
            'statusLabel' => (string) ($request->input('status') ?: 'Todos'),
            'dateRangeLabel' => $chartDateRange !== null
                ? Carbon::parse($chartDateRange['start'])->format('d/m/Y') . ' - ' . Carbon::parse($chartDateRange['end'])->format('d/m/Y')
                : 'Sin rango especificado',
        ];

        $pdf = Pdf::loadView('crm.daily-tracking.charts-pdf', $pdfData)->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('graficas-analisis-' . now()->format('Y-m-d-His') . '.pdf');
    }

    private function generateQuickChartImage(array $config, int $width = 900, int $height = 430): string
    {
        $chartConfig = json_encode($config);
        $width = max(300, $width);
        $height = max(200, $height);
        $url = 'https://quickchart.io/chart?c=' . urlencode((string) $chartConfig) . '&width=' . $width . '&height=' . $height . '&devicePixelRatio=2';

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

            Log::info('Daily tracking import started', [
                'user_id' => optional($request->user())->id,
                'has_excel_file' => $request->hasFile('excel_file'),
                'excel_file_name' => $file?->getClientOriginalName(),
                'excel_file_mime' => $file?->getMimeType(),
                'excel_file_size' => $file?->getSize(),
            ]);

            $filePath = $file->store('imports', 'local');

            $result = $importService->importFile(storage_path('app/' . $filePath));

            Log::info('Daily tracking import processed', [
                'user_id' => optional($request->user())->id,
                'file_path' => $filePath,
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? null,
            ]);

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
            Log::error('Daily tracking import failed', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage())
                ->withInput();
        }
    }
}
