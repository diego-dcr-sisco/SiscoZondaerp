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
                    <div class="border p-3 text-dark rounded bg-light filters-card">
                        <form method="GET" action="{{ route('crm.daily-tracking.charts') }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Grafica a visualizar</label>
                                    <select name="chart_view" class="form-select form-select-sm">
                                        <option value="contact" {{ ($chartView ?? request('chart_view', 'contact')) === 'contact' ? 'selected' : '' }}>Medio de contacto</option>
                                        <option value="amounts" {{ ($chartView ?? request('chart_view', 'contact')) === 'amounts' ? 'selected' : '' }}>Montos facturados</option>
                                        <option value="clients" {{ ($chartView ?? request('chart_view', 'contact')) === 'clients' ? 'selected' : '' }}>Clientes por periodo</option>
                                        <option value="conversion" {{ ($chartView ?? request('chart_view', 'contact')) === 'conversion' ? 'selected' : '' }}>Tasa de conversion</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Tipo de grafica</label>
                                    <select name="chart_type" class="form-select form-select-sm">
                                        <option value="bar" {{ ($chartType ?? request('chart_type', 'bar')) === 'bar' ? 'selected' : '' }}>Barras</option>
                                        <option value="line" {{ ($chartType ?? request('chart_type', 'bar')) === 'line' ? 'selected' : '' }}>Lineal</option>
                                        <option value="pie" {{ ($chartType ?? request('chart_type', 'bar')) === 'pie' ? 'selected' : '' }}>Circular</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Rango de fechas (creación)</label>
                                    <input type="text" name="date_range" class="form-control form-control-sm"
                                        placeholder="dd/mm/yyyy - dd/mm/yyyy" value="{{ request('date_range') }}" autocomplete="off"
                                        readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label form-label-sm mb-1">Servicio</label>
                                    <select name="service_id" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}"
                                                {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-8 col-xl-9">
                    @if (($chartView ?? 'contact') === 'contact')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-diagram-3 text-info"></i> Medio de contacto con mayor cantidad
                            </div>
                            <div class="card-body">
                                {!! $contactMethodChart->renderHtml() !!}
                            </div>
                        </div>
                    @elseif (($chartView ?? 'contact') === 'amounts')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-currency-dollar text-success"></i> Montos facturados ($) por período
                            </div>
                            <div class="card-body">
                                {!! $amountsChart->renderHtml() !!}
                            </div>
                        </div>
                    @elseif (($chartView ?? 'contact') === 'clients')
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-people text-primary"></i> Clientes ingresados por {{ $periodDivisionLabel ?? 'periodo' }}
                            </div>
                            <div class="card-body">
                                {!! $clientsPeriodChart->renderHtml() !!}
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm h-100 chart-card">
                            <div class="card-header bg-white fw-semibold">
                                <i class="bi bi-percent text-warning"></i> Tasa de conversión (%)
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

    {!! $contactMethodChart->renderChartJsLibrary() !!}
    @if (($chartView ?? 'contact') === 'contact')
        {!! $contactMethodChart->renderJs() !!}
    @elseif (($chartView ?? 'contact') === 'amounts')
        {!! $amountsChart->renderJs() !!}
    @elseif (($chartView ?? 'contact') === 'clients')
        {!! $clientsPeriodChart->renderJs() !!}
    @endif

    <script>
        const selectedConversionType = @json($chartType ?? 'bar')
        const conversionCtx = document.getElementById('dailyTrackingConversionChartPage')
        if (conversionCtx) {
            const conversionOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }

            if (selectedConversionType !== 'pie') {
                conversionOptions.scales = {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%'
                            }
                        }
                    }
                }
            }

            new Chart(conversionCtx, {
                type: selectedConversionType,
                data: {
                    labels: @json($conversionLabels),
                    datasets: [{
                        label: 'Tasa de conversión (%)',
                        data: @json($conversionData),
                        borderColor: '#DD513A',
                        backgroundColor: 'rgba(221, 81, 58, 0.20)',
                        borderWidth: 2,
                        fill: selectedConversionType !== 'pie',
                    }]
                },
                options: conversionOptions
            })
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
