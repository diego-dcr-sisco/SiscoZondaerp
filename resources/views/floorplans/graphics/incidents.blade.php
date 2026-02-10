@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
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

        <div class="row row-cols-1 row-cols-lg-2 m-3">
            <div class="col-lg-6 col-12">
                <div class="border rounded shadow p-3">
                    <div class="mb-3">
                        <h4 class="fw-bold">Incidencias por dispositivo</h4>
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
                    <div class="mb-3">
                        <h4 class="fw-bold">Incidencias por tipo de plaga</h4>
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
                    <div class="mb-3">
                        <h4 class="fw-bold">Tendencia de incidencias mensuales</h4>
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
            console.log('📊 fetchGraphDataByRange - Parámetros:', { version, startDate, endDate });
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
                console.log('✅ fetchGraphDataByRange - Datos recibidos:', data);
                return data;
            } catch (error) {
                console.error('Error al cargar datos:', error);
                alert('Error al cargar los datos del gráfico');
                return null;
            }
        }

        // Función para cargar los datos de incidentes vía AJAX
        async function fetchGraphData(version, month, year) {
            console.log('📊 fetchGraphData - Parámetros:', { version, month, year });
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
                console.log('✅ fetchGraphData - Datos recibidos:', data);
                return data;
            } catch (error) {
                console.error('Error al cargar datos:', error);
                alert('Error al cargar los datos del gráfico');
                return null;
            }
        }

        // Función para cargar datos de tendencia (todos los meses del año)
        async function fetchTrendData(version, year) {
            console.log('📊 fetchTrendData - Parámetros:', { version, year });
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
                console.log('✅ fetchTrendData - Datos recibidos:', data);
                return data;
            } catch (error) {
                console.error('Error al cargar datos de tendencia:', error);
                alert('Error al cargar los datos de tendencia');
                return null;
            }
        }

        // Función para actualizar el gráfico de dispositivos
        function updateDevicesChart(labels, data) {
            console.log('📈 updateDevicesChart - Labels:', labels);
            console.log('📈 updateDevicesChart - Data:', data);
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
                        borderWidth: 1,
                        borderColor: 'rgba(2, 38, 90)',
                        backgroundColor: 'rgba(2, 38, 90, 0.5)',
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
            console.log('🐛 updatePestsChart - Labels:', labels);
            console.log('🐛 updatePestsChart - Data:', data);
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
                        borderColor: 'rgba(222, 82, 59)',
                        backgroundColor: 'rgba(222, 82, 59, 0.5)',
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

        // Función para actualizar el gráfico de tendencia
        function updateTrendChart(labels, data) {
            console.log('📉 updateTrendChart - Labels:', labels);
            console.log('📉 updateTrendChart - Data:', data);
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
                        borderColor: 'rgba(75, 192, 75)',
                        backgroundColor: 'rgba(75, 192, 75, 0.1)',
                        borderWidth: 2,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgba(75, 192, 75)',
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
            console.log('🚀 Inicializando gráficos con datos del servidor...');
            console.log('📦 Datos iniciales dispositivos:', { 
                labels: {!! json_encode($graph_per_devices['labels']) !!}, 
                data: {!! json_encode($graph_per_devices['data']) !!} 
            });
            console.log('📦 Datos iniciales plagas:', { 
                labels: {!! json_encode($graph_per_pests['labels']) !!}, 
                data: {!! json_encode($graph_per_pests['data']) !!} 
            });
            console.log('📦 Datos iniciales tendencia:', { 
                labels: {!! json_encode($graph_per_months['labels']) !!}, 
                data: {!! json_encode($graph_per_months['data']) !!} 
            });
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
                console.log('🔄 Actualizando gráfico de dispositivos con:', graphData.devices);
                updateDevicesChart(graphData.devices.labels, graphData.devices.data);
            } else {
                console.warn('⚠️ No se recibieron datos válidos para dispositivos');
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
                console.log('🔄 Actualizando gráfico de plagas con:', graphData.pests);
                updatePestsChart(graphData.pests.labels, graphData.pests.data);
            } else {
                console.warn('⚠️ No se recibieron datos válidos para plagas');
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
                console.log('🔄 Actualizando gráfico de tendencia con:', graphData.trend);
                updateTrendChart(graphData.trend.labels, graphData.trend.data);
            } else {
                console.warn('⚠️ No se recibieron datos válidos para tendencia');
            }

            document.getElementById('trend-loader').style.display = 'none';
        });

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    </script>
@endsection
