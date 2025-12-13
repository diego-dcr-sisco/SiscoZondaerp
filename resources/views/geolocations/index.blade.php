@extends('layouts.app')

@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <style>
        #map {
            height: calc(100vh - 120px);
            width: 100%;
        }

        .device-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .device-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .device-item.selected {
            background-color: #e3f2fd;
            border-left-color: #2196F3;
            font-weight: bold;
        }

        .device-item.geolocated {
            background-color: #e8f5e9;
            border-left-color: #4CAF50;
        }

        .device-item.geolocated .badge {
            background-color: #4CAF50 !important;
        }

        .device-item .badge {
            background-color: #dc3545;
        }

        .sidebar {
            height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }

        .search-box {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: 400px;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .color-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            border: 2px solid #fff;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        }

        .filter-section {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>

    <div class="container-fluid p-0">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loading-overlay">
            <div class="text-center text-white">
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Guardando...</span>
                </div>
                <div class="mt-3 fs-5">Guardando coordenadas...</div>
            </div>
        </div>

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between border-bottom ps-4 p-2 bg-light">
            <div class="d-flex align-items-center">
                <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <span class="text-black fw-bold fs-4">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    Geolocalización - <span class="fs-5 bg-warning p-1 rounded">{{ $floorplan->filename }}</span>
                </span>
            </div>
            <div class="pe-4">
                <span class="badge bg-info me-2">Cliente: {{ $customer->name }}</span>
                <span class="badge bg-secondary" id="stats-badge">0 de {{ count($devices) }} geolocalizados</span>
            </div>
        </div>

        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 border-end bg-light sidebar">
                <div class="p-3">
                    <!-- Search Address -->
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-search"></i> Buscar Dirección
                        </div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" class="form-control" id="address-search" 
                                       placeholder="Buscar dirección...">
                                <button class="btn btn-primary" type="button" id="search-btn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">Busca una dirección para centrar el mapa</small>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-funnel"></i> Filtros
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Tipo de Control Point</label>
                                <select class="form-select form-select-sm" id="filter-control-point">
                                    <option value="">Todos los tipos</option>
                                    @foreach($controlPoints as $cp)
                                        <option value="{{ $cp['id'] }}">{{ $cp['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Zona de Aplicación</label>
                                <select class="form-select form-select-sm" id="filter-area">
                                    <option value="">Todas las zonas</option>
                                    @foreach($applicationAreas as $area)
                                        <option value="{{ $area['id'] }}">{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Estado</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-status" 
                                           id="filter-all" value="all" checked>
                                    <label class="form-check-label small" for="filter-all">
                                        Todos
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-status" 
                                           id="filter-geolocated" value="geolocated">
                                    <label class="form-check-label small" for="filter-geolocated">
                                        <i class="bi bi-check-circle text-success"></i> Geolocalizados
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-status" 
                                           id="filter-pending" value="pending">
                                    <label class="form-check-label small" for="filter-pending">
                                        <i class="bi bi-exclamation-circle text-danger"></i> Pendientes
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-bar-chart"></i> Estadísticas
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">Total dispositivos:</span>
                                <span class="badge bg-primary" id="total-devices">{{ count($devices) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">Geolocalizados:</span>
                                <span class="badge bg-success" id="geolocated-count">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small">Pendientes:</span>
                                <span class="badge bg-danger" id="pending-count">{{ count($devices) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small">Modificados:</span>
                                <span class="badge bg-warning" id="modified-count">0</span>
                            </div>
                            <div class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" id="progress-bar" 
                                     style="width: 0%">0%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="alert alert-light border shadow-sm">
                        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Instrucciones</h6>
                        <small>
                            <ol class="mb-0 ps-3">
                                <li>Selecciona un dispositivo de la lista</li>
                                <li>Haz clic en el mapa para colocarlo</li>
                                <li>Arrastra los marcadores para ajustar</li>
                                <li>Guarda cuando termines</li>
                            </ol>
                        </small>
                    </div>
                    
                    <!-- Devices List -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-list-ul"></i> Dispositivos</span>
                            <button class="btn btn-sm btn-light" id="clear-selection">
                                <i class="bi bi-x-circle"></i> Limpiar selección
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div id="devices-list" style="max-height: calc(100vh - 400px); overflow-y: auto;">
                                <!-- Se llenará con JavaScript -->
                            </div>
                        </div>
                    </div>
                    

                    <!-- Save Button -->
                    <div class="d-grid gap-2 mt-3 sticky-bottom bg-light pb-3">
                        <button class="btn btn-success btn-lg" id="save-btn" disabled>
                            <i class="bi bi-save"></i> Guardar Coordenadas
                        </button>
                        <button class="btn btn-outline-secondary" id="reset-btn">
                            <i class="bi bi-arrow-clockwise"></i> Restablecer Cambios
                        </button>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="col-md-9 position-relative">
                <!-- Map Type Controls -->
                <div class="map-controls">
                    <div class="btn-group shadow">
                        <button type="button" class="btn btn-sm btn-light" onclick="changeMapType('roadmap')">
                            Mapa
                        </button>
                        <button type="button" class="btn btn-sm btn-light active" onclick="changeMapType('satellite')">
                            Satélite
                        </button>
                        <button type="button" class="btn btn-sm btn-light" onclick="changeMapType('hybrid')">
                            Híbrido
                        </button>
                    </div>
                </div>

                <div id="map"></div>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places,geometry"></script>
    <script src="{{ asset('js/geolocations/map.js') }}"></script>
    <script>
        // Inicializar cuando cargue la página
        window.onload = function() {
            const devicesData = @json($devices);
            const customerAddress = "{{ $customer->address ?? '' }}";
            const customerCity = "{{ $customer->city ?? '' }}";
            const customerState = "{{ $customer->state ?? '' }}";
            const updateUrl = "{{ route('floorplan.geolocation.update') }}";
            const csrfToken = "{{ csrf_token() }}";

            // Inicializar mapa
            initMap(devicesData, customerAddress, customerCity, customerState);

            // Configurar event listeners
            setupEventListeners(updateUrl, csrfToken);
        };
    </script>
@endsection
