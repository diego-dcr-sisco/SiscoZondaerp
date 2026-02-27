@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="py-3 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">
                <i class="bi bi-geo-alt-fill"></i> Ubicaciones GPS
            </h2>
            <a href="{{ route('user.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver a Usuarios
            </a>
        </div>

        <!-- Mapa General -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-map"></i> Últimas Ubicaciones de Usuarios
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="card">
            <div class="card-header bg-light">
                <i class="bi bi-people-fill"></i> Usuarios con Ubicación
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Última Ubicación</th>
                                <th>Precisión</th>
                                <th>Fecha/Hora</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong><br>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $user->simpleRole->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="bi bi-geo"></i>
                                            {{ number_format($user->last_latitude, 6) }},
                                            {{ number_format($user->last_longitude, 6) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($user->last_location_accuracy)
                                            <span class="badge {{ $user->last_location_accuracy < 20 ? 'bg-success' : ($user->last_location_accuracy < 50 ? 'bg-warning' : 'bg-danger') }}">
                                                ± {{ number_format($user->last_location_accuracy, 0) }}m
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ $user->last_location_at ? $user->last_location_at->format('d/m/Y H:i') : '-' }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $user->last_location_at ? $user->last_location_at->diffForHumans() : '' }}
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ route('user.locations', ['id' => $user->id]) }}" 
                                           class="btn btn-sm btn-primary"
                                           data-bs-toggle="tooltip" 
                                           title="Ver historial completo">
                                            <i class="bi bi-clock-history"></i> Historial
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-info" 
                                                onclick="centerMapOnUser({{ $user->last_latitude }}, {{ $user->last_longitude }})"
                                                data-bs-toggle="tooltip" 
                                                title="Centrar en mapa">
                                            <i class="bi bi-pin-map-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-geo-alt-fill fs-1"></i>
                                        <p class="mb-0">No hay usuarios con ubicaciones registradas</p>
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

        // Inicializar mapa
        document.addEventListener('DOMContentLoaded', function() {
            // Centro de Bolivia como punto inicial
            map = L.map('map').setView([-17.78, -63.18], 12);

            // Añadir capa de tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Añadir marcadores de usuarios
            const users = @json($users);
            
            if (users.length > 0) {
                const bounds = [];

                users.forEach((user, index) => {
                    const lat = parseFloat(user.last_latitude);
                    const lng = parseFloat(user.last_longitude);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        // Crear icono personalizado según el rol
                        const markerColor = getMarkerColor(user.role_id);
                        
                        const marker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: `<div style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">${index + 1}</div>`,
                                iconSize: [30, 30],
                                iconAnchor: [15, 15]
                            })
                        }).addTo(map);

                        // Popup con información
                        const lastLocationDate = new Date(user.last_location_at);
                        const accuracy = user.last_location_accuracy ? `${Math.round(user.last_location_accuracy)}m` : 'N/A';
                        
                        marker.bindPopup(`
                            <div class="p-2">
                                <h6 class="mb-1"><strong>${user.name}</strong></h6>
                                <p class="mb-1 small text-muted">${user.email}</p>
                                <hr class="my-2">
                                <p class="mb-1 small"><strong>Rol:</strong> ${user.simple_role ? user.simple_role.name : '-'}</p>
                                <p class="mb-1 small"><strong>Precisión:</strong> ± ${accuracy}</p>
                                <p class="mb-1 small"><strong>Última actualización:</strong><br>${lastLocationDate.toLocaleString('es-BO')}</p>
                                <hr class="my-2">
                                <a href="/user/locations/${user.id}" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-clock-history"></i> Ver Historial
                                </a>
                            </div>
                        `);

                        markers.push(marker);
                        bounds.push([lat, lng]);
                    }
                });

                // Ajustar el mapa para mostrar todos los marcadores
                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            }

            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Función para obtener color del marcador según el rol
        function getMarkerColor(roleId) {
            const colors = {
                1: '#dc3545',  // Admin - Rojo
                2: '#0dcaf0',  // Administrativo - Cyan
                3: '#198754',  // Técnico - Verde
                5: '#ffc107',  // Cliente - Amarillo
            };
            return colors[roleId] || '#6c757d'; // Gris por defecto
        }

        // Función para centrar el mapa en un usuario
        function centerMapOnUser(lat, lng) {
            map.setView([lat, lng], 16);
            
            // Encontrar y abrir el popup del marcador
            markers.forEach(marker => {
                const markerLatLng = marker.getLatLng();
                if (markerLatLng.lat === lat && markerLatLng.lng === lng) {
                    marker.openPopup();
                }
            });
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
