@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="py-3 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-geo-alt-fill"></i> Historial de Ubicaciones
                </h2>
                <h5 class="text-muted mb-0">{{ $user->name }}</h5>
            </div>
            <div>
                <a href="{{ route('user.locations.dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('user.locations', ['id' => $user->id]) }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" 
                               value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" 
                               value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-funnel-fill"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="setQuickFilter('today')">
                            Hoy
                        </button>
                        <button type="button" class="btn btn-info btn-sm ms-1" onclick="setQuickFilter('week')">
                            7 días
                        </button>
                        <button type="button" class="btn btn-info btn-sm ms-1" onclick="setQuickFilter('month')">
                            30 días
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Ubicaciones</h6>
                        <h3 class="mb-0">{{ $locations->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Última Ubicación</h6>
                        <p class="mb-0">
                            @if($locations->count() > 0)
                                {{ $locations->first()->recorded_at->format('d/m/Y H:i') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Precisión Promedio</h6>
                        <h3 class="mb-0">
                            @if($locations->count() > 0)
                                ± {{ number_format($locations->avg('accuracy'), 0) }}m
                            @else
                                -
                            @endif
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title">Rango de Fechas</h6>
                        <p class="mb-0 small">
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa con Ruta -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-map"></i> Mapa de Ubicaciones
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>

        <!-- Tabla de Ubicaciones -->
        <div class="card">
            <div class="card-header bg-light">
                <i class="bi bi-list-ul"></i> Detalle de Ubicaciones
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Fecha/Hora</th>
                                <th>Coordenadas</th>
                                <th>Precisión</th>
                                <th>Altitud</th>
                                <th>Velocidad</th>
                                <th>Origen</th>
                                <th>Acciones</th>
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
                                        <span class="badge {{ $location->accuracy < 20 ? 'bg-success' : ($location->accuracy < 50 ? 'bg-warning' : 'bg-danger') }}">
                                            ± {{ number_format($location->accuracy, 0) }}m
                                        </span>
                                    </td>
                                    <td>{{ $location->altitude ? number_format($location->altitude, 0) . 'm' : '-' }}</td>
                                    <td>{{ $location->speed ? number_format($location->speed, 2) . ' m/s' : '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $location->source }}</span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-info" 
                                                onclick="centerMapOnLocation({{ $location->latitude }}, {{ $location->longitude }}, {{ $index }})"
                                                data-bs-toggle="tooltip" 
                                                title="Ver en mapa">
                                            <i class="bi bi-pin-map-fill"></i>
                                        </button>
                                        <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success"
                                           data-bs-toggle="tooltip" 
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

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let map;
        let markers = [];
        let polyline = null;

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
                        const markerColor = index === 0 ? '#198754' : (index === locations.length - 1 ? '#dc3545' : '#0d6efd');
                        const markerLabel = index === 0 ? 'Fin' : (index === locations.length - 1 ? 'Inicio' : (index + 1));
                        
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

                        markers.push({ marker, lat, lng, index });
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
                    map.fitBounds(bounds, { padding: [50, 50] });
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

        function setQuickFilter(period) {
            const endDate = new Date();
            let startDate = new Date();
            
            switch(period) {
                case 'today':
                    startDate = new Date();
                    break;
                case 'week':
                    startDate.setDate(endDate.getDate() - 7);
                    break;
                case 'month':
                    startDate.setDate(endDate.getDate() - 30);
                    break;
            }
            
            document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
            document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
            document.querySelector('form').submit();
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
