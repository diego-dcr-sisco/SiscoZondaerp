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
                    <i class="bi bi-file-pdf-fill"></i> Generar Reporte
                    <span id="reportLoading" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" style="display: none;"></span>
                </button>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 m-3">
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Incidencias por dispositivo</h4>
                        <button class="btn btn-dark btn-sm" onclick="exportChartToPDF('devicesChart', 'Incidencias_por_Dispositivo')">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </div>
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
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Incidencias por tipo de plaga</h4>
                        <button class="btn btn-dark btn-sm" onclick="exportChartToPDF('pestsChart', 'Incidencias_por_Plaga')">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </div>
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
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Tendencia de incidencias mensuales</h4>
                        <button class="btn btn-dark btn-sm" onclick="exportChartToPDF('trendChart', 'Tendencia_Incidencias_Mensuales')">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </div>
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
                format: 'DD/MM/YYYY'
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
            autoUpdateInput: false,
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

            // Mostrar rango inicial
            const initialText = moment().startOf('month').format('DD/MM/YYYY') + ' - ' + moment().endOf('month').format('DD/MM/YYYY');
            $('#date-range-device').val(initialText);
            $('#date-range-pests').val(initialText);
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
                console.error('Error al cargar datos:', error);
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
                console.error('Error al cargar datos:', error);
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
                console.error('Error al cargar datos de tendencia:', error);
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

        // Función para exportar una gráfica individual a PDF
        async function exportChartToPDF(chartId, filename) {
            try {
                const canvas = document.getElementById(chartId);
                if (!canvas) {
                    alert('No se encontró la gráfica');
                    return;
                }

                // Crear una imagen desde el canvas
                const imgData = canvas.toDataURL('image/png', 1.0);
                
                // Crear el PDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                // Dimensiones del PDF
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                
                // Calcular dimensiones para mantener la proporción
                const canvasWidth = canvas.width;
                const canvasHeight = canvas.height;
                const ratio = Math.min(pdfWidth / canvasWidth, pdfHeight / canvasHeight);
                const imgWidth = canvasWidth * ratio * 0.9;
                const imgHeight = canvasHeight * ratio * 0.9;
                
                // Centrar la imagen
                const x = (pdfWidth - imgWidth) / 2;
                const y = (pdfHeight - imgHeight) / 2;

                // Agregar título
                pdf.setFontSize(16);
                pdf.text(filename.replace(/_/g, ' '), pdfWidth / 2, 15, { align: 'center' });
                
                // Agregar la imagen
                pdf.addImage(imgData, 'PNG', x, y + 5, imgWidth, imgHeight);
                
                // Agregar fecha de generación
                pdf.setFontSize(10);
                pdf.text(`Generado: ${new Date().toLocaleDateString('es-ES')} ${new Date().toLocaleTimeString('es-ES')}`, 10, pdfHeight - 10);
                
                // Descargar el PDF
                pdf.save(`${filename}_${new Date().toISOString().split('T')[0]}.pdf`);
            } catch (error) {
                alert('Error al generar el PDF');
            }
        }

        // Función para exportar todas las gráficas en un solo PDF
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

        async function exportAllChartsToPDF() {
            const btn = document.getElementById('generateReportBtn');
            const loading = document.getElementById('reportLoading');
            
            btn.disabled = true;
            loading.style.display = 'inline-block';

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
                        console.error('Error agregando logo:', error);
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

                // Gráficas
                const charts = [
                    { id: 'devicesChart', title: 'Incidencias por dispositivo', description: 'Análisis de incidencias por dispositivo' },
                    { id: 'pestsChart', title: 'Incidencias por tipo de plaga', description: 'Distribución de incidencias por tipo de plaga' },
                    { id: 'trendChart', title: 'Tendencia de incidencias mensuales', description: 'Evolución temporal de las incidencias' }
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
                    
                    currentY += 8;

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
                        console.error('Error adding chart:', chart.id, error);
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
                console.error('Error generando PDF:', error);
                alert('Error al generar el PDF. Por favor, intente nuevamente.');
            } finally {
                btn.disabled = false;
                loading.style.display = 'none';
            }
        }
    </script>
@endsection
