@extends('layouts.app')
@section('content')
    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-12 p-3">
            <div class="container-fluid">
                <!-- Titulo y Flecha de regreso -->
                <div class="row border-bottom p-3 mb-4">
                    <a href="{{ route('quality.customer', ['id' => $customer->id]) }}" class="col-auto btn-primary p-0">
                        <i class="bi bi-arrow-left m-3 fs-4"></i>
                    </a>
                    <h1 class="col-auto fs-2 fw-bold m-0">Analíticas - {{ $customer->name }}</h1>
                    <div class="col-auto ms-auto">
                            <div class="btn-group" role="group">
                                <a href="#cardOrdersSummary" class="btn btn-outline-primary">
                                    <i class="bi bi-clipboard-check"></i> Órdenes
                                </a>
                                <a href="#cardDeviceConsumption" class="btn btn-outline-primary">
                                    <i class="bi bi-speedometer2"></i> Consumo
                                </a>
                                <a href="#cardPestIncidents" class="btn btn-outline-primary">
                                    <i class="bi bi-bug"></i> Plagas
                                </a>
                                <a href="#cardLastApprovedOrders" class="btn btn-outline-primary">
                                    <i class="bi bi-list-check"></i> Últimas órdenes
                                </a>
                            </div>
                    </div>
                </div>
                <!-- Total Orders Summary Card -->
                <div class="row mb-4" id="cardOrdersSummary">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button"
                                data-bs-toggle="collapse" data-bs-target="#ordersSummary" aria-expanded="true"
                                aria-controls="ordersSummary">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    Resumen de Órdenes
                                </h5>
                                <span class="badge text-dark fs-5">Total: {{ $totalOrders }}</span>
                                <span class="ms-3 badge bg-warning">Pendiente: {{ $ordersByStatus['pending'] ?? 0 }}</span>
                                <span class="ms-1 badge bg-secondary">Aceptada: {{ $ordersByStatus['accepted'] ?? 0 }}</span>
                                <span class="ms-1 badge bg-primary">Finalizada: {{ $ordersByStatus['finished'] ?? 0 }}</span>
                                <span class="ms-1 badge bg-info">Verificada: {{ $ordersByStatus['verified'] ?? 0 }}</span>
                                <span class="ms-1 badge bg-success">Aprobada: {{ $ordersByStatus['approved'] ?? 0 }}</span>
                                <span class="ms-1 badge bg-danger">Cancelada: {{ $ordersByStatus['canceled'] ?? 0 }}</span>
                            </div>
                            <div class="collapse show" id="ordersSummary">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">Últimas Órdenes Modificadas</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Folio</th>
                                                    <th>Estado</th>
                                                    <th>Última Modificación</th>
                                                    <th>Servicios</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($lastModifiedOrders as $order)
                                                    <tr>
                                                        <td>{{ $order->folio }}</td>
                                                        <td>
                                                            @switch($order->status_id)
                                                                @case(1)
                                                                    <span class="badge bg-warning">Pendiente</span>
                                                                @break

                                                                @case(2)
                                                                    <span class="badge bg-info">Aceptada</span>
                                                                @break

                                                                @case(3)
                                                                    <span class="badge bg-primary">Finalizada</span>
                                                                @break

                                                                @case(4)
                                                                    <span class="badge bg-info">Verificada</span>
                                                                @break

                                                                @case(5)
                                                                    <span class="badge bg-success">Aprobada</span>
                                                                @break

                                                                @case(6)
                                                                    <span class="badge bg-danger">Cancelada</span>
                                                                @break

                                                                @default
                                                                    <span class="badge bg-secondary">Otro</span>
                                                            @endswitch
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y H:i') }}
                                                        </td>
                                                        <td>
                                                            @foreach ($order->services as $service)
                                                                <span>{{ $service->name }}</span>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('order.edit', ['id' => $order->id, 'section' => 1]) }}"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="bi bi-eye"></i> Ver
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Comsumo mensual por dispositivo -->
                <div class="row mb-4" id="cardDeviceConsumption">
                    <!-- CARD de consumo mensual por dispositivo -->
                    <div class="col-12">
                        <div class="card shadow text-end" style="height: 430px;">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button"
                                data-bs-toggle="collapse" data-bs-target="#deviceConsumptionTable" aria-expanded="true"
                                aria-controls="deviceConsumptionTable">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    Consumo por dispositivo
                                </h5>
                            </div>
                            <div class="collapse show" id="deviceConsumptionTable" style="height: calc(100% - 56px);">
                                <div class="card-body p-3" style="height: 100%; overflow-y: auto;">
                                    @include('dashboard.quality.device.consumptionTable', [
                                        'customer' => $customer,
                                        'allServices' => $consumptionData['allServices'] ?? [],
                                        'selectedService' => $consumptionData['selectedService'] ?? null,
                                        'table' => $consumptionData['table'] ?? [],
                                        'start_date' => $consumptionData['start_date'] ?? now()->subMonth(),
                                        'end_date' => $consumptionData['end_date'] ?? now(),
                                        'reportType' => $consumptionData['reportType'] ?? 'weekly',
                                        'timeKeys' => $consumptionData['timeKeys'] ?? [],
                                    ])
                                    <button class="btn btn-secondary mt-3" onclick="copyTableToClipboard()"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Copiar tabla al portapapeles">
                                        <i class="bi bi-clipboard"></i> Copiar tabla
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Chart consumptionTable -->
                    <div class="col-lg-12">
                        <div class="card shadow" id="deviceConsumptionChart">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button" <h5
                                class="card-title mb-0 d-flex align-items-center">
                                Consumo por dispositivo
                                </h5>
                            </div>
                            <div class="collapse show" id="divDeviceConsumptionChart">
                                <div class="chart-container mt-4" style="position: relative; height:400px; width:100%">
                                <canvas id="consumptionChart"></canvas>
                            </div>
                            </div>
                        </div>
                    </div>
                    <!-- Chart card -->
                        {{--<div class="col-lg-6">
                        <div class="card shadow" id="deviceConsumptionChart">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button" <h5
                                class="card-title mb-0 d-flex align-items-center">
                                Consumo por dispositivo
                                </h5>
                            </div>
                            <div class="collapse show" id="divDeviceConsumptionChart">
                                <div class="card-body">
                                    <canvas id="deviceSummaryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>--}}
                </div>
                <!--Incidencias de plagas por dispositivo -->
                <div class="row mb-4" id="cardPestIncidents">
                   
                    <div class="col-6">
                        <div class="card shadow text-end" style="height: 430px;">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button"
                                data-bs-toggle="collapse" data-bs-target="#devicePestIncidents" aria-expanded="true"
                                aria-controls="devicePestIncidents">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    Incidencia de Plagas por Dispositivo
                                </h5>
                            </div>
                            <div class="collapse show" id="devicePestIncidents" style="height: calc(100% - 56px);">
                                <div class="card-body p-3" style="height: 100%; overflow-y: auto;">
                                    @include('dashboard.quality.analytics.pest_incidents_table')
                                    <button class="btn btn-secondary mt-3" onclick="copyIncidentsTableToClipboard()"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Copiar tabla al portapapeles">
                                        <i class="bi bi-clipboard"></i> Copiar tabla
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Chart card -->
                    <div class="col-lg-6">
                        <div class="card shadow" id="pestIncidentsChart">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button"><h5
                                class="card-title mb-0 d-flex align-items-center">
                                Consumo por dispositivo
                                </h5>
                            </div>
                            <div class="collapse show" id="pestIncidentsChart">
                                <div class="card-body">
                                    <canvas id="devicePestIncidentsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Orders and Services Chart --> 
                <div class="row mb-4">
                    <!-- Monthly Orders Chart -->
                    <div class="col-lg-6">
                        <div class="card shadow">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button"
                                data-bs-toggle="collapse" data-bs-target="#monthlyOrders" aria-expanded="true"
                                aria-controls="monthlyOrders">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    Órdenes Mensuales
                                </h5>
                            </div>
                            <div class="collapse show" id="monthlyOrders">
                                <div class="card-body">
                                    <canvas id="monthlyOrdersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services Chart -->
                    <div class="col-lg-6">
                        <div class="card shadow">
                            <div class="card-header d-flex justify-content-between align-items-center" role="button"
                                data-bs-toggle="collapse" data-bs-target="#servicesChart" aria-expanded="true"
                                aria-controls="servicesChart">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    Servicios Realizados
                                </h5>
                            </div>
                            <div class="collapse show" id="servicesChart">
                                <div class="card-body">
                                    <canvas id="servicesChartCanvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Approved Orders --> 
                <div class="row mb-4" id="cardLastApprovedOrders">
                    <!-- Orders Table -->
                    <div class="col-lg-12">
                        <div class="card shadow">
                            <div class="card-header d-flex justify-content-between align-items-center"
                                role="button" data-bs-toggle="collapse" data-bs-target="#approvedOrders"
                                aria-expanded="true" aria-controls="approvedOrders">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <i class="bi bi-chevron-down me-2"></i>
                                    Últimas Órdenes Aprobadas
                                </h5>
                            </div>
                            <div class="collapse show" id="approvedOrders">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Folio</th>
                                                    <th>Fecha</th>
                                                    <th>Servicios</th>
                                                    <th>Técnicos</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($approvedOrders->take(10) as $order)
                                                    <tr>
                                                        <td>{{ $order->folio }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($order->programmed_date)->format('d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            @foreach ($order->services as $service)
                                                                {{ $service->name }}
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach ($order->technicians as $technician)
                                                                <span
                                                                    class="badge bg-primary">{{ $technician->user->name }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- Required Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker"></script>
    <script src="{{ asset('js/analytics.js') }}"></script>

    <!-- Pass data to JavaScript -->
    <script>
        // Pasar los datos a JavaScript
        window.monthlyOrdersLabels = @json($monthlyOrders->map(fn($order) => Carbon\Carbon::create()->month($order->month)->year($order->year)->format('M Y')));
        window.monthlyOrdersData = @json($monthlyOrders->pluck('total'));
        window.serviceStatsLabels = @json($serviceStats->pluck('name'));
        window.serviceStatsData = @json($serviceStats->pluck('total'));
        window.technicianStatsLabels = @json($technicianStats->pluck('name'));
        window.technicianStatsData = @json($technicianStats->pluck('total'));

        // Pasar los datos de consumo de dispositivos para la gráfica
        @if (isset($consumptionData['consumption']))
            window.deviceConsumptionData = {
                labels: {!! json_encode(array_keys($consumptionData['consumption']['devices'])) !!},
                data: {!! json_encode(array_values($consumptionData['consumption']['devices'])) !!}
            };
        @else
            window.deviceConsumptionData = {
                labels: [],
                data: []
            };
        @endif

        // Pasar los datos de consumo de dispositivos para la gráfica de resumen
        @if (isset($consumptionData['consumption']))
            window.deviceSummaryData = {
                devices: [
                    @foreach ($consumptionData['consumption']['devices'] as $device)
                        @php
                            // Calcular el consumo total sumando los valores normalizados (los que son números)
                            $totalConsumption = array_sum(array_filter($device['consumptions'], 'is_numeric'));
                        @endphp {
                            id: {{ $device['id'] }},
                            code: "{{ $device['code'] }}",
                            type: {{ $device['type'] ?? 0 }},
                            totalConsumption: {{ $totalConsumption }}
                        }
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            };
        @else
            window.deviceSummaryData = {
                devices: []
            };
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltip = new bootstrap.Tooltip(document.querySelector('[data-bs-toggle="tooltip"]'));
        });
    </script>

    <script>
        function copyTableToClipboard() {
            const table = document.querySelector('#deviceConsumptionTable');
            const range = document.createRange();
            range.selectNode(table);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
        }
    </script>

<!-- Script de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // DECLARAR VARIABLES GLOBALES UNA SOLA VEZ
    window.globalChartReferences = window.globalChartReferences || {};
    window.globalChartReferences.consumptionChart = null;

    // FUNCIÓN GLOBAL PARA DESTRUIR GRÁFICAS (UNA SOLA VEZ)
    window.destroyChartIfExists = function(chartName) {
        if (window.globalChartReferences[chartName] && 
            window.globalChartReferences[chartName] instanceof Chart) {
            try {
                window.globalChartReferences[chartName].destroy();
            } catch (error) {
                //console.error(`Error destruyendo ${chartName}:`, error);
            } finally {
                window.globalChartReferences[chartName] = null;
            }
        }
    };

    // FUNCIÓN ÚNICA PARA CREAR GRÁFICAS
    window.createConsumptionChart = function(data, canvasId = 'consumptionChart') {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        //console.error(`Canvas ${canvasId} no encontrado`);
        return null;
    }

    // Manejar ambos formatos de datos
    const devices = data.table || data.devices || {};
    const timeKeys = data.timeKeys || [];
    
    if (Object.keys(devices).length === 0 || timeKeys.length === 0) {
        console.warn('Datos insuficientes para gráfica');
        return null;
    }
    
    const datasets = [];
    const colors = [
        'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 
        'rgba(75, 192, 192, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(153, 102, 255, 0.7)', 'rgba(255, 205, 86, 0.7)'
    ];
    
    // CALCULAR ALTURA MÁXIMA NECESARIA PARA NOMBRES LARGOS
    let maxNameLength = 0;
    Object.keys(devices).forEach(deviceName => {
        maxNameLength = Math.max(maxNameLength, deviceName.length);
    });
    
    // CALCULAR PADDING DINÁMICO BASADO EN LONGITUD DE NOMBRES
    const dynamicPadding = Math.max(50, maxNameLength * 6); // Mínimo 50px, más para nombres largos
    
    Object.entries(devices).forEach(([deviceName, deviceData], index) => {
        if (!deviceData || typeof deviceData !== 'object') return;
        
        const values = timeKeys.map(timeKey => {
            const value = deviceData[timeKey];
            return typeof value === 'number' ? value : 0;
        });
        
        datasets.push({
            label: deviceName,
            data: values,
            backgroundColor: colors[index % colors.length],
            borderColor: colors[index % colors.length].replace('0.7', '1'),
            borderWidth: 2,
            tension: 0.3
        });
    });
    
    if (datasets.length === 0) return null;
    
    return new Chart(ctx, {
        type: 'bar',
        data: { labels: timeKeys, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Consumo' },
                    // ESPACIO ADICIONAL EN EJE Y
                    grace: '5%',
                },
                x: { 
                    title: { 
                        display: true, 
                        text: 'Fecha' 
                    },
                    ticks: {
                        // MÁS ESPACIO PARA ETIQUETAS LARGAS
                        padding: 50,
                        // AUTO-ROTACIÓN PARA ETIQUETAS DE FECHA LARGAS
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            },
            plugins: {
                legend: { 
                    position: 'top',
                    // REDUCIR ESPACIO DE LEYENDA PARA DAR MÁS ESPACIO AL GRÁFICO
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 10
                        }
                    }
                },
                title: { 
                    display: true, 
                    text: 'Consumo por Dispositivo',
                    padding: {
                        bottom: 20
                    }
                }
            },
            // PADDING DINÁMICO EN LA PARTE INFERIOR
            layout: {
                padding: {
                    bottom: dynamicPadding, // ESPACIO DINÁMICO SEGÚN LONGITUD DE NOMBRES
                    top: 10,
                    left: 10,
                    right: 10
                }
            },
            animation: {
                onComplete: function() {
                    const ctx = this.ctx;
                    const chartArea = this.chartArea;
                    const scale = this.scales.x;
                    // CONFIGURACIÓN MEJORADA PARA ETIQUETAS VERTICALES
                    ctx.font = 'bold 7px Arial';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'top';
                    ctx.fillStyle = '#2c3e50';
                    
                    this.data.datasets.forEach((dataset, datasetIndex) => {
                        const meta = this.getDatasetMeta(datasetIndex);
                        meta.data.forEach((bar, index) => {
                            if (dataset.data[index] > 0) {
                                const x = bar.x;
                                // Posición basada en longitud de nombre
                                const nameLength = dataset.label.length;
                                const yOffset = 10 + (nameLength * 2); 
                                const y = chartArea.bottom + yOffset;
                                
                                // GUARDAR ESTADO DEL CONTEXTO
                                ctx.save();

                                // Trasladar y rotar los nombres de dispositivos
                                ctx.translate(x, y);
                                ctx.rotate(-Math.PI / 2); // -90 grados

                                // Dibujar texto vertical
                                ctx.fillText(dataset.label, 0, 0);
                                
                            
                                ctx.restore();

                                // Línea que conecta barra-etiqueta
                                ctx.save();
                                ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)';
                                ctx.setLineDash([2, 2]);
                                ctx.beginPath();
                                ctx.moveTo(x, chartArea.bottom);
                                ctx.lineTo(x, y - 5);
                                ctx.stroke();
                                ctx.restore();
                            }
                        });
                    });
                }
            }
        }
    });
};
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar moment.js
    moment.locale('es-mx');

    // Obtener elementos con verificación
    const dateRangeInput = document.getElementById('dateRangePicker');
    const filterForm = document.getElementById('consumptionFilterForm');
    
    // Verificar que los elementos existen
    if (!dateRangeInput || !filterForm) {
        //console.error('Elementos del formulario no encontrados');
        return;
    }

    // Valores iniciales
    const initialValue = dateRangeInput.value || '';
    let startDate = moment().subtract(1, 'month');
    let endDate = moment();

    // Parsear valores existentes
    if (initialValue.includes(' - ')) {
        const dates = initialValue.split(' - ');
        startDate = moment(dates[0], 'DD/MM/YYYY');
        endDate = moment(dates[1], 'DD/MM/YYYY');
        
        if (!startDate.isValid()) startDate = moment().subtract(1, 'month');
        if (!endDate.isValid()) endDate = moment();
    }

    
    $(dateRangeInput).daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            monthNames: moment.months(),
            firstDay: 1
        },
        startDate: startDate,
        endDate: endDate,
        autoUpdateInput: true,
        opens: 'right'
    });

    // Eventos del datepicker
    $(dateRangeInput).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    $(dateRangeInput).on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

});



