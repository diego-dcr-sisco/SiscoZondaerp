@extends('layouts.app')

@section('content')
    <style>
        .charts-page-wrapper .nav-tabs {
            flex-wrap: wrap;
            row-gap: 0.5rem;
        }

        .charts-page-wrapper .chart-card .card-body {
            min-height: 320px;
            overflow-x: auto;
        }

        .charts-page-wrapper .chart-canvas-wrap {
            height: 320px;
        }

        .charts-page-wrapper .chart-canvas-wrap canvas {
            max-width: 100% !important;
            width: 100% !important;
        }

        .charts-page-wrapper .filters-card {
            position: sticky;
            top: 1rem;
        }

        @media (max-width: 991.98px) {
            .charts-page-wrapper .charts-header {
                gap: 0.75rem;
            }

            .charts-page-wrapper .charts-header h5 {
                font-size: 1rem;
            }
        }
    </style>

    <div class="container-fluid font-small p-0 charts-page-wrapper">

        <div class="p-3">

            {{-- Tabs CRM --}}
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('crm.agenda') }}">
                        <i class="bi bi-calendar-week"></i> Calendario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('crm.tracking') }}">
                        <i class="bi bi-arrow-repeat"></i> Seguimientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('crm.quotation') }}">
                        <i class="bi bi-receipt"></i> Cotizaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('crm.daily-tracking.index') }}">
                        <i class="bi bi-clock-history"></i> Actividades diarias
                    </a>
                </li>
            </ul>

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 charts-header">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('crm.daily-tracking.index', request()->query()) }}"
                        class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-line"></i> Gráficas de análisis</h5>
                </div>
                <a href="{{ route('crm.daily-tracking.export-charts', request()->query()) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-filetype-pdf"></i> Exportar PDF
                </a>
            </div>

            <div class="row g-3 align-items-start">
                <div class="col-12 col-lg-4 col-xl-3">
                    <form method="GET" action="{{ route('crm.daily-tracking.charts') }}" class="filters-card d-flex flex-column gap-3">
                        <div class="border p-3 text-dark rounded bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Grafica a visualizar</label>
                                    <select name="chart_view" class="form-select form-select-sm">
                                        <option value="contact" {{ ($chartView ?? request('chart_view', 'contact')) === 'contact' ? 'selected' : '' }}>Medio de contacto</option>
                                        <option value="amounts" {{ ($chartView ?? request('chart_view', 'contact')) === 'amounts' ? 'selected' : '' }}>Montos facturados</option>
                                        <option value="clients" {{ ($chartView ?? request('chart_view', 'contact')) === 'clients' ? 'selected' : '' }}>Clientes por periodo</option>
                                        <option value="services" {{ ($chartView ?? request('chart_view', 'contact')) === 'services' ? 'selected' : '' }}>Top 10 servicios</option>
                                        <option value="conversion" {{ ($chartView ?? request('chart_view', 'contact')) === 'conversion' ? 'selected' : '' }}>Tasa de conversion</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border p-3 text-dark rounded bg-light">
                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Tipo de grafica</label>
                                    <select name="chart_type" class="form-select form-select-sm">
                                        <option value="bar" {{ ($chartType ?? request('chart_type', 'bar')) === 'bar' ? 'selected' : '' }}>Barras</option>
                                        <option value="line" {{ ($chartType ?? request('chart_type', 'bar')) === 'line' ? 'selected' : '' }}>Lineal</option>
                                        <option value="pie" {{ ($chartType ?? request('chart_type', 'bar')) === 'pie' ? 'selected' : '' }}>Circular (solo conversión)</option>
                                    </select>
                                    <small class="text-muted">Solo aplica a Montos, Top servicios y Conversión</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Rango de fechas (creación)</label>
                                    <input type="text" name="date_range" class="form-control form-control-sm"
                                        placeholder="dd/mm/yyyy - dd/mm/yyyy" value="{{ request('date_range') }}" autocomplete="off"
                                        readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Estatus</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        @foreach ($statusOptions as $opt)
                                            <option value="{{ $opt->value }}"
                                                {{ request('status') == $opt->value ? 'selected' : '' }}>
                                                {{ $opt->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Division</label>
                                    <select name="period_division" class="form-select form-select-sm">
                                        <option value="auto"
                                            {{ ($periodDivision ?? request('period_division', 'auto')) === 'auto' ? 'selected' : '' }}>
                                            Auto
                                        </option>
                                        <option value="week"
                                            {{ ($periodDivision ?? request('period_division', 'auto')) === 'week' ? 'selected' : '' }}>
                                            Semanal
                                        </option>
                                        <option value="month"
                                            {{ ($periodDivision ?? request('period_division', 'auto')) === 'month' ? 'selected' : '' }}>
                                            Mensual
                                        </option>
                                        <option value="year"
                                            {{ ($periodDivision ?? request('period_division', 'auto')) === 'year' ? 'selected' : '' }}>
                                            Anual
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                    <a href="{{ route('crm.daily-tracking.charts') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-lg-8 col-xl-9">
                    @if (($chartView ?? 'contact') === 'contact')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-diagram-3 text-info"></i> Medios de contacto por {{ $periodDivisionLabel ?? 'periodo' }}
                            </div>
                            <div class="card-body">
                                <div class="chart-canvas-wrap">
                                    <canvas id="contactMethodChartCanvas"></canvas>
                                </div>
                            </div>
                        </div>
                    @elseif (($chartView ?? 'contact') === 'amounts')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-currency-dollar text-success"></i> Montos facturados ($) por {{ $periodDivisionLabel ?? 'periodo' }} y tipo de cliente
                            </div>
                            <div class="card-body">
                                <div class="chart-canvas-wrap">
                                    <canvas id="amountsChartCanvas"></canvas>
                                </div>
                            </div>
                        </div>
                    @elseif (($chartView ?? 'contact') === 'clients')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-people text-primary"></i> Clientes ingresados por {{ $periodDivisionLabel ?? 'periodo' }} y tipo
                            </div>
                            <div class="card-body">
                                <div class="chart-canvas-wrap">
                                    <canvas id="clientsPeriodChartCanvas"></canvas>
                                </div>
                            </div>
                        </div>
                    @elseif (($chartView ?? 'contact') === 'services')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-tools text-dark"></i> Top 10 servicios por {{ $periodDivisionLabel ?? 'periodo' }}
                            </div>
                            <div class="card-body">
                                <div class="chart-canvas-wrap">
                                    <canvas id="topServicesChartCanvas"></canvas>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-percent text-warning"></i> Tasa de conversión (%) por {{ $periodDivisionLabel ?? 'periodo' }} y tipo de cliente
                            </div>
                            <div class="card-body">
                                <div class="chart-canvas-wrap">
                                    <canvas id="dailyTrackingConversionChartPage"></canvas>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <script>
        // ── Medios de contacto (grouped bar — always) ────────────────────────
        var contactCtx = document.getElementById('contactMethodChartCanvas');
        if (contactCtx) {
            new Chart(contactCtx, {
                type: 'bar',
                data: {
                    labels: @json($contactPeriods),
                    datasets: [
                        {
                            label: 'Google',
                            data: @json($contactDatasets['google']),
                            backgroundColor: 'rgba(66,133,244,0.8)',
                            borderColor: '#4285F4',
                            borderWidth: 1,
                        },
                        {
                            label: 'Página web',
                            data: @json($contactDatasets['pagina']),
                            backgroundColor: 'rgba(52,168,83,0.8)',
                            borderColor: '#34A853',
                            borderWidth: 1,
                        },
                        {
                            label: 'Llamada',
                            data: @json($contactDatasets['llamada']),
                            backgroundColor: 'rgba(251,188,5,0.85)',
                            borderColor: '#FBBC05',
                            borderWidth: 1,
                        },
                        {
                            label: 'Cambaceo',
                            data: @json($contactDatasets['cambaceo']),
                            backgroundColor: 'rgba(234,67,53,0.8)',
                            borderColor: '#EA4335',
                            borderWidth: 1,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: {
                        x: { stacked: false },
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // ── Montos facturados (bar o line según filtro) ──────────────────────
        var amountsCtx = document.getElementById('amountsChartCanvas');
        if (amountsCtx) {
            var amountsType = @json($chartType ?? 'bar');
            var isLine = amountsType === 'line';
            new Chart(amountsCtx, {
                type: amountsType,
                data: {
                    labels: @json($amountsPeriods),
                    datasets: [
                        {
                            label: 'Doméstico',
                            data: @json($amountsDatasets['domestico']),
                            backgroundColor: isLine ? 'rgba(0,188,212,0.2)' : 'rgba(0,188,212,0.8)',
                            borderColor: '#00BCD4',
                            borderWidth: 2,
                            fill: isLine,
                            tension: 0.3,
                            pointRadius: isLine ? 4 : 0,
                        },
                        {
                            label: 'Comercial',
                            data: @json($amountsDatasets['comercial']),
                            backgroundColor: isLine ? 'rgba(183,68,83,0.2)' : 'rgba(183,68,83,0.8)',
                            borderColor: '#B74453',
                            borderWidth: 2,
                            fill: isLine,
                            tension: 0.3,
                            pointRadius: isLine ? 4 : 0,
                        },
                        {
                            label: 'Industrial',
                            data: @json($amountsDatasets['industrial']),
                            backgroundColor: isLine ? 'rgba(81,42,135,0.2)' : 'rgba(81,42,135,0.8)',
                            borderColor: '#512A87',
                            borderWidth: 2,
                            fill: isLine,
                            tension: 0.3,
                            pointRadius: isLine ? 4 : 0,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString('es-MX', { minimumFractionDigits: 0 });
                                }
                            }
                        }
                    }
                }
            });
        }

        // ── Clientes por periodo (grouped bar — always) ──────────────────────
        var clientsCtx = document.getElementById('clientsPeriodChartCanvas');
        if (clientsCtx) {
            new Chart(clientsCtx, {
                type: 'bar',
                data: {
                    labels: @json($clientsPeriods),
                    datasets: [
                        {
                            label: 'Doméstico',
                            data: @json($clientsDatasets['domestico']),
                            backgroundColor: 'rgba(0,188,212,0.8)',
                            borderColor: '#00BCD4',
                            borderWidth: 1,
                        },
                        {
                            label: 'Comercial',
                            data: @json($clientsDatasets['comercial']),
                            backgroundColor: 'rgba(183,68,83,0.8)',
                            borderColor: '#B74453',
                            borderWidth: 1,
                        },
                        {
                            label: 'Industrial',
                            data: @json($clientsDatasets['industrial']),
                            backgroundColor: 'rgba(81,42,135,0.8)',
                            borderColor: '#512A87',
                            borderWidth: 1,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: {
                        x: { stacked: false },
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // ── Top 10 servicios (bar o line según filtro) ───────────────────────
        var topServicesCtx = document.getElementById('topServicesChartCanvas');
        if (topServicesCtx) {
            var topServicesType = @json($chartType ?? 'bar');
            if (topServicesType === 'pie') {
                topServicesType = 'bar';
            }

            var baseColors = [
                '#2563EB', '#16A34A', '#DC2626', '#D97706', '#7C3AED',
                '#0891B2', '#DB2777', '#4F46E5', '#65A30D', '#EA580C'
            ];

            var serviceDatasets = @json($topServicesDatasets);
            serviceDatasets = serviceDatasets.map(function(dataset, index) {
                var color = baseColors[index % baseColors.length];
                return {
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: color,
                    backgroundColor: topServicesType === 'line'
                        ? color + '33'
                        : color + 'CC',
                    borderWidth: 2,
                    fill: topServicesType === 'line',
                    tension: 0.25,
                    pointRadius: topServicesType === 'line' ? 3 : 0,
                };
            });

            new Chart(topServicesCtx, {
                type: topServicesType,
                data: {
                    labels: @json($topServicesPeriods),
                    datasets: serviceDatasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        // ── Tasa de conversión (bar o line según filtro) ──────────────────────
        var selectedConversionType = @json($chartType ?? 'bar');
        var conversionCtx = document.getElementById('dailyTrackingConversionChartPage');
        if (conversionCtx) {
            var isConversionLine = selectedConversionType === 'line';
            new Chart(conversionCtx, {
                type: selectedConversionType,
                data: {
                    labels: @json($conversionPeriods),
                    datasets: [
                        {
                            label: 'Doméstico',
                            data: @json($conversionDatasets['domestico']),
                            backgroundColor: isConversionLine ? 'rgba(0,188,212,0.2)' : 'rgba(0,188,212,0.8)',
                            borderColor: '#00BCD4',
                            borderWidth: 2,
                            fill: isConversionLine,
                            tension: 0.3,
                            pointRadius: isConversionLine ? 4 : 0,
                        },
                        {
                            label: 'Comercial',
                            data: @json($conversionDatasets['comercial']),
                            backgroundColor: isConversionLine ? 'rgba(183,68,83,0.2)' : 'rgba(183,68,83,0.8)',
                            borderColor: '#B74453',
                            borderWidth: 2,
                            fill: isConversionLine,
                            tension: 0.3,
                            pointRadius: isConversionLine ? 4 : 0,
                        },
                        {
                            label: 'Industrial',
                            data: @json($conversionDatasets['industrial']),
                            backgroundColor: isConversionLine ? 'rgba(81,42,135,0.2)' : 'rgba(81,42,135,0.8)',
                            borderColor: '#512A87',
                            borderWidth: 2,
                            fill: isConversionLine,
                            tension: 0.3,
                            pointRadius: isConversionLine ? 4 : 0,
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            $('input[name="date_range"]').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                },
                showDropdowns: true,
                alwaysShowCalendars: true,
                opens: 'left',
                autoUpdateInput: false
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            $('input[name="date_range"]').on('cancel.daterangepicker', function() {
                $(this).val('');
            });
        });
    </script>
@endsection
