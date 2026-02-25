@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between border-bottom ps-4 p-2">
            <div class="d-flex align-items-center">
                <!-- <a href="{{ route('order.index') }}" class="text-decoration-none pe-3">
                                                                <i class="bi bi-arrow-left fs-4"></i>
                                                            </a> -->
                <a href="#" onclick="window.history.back(); return false;" class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <span class="text-black fw-bold fs-4">
                    Consultar Grafico
                </span>
            </div>
            <div class="pe-4">
                <button class="btn btn-dark btn-sm" id="generateReportBtn" onclick="exportAllChartsToPDF()">
                    <span id="btnContent">
                        <i class="bi bi-file-pdf-fill"></i> Generar Reporte
                    </span>
                    <span id="btnLoading" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Generando reporte...
                    </span>
                </button>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 m-3">
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <h4 class="fw-bold mb-3">Incidencias por dispositivo</h4>
                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Plano </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->filename }}" disabled>
                            <input type="hidden" id="floorplan-id" name="floorplan_id" value="{{ $floorplan->id }}">
                        </div>

                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Servicio </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->service->name ?? 'No aplica' }}" disabled>
                        </div>

                        <div class="col-2">
                            <label for="floorplan-name" class="form-label is-required">Version</label>
                            <select class="form-select form-select-sm filter-select" id="floorplan-version-device" name="version">
                                @forelse ($floorplan->versions as $version)
                                    <option value="{{ $version->version }}"
                                        @if ($version->version == $floorplan->version) selected @endif>
                                        {{ $version->version }}</option>
                                @empty
                                    <option value="" selected>Sin version</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-4">
                            <label for="floorplan-name" class="form-label is-required">Rango de fechas</label>
                            <input type="text" class="form-control form-control-sm" id="date-range-device" name="daterange" placeholder="Seleccionar rango">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" id="search-devices-btn">Buscar</button>
                    </div>

                    <div class="position-relative">
                        <div id="devices-loader" class="spinner-border spinner-border-sm position-absolute" role="status" style="display:none; top: 10px; right: 10px;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="devicesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <h4 class="fw-bold mb-3">Incidencias por tipo de plaga</h4>
                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Plano </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->filename }}" disabled>
                            <input type="hidden" id="floorplan-id" name="floorplan_id" value="{{ $floorplan->id }}">
                        </div>

                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Servicio </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->service->name ?? 'No aplica' }}" disabled>
                        </div>

                        <div class="col-2">
                            <label for="floorplan-name" class="form-label is-required">Version</label>
                            <select class="form-select form-select-sm filter-select" id="floorplan-version-pests" name="version">
                                @forelse ($floorplan->versions as $version)
                                    <option value="{{ $version->version }}"
                                        @if ($version->version == $floorplan->version) selected @endif>
                                        {{ $version->version }}</option>
                                @empty
                                    <option value="" selected>Sin version</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-4">
                            <label for="floorplan-name" class="form-label is-required">Rango de fechas</label>
                            <input type="text" class="form-control form-control-sm" id="date-range-pests" name="daterange" placeholder="Seleccionar rango">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" id="search-pests-btn">Buscar</button>
                    </div>

                    <div class="position-relative">
                        <div id="pests-loader" class="spinner-border spinner-border-sm position-absolute" style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="pestsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-3">
            <div class="col-12">
                <div class="border rounded shadow p-3">
                    <h4 class="fw-bold mb-3">Tendencia de incidencias mensuales</h4>
                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Plano </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->filename }}" disabled>
                        </div>

                        <div class="col-3">
                            <label for="floorplan-name" class="form-label">Servicio </label>
                            <input type="text" class="form-control form-control-sm" id="floorplan-name"
                                value="{{ $floorplan->service->name ?? 'No aplica' }}" disabled>
                        </div>

                        <div class="col-2">
                            <label for="floorplan-name" class="form-label is-required">Version</label>
                            <select class="form-select form-select-sm filter-select" id="floorplan-version-trend" name="version">
                                @forelse ($floorplan->versions as $version)
                                    <option value="{{ $version->version }}"
                                        @if ($version->version == $floorplan->version) selected @endif>
                                        {{ $version->version }}</option>
                                @empty
                                    <option value="" selected">Sin version</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-4">
                            <label for="floorplan-name" class="form-label is-required">Año</label>
                            <select class="form-select form-select-sm filter-select" id="floorplan-year-trend" name="year">
                                @forelse ($years as $year)
                                    <option value="{{ $year }}"
                                        @if ($year == Carbon\Carbon::now()->year) selected @endif>
                                        {{ $year }}</option>
                                @empty
                                    <option value="" selected>Sin año</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm" id="search-trend-btn">Buscar</button>
                    </div>

                    <div class="position-relative">
                        <div id="trend-loader" class="spinner-border spinner-border-sm position-absolute" style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">

    <script>
        let devicesChart = null;
        let pestsChart = null;
        let trendChart = null;
        const floorplanId = document.getElementById('floorplan-id').value;

        // Función para obtener instancia de daterangepicker
        function getDateRangeData(elementId) {
            return $(elementId).data('daterangepicker');
        }

        // Configuración común para los date range pickers
        const commonOptions = {
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este año': [moment().startOf('year'), moment().endOf('year')],
            },
            showDropdowns: true,
            alwaysShowCalendars: true,
            autoUpdateInput: true,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month')
        };

        // Inicializar date range picker para gráfico de dispositivos
        $(document).ready(function() {
            $('#date-range-device').daterangepicker(commonOptions);
            $('#date-range-device').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            // Inicializar date range picker para gráfico de plagas
            $('#date-range-pests').daterangepicker(commonOptions);
            $('#date-range-pests').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });
        });

        // Función para cargar los datos de incidentes vía AJAX con rango de fechas
        async function fetchGraphDataByRange(version, startDate, endDate) {
            try {
                const response = await fetch(`{{ route('floorplan.graphic.incidents', $floorplan->id) }}?version=${version}&startDate=${startDate}&endDate=${endDate}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }

                const data = await response.json();
                return data;
            } catch (error) {
                //console.error('Error al cargar datos:', error);
                alert('Error al cargar los datos del gráfico');
                return null;
            }
        }

        // Función para cargar los datos de incidentes vía AJAX
        async function fetchGraphData(version, month, year) {
            try {
                const response = await fetch(`{{ route('floorplan.graphic.incidents', $floorplan->id) }}?version=${version}&month=${month}&year=${year}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }

                const data = await response.json();
                return data;
            } catch (error) {
                //console.error('Error al cargar datos:', error);
                alert('Error al cargar los datos del gráfico');
                return null;
            }
        }

        // Función para cargar datos de tendencia (todos los meses del año)
        async function fetchTrendData(version, year) {
            try {
                const response = await fetch(`{{ route('floorplan.graphic.incidents', $floorplan->id) }}?version=${version}&year=${year}&trend=true`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }

                const data = await response.json();
                return data;
            } catch (error) {
                //console.error('Error al cargar datos de tendencia:', error);
                alert('Error al cargar los datos de tendencia');
                return null;
            }
        }

        // Función para actualizar el gráfico de dispositivos
        function updateDevicesChart(labels, data) {
            const ctx_d = document.getElementById('devicesChart').getContext('2d');

            if (devicesChart) {
                devicesChart.destroy();
            }

            devicesChart = new Chart(ctx_d, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Incidentes por dispositivo',
                        data: data,
                        borderWidth: 2,
                        borderColor: '#0A2986', // True Cobalt
                        backgroundColor: '#0A298640', // True Cobalt con transparencia
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Función para actualizar el gráfico de plagas
        function updatePestsChart(labels, data) {
            const ctx_p = document.getElementById('pestsChart').getContext('2d');

            if (pestsChart) {
                pestsChart.destroy();
            }

            pestsChart = new Chart(ctx_p, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Incidentes por plaga',
                        data: data,
                        borderColor: '#DE523B', // Fiery Terracotta
                        backgroundColor: '#DE523B40', // Fiery Terracotta con transparencia
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Función para actualizar el gráfico de tendencia
        function updateTrendChart(labels, data) {
            const ctx_t = document.getElementById('trendChart').getContext('2d');

            if (trendChart) {
                trendChart.destroy();
            }

            trendChart = new Chart(ctx_t, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total de incidentes por mes',
                        data: data,
                        borderColor: '#512A87', // Indigo Velvet
                        backgroundColor: '#512A8720', // Indigo Velvet con transparencia
                        borderWidth: 2,
                        pointRadius: 5,
                        pointBackgroundColor: '#512A87', // Indigo Velvet
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    }
                }
            });
        }

        // Inicializar gráficos con datos iniciales
        function initializeCharts() {
            updateDevicesChart({!! json_encode($graph_per_devices['labels']) !!}, {!! json_encode($graph_per_devices['data']) !!});
            updatePestsChart({!! json_encode($graph_per_pests['labels']) !!}, {!! json_encode($graph_per_pests['data']) !!});
            updateTrendChart({!! json_encode($graph_per_months['labels']) !!}, {!! json_encode($graph_per_months['data']) !!});
        }

        // Event listeners para los botones de búsqueda
        document.getElementById('search-devices-btn').addEventListener('click', async function() {
            const version = document.getElementById('floorplan-version-device').value;
            const dateRangeData = getDateRangeData('#date-range-device');

            if (!version) {
                alert('Por favor, seleccione una versión');
                return;
            }

            if (!dateRangeData || !dateRangeData.startDate || !dateRangeData.endDate) {
                alert('Por favor, seleccione un rango de fechas');
                return;
            }

            const startDate = dateRangeData.startDate.format('YYYY-MM-DD');
            const endDate = dateRangeData.endDate.format('YYYY-MM-DD');

            document.getElementById('devices-loader').style.display = 'block';

            const graphData = await fetchGraphDataByRange(version, startDate, endDate);
            if (graphData && graphData.success) {
                updateDevicesChart(graphData.devices.labels, graphData.devices.data);
            }

            document.getElementById('devices-loader').style.display = 'none';
        });

        document.getElementById('search-pests-btn').addEventListener('click', async function() {
            const version = document.getElementById('floorplan-version-pests').value;
            const dateRangeData = getDateRangeData('#date-range-pests');

            if (!version) {
                alert('Por favor, seleccione una versión');
                return;
            }

            if (!dateRangeData || !dateRangeData.startDate || !dateRangeData.endDate) {
                alert('Por favor, seleccione un rango de fechas');
                return;
            }

            const startDate = dateRangeData.startDate.format('YYYY-MM-DD');
            const endDate = dateRangeData.endDate.format('YYYY-MM-DD');

            document.getElementById('pests-loader').style.display = 'block';

            const graphData = await fetchGraphDataByRange(version, startDate, endDate);
            if (graphData && graphData.success) {
                updatePestsChart(graphData.pests.labels, graphData.pests.data);
            }

            document.getElementById('pests-loader').style.display = 'none';
        });

        document.getElementById('search-trend-btn').addEventListener('click', async function() {
            const version = document.getElementById('floorplan-version-trend').value;
            const year = document.getElementById('floorplan-year-trend').value;

            if (!version || !year) {
                alert('Por favor, complete todos los filtros');
                return;
            }

            document.getElementById('trend-loader').style.display = 'block';

            const graphData = await fetchTrendData(version, year);
            if (graphData && graphData.success) {
                updateTrendChart(graphData.trend.labels, graphData.trend.data);
            }

            document.getElementById('trend-loader').style.display = 'none';
        });

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
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

            const chartInstance = Chart.getChart(canvas);
            if (!chartInstance || !chartInstance.data) return '';

            const datasets = chartInstance.data.datasets;
            const labels = chartInstance.data.labels;

            if (!datasets || datasets.length === 0) return '';

            // Determinar el tipo de análisis
            const isDeviceAnalysis = chartId === 'devicesChart' || chartTitle.toLowerCase().includes('dispositivo');
            const isPestAnalysis = chartId === 'pestsChart' || chartTitle.toLowerCase().includes('plaga');
            const entityType = isDeviceAnalysis ? 'dispositivo' : 'periodo';

            // Análisis para gráficas de línea/barra
            if (chartInstance.config.type === 'line' || chartInstance.config.type === 'bar') {
                const totals = labels.map((label, index) => {
                    return datasets.reduce((sum, dataset) => {
                        const value = dataset.data[index] || 0;
                        return sum + value;
                    }, 0);
                });

                const total = totals.reduce((a, b) => a + b, 0);
                const avg = total / totals.length;

                const maxValue = Math.max(...totals);
                const minValue = Math.min(...totals);
                const maxIndex = totals.indexOf(maxValue);
                const minIndex = totals.indexOf(minValue);
                
                // Validar que los labels existan y no estén vacíos
                let maxLabel = 'N/A';
                let minLabel = 'N/A';
                
                if (maxIndex !== -1 && labels[maxIndex]) {
                    maxLabel = labels[maxIndex];
                }
                
                if (minIndex !== -1 && labels[minIndex]) {
                    minLabel = labels[minIndex];
                }


                // Análisis específico para plagas
                if (isPestAnalysis) {
                    const pestCount = labels.length;
                    const pestList = labels.length <= 3 ? labels.join(' y ') : `${pestCount} tipos de plagas`;
                    
                    let insight = '';
                    if (pestCount === 1) {
                        insight = 'Se recomienda mantener monitoreo constante y aplicar medidas preventivas.';
                    } else if (maxValue > avg * 2) {
                        insight = `La tendencia indica alta concentración en ${maxLabel}, requiere acciones correctivas inmediatas.`;
                    } else {
                        insight = 'La tendencia indica una presencia de atención sobre las plagas y acciones correctivas.';
                    }

                    return `Durante el periodo analizado se registraron ${pestList}, alcanzando su punto máximo en ${maxLabel} con ${maxValue} incidencias. La plaga que menos se registró fue ${minLabel} (${minValue} incidencias). ${insight}`;
                }

                // Para dispositivos o análisis temporal
                let trend = 'estable';
                let increases = 0, decreases = 0;
                for (let i = 1; i < totals.length; i++) {
                    if (totals[i] > totals[i - 1]) increases++;
                    else if (totals[i] < totals[i - 1]) decreases++;
                }
                if (increases > decreases * 1.5) trend = 'creciente';
                else if (decreases > increases * 1.5) trend = 'descendente';

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
                        variationText = `En comparación con ${labels[prevIndex]}, se registró un ${changeType} del ${Math.abs(variation)}%. `;
                    }
                }

                let insight = '';
                if (isDeviceAnalysis) {
                    if (maxValue === minValue) {
                        insight = 'Los valores se mantienen iguales, lo que indica la misma presencia de plagas en todos los dispositivos analizados.';
                    } else if (maxValue > avg * 1.5) {
                        insight = `Se destaca ${maxLabel} con un nivel significativo de incidencias que supera el promedio en ${((maxValue / avg - 1) * 100).toFixed(0)}%.`;
                    } else if (trend === 'creciente') {
                        insight = 'Se observa variabilidad en los niveles de incidencia entre dispositivos.';
                    } else {
                        insight = 'Los valores entre dispositivos muestran patrones similares de incidencia.';
                    }
                } else {
                    if (maxValue > avg * 1.5) {
                        insight = `Se destaca un pico significativo en ${maxLabel} que supera el promedio en ${((maxValue / avg - 1) * 100).toFixed(0)}%.`;
                    } else if (trend === 'creciente') {
                        insight = 'La tendencia general muestra crecimiento sostenido.';
                    } else if (trend === 'descendente') {
                        insight = 'Se observa una tendencia a la baja que requiere atención.';
                    } else {
                        insight = 'Los valores se mantienen relativamente estables.';
                    }
                }

                return `Durante el periodo analizado se observa una tendencia ${trend}, alcanzando su punto máximo en ${maxLabel} con ${maxValue} incidencias. ${variationText}El ${entityType} con menor actividad fue ${minLabel} (${minValue} incidencias). ${insight}`;
            }

            // Análisis para gráficas donut/pie
            if (chartInstance.config.type === 'doughnut' || chartInstance.config.type === 'pie') {
                const data = datasets[0].data.map(v => Number(v) || 0);
                const total = data.reduce((a, b) => a + b, 0);
                
                if (total === 0) return 'No se registraron datos para el periodo seleccionado.';

                const maxValue = Math.max(...data);
                const maxIndex = data.indexOf(maxValue);
                const maxLabel = labels[maxIndex] || 'N/A';
                const percentage = ((maxValue / total) * 100).toFixed(1);

                const dataWithIndices = data.map((value, index) => ({ value, index }))
                    .sort((a, b) => b.value - a.value);
                
                const secondItem = dataWithIndices[1] || dataWithIndices[0];
                const secondValue = secondItem.value;
                const secondIndex = secondItem.index;
                const secondLabel = labels[secondIndex] || 'N/A';
                const secondPercentage = total > 0 ? ((secondValue / total) * 100).toFixed(1) : '0.0';

                let distribution = 'equilibrada';
                const maxPercent = parseFloat(percentage);
                if (maxPercent > 50) distribution = 'concentrada';
                else if (maxPercent < 25) distribution = 'diversificada';

                return `La distribución de los datos muestra que ${maxLabel} representa el ${percentage}% del total con ${maxValue} incidencias, siendo la categoría predominante. Le sigue ${secondLabel} con ${secondPercentage}% (${secondValue} incidencias). La distribución general es ${distribution}.`;
            }

            return '';
        }

        async function exportAllChartsToPDF() {
            const btn = document.getElementById('generateReportBtn');
            const btnContent = document.getElementById('btnContent');
            const btnLoading = document.getElementById('btnLoading');
            
            btn.disabled = true;
            btnContent.style.display = 'none';
            btnLoading.style.display = 'inline-block';

            try {
                // Cargar el logo
                const logoData = await loadImageAsBase64('/images/logo.png');
                
                // Esperar a que todas las gráficas estén renderizadas
                await new Promise(resolve => setTimeout(resolve, 1000));

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'letter');
                
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const margin = 15;
                const contentWidth = pageWidth - (margin * 2);
                
                let currentY = margin;

                // Header
                const headerStartY = 10;
                pdf.setFillColor(255, 255, 255);
                pdf.rect(0, headerStartY, pageWidth, 20, 'F');
                
                // Agregar logo en el header (lado derecho)
                if (logoData) {
                    try {
                        pdf.addImage(logoData, 'PNG', pageWidth - margin - 20, headerStartY + 3, 20, 15);
                    } catch (error) {
                        //console.error('Error agregando logo:', error);
                    }
                }
                
                // Texto del lado izquierdo
                pdf.setTextColor(1, 38, 64); // #012640
                pdf.setFontSize(14);
                pdf.setFont(undefined, 'bold');
                pdf.text('Reporte de Incidencias', margin, headerStartY + 10);
                
                pdf.setFontSize(8);
                pdf.setFont(undefined, 'normal');
                pdf.setTextColor(100, 100, 100);
                pdf.text('Sistema de Gestión Empresarial SISCO ZONDA', margin, headerStartY + 16);

                currentY = headerStartY + 28;

                // Información del reporte
                pdf.setTextColor(0, 0, 0);
                pdf.setFontSize(10);
                pdf.setFont(undefined, 'bold');
                pdf.text('Fecha de generación:', margin, currentY);
                pdf.setFont(undefined, 'normal');
                pdf.text(new Date().toLocaleString('es-MX'), margin + 50, currentY);
                
                currentY += 7;
                pdf.setFont(undefined, 'bold');
                pdf.text('Plano:', margin, currentY);
                pdf.setFont(undefined, 'normal');
                pdf.text('{{ $floorplan->filename }}', margin + 50, currentY);
                
                currentY += 7;
                pdf.setFont(undefined, 'bold');
                pdf.text('Servicio:', margin, currentY);
                pdf.setFont(undefined, 'normal');
                pdf.text('{{ $floorplan->service->name ?? "No aplica" }}', margin + 50, currentY);

                currentY += 12;

                // Obtener periodos de los filtros
                const getDateRangeText = (inputId) => {
                    const input = document.getElementById(inputId);
                    return input && input.value ? input.value : 'Periodo completo';
                };

                // Gráficas
                const charts = [
                    { 
                        id: 'devicesChart', 
                        title: 'Incidencias por dispositivo', 
                        description: 'Análisis de incidencias por dispositivo',
                        period: getDateRangeText('date-range-device')
                    },
                    { 
                        id: 'pestsChart', 
                        title: 'Incidencias por tipo de plaga', 
                        description: 'Distribución de incidencias por tipo de plaga',
                        period: getDateRangeText('date-range-pests')
                    },
                    { 
                        id: 'trendChart', 
                        title: 'Tendencia de incidencias mensuales', 
                        description: 'Evolución temporal de las incidencias',
                        period: getDateRangeText('date-range-trend')
                    }
                ];

                for (let i = 0; i < charts.length; i++) {
                    const chart = charts[i];
                    const canvas = document.getElementById(chart.id);
                    
                    if (!canvas) continue;

                    // Verificar si necesitamos nueva página
                    if (currentY > pageHeight - 100) {
                        pdf.addPage();
                        currentY = margin;
                    }

                    // Título de la gráfica
                    pdf.setFontSize(14);
                    pdf.setFont(undefined, 'bold');
                    pdf.setTextColor(1, 38, 64);
                    pdf.text(chart.title, margin, currentY);
                    
                    currentY += 6;
                    
                    // Descripción
                    pdf.setFontSize(9);
                    pdf.setFont(undefined, 'normal');
                    pdf.setTextColor(100, 100, 100);
                    pdf.text(chart.description, margin, currentY);
                    
                    currentY += 5;

                    // Periodo filtrado
                    if (chart.period) {
                        pdf.setFontSize(8);
                        pdf.setFont(undefined, 'bold');
                        pdf.setTextColor(10, 41, 134);
                        pdf.text('Periodo: ' + chart.period, margin, currentY);
                        currentY += 6;
                    }

                    // Generar y agregar análisis descriptivo
                    const analysis = generateChartAnalysis(chart.id, chart.title);
                    if (analysis) {
                        if (currentY > pageHeight - 120) {
                            pdf.addPage();
                            currentY = margin;
                        }

                        pdf.setFontSize(9);
                        pdf.setFont(undefined, 'normal');
                        pdf.setTextColor(40, 40, 40);
                        
                        const maxWidth = contentWidth - 10;
                        const lines = pdf.splitTextToSize(analysis, maxWidth);
                        
                        const textHeight = lines.length * 4;
                        pdf.setFillColor(245, 247, 250);
                        pdf.roundedRect(margin, currentY - 2, contentWidth, textHeight + 4, 2, 2, 'F');
                        
                        pdf.text(lines, margin + 5, currentY + 2);
                        currentY += textHeight + 8;
                    }

                    // Agregar imagen de la gráfica
                    try {
                        const imgData = canvas.toDataURL('image/png', 1.0);
                        const imgWidth = contentWidth;
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        
                        // Limitar altura máxima
                        const maxHeight = 80;
                        const finalHeight = Math.min(imgHeight, maxHeight);
                        const finalWidth = (canvas.width * finalHeight) / canvas.height;
                        
                        pdf.addImage(imgData, 'PNG', margin, currentY, finalWidth, finalHeight);
                        currentY += finalHeight + 15;
                    } catch (error) {
                        //console.error('Error adding chart:', chart.id, error);
                    }
                }

                // Footer en todas las páginas
                const totalPages = pdf.internal.getNumberOfPages();
                for (let i = 1; i <= totalPages; i++) {
                    pdf.setPage(i);
                    pdf.setFontSize(8);
                    pdf.setTextColor(100, 100, 100);
                    pdf.text(
                        `Página ${i} de ${totalPages} | Generado automáticamente por SISCO ZONDA ERP`,
                        pageWidth / 2,
                        pageHeight - 10,
                        { align: 'center' }
                    );
                }

                // Descargar PDF
                const fileName = `reporte_incidencias_${new Date().toISOString().slice(0, 10)}.pdf`;
                pdf.save(fileName);

            } catch (error) {
                //console.error('Error generando PDF:', error);
                alert('Error al generar el PDF. Por favor, intente nuevamente.');
            } finally {
                btn.disabled = false;
                btnContent.style.display = 'inline-block';
                btnLoading.style.display = 'none';
            }
        }
    </script>
@endsection
