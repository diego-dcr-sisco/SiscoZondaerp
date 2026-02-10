@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between border-bottom ps-4 p-2">
            <div class="d-flex align-items-center">
                <a href="{{ route('floorplan.devices', ['id' => $device->floorplan_id, 'version' => $device->version]) }}"
                    class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <span class="text-black fw-bold fs-4">Estadísticas del dispositivo</span>
            </div>
            <div class="pe-4">
                <button class="btn btn-danger btn-sm" onclick="exportAllChartsToPDF()">
                    <i class="bi bi-file-pdf-fill"></i> Exportar Todo a PDF
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
                        <button class="btn btn-outline-danger btn-sm" onclick="exportChartToPDF('devicePestsChart', 'Incidencias_Plagas_{{ $device->code }}')">
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
                        <h5 class="fw-bold mb-0">Tendencia Anual - {{ $device->code ?? 'Dispositivo' }}</h5>
                        <button class="btn btn-outline-danger btn-sm" onclick="exportChartToPDF('deviceTrendChart', 'Tendencia_Anual_{{ $device->code }}')">
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
                        backgroundColor: 'rgba(222,82,59,0.5)',
                        borderColor: 'rgba(222,82,59)',
                        borderWidth: 1
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
                        borderColor: 'rgba(75,192,75)',
                        backgroundColor: 'rgba(75,192,75,0.1)',
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
        async function exportAllChartsToPDF() {
            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                const charts = [
                    { id: 'devicePestsChart', title: 'Incidencias por Tipo de Plaga - {{ $device->code }}' },
                    { id: 'deviceTrendChart', title: 'Tendencia Anual - {{ $device->code }}' }
                ];

                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();

                for (let i = 0; i < charts.length; i++) {
                    const chart = charts[i];
                    const canvas = document.getElementById(chart.id);
                    
                    if (!canvas) {
                        console.warn(`⚠️ No se encontró el canvas: ${chart.id}`);
                        continue;
                    }

                    if (i > 0) {
                        pdf.addPage();
                    }

                    // Crear imagen desde el canvas
                    const imgData = canvas.toDataURL('image/png', 1.0);
                    
                    // Calcular dimensiones
                    const canvasWidth = canvas.width;
                    const canvasHeight = canvas.height;
                    const ratio = Math.min(pdfWidth / canvasWidth, pdfHeight / canvasHeight);
                    const imgWidth = canvasWidth * ratio * 0.9;
                    const imgHeight = canvasHeight * ratio * 0.9;
                    
                    const x = (pdfWidth - imgWidth) / 2;
                    const y = (pdfHeight - imgHeight) / 2;

                    // Agregar título
                    pdf.setFontSize(16);
                    pdf.text(chart.title, pdfWidth / 2, 15, { align: 'center' });
                    
                    // Agregar la imagen
                    pdf.addImage(imgData, 'PNG', x, y + 5, imgWidth, imgHeight);
                    
                    // Número de página
                    pdf.setFontSize(10);
                    pdf.text(`Página ${i + 1} de ${charts.length}`, pdfWidth - 30, pdfHeight - 10);
                }

                // Agregar fecha en la última página
                pdf.setFontSize(10);
                pdf.text(`Generado: ${new Date().toLocaleDateString('es-ES')} ${new Date().toLocaleTimeString('es-ES')}`, 10, pdfHeight - 10);
                
                // Descargar el PDF
                const filename = `Estadisticas_{{ $device->code }}_${new Date().toISOString().split('T')[0]}.pdf`;
                pdf.save(filename);
                
                console.log('✅ PDF completo generado correctamente');
            } catch (error) {
                console.error('❌ Error al generar PDF completo:', error);
                alert('Error al generar el PDF');
            }
        }
    </script>
@endsection