</script>

<script>
//Inicialización de la gráfica
document.addEventListener('DOMContentLoaded', function() {
    try {
        const chartData = {!! $chartData !!};
        if (!chartData || typeof chartData !== 'object') return;
        
        
        window.globalChartReferences.consumptionChart = window.createConsumptionChart(chartData);
        
    } catch (error) {
        //console.error('Error al inicializar gráfica:', error);
    }
});
</script>







<script>
    $(document).ready(function() {
    

    // Función para actualizar la tabla con datos JSON
    function updateConsumptionTable(data) {
        const $container = $('#consumptionResultsContainer');
        
        // Determinar si es reporte diario o semanal
        const isDaily = data.reportType === 'daily';
        
        $container.find('h4').text(isDaily ? 'Reporte Diario de Consumo' : 'Reporte Semanal de Consumo');
        
        // Obtener referencia a la tabla (crear una nueva si no existe)
        let $table = $container.find('table');
        if ($table.length === 0) {
            $container.html(`
                <h4 class="mb-3" style="width: 100%; text-align: center;">${isDaily ? 'Reporte Diario de Consumo' : 'Reporte Semanal de Consumo'}</h4>
                <table class="table table-striped table-bordered table-sm table-sticky align-middle">
                    <thead class="table-success"></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
                <small class="text-muted"></small>
            `);
            $table = $container.find('table');
        }
        
        // Construir encabezados
        let headers = '<th style="min-width:160px">DISPOSITIVO</th>';
        data.timeKeys.forEach(key => {
            headers += `<th class="text-center">${key}</th>`;
        });
        headers += '<th class="text-center">TOTAL</th>';
        
        $table.find('thead').html(`<tr>${headers}</tr>`);
        
        // Construir filas de datos
        let rows = '';
        Object.entries(data.table).forEach(([deviceLabel, rowData]) => {
            let row = `<tr><td class="fw-semibold">${deviceLabel}</td>`;
            let total = 0;
            
            data.timeKeys.forEach(key => {
                const val = parseFloat(rowData[key] || 0);
                total += val;
                const badgeClass = getBadgeClassJS(val);
                row += `<td><span class="badge ${badgeClass}">${val.toFixed(2)}</span></td>`;
            });
            
            row += `<td class="text-center fw-bold">${total.toFixed(2)}</td></tr>`;
            rows += row;
        });
        
        $table.find('tbody').html(rows);
        
        // Construir pie de tabla (totales)
        let footers = '<th>Total general</th>';
        let granTotal = 0;

        // 1. Calcular totales por columna (días/semanas)
        data.timeKeys.forEach(key => {
            const sum = Object.values(data.table).reduce((acc, row) => acc + parseFloat(row[key] || 0), 0);
            footers += `<th class="text-center">${sum.toFixed(2)}</th>`;
        });

        // 2. Calcular GRAN TOTAL sumando los TOTALES de cada dispositivo
        Object.values(data.table).forEach(row => {
            granTotal += parseFloat(row['TOTAL'] || 0);
        });

        footers += `<th class="text-center">${granTotal.toFixed(2)}</th>`;
        $table.find('tfoot').html(`<tr class="table-light">${footers}</tr>`);
        



    }
    // FUNCIÓN PARA ACTUALIZAR GRÁFICA
    window.updateConsumptionChart = function(data) {
        window.destroyChartIfExists('consumptionChart');
        window.globalChartReferences.consumptionChart = window.createConsumptionChart(data);
    };
    
    // Versión JavaScript de tu función getBadgeClass
    function getBadgeClassJS(value) {
        if (value == 0) return 'bg-secondary text-white';
        else if (value <= 0.25) return 'bg-success text-white';
        else if (value <= 0.5) return 'bg-warning text-dark';
        else if (value <= 0.75) return 'bg-danger text-white';
        else return 'bg-danger text-white';
    }
    
    // Función para formatear fechas
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
    
    
    
    // Manejar el envío del formulario
    $('#consumptionFilterForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        const originalText = $button.html();
        const $container = $('#consumptionResultsContainer');
        const $chartContainer = $('#chartContainer');
        
        // Mostrar loading
        $button.prop('disabled', true).html('<i class="bi bi-hourglass"></i> Filtrando...');
        $container.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
        $chartContainer.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
        
        $.ajax({
            url: $form.attr('action'),
            type: 'GET',
            data: $form.serialize(),
            dataType: 'json',
            success: function(data) {
                if (data.table && data.timeKeys) {
                    // ACTUALIZAR TABLA Y GRÁFICA
                    updateConsumptionTable(data);
                    updateConsumptionChart(data);
                } else {
                    $container.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> No hay datos para los filtros seleccionados</div>');
                    $chartContainer.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> No hay datos para graficar</div>');
                    
                    // Destruir gráfica si no hay datos
                if (typeof window.destroyChartIfExists === 'function') {
                    window.destroyChartIfExists('consumptionChart');
                }
                    }
            },
            error: function(xhr) {
                $container.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> No hay datos de consumo para los filtros seleccionados.</div>');
                $chartContainer.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> Error al cargar gráfica</div>');
                
                
                // Destruir gráfica en caso de error
                window.destroyChartIfExists('consumptionChart');
            

            },
            complete: function() {
                $button.prop('disabled', false).html(originalText);
            }
        });
    });
    
    
});
</script>

@endsection
