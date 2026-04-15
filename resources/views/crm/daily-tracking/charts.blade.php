@extends('layouts.app')

@section('content')
    <div class="container-fluid font-small p-3">

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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('crm.daily-tracking.index', request()->query()) }}"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h5 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-line"></i> Gráficas de análisis</h5>
            </div>
            <a href="{{ route('crm.daily-tracking.export-charts', request()->query()) }}"
                class="btn btn-sm btn-danger">
                <i class="bi bi-filetype-pdf"></i> Exportar PDF
            </a>
        </div>

        {{-- Filtros --}}
        <div class="border p-2 text-dark rounded mb-3 bg-light">
            <form method="GET" action="{{ route('crm.daily-tracking.charts') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label form-label-sm mb-1">Rango de fechas (creación)</label>
                        <input type="text" name="date_range" class="form-control form-control-sm"
                            placeholder="dd/mm/yyyy - dd/mm/yyyy"
                            value="{{ request('date_range') }}" autocomplete="off" readonly>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label form-label-sm mb-1">Servicio</label>
                        <select name="service_id" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label form-label-sm mb-1">Estatus</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach ($statusOptions as $opt)
                                <option value="{{ $opt->value }}" {{ request('status') == $opt->value ? 'selected' : '' }}>
                                    {{ $opt->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="{{ route('crm.daily-tracking.charts') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Gráficas --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-diagram-3 text-info"></i> 1) Medio de contacto con mayor cantidad
                    </div>
                    <div class="card-body">
                        {!! $contactMethodChart->renderHtml() !!}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-currency-dollar text-success"></i> 2) Montos facturados ($) por período
                    </div>
                    <div class="card-body">
                        {!! $amountsChart->renderHtml() !!}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-people text-primary"></i> 3) Clientes ingresados por semana/mes
                    </div>
                    <div class="card-body">
                        {!! $clientsPeriodChart->renderHtml() !!}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold">
                        <i class="bi bi-percent text-warning"></i> 4) Tasa de conversión (%)
                    </div>
                    <div class="card-body">
                        <canvas id="dailyTrackingConversionChartPage"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {!! $contactMethodChart->renderChartJsLibrary() !!}
    {!! $contactMethodChart->renderJs() !!}
    {!! $amountsChart->renderJs() !!}
    {!! $clientsPeriodChart->renderJs() !!}

    <script>
        const conversionCtx = document.getElementById('dailyTrackingConversionChartPage')
        if (conversionCtx) {
            new Chart(conversionCtx, {
                type: 'line',
                data: {
                    labels: @json($conversionLabels),
                    datasets: [{
                        label: 'Tasa de conversión (%)',
                        data: @json($conversionData),
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderWidth: 2,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return value + '%' }
                            }
                        }
                    }
                }
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
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $('input[name="date_range"]').on('cancel.daterangepicker', function() {
                $(this).val('');
            });
        });
    </script>
@endsection
