@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('user.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                HISTORIAL DE UBICACIONES DE <span
                    class="bg-warning fs-5 fw-bold text-dark p-1 rounded">{{ strtoupper($user->name) }}</span>
            </span>
        </div>

        <div class="p-3"> <!-- Filtros -->
            <div class="row">
                <div class="col-6">
                    <div class="border rounded shadow-sm p-3 mb-3">
                        <form method="GET" action="{{ route('user.locations', ['id' => $user->id]) }}" class="row g-3"
                            id="filterForm">
                            <div class="col-lg-8 col-12">
                                <label for="date-range" class="form-label">Rango de Fechas</label>
                                <input type="text" class="form-control form-control-sm" id="date-range" name="date_range"
                                    value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                    autocomplete="off">
                            </div>
                            <div class="col-lg-4 col-12 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearFilter()">
                                    <i class="bi bi-x-circle"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-6">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Estadisticas</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total de ubicaciones</td>
                                <td>
                                    @if ($locations->count() > 0)
                                        {{ $locations->first()->recorded_at->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Ultima ubicación</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Precisión Promedio</td>
                                <td>
                                    @if ($locations->count() > 0)
                                        ± {{ number_format($locations->avg('accuracy'), 0) }}m
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Rango de Fechas</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mapa y Tabla en row -->
            <div class="row">
                <!-- Mapa -->
                <div class="col-12 col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-map-fill"></i> Mapa de Ubicaciones
                        </div>
                        <div class="card-body p-0">
                            <div id="map" style="height: 500px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="col-12 col-lg-6 mb-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha/Hora</th>
                                    <th>Coordenadas</th>
                                    <th>Precisión</th>
                                    <th>Altitud</th>
                                    <th>Velocidad</th>
                                    <th>Origen</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $index => $location)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $location->recorded_at->format('d/m/Y H:i:s') }}
                                            <br>
                                            <small class="text-muted">{{ $location->recorded_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="bi bi-geo"></i>
                                                {{ number_format($location->latitude, 6) }},<br>
                                                {{ number_format($location->longitude, 6) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $location->accuracy < 20 ? 'bg-success' : ($location->accuracy < 50 ? 'bg-warning' : 'bg-danger') }}">
                                                ± {{ number_format($location->accuracy, 0) }}m
                                            </span>
                                        </td>
                                        <td>{{ $location->altitude ? number_format($location->altitude, 0) . 'm' : '-' }}
                                        </td>
                                        <td>{{ $location->speed ? number_format($location->speed, 2) . ' m/s' : '-' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $location->source }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info"
                                                onclick="centerMapOnLocation({{ $location->latitude }}, {{ $location->longitude }}, {{ $index }})"
                                                data-bs-toggle="tooltip" title="Ver en mapa">
                                                <i class="bi bi-pin-map-fill"></i>
                                            </button>
                                            <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                                                target="_blank" class="btn btn-sm btn-success" data-bs-toggle="tooltip"
                                                title="Abrir en Google Maps">
                                                <i class="bi bi-google"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-geo-alt-fill fs-1"></i>
                                            <p class="mb-0">No hay ubicaciones registradas en el rango seleccionado</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Daterangepicker CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Moment.js y Daterangepicker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        let map;
        let markers = [];
        let polyline = null;

        $(function() {
            // Configuración de moment.js en español
            moment.locale('es', {
                months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'
                    .split('_'),
                monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
                weekdays: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
                weekdaysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
                weekdaysMin: 'Do_Lu_Ma_Mi_Ju_Vi_Sá'.split('_')
            });

            // Configuración del daterangepicker
            const dateRangeConfig = {
                opens: 'left',
                drops: 'down',
                autoUpdateInput: false,
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Rango personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                        .endOf('month')
                    ],
                    'Este año': [moment().startOf('year'), moment().endOf('year')]
                },
                showDropdowns: true,
                alwaysShowCalendars: true,
                maxDate: moment()
            };

            // Inicializar daterangepicker
            const dateRangePicker = $('#date-range').daterangepicker(dateRangeConfig);

            // Aplicar valor al input cuando se selecciona
            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            // Limpiar valor cuando se cancela
            $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Si hay un valor previo, establecerlo
            @if (request('date_range'))
                const dateRangeValue = '{{ request('date_range') }}';
                $('#date-range').val(dateRangeValue);
            @endif
        });

        function clearFilter() {
            $('#date-range').val('');
            $('#filterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar mapa
            map = L.map('map').setView([-17.78, -63.18], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            const locations = @json($locations);

            if (locations.length > 0) {
                const bounds = [];
                const routePoints = [];

                locations.forEach((location, index) => {
                    const lat = parseFloat(location.latitude);
                    const lng = parseFloat(location.longitude);

                    if (!isNaN(lat) && !isNaN(lng)) {
                        routePoints.push([lat, lng]);

                        // Marcador para cada ubicación
                        const markerColor = index === 0 ? '#198754' : (index === locations.length - 1 ?
                            '#dc3545' : '#0d6efd');
                        const markerLabel = index === 0 ? 'Fin' : (index === locations.length - 1 ?
                            'Inicio' : (index + 1));

                        const marker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: `<div style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px;">${markerLabel}</div>`,
                                iconSize: [30, 30],
                                iconAnchor: [15, 15]
                            })
                        }).addTo(map);

                        const recordedDate = new Date(location.recorded_at);
                        const accuracy = location.accuracy ? `${Math.round(location.accuracy)}m` : 'N/A';
                        const altitude = location.altitude ? `${Math.round(location.altitude)}m` : 'N/A';
                        const speed = location.speed ? `${(location.speed * 3.6).toFixed(1)} km/h` : 'N/A';

                        marker.bindPopup(`
                            <div class="p-2">
                                <h6 class="mb-2"><strong>Punto #${locations.length - index}</strong></h6>
                                <p class="mb-1 small"><strong>Fecha:</strong><br>${recordedDate.toLocaleString('es-BO')}</p>
                                <p class="mb-1 small"><strong>Precisión:</strong> ± ${accuracy}</p>
                                <p class="mb-1 small"><strong>Altitud:</strong> ${altitude}</p>
                                <p class="mb-1 small"><strong>Velocidad:</strong> ${speed}</p>
                                <hr class="my-2">
                                <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="btn btn-sm btn-success w-100">
                                    <i class="bi bi-google"></i> Ver en Google Maps
                                </a>
                            </div>
                        `);

                        markers.push({
                            marker,
                            lat,
                            lng,
                            index
                        });
                        bounds.push([lat, lng]);
                    }
                });

                // Dibujar ruta entre puntos
                if (routePoints.length > 1) {
                    polyline = L.polyline(routePoints, {
                        color: '#0d6efd',
                        weight: 3,
                        opacity: 0.7,
                        smoothFactor: 1
                    }).addTo(map);
                }

                // Ajustar vista para mostrar todos los puntos
                if (bounds.length > 0) {
                    map.fitBounds(bounds, {
                        padding: [50, 50]
                    });
                }
            }

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        function centerMapOnLocation(lat, lng, index) {
            map.setView([lat, lng], 16);

            const markerData = markers.find(m => m.lat === lat && m.lng === lng && m.index === index);
            if (markerData) {
                markerData.marker.openPopup();
            }
        }
    </script>

    <style>
        .custom-marker {
            background: transparent;
            border: none;
        }

        .leaflet-popup-content {
            margin: 0;
            min-width: 250px;
        }

        .leaflet-popup-content-wrapper {
            padding: 0;
            border-radius: 8px;
        }
    </style>
@endsection
