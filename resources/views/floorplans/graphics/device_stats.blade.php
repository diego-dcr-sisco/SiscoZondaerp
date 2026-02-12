@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between border-bottom ps-4 p-2">
            <div class="d-flex align-items-center">
                <a href="{{ route('floorplan.devices', ['id' => $device->floorplan_id, 'version' => $device->version]) }}"
                    class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <span class="text-black fw-bold fs-4">Estadísticas del dispositivo {{ $device->code ?? '' }}</span>
            </div>
            <div class="pe-4">
                <button class="btn btn-dark btn-sm" id="generateReportBtn" onclick="exportAllChartsToPDF()">
                    <i class="bi bi-file-pdf-fill"></i> Generar Reporte
                    <span id="reportLoading" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" style="display: none;"></span>
                </button>
            </div>
        </div>
        <div class="row m-3">
            <div class="col-12">
                <div class="border rounded shadow p-3">
                    <h5 class="fw-bold">Últimas 10 revisiones</h5>
                    @if (isset($reviews) && $reviews->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Pregunta</th>
                                        <th>Respuesta</th>
                                        <th>Orden</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reviews as $rev)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($rev->updated_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $rev->question?->text ?? 'Pregunta' }}</td>
                                            <td>{{ $rev->answer }}</td>
                                            <td>
                                                @if ($rev->order)
                                                    <a
                                                        href="{{ route('order.show', ['id' => $rev->order->id, 'section' => 1]) }}">#{{ $rev->order->folio }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">No se encontraron revisiones recientes para este dispositivo.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 m-3">
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Incidencias por tipo de plaga - {{ $device->code ?? 'Dispositivo' }}</h5>
                        <button class="btn btn-dark btn-sm" onclick="exportChartToPDF('devicePestsChart', 'Incidencias_Plagas_{{ $device->code }}')">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label class="form-label">Plano</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $floorplan->filename }}"
                                disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Rango de Fechas</label>
                            <input type="text" id="device-date-range" class="form-control form-control-sm"
                                placeholder="Seleccionar rango">
                        </div>
                        <div class="col-3 d-flex align-items-end">
                            <button id="search-device-pests" class="btn btn-primary btn-sm">Buscar</button>
                        </div>
                    </div>

                    <div class="position-relative w-100">
                        <div id="pests-loader" class="spinner-border spinner-border-sm position-absolute"
                            style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="devicePestsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Tendencia mensual por año - {{ $device->code ?? 'Dispositivo' }}</h5>
                        <button class="btn btn-dark btn-sm" onclick="exportChartToPDF('deviceTrendChart', 'Tendencia_Anual_{{ $device->code }}')">
                            <i class="bi bi-file-pdf"></i> Exportar PDF
                        </button>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-3">
                            <label class="form-label">Año</label>
                            <select id="trend-year" class="form-select form-select-sm">
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" @if ($y == \Carbon\Carbon::now()->year) selected @endif>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-9 d-flex align-items-end justify-content-end">
                            <button id="search-device-trend" class="btn btn-primary btn-sm">Buscar</button>
                        </div>
                    </div>

                    <div class="position-relative w-100">
                        <div id="trend-loader" class="spinner-border spinner-border-sm position-absolute"
                            style="display:none; top: 10px; right: 10px;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <canvas id="deviceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        const deviceId = {{ $device->id }};
        const floorplanId = {{ $floorplan->id }};
        let pestsChart = null;
        let trendChart = null;

        function updatePestsChart(labels, data) {

            const ctx = document.getElementById('devicePestsChart').getContext('2d');
            if (pestsChart) pestsChart.destroy();
            pestsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Incidentes por plaga',
                        data: data,
                        backgroundColor: '#DE523B40', // Fiery Terracotta con transparencia
                        borderColor: '#DE523B', // Fiery Terracotta
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

        function updateTrendChart(labels, data) {
            const ctx = document.getElementById('deviceTrendChart').getContext('2d');
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Incidentes por mes',
                        data: data,
                        borderColor: '#512A87', // Indigo Velvet
                        backgroundColor: '#512A8720', // Indigo Velvet con transparencia
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
                    }
                }
            });
        }

        // Inicializar con los datos pasados desde el controlador
        document.addEventListener('DOMContentLoaded', function() {
            updatePestsChart({!! json_encode($graph_per_pests['labels']) !!}, {!! json_encode($graph_per_pests['data']) !!});
            updateTrendChart({!! json_encode($graph_per_months['labels']) !!}, {!! json_encode($graph_per_months['data']) !!});

            // Inicializar daterangepicker
            $('#device-date-range').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    weekLabel: 'S',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                },
                alwaysShowCalendars: true,
                autoUpdateInput: false,
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month')
            });

            // Actualizar el input cuando se selecciona un rango
            $('#device-date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            $('#device-date-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Establecer valor inicial
            $('#device-date-range').val(moment().startOf('month').format('DD/MM/YYYY') + ' - ' + moment().endOf(
                'month').format('DD/MM/YYYY'));
        });

        async function fetchDeviceDataByRange(startDate, endDate) {
            try {
                const url =
                    `/floorplans/devices/${floorplanId}/device/${deviceId}/stats?start_date=${startDate}&end_date=${endDate}`;

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const jsonData = await res.json();

                return jsonData;
            } catch (e) {
                return null;
            }
        }

        async function fetchDeviceDataByYear(year) {
            try {
                const url = `/floorplans/devices/${floorplanId}/device/${deviceId}/stats?year=${year}&trend=1`;

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const jsonData = await res.json();

                return jsonData;
            } catch (e) {
                return null;
            }
        }

        document.getElementById('search-device-pests').addEventListener('click', async function() {
            const dateRange = $('#device-date-range').data('daterangepicker');
            if (!dateRange || !dateRange.startDate || !dateRange.endDate) {
                alert('Por favor seleccione un rango de fechas');
                return;
            }
            const startDate = dateRange.startDate.format('YYYY-MM-DD');
            const endDate = dateRange.endDate.format('YYYY-MM-DD');

            document.getElementById('pests-loader').style.display = 'block';

            const data = await fetchDeviceDataByRange(startDate, endDate);

            if (data && data.success) {
                updatePestsChart(data.pests.labels, data.pests.data);
            } else {
                alert('Error al obtener los datos');
            }

            document.getElementById('pests-loader').style.display = 'none';
        });

        document.getElementById('search-device-trend').addEventListener('click', async function() {
            const year = document.getElementById('trend-year').value;
            document.getElementById('trend-loader').style.display = 'block';

            const data = await fetchDeviceDataByYear(year);

            if (data && data.success) {
                updateTrendChart(data.trend.labels, data.trend.data);
            } else {
                alert('Error al obtener los datos de tendencia');
            }

            document.getElementById('trend-loader').style.display = 'none';
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
                
                console.log('✅ PDF generado correctamente:', filename);
            } catch (error) {
                console.error('❌ Error al generar PDF:', error);
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
                pdf.text('Estadísticas del Dispositivo', margin, headerStartY + 10);
                
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
                pdf.text('Dispositivo:', margin, currentY);
                pdf.setFont(undefined, 'normal');
                pdf.text('{{ $device->code }}', margin + 50, currentY);
                
                currentY += 7;
                pdf.setFont(undefined, 'bold');
                pdf.text('Tipo:', margin, currentY);
                pdf.setFont(undefined, 'normal');
                pdf.text('{{ $device->type->name ?? "N/A" }}', margin + 50, currentY);

                currentY += 12;

                // Gráficas
                const charts = [
                    { id: 'devicePestsChart', title: 'Incidencias por tipo de plaga', description: 'Distribución de incidencias por tipo de plaga en el dispositivo' },
                    { id: 'deviceTrendChart', title: 'Tendencia mensual por año', description: 'Evolución temporal de las incidencias del dispositivo' }
                ];

                for (let i = 0; i < charts.length; i++) {
                    const chart = charts[i];
                    const canvas = document.getElementById(chart.id);
                    
                    if (!canvas) {
                        console.warn(`⚠️ No se encontró el canvas: ${chart.id}`);
                        continue;
                    }

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
                const filename = `estadisticas_{{ $device->code }}_${new Date().toISOString().slice(0, 10)}.pdf`;
                pdf.save(filename);
                
                console.log('✅ PDF completo generado correctamente');

            } catch (error) {
                console.error('❌ Error al generar PDF completo:', error);
                alert('Error al generar el PDF. Por favor, intente nuevamente.');
            } finally {
                btn.disabled = false;
                loading.style.display = 'none';
            }
        }
    </script>
@endsection
