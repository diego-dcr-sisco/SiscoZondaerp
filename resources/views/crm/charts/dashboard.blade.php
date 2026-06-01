@extends('layouts.app')
@section('content')
    @php
        use Carbon\Carbon;

        $selectedMetric = request('chart_metric');
        $hasChartSearch = request()->has('chart_metric');
        $dateRange = request('date_range');
        $chartYear = Carbon::now()->year;
        $periodDivisionLabels = [
            'weekly' => 'Semanal',
            'monthly' => 'Mensual',
            'bimonthly' => 'Bimestral',
            'quarterly' => 'Trimestral',
            'four_monthly' => 'Cuatrimestral',
            'semiannual' => 'Semestral',
            'annual' => 'Anual',
        ];
        $periodDivisionLabel = $periodDivisionLabels[request('period_division', 'monthly')] ?? 'Mensual';
        $customerTypeLabels = [
            '' => 'Todos',
            '1' => 'Domestico',
            '2' => 'Comercial',
            '3' => 'Industrial',
        ];
        $customerTypeLabel = $customerTypeLabels[request('customer_type', '')] ?? 'Todos';

        if ($dateRange && preg_match('/\d{2}\/\d{2}\/(\d{4})/', $dateRange, $matches)) {
            $chartYear = (int) $matches[1];
        }

        $chartDescriptions = [
            'new_customers' => [
                'title' => 'Nuevos clientes',
                'description' => '',
            ],
            'new_leads' => [
                'title' => 'Nuevos leads',
                'description' => 'La grafica muestra los leads captados por periodo y tipo de cliente. La tabla resume los totales usados para comparar el comportamiento.',
            ],
            'services_amount' => [
                'title' => 'Cantidad de servicios',
                'description' => 'La grafica muestra los servicios registrados por periodo y tipo de cliente. La tabla concentra los totales para revisar el volumen atendido.',
            ],
            'scheduled_trackings' => [
                'title' => 'Cantidad de seguimientos programados',
                'description' => 'La grafica muestra los seguimientos programados por periodo y tipo de cliente. La tabla presenta los totales para validar la carga de seguimiento.',
            ],
            'pest_presence' => [
                'title' => 'Presencia de plagas',
                'description' => 'La grafica muestra las plagas con mayor presencia en el periodo seleccionado. La tabla desglosa sus apariciones por cada division periodica.',
            ],
        ];

        $chartDescription = $chartDescriptions[$selectedMetric] ?? [
            'title' => 'Grafica seleccionada',
            'description' => 'Selecciona los filtros para generar la informacion correspondiente.',
        ];

        $reportChartConfig = $hasChartSearch ? [
            'title' => $chartDescription['title'],
            'description' => $chartDescription['description'],
            'canvasId' => $selectedMetric === 'pest_presence' ? 'pestPresenceChart' : 'crmMetricSearchChart',
            'tableSelector' => $selectedMetric === 'pest_presence' ? '#pestPresenceTableHead, #pestPresenceTableBody' : '#crmMetricSummaryTable',
        ] : null;
    @endphp
    
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    @include('components.page-header', [
            'title' => 'Estadisticas, graficas e indicadores del cliente',
            'icon' => 'bi-graph-up-arrow',
            'backRoute' => 'javascript:history.back()',
            'actionText' => 'Generar Reporte',
            'actionIcon' => 'bi-file-pdf-fill',
            'actionClass' => 'btn-dark',
            'actionButtonId' => 'generatePdfBtn',
            'actionButtonContentId' => 'btnContent',
            'actionButtonLoadingId' => 'btnLoading',
            'actionLoadingText' => 'Generando reporte...',
        ])
        
    <div class="container-fluid">
        <div class="my-3">
            <form action="{{ url()->current() }}" method="GET">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                            </h5>
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target=".crm-chart-search-collapse" aria-expanded="true"
                                aria-controls="crmChartSearchFilters crmChartSearchFooter">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body collapse show crm-chart-search-collapse" id="crmChartSearchFilters">
                        <div class="row g-3 mb-3">
                            <div class="col-lg-4 col-md-6 col-12">
                                <label for="chart_metric" class="form-label is-required">Grafica a generar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-bar-chart-fill"></i></span>
                                    <select class="form-select" id="chart_metric" name="chart_metric" required>
                                        <option value="new_customers" {{ request('chart_metric') == 'new_customers' ? 'selected' : '' }}>
                                            Nuevos clientes
                                        </option>
                                        <option value="new_leads" {{ request('chart_metric') == 'new_leads' ? 'selected' : '' }}>
                                            Nuevos leads
                                        </option>
                                        <option value="services_amount" {{ request('chart_metric') == 'services_amount' ? 'selected' : '' }}>
                                            Cantidad de servicios
                                        </option>
                                        <option value="scheduled_trackings" {{ request('chart_metric') == 'scheduled_trackings' ? 'selected' : '' }}>
                                            Cantidad de seguimientos programados
                                        </option>
                                        <option value="pest_presence" {{ request('chart_metric') == 'pest_presence' ? 'selected' : '' }}>
                                            Presencia de plagas
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-12">
                                <label for="date-range" class="form-label is-required">Rango de fechas</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-range-fill"></i></span>
                                    <input type="text" class="form-control" id="date-range" name="date_range"
                                        value="{{ request('date_range') }}" placeholder="Selecciona un rango" required>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-12">
                                <label for="period_division" class="form-label is-required">Division periodica</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <select class="form-select" id="period_division" name="period_division" required>
                                        <option value="weekly" {{ request('period_division') == 'weekly' ? 'selected' : '' }}>
                                            Semanal
                                        </option>
                                        <option value="monthly" {{ request('period_division', 'monthly') == 'monthly' ? 'selected' : '' }}>
                                            Mensual
                                        </option>
                                        <option value="bimonthly" {{ request('period_division') == 'bimonthly' ? 'selected' : '' }}>
                                            Bimestral
                                        </option>
                                        <option value="quarterly" {{ request('period_division') == 'quarterly' ? 'selected' : '' }}>
                                            Trimestral
                                        </option>
                                        <option value="four_monthly" {{ request('period_division') == 'four_monthly' ? 'selected' : '' }}>
                                            Cuatrimestral
                                        </option>
                                        <option value="semiannual" {{ request('period_division') == 'semiannual' ? 'selected' : '' }}>
                                            Semestral
                                        </option>
                                        <option value="annual" {{ request('period_division') == 'annual' ? 'selected' : '' }}>
                                            Anual
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-12">
                                <label for="chart_type" class="form-label is-required">Tipo de grafica</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-pie-chart-fill"></i></span>
                                    <select class="form-select" id="chart_type" name="chart_type" required>
                                        <option value="bar" {{ request('chart_type', 'bar') == 'bar' ? 'selected' : '' }}>
                                            Barra
                                        </option>
                                        <option value="line" {{ request('chart_type') == 'line' ? 'selected' : '' }}>
                                            Linea
                                        </option>
                                        <option value="doughnut" {{ request('chart_type') == 'doughnut' ? 'selected' : '' }}>
                                            Dona / Doughnut
                                        </option>
                                        <option value="pie" {{ request('chart_type') == 'pie' ? 'selected' : '' }}>
                                            Pie
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-12">
                                <label for="customer_type" class="form-label">Tipo de clientes</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                                    <select class="form-select" id="customer_type" name="customer_type">
                                        <option value="">Todos</option>
                                        <option value="1" {{ request('customer_type') == '1' ? 'selected' : '' }}>
                                            Domestico
                                        </option>
                                        <option value="2" {{ request('customer_type') == '2' ? 'selected' : '' }}>
                                            Comercial
                                        </option>
                                        <option value="3" {{ request('customer_type') == '3' ? 'selected' : '' }}>
                                            Industrial
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer collapse show crm-chart-search-collapse" id="crmChartSearchFooter">
                        <div class="row justify-content-end">
                            <div class="col-lg-1 col-md-2 col-6">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-lg-1 col-md-2 col-6">
                                <a href="{{ url()->current() }}" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if ($hasChartSearch)
            <div class="mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <h5 class="card-title fw-bold mb-1">{{ $chartDescription['title'] }}</h5>
                        @if ($chartDescription['description'])
                            <p class="card-text text-muted mb-0">{{ $chartDescription['description'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($hasChartSearch && in_array($selectedMetric, ['new_customers', 'new_leads', 'services_amount', 'scheduled_trackings']))
            <div class="row">
                <div class="col-lg-6 col-12 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">
                                @if ($selectedMetric === 'new_leads')
                                    Nuevos leads
                                @elseif ($selectedMetric === 'services_amount')
                                    Cantidad de servicios
                                @elseif ($selectedMetric === 'scheduled_trackings')
                                    Cantidad de seguimientos programados
                                @else
                                    Nuevos clientes
                                @endif
                            </h5>
                            <div id="crmMetricSearchChartContainer" class="position-relative">
                                <div id="crmMetricSearchSpinner" class="d-none"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                                <canvas id="crmMetricSearchChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">
                                @if ($selectedMetric === 'new_leads')
                                    Leads por tipo
                                @elseif ($selectedMetric === 'services_amount')
                                    Servicios por tipo
                                @elseif ($selectedMetric === 'scheduled_trackings')
                                    Seguimientos por tipo
                                @else
                                    Clientes por tipo
                                @endif
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody id="crmMetricSummaryTable">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Cargando...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-end" id="crmMetricSummaryTotal">0</th>
                                            <th class="text-end">100%</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($hasChartSearch && $selectedMetric === 'pest_presence')
            <div class="row">
                <div class="col-lg-6 col-12 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Plagas con mayor presencia</h5>
                            <div id="pestPresenceChartContainer" class="position-relative">
                                <div id="pestPresenceSpinner" class="d-none"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                                <canvas id="pestPresenceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Presencia por periodo</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped mb-0">
                                    <thead id="pestPresenceTableHead">
                                        <tr>
                                            <th>Plaga</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pestPresenceTableBody">
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">Cargando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($hasChartSearch)
            <div class="row pt-3">
                <div class="col-12">
                    <div class="alert alert-warning mb-0 fw-bold">
                        * La grafica seleccionada aun no tiene una vista configurada.
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- jsPDF Library -->
    @if ($hasChartSearch)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>
    <script>
        @if ($hasChartSearch && in_array($selectedMetric, ['new_customers', 'new_leads', 'services_amount', 'scheduled_trackings']))
            const selectedCrmMetric = @json($selectedMetric);
            const selectedCrmMetricYear = @json($chartYear);
            const selectedCrmMetricChartType = @json(request('chart_type', 'bar'));
            const selectedCustomerType = @json(request('customer_type'));
            const selectedDateRange = @json(request('date_range'));
            const selectedPeriodDivision = @json(request('period_division', 'monthly'));
            let crmMetricSearchChart;

            function allowedChartType(type) {
                return ['bar', 'line', 'doughnut', 'pie'].includes(type) ? type : 'bar';
            }

            function crmMetricLabels() {
                if (selectedCrmMetric === 'new_leads') {
                    return {
                        singular: 'lead',
                        plural: 'leads',
                        chartByPeriod: 'Nuevos leads',
                        chartByType: 'Nuevos leads por tipo'
                    };
                }

                if (selectedCrmMetric === 'services_amount') {
                    return {
                        singular: 'servicio',
                        plural: 'servicios',
                        chartByPeriod: 'Cantidad de servicios',
                        chartByType: 'Servicios por tipo'
                    };
                }

                if (selectedCrmMetric === 'scheduled_trackings') {
                    return {
                        singular: 'seguimiento',
                        plural: 'seguimientos',
                        chartByPeriod: 'Seguimientos programados',
                        chartByType: 'Seguimientos por tipo'
                    };
                }

                return {
                    singular: 'cliente',
                    plural: 'clientes',
                    chartByPeriod: 'Nuevos clientes',
                    chartByType: 'Nuevos clientes por tipo'
                };
            }

            function periodDivisionLabel(division) {
                const labels = {
                    weekly: 'semanal',
                    monthly: 'mensual',
                    bimonthly: 'bimestral',
                    quarterly: 'trimestral',
                    four_monthly: 'cuatrimestral',
                    semiannual: 'semestral',
                    annual: 'anual'
                };

                return labels[division] || 'mensual';
            }

            function crmMetricDatasets(data, chartType) {
                const series = [
                    {
                        key: 'domestics',
                        serviceTypeId: '1',
                        label: 'Domesticos',
                        color: '#0A2986',
                        backgroundColor: chartType === 'line' ? 'rgba(10, 41, 134, 0.2)' : '#0A2986'
                    },
                    {
                        key: 'comercials',
                        serviceTypeId: '2',
                        label: 'Comerciales',
                        color: '#512A87',
                        backgroundColor: chartType === 'line' ? 'rgba(81, 42, 135, 0.2)' : '#512A87'
                    },
                    {
                        key: 'industrials',
                        serviceTypeId: '3',
                        label: 'Industrial/Planta',
                        color: '#DE523B',
                        backgroundColor: chartType === 'line' ? 'rgba(222, 82, 59, 0.2)' : '#DE523B'
                    }
                ];

                return series.filter(item => !selectedCustomerType || item.serviceTypeId === selectedCustomerType)
                    .map(item => ({
                        label: item.label,
                        data: data[item.key] || [],
                        borderColor: item.color,
                        backgroundColor: item.backgroundColor,
                        borderWidth: 2,
                        fill: chartType === 'line',
                        tension: 0.35
                    }));
            }

            function renderCrmMetricSummary(data) {
                const rows = [
                    { label: 'Domesticos', serviceTypeId: '1', total: (data.domestics || []).reduce((sum, value) => sum + Number(value || 0), 0) },
                    { label: 'Comerciales', serviceTypeId: '2', total: (data.comercials || []).reduce((sum, value) => sum + Number(value || 0), 0) },
                    { label: 'Industrial/Planta', serviceTypeId: '3', total: (data.industrials || []).reduce((sum, value) => sum + Number(value || 0), 0) },
                ].filter(item => !selectedCustomerType || item.serviceTypeId === selectedCustomerType);

                const total = rows.reduce((sum, item) => sum + item.total, 0);
                const tableBody = document.getElementById('crmMetricSummaryTable');
                const totalCell = document.getElementById('crmMetricSummaryTotal');

                if (!tableBody || !totalCell) return;

                tableBody.innerHTML = rows.map(item => {
                    const percentage = total > 0 ? ((item.total / total) * 100).toFixed(1) : '0.0';
                    return `
                        <tr>
                            <td>${item.label}</td>
                            <td class="text-end">${item.total}</td>
                            <td class="text-end">${percentage}%</td>
                        </tr>
                    `;
                }).join('');

                totalCell.textContent = total;
            }

            function renderCrmMetricSearchChart(data) {
                const ctx = document.getElementById('crmMetricSearchChart');
                if (!ctx) return;

                const labels = crmMetricLabels();
                const chartType = allowedChartType(selectedCrmMetricChartType);
                const datasets = crmMetricDatasets(data, chartType);

                if (crmMetricSearchChart) {
                    crmMetricSearchChart.destroy();
                }

                if (chartType === 'doughnut' || chartType === 'pie') {
                    const totals = datasets.map(dataset => dataset.data.reduce((sum, value) => sum + Number(value || 0), 0));
                    crmMetricSearchChart = new Chart(ctx, {
                        type: chartType,
                        data: {
                            labels: datasets.map(dataset => dataset.label),
                            datasets: [{
                                label: labels.chartByType,
                                data: totals,
                                backgroundColor: datasets.map(dataset => dataset.borderColor),
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' },
                                title: { display: true, text: labels.chartByType }
                            }
                        }
                    });
                    return;
                }

                crmMetricSearchChart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: data.labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            title: {
                                display: true,
                                text: `${labels.chartByPeriod} (${periodDivisionLabel(data.period_division)})`
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            function fetchCrmMetricSearchData() {
                const spinner = document.getElementById('crmMetricSearchSpinner');
                const endpoints = {
                    new_customers: '/crm/chart/customers-by-month',
                    new_leads: '/crm/chart/leads-by-month',
                    services_amount: '/crm/chart/services-by-type',
                    scheduled_trackings: '/crm/chart/trackings-by-month'
                };
                const endpoint = endpoints[selectedCrmMetric] || endpoints.new_customers;
                const params = new URLSearchParams({
                    year: selectedCrmMetricYear,
                    period_division: selectedPeriodDivision
                });

                if (selectedDateRange) {
                    params.append('date_range', selectedDateRange);
                }

                if (spinner) spinner.classList.remove('d-none');

                fetch(`${endpoint}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        renderCrmMetricSearchChart(data);
                        renderCrmMetricSummary(data);
                    })
                    .finally(() => {
                        if (spinner) spinner.classList.add('d-none');
                    });
            }

            document.addEventListener('DOMContentLoaded', fetchCrmMetricSearchData);
        @endif

        @if ($hasChartSearch && $selectedMetric === 'pest_presence')
            const selectedPestPresenceYear = @json($chartYear);
            const selectedPestPresenceChartType = @json(request('chart_type', 'bar'));
            const selectedPestPresenceDateRange = @json(request('date_range'));
            const selectedPestPresencePeriodDivision = @json(request('period_division', 'monthly'));
            let pestPresenceChart;

            function pestPresenceChartType(type) {
                return ['bar', 'line', 'doughnut', 'pie'].includes(type) ? type : 'bar';
            }

            function renderPestPresenceTable(data) {
                const head = document.getElementById('pestPresenceTableHead');
                const body = document.getElementById('pestPresenceTableBody');

                if (!head || !body) return;

                const periodLabels = data.period_labels || [];
                head.innerHTML = `
                    <tr>
                        <th>Plaga</th>
                        <th class="text-end">Total</th>
                        ${periodLabels.map(label => `<th class="text-end">${label}</th>`).join('')}
                    </tr>
                `;

                if (!data.rows || data.rows.length === 0) {
                    body.innerHTML = `<tr><td colspan="${periodLabels.length + 2}" class="text-center text-muted">Sin datos para mostrar.</td></tr>`;
                    return;
                }

                body.innerHTML = data.rows.map(row => `
                    <tr>
                        <td>${row.name}</td>
                        <td class="text-end fw-bold">${row.total}</td>
                        ${(row.periods || []).map(value => `<td class="text-end">${value}</td>`).join('')}
                    </tr>
                `).join('');
            }

            function renderPestPresenceChart(data) {
                const ctx = document.getElementById('pestPresenceChart');
                if (!ctx) return;

                const chartType = pestPresenceChartType(selectedPestPresenceChartType);
                const colors = ['#012640', '#02265A', '#0A2986', '#512A87', '#773774', '#B74453', '#DE523B', '#2563EB', '#16A34A', '#D97706'];

                if (pestPresenceChart) {
                    pestPresenceChart.destroy();
                }

                pestPresenceChart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'Presencia',
                            data: data.data || [],
                            backgroundColor: chartType === 'line' ? 'rgba(81, 42, 135, 0.2)' : colors,
                            borderColor: chartType === 'line' ? '#512A87' : colors,
                            borderWidth: 2,
                            fill: chartType === 'line',
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: true, text: 'Top plagas con mayor presencia' }
                        },
                        scales: chartType === 'doughnut' || chartType === 'pie'
                            ? {}
                            : { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });
            }

            function fetchPestPresenceData() {
                const spinner = document.getElementById('pestPresenceSpinner');
                const params = new URLSearchParams({
                    year: selectedPestPresenceYear,
                    period_division: selectedPestPresencePeriodDivision
                });

                if (selectedPestPresenceDateRange) {
                    params.append('date_range', selectedPestPresenceDateRange);
                }

                if (spinner) spinner.classList.remove('d-none');

                fetch(`/crm/chart/pests-by-customer?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        renderPestPresenceChart(data);
                        renderPestPresenceTable(data);
                    })
                    .finally(() => {
                        if (spinner) spinner.classList.add('d-none');
                    });
            }

            document.addEventListener('DOMContentLoaded', fetchPestPresenceData);
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ !== 'undefined' && $.fn.daterangepicker) {
                $('#date-range').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        format: 'DD/MM/YYYY',
                        separator: ' - ',
                        applyLabel: 'Aplicar',
                        cancelLabel: 'Limpiar',
                        fromLabel: 'Desde',
                        toLabel: 'Hasta',
                        customRangeLabel: 'Personalizado',
                        weekLabel: 'S',
                        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                        monthNames: [
                            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                        ],
                        firstDay: 1
                    }
                });

                $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                });

                $('#date-range').on('cancel.daterangepicker', function() {
                    $(this).val('');
                });
            }
        });

        // Función para cargar imagen como base64
        function loadImageAsBase64(url) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                };
                img.onerror = () => resolve(null);
                img.src = url;
            });
        }

        // Función para generar análisis descriptivo de las gráficas
        function generateChartAnalysis(chartId, chartTitle) {
            const canvas = document.getElementById(chartId);
            if (!canvas) return '';

            // Obtener la instancia del chart
            const chartInstance = Chart.getChart(canvas);
            if (!chartInstance || !chartInstance.data) return '';

            const datasets = chartInstance.data.datasets;
            const labels = chartInstance.data.labels;

            if (!datasets || datasets.length === 0) return '';

            // Análisis para gráficas de línea/barra con series temporales
            if (chartInstance.config.type === 'line' || chartInstance.config.type === 'bar') {
                // Sumar todos los datasets para obtener totales por periodo
                const totals = labels.map((label, index) => {
                    return datasets.reduce((sum, dataset) => {
                        const value = dataset.data[index] || 0;
                        return sum + value;
                    }, 0);
                });

                const total = totals.reduce((a, b) => a + b, 0);
                const avg = total / totals.length;

                // Encontrar máximo y mínimo
                const maxValue = Math.max(...totals);
                const minValue = Math.min(...totals);
                const maxIndex = totals.indexOf(maxValue);
                const minIndex = totals.indexOf(minValue);
                const maxLabel = labels[maxIndex];
                const minLabel = labels[minIndex];

                // Calcular tendencia
                let trend = 'estable';
                let trendText = 'estable';
                let increases = 0, decreases = 0;
                for (let i = 1; i < totals.length; i++) {
                    if (totals[i] > totals[i - 1]) increases++;
                    else if (totals[i] < totals[i - 1]) decreases++;
                }
                if (increases > decreases * 1.5) {
                    trend = 'creciente';
                    trendText = 'al alza';
                } else if (decreases > increases * 1.5) {
                    trend = 'descendente';
                    trendText = 'a la baja';
                }

                // Variación entre último y penúltimo periodo con datos
                let variationText = '';
                const nonZeroIndices = totals.map((v, i) => v > 0 ? i : -1).filter(i => i !== -1);
                if (nonZeroIndices.length >= 2) {
                    const lastIndex = nonZeroIndices[nonZeroIndices.length - 1];
                    const prevIndex = nonZeroIndices[nonZeroIndices.length - 2];
                    const lastValue = totals[lastIndex];
                    const prevValue = totals[prevIndex];
                    
                    if (prevValue > 0) {
                        const variation = ((lastValue - prevValue) / prevValue * 100).toFixed(1);
                        const changeType = variation > 0 ? 'incremento' : 'disminución';
                        variationText = `En comparación con ese mes, se observa una ${changeType} del ${Math.abs(variation)}%. `;
                    }
                }

                // Generar insight
                let insight = '';
                if (maxValue > avg * 1.5) {
                    insight = `Asimismo, resalta un pico importante en ${maxLabel.toLowerCase()} que supera el promedio en un ${((maxValue / avg - 1) * 100).toFixed(0)}%.`;
                } else if (trend === 'creciente') {
                    insight = 'La tendencia general muestra crecimiento sostenido.';
                } else if (trend === 'descendente') {
                    insight = 'Se observa una tendencia a la baja que requiere atención.';
                } else {
                    insight = 'Los valores se mantienen relativamente estables.';
                }

                const minText = minValue === 0 ? 'sin registros' : `${minValue} ${minValue === 1 ? 'registro' : 'registros'}`;
                        return `La grafica resume el comportamiento por periodo y permite comparar los registros entre las categorias evaluadas. El punto mas alto se encuentra en ${maxLabel.toLowerCase()}, con ${maxValue} ${maxValue === 1 ? 'registro' : 'registros'}, mientras que el menor valor corresponde a ${minLabel.toLowerCase()}, ${minText}. ${variationText}${insight} La tabla de datos incluida en el reporte muestra los totales que respaldan esta lectura.`;
            }

            // Análisis para gráficas donut/pie
            if (chartInstance.config.type === 'doughnut' || chartInstance.config.type === 'pie') {
                const data = datasets[0].data.map(v => Number(v) || 0); // Convertir a números
                const total = data.reduce((a, b) => a + b, 0);
                
                if (total === 0) return 'No se registraron datos para el periodo seleccionado.';

                const maxValue = Math.max(...data);
                const maxIndex = data.indexOf(maxValue);
                const maxLabel = labels[maxIndex] || 'N/A';
                const percentage = ((maxValue / total) * 100).toFixed(1);

                // Encontrar segundo lugar usando un enfoque diferente
                const dataWithIndices = data.map((value, index) => ({ value, index }))
                    .sort((a, b) => b.value - a.value);
                
                const secondItem = dataWithIndices[1] || dataWithIndices[0];
                const secondValue = secondItem.value;
                const secondIndex = secondItem.index;
                const secondLabel = labels[secondIndex] || 'N/A';
                const secondPercentage = total > 0 ? ((secondValue / total) * 100).toFixed(1) : '0.0';

                // Calcular distribución
                let distribution = 'equilibrada';
                const maxPercent = parseFloat(percentage);
                if (maxPercent > 50) distribution = 'concentrada';
                else if (maxPercent < 25) distribution = 'diversificada';

                return `La grafica muestra la distribucion proporcional de los registros del periodo seleccionado. ${maxLabel} concentra la mayor presencia con ${maxValue} ${maxValue === 1 ? 'registro' : 'registros'} (${percentage}% del total), seguido por ${secondLabel} con ${secondValue} ${secondValue === 1 ? 'registro' : 'registros'} (${secondPercentage}%). La tabla de datos incluida en el reporte permite revisar el detalle numerico de cada categoria.`;
            }

            return '';
        }

        function addPdfSectionTitle(pdf, title, x, y, width) {
            pdf.setFillColor(1, 38, 64);
            pdf.roundedRect(x, y - 5, 2.5, 8, 1, 1, 'F');
            pdf.setFontSize(13);
            pdf.setFont(undefined, 'bold');
            pdf.setTextColor(1, 38, 64);
            pdf.text(title, x + 5, y);
            pdf.setDrawColor(225, 230, 236);
            pdf.setLineWidth(0.2);
            pdf.line(x, y + 4, x + width, y + 4);
            return y + 11;
        }

        function addPdfInfoItem(pdf, label, value, x, y, width) {
            pdf.setFillColor(247, 249, 252);
            pdf.setDrawColor(225, 230, 236);
            pdf.roundedRect(x, y, width, 10, 1.5, 1.5, 'FD');
            pdf.setFontSize(6);
            pdf.setFont(undefined, 'bold');
            pdf.setTextColor(90, 100, 115);
            pdf.text(label.toUpperCase(), x + 3, y + 3.8);
            pdf.setFontSize(7.5);
            pdf.setFont(undefined, 'normal');
            pdf.setTextColor(30, 41, 59);
            pdf.text(String(value), x + 3, y + 7.8, { maxWidth: width - 6 });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const generatePdfBtn = document.getElementById('generatePdfBtn');
            const btnContent = document.getElementById('btnContent');
            const btnLoading = document.getElementById('btnLoading');

            if (generatePdfBtn) {
                generatePdfBtn.addEventListener('click', async function() {
                    generatePdfBtn.disabled = true;
                    btnContent.style.display = 'none';
                    btnLoading.style.display = 'inline-block';

                    try {
                        // Cargar el logo
                        const logoData = await loadImageAsBase64('/images/logo.png');

                        // Esperar a que todas las gráficas estén renderizadas
                        await new Promise(resolve => setTimeout(resolve, 1000));

                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF('p', 'mm', 'letter');

                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const margin = 15;
                        const contentWidth = pageWidth - (margin * 2);

                        let currentY = margin;
                        const brandColor = [1, 38, 64];
                        const accentColor = [10, 41, 134];
                        const mutedText = [90, 100, 115];

                        // Header
                        const headerStartY = 10;
                        pdf.setFillColor(...brandColor);
                        pdf.rect(0, 0, pageWidth, 35, 'F');
                        pdf.setFillColor(...accentColor);
                        pdf.rect(0, 33, pageWidth, 2, 'F');

                        if (logoData) {
                            try {
                                pdf.setFillColor(255, 255, 255);
                                pdf.roundedRect(pageWidth - margin - 31, headerStartY - 2, 31, 18, 2, 2, 'F');
                                pdf.addImage(logoData, 'PNG', pageWidth - margin - 27, headerStartY + 1, 23, 12);
                            } catch (error) {
                                //console.error('Error agregando logo:', error);
                            }
                        }

                        pdf.setTextColor(255, 255, 255);
                        pdf.setFontSize(16);
                        pdf.setFont(undefined, 'bold');
                        pdf.text('Reporte CRM', margin, headerStartY + 4);

                        pdf.setFontSize(9);
                        pdf.setFont(undefined, 'normal');
                        pdf.setTextColor(210, 220, 235);
                        pdf.text('Estadisticas, graficas e indicadores del cliente', margin, headerStartY + 10);
                        pdf.text('SISCO ZONDA ERP', margin, headerStartY + 16);

                        currentY = headerStartY + 30;

                        const reportDate = new Date().toLocaleString('es-MX');
                        const infoGap = 2;
                        const infoWidth = (contentWidth - infoGap) / 2;
                        addPdfInfoItem(pdf, 'Fecha de generacion', reportDate, margin, currentY, infoWidth);
                        addPdfInfoItem(pdf, 'Usuario', @json(auth()->user()->name ?? 'Sistema'), margin + infoWidth + infoGap, currentY, infoWidth);
                        currentY += 12;
                        addPdfInfoItem(pdf, 'Periodo', @json(request('date_range') ?: 'Sin rango seleccionado'), margin, currentY, infoWidth);
                        addPdfInfoItem(pdf, 'Division', @json($periodDivisionLabel), margin + infoWidth + infoGap, currentY, infoWidth);
                        currentY += 12;
                        addPdfInfoItem(pdf, 'Tipo de cliente', @json($customerTypeLabel), margin, currentY, infoWidth);
                        addPdfInfoItem(pdf, 'Grafica', @json($chartDescription['title']), margin + infoWidth + infoGap, currentY, infoWidth);
                        currentY += 25;

                        const reportChart = @json($reportChartConfig);

                        if (!reportChart) {
                            pdf.setFontSize(11);
                            pdf.setTextColor(160, 80, 0);
                            pdf.text('No hay una busqueda generada para exportar.', margin, currentY);
                            currentY += 10;
                        } else {
                            const canvas = document.getElementById(reportChart.canvasId);

                            currentY = addPdfSectionTitle(pdf, reportChart.title, margin, currentY, contentWidth);

                            if (reportChart.description) {
                                pdf.setFontSize(9);
                                pdf.setFont(undefined, 'normal');
                                pdf.setTextColor(...mutedText);
                                const descriptionLines = pdf.splitTextToSize(reportChart.description, contentWidth - 10);
                                const descriptionHeight = descriptionLines.length * 4 + 8;
                                pdf.setFillColor(248, 250, 252);
                                pdf.setDrawColor(226, 232, 240);
                                pdf.roundedRect(margin, currentY - 5, contentWidth, descriptionHeight, 2, 2, 'FD');
                                pdf.text(descriptionLines, margin + 5, currentY);
                                currentY += descriptionHeight + 4;
                            }

                            if (canvas) {
                                const analysis = generateChartAnalysis(reportChart.canvasId, reportChart.title);
                                if (analysis) {
                                    pdf.setFontSize(9);
                                    pdf.setFont(undefined, 'normal');
                                    pdf.setTextColor(30, 41, 59);
                                    const lines = pdf.splitTextToSize(analysis, contentWidth - 12);
                                    const textHeight = lines.length * 4 + 8;
                                    pdf.setFillColor(241, 245, 249);
                                    pdf.setDrawColor(226, 232, 240);
                                    pdf.roundedRect(margin, currentY - 5, contentWidth, textHeight, 2, 2, 'FD');
                                    pdf.setFillColor(...accentColor);
                                    pdf.roundedRect(margin, currentY - 5, 2.5, textHeight, 1, 1, 'F');
                                    pdf.text(lines, margin + 7, currentY);
                                    currentY += textHeight + 6;
                                }

                                try {
                                    const imgData = canvas.toDataURL('image/png', 1.0);
                                    const imgHeight = (canvas.height * contentWidth) / canvas.width;
                                    const finalHeight = Math.min(imgHeight, 90);
                                    const finalWidth = (canvas.width * finalHeight) / canvas.height;
                                    const chartX = margin + ((contentWidth - finalWidth) / 2);

                                    if (currentY > pageHeight - finalHeight - 20) {
                                        pdf.addPage();
                                        currentY = margin;
                                    }

                                    pdf.setFillColor(255, 255, 255);
                                    pdf.setDrawColor(226, 232, 240);
                                    pdf.roundedRect(chartX - 3, currentY - 3, finalWidth + 6, finalHeight + 6, 2, 2, 'FD');
                                    pdf.addImage(imgData, 'PNG', chartX, currentY, finalWidth, finalHeight);
                                    currentY += finalHeight + 12;
                                } catch (error) {
                                    // No se pudo exportar la grafica.
                                }
                            }

                            const table = reportChart.canvasId === 'pestPresenceChart'
                                ? document.querySelector('#pestPresenceTableHead')?.closest('table')
                                : document.querySelector('#crmMetricSummaryTable')?.closest('table');

                            if (table && pdf.autoTable) {
                                if (currentY > pageHeight - 50) {
                                    pdf.addPage();
                                    currentY = margin;
                                }

                                currentY = addPdfSectionTitle(pdf, 'Tabla de datos', margin, currentY, contentWidth);

                                pdf.setFontSize(8);
                                pdf.setFont(undefined, 'normal');
                                pdf.setTextColor(...mutedText);
                                const tableDescription = pdf.splitTextToSize(
                                    'Detalle numerico utilizado para construir la grafica y validar los totales del reporte.',
                                    contentWidth
                                );
                                pdf.text(tableDescription, margin, currentY);
                                currentY += tableDescription.length * 4 + 3;

                                pdf.autoTable({
                                    html: table,
                                    startY: currentY,
                                    theme: 'grid',
                                    styles: {
                                        fontSize: 7,
                                        cellPadding: 2.5,
                                        lineColor: [226, 232, 240],
                                        lineWidth: 0.1,
                                        textColor: [30, 41, 59],
                                    },
                                    headStyles: {
                                        fillColor: brandColor,
                                        textColor: 255,
                                        fontStyle: 'bold',
                                    },
                                    footStyles: {
                                        fillColor: [241, 245, 249],
                                        textColor: brandColor,
                                        fontStyle: 'bold',
                                    },
                                    alternateRowStyles: { fillColor: [248, 250, 252] },
                                    margin: { left: margin, right: margin },
                                });

                                currentY = pdf.lastAutoTable.finalY + 10;
                            }
                        }

                        // Footer en todas las páginas
                        const totalPages = pdf.internal.getNumberOfPages();
                        for (let i = 1; i <= totalPages; i++) {
                            pdf.setPage(i);
                            pdf.setDrawColor(226, 232, 240);
                            pdf.setLineWidth(0.2);
                            pdf.line(margin, pageHeight - 16, pageWidth - margin, pageHeight - 16);
                            pdf.setFontSize(8);
                            pdf.setFont(undefined, 'normal');
                            pdf.setTextColor(...mutedText);
                            pdf.text('SISCO ZONDA ERP', margin, pageHeight - 10);
                            pdf.text(`Pagina ${i} de ${totalPages}`, pageWidth - margin, pageHeight - 10, {
                                align: 'right'
                            });
                        }

                        // Descargar PDF
                        const fileName =
                            `reporte_estadisticas_${new Date().toISOString().slice(0, 10)}.pdf`;
                        pdf.save(fileName);

                    } catch (error) {
                        //console.error('Error generando PDF:', error);
                        alert('Error al generar el PDF. Por favor, intente nuevamente.');
                    } finally {
                        generatePdfBtn.disabled = false;
                        btnContent.style.display = 'inline-block';
                        btnLoading.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection
