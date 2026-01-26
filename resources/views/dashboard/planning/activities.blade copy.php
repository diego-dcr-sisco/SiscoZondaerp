@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <style>
        .technician-header {
            background-color: #f8f9fa;
            writing-mode: vertical-lr;
            transform: rotate(180deg);
            text-align: center;
            padding: 8px 4px;
            white-space: nowrap;
            min-width: 40px;
            font-weight: 500;
            border-left: 1px solid #dee2e6;
        }

        .time-cell {
            background-color: #f8f9fa;
            font-weight: 500;
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 70px;
            padding: 8px;
            border-right: 1px solid #dee2e6;
        }

        .order-badge {
            cursor: grab;
            transition: all 0.2s;
            font-size: 0.75rem;
            margin-bottom: 4px;
            border: 1px solid rgba(0,0,0,0.1);
            border-left: 3px solid rgba(0,0,0,0.2);
        }

        .order-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-badge.dragging {
            opacity: 0.7;
        }

        .table-container {
            overflow-x: auto;
        }

        .drop-zone {
            min-height: 60px;
            transition: all 0.2s ease;
            padding: 4px;
        }

        .drop-zone.drag-over {
            background-color: rgba(0, 123, 255, 0.05);
            border: 1px dashed #0d6efd;
        }

        .drag-handle {
            cursor: grab;
            margin-right: 4px;
            opacity: 0.6;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .schedule-table {
            font-size: 0.875rem;
        }

        .empty-cell {
            color: #6c757d;
            font-size: 0.8rem;
        }

        /* Colores por tipo de cliente */
        .order-badge[data-customer-type="Doméstico"] {
            background-color: #fff3cd; /* Naranja claro */
            border-left-color: #fd7e14;
        }

        .order-badge[data-customer-type="Comercial"] {
            background-color: #d1e7dd; /* Verde claro */
            border-left-color: #198754;
        }

        .order-badge[data-customer-type="Industrial"] {
            background-color: #cfe2ff; /* Azul claro */
            border-left-color: #0d6efd;
        }

        .customer-type-badge {
            font-size: 0.65rem;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 500;
        }

        .customer-type-domestico {
            background-color: #fd7e14;
            color: white;
        }

        .customer-type-comercial {
            background-color: #198754;
            color: white;
        }

        .customer-type-industrial {
            background-color: #0d6efd;
            color: white;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 15px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            margin-right: 5px;
        }

        /* Nuevos estilos para filtros de tipo de cliente */
        .customer-filter-btn {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .customer-filter-btn.active {
            transform: scale(1.05);
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .order-badge.filtered-out {
            opacity: 0.3;
            transform: scale(0.95);
        }

        .order-badge.highlighted {
            animation: pulse-highlight 1.5s ease-in-out;
            box-shadow: 0 0 0 2px currentColor;
        }

        @keyframes pulse-highlight {
            0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
        }

        .filter-badge {
            font-size: 0.7rem;
            margin-left: 5px;
        }
    </style>

    <div class="container-fluid py-2">
        <!-- Encabezado -->
        <div class="d-flex align-items-center mb-3">
            <h5 class="text-dark mb-0">
                <i class="bi bi-people-fill me-1"></i>Cronograma por Técnico
            </h5>
            <div class="ms-auto">
                <button id="saveChanges" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-check-circle me-1"></i>Guardar
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <form action="{{ route('planning.activities') }}" method="GET" id="filterForm">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="date_range" class="form-label small">Rango de Fechas</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="text" class="form-control date-range-picker" id="date-range"
                                    name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona rango"
                                    autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="col-md-8 text-end pt-2">
                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                <i class="bi bi-funnel me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('planning.activities') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filtros rápidos por tipo de cliente - NUEVA SECCIÓN -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="small mb-2">Filtrar por tipo de cliente:</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-warning customer-filter-btn" data-customer-type="Doméstico">
                                <i class="bi bi-house me-1"></i>Doméstico
                                <span class="filter-badge badge bg-warning text-dark" id="domestico-count">0</span>
                            </button>
                            <button type="button" class="btn btn-outline-success customer-filter-btn" data-customer-type="Comercial">
                                <i class="bi bi-building me-1"></i>Comercial
                                <span class="filter-badge badge bg-success" id="comercial-count">0</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary customer-filter-btn" data-customer-type="Industrial">
                                <i class="bi bi-gear me-1"></i>Industrial
                                <span class="filter-badge badge bg-primary" id="industrial-count">0</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary customer-filter-btn active" data-customer-type="all">
                                <i class="bi bi-eye me-1"></i>Todos
                                <span class="filter-badge badge bg-secondary" id="total-count">0</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small mb-2">Leyenda de tipos de cliente:</h6>
                        <div class="d-flex flex-wrap">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #fff3cd; border-left: 3px solid #fd7e14;"></div>
                                <span class="small">Doméstico</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #d1e7dd; border-left: 3px solid #198754;"></div>
                                <span class="small">Comercial</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #cfe2ff; border-left: 3px solid #0d6efd;"></div>
                                <span class="small">Industrial</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Órdenes -->
        <div class="row mb-3">
            @foreach($technician_orders as $techId => $techData)
                <div class="col-6 col-md-3 col-lg-2 mb-2">
                    <div class="card">
                        <div class="card-body py-2 text-center">
                            <h6 class="card-title mb-0 small">{{ $techData['name'] }}</h6>
                            <div class="text-primary fw-bold mt-1">{{ count($techData['orders']) }}</div>
                            <small class="text-muted">órdenes</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tabla de Cronograma -->
        <div class="card">
            <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                <span class="small">Distribución de órdenes - Arrastra para reorganizar</span>
                <span class="badge bg-info" id="visible-orders-counter">Mostrando 0 de 0 órdenes</span>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <table class="table table-sm align-middle schedule-table mb-0">
                        <thead>
                            <tr>
                                <th class="time-cell">Hora</th>
                                @foreach ($technicians as $techId => $techName)
                                    <th class="technician-header">{{ $techName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timelapse as $hrs)
                                <tr>
                                    <td class="time-cell fw-medium">{{ $hrs }}</td>
                                    @foreach ($technicians as $techId => $techName)
                                        <td class="p-1 drop-zone" data-hour="{{ $hrs }}"
                                            data-technician="{{ $techId }}">
                                            @if (isset($data[$hrs][$techId]) && count($data[$hrs][$techId]) > 0)
                                                @foreach ($data[$hrs][$techId] as $order)
                                                    @php
                                                        // Determinar el tipo de cliente basado en el nombre o alguna lógica
                                                        $customerType = 'Doméstico'; // Valor por defecto
                                                        if (strpos(strtolower($order['customer']), 'comercial') !== false || 
                                                            strpos(strtolower($order['customer']), 'empresa') !== false || 
                                                            strpos(strtolower($order['customer']), 's.a.') !== false || 
                                                            strpos(strtolower($order['customer']), 's.r.l.') !== false) {
                                                            $customerType = 'Comercial';
                                                        } elseif (strpos(strtolower($order['customer']), 'industrial') !== false || 
                                                                  strpos(strtolower($order['customer']), 'factory') !== false || 
                                                                  strpos(strtolower($order['customer']), 'manufactur') !== false) {
                                                            $customerType = 'Industrial';
                                                        }
                                                    @endphp
                                                    <div class="order-badge rounded p-1"
                                                        draggable="true" 
                                                        data-order-id="{{ $order['order_id'] }}"
                                                        data-customer-type="{{ $customerType }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $customerType }}: {{ $order['customer'] }} - {{ $order['service'] }}">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="bi bi-grip-vertical drag-handle"></i>
                                                            <span class="fw-medium">#{{ $order['order_folio'] }}</span>
                                                            <span class="badge ms-auto 
                                                                @if ($order['status'] == 'Pendiente') bg-warning text-dark
                                                                @elseif($order['status'] == 'Completado') bg-success
                                                                @elseif($order['status'] == 'Cancelado') bg-danger
                                                                @elseif($order['status'] == 'En proceso') bg-info
                                                                @else bg-secondary @endif" 
                                                                style="font-size: 0.65rem;">
                                                                {{ substr($order['status'], 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div class="text-truncate" style="max-width: 120px; font-size: 0.7rem;">
                                                            {{ $order['customer'] }}
                                                        </div>
                                                        <div class="customer-type-badge mt-1 
                                                            @if($customerType == 'Doméstico') customer-type-domestico
                                                            @elseif($customerType == 'Comercial') customer-type-comercial
                                                            @else customer-type-industrial @endif">
                                                            {{ $customerType }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="empty-cell text-center py-1">
                                                    <i class="bi bi-dash-lg"></i>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Configuración del date range picker
            $(function() {
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
                    autoUpdateInput: false
                };

                $('#date-range').daterangepicker(commonOptions);

                $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                });
            });

            // NUEVA FUNCIONALIDAD: Filtrado por tipo de cliente
            let currentFilter = 'all';
            let orderStatistics = {
                'Doméstico': 0,
                'Comercial': 0,
                'Industrial': 0,
                'total': 0
            };

            // Contar órdenes por tipo
            function countOrdersByType() {
                orderStatistics = {
                    'Doméstico': 0,
                    'Comercial': 0,
                    'Industrial': 0,
                    'total': 0
                };

                document.querySelectorAll('.order-badge').forEach(badge => {
                    const type = badge.getAttribute('data-customer-type');
                    orderStatistics[type]++;
                    orderStatistics.total++;
                });

                // Actualizar contadores en los botones
                document.getElementById('domestico-count').textContent = orderStatistics['Doméstico'];
                document.getElementById('comercial-count').textContent = orderStatistics['Comercial'];
                document.getElementById('industrial-count').textContent = orderStatistics['Industrial'];
                document.getElementById('total-count').textContent = orderStatistics.total;
            }

            // Aplicar filtro
            function applyFilter(customerType) {
                currentFilter = customerType;
                let visibleCount = 0;

                document.querySelectorAll('.order-badge').forEach(badge => {
                    const badgeType = badge.getAttribute('data-customer-type');
                    
                    if (customerType === 'all' || badgeType === customerType) {
                        badge.classList.remove('filtered-out');
                        badge.classList.add('highlighted');
                        visibleCount++;
                        
                        // Remover highlight después de la animación
                        setTimeout(() => {
                            badge.classList.remove('highlighted');
                        }, 1500);
                    } else {
                        badge.classList.add('filtered-out');
                        badge.classList.remove('highlighted');
                    }
                });

                // Actualizar contador de órdenes visibles
                document.getElementById('visible-orders-counter').textContent = 
                    `Mostrando ${visibleCount} de ${orderStatistics.total} órdenes`;

                // Actualizar estado activo de los botones
                document.querySelectorAll('.customer-filter-btn').forEach(btn => {
                    if (btn.getAttribute('data-customer-type') === customerType) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            // Event listeners para botones de filtro
            document.querySelectorAll('.customer-filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerType = this.getAttribute('data-customer-type');
                    applyFilter(customerType);
                });
            });

            // Inicializar contadores y aplicar filtro por defecto
            countOrdersByType();
            applyFilter('all');

            // Implementación de Drag and Drop
            let draggedItem = null;
            const changes = [];

            // Eventos para elementos arrastrables
            document.querySelectorAll('.order-badge[draggable="true"]').forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    draggedItem = this;
                    this.classList.add('dragging');
                    e.dataTransfer.setData('text/plain', this.getAttribute('data-order-id'));
                    e.dataTransfer.effectAllowed = 'move';
                });

                item.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                    document.querySelectorAll('.drop-zone').forEach(zone => {
                        zone.classList.remove('drag-over');
                    });
                });
            });

            // Eventos para zonas de destino
            document.querySelectorAll('.drop-zone').forEach(zone => {
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                    e.dataTransfer.dropEffect = 'move';
                });

                zone.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                });

                zone.addEventListener('dragleave', function() {
                    this.classList.remove('drag-over');
                });

                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');

                    if (draggedItem) {
                        const orderId = draggedItem.getAttribute('data-order-id');
                        const customerType = draggedItem.getAttribute('data-customer-type');
                        const fromZone = draggedItem.closest('.drop-zone');
                        const toZone = this;

                        // Guardar el cambio
                        changes.push({
                            order_id: orderId,
                            from: {
                                hour: fromZone.getAttribute('data-hour'),
                                technician: fromZone.getAttribute('data-technician')
                            },
                            to: {
                                hour: toZone.getAttribute('data-hour'),
                                technician: toZone.getAttribute('data-technician')
                            }
                        });

                        // Mover visualmente el elemento
                        if (fromZone !== toZone) {
                            toZone.appendChild(draggedItem);

                            // Si la zona de origen queda vacía, añadir el indicador de vacío
                            if (fromZone.querySelectorAll('.order-badge').length === 0) {
                                fromZone.innerHTML = '<div class="empty-cell text-center py-1"><i class="bi bi-dash-lg"></i></div>';
                            }
                            
                            // Si la zona tenía el indicador de vacío, removerlo
                            const emptyIndicator = toZone.querySelector('.empty-cell');
                            if (emptyIndicator) {
                                emptyIndicator.remove();
                            }

                            // Recontar estadísticas después del movimiento
                            setTimeout(countOrdersByType, 100);
                        }
                    }
                });
            });

            // Guardar cambios
            document.getElementById('saveChanges').addEventListener('click', function() {
                if (changes.length === 0) {
                    alert('No hay cambios para guardar');
                    return;
                }

                // Mostrar loading
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...';
                this.disabled = true;

                // Enviar cambios al servidor
                fetch('{{ route('planning.updateAssignments') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ changes: changes })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Cambios guardados correctamente');
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error al guardar los cambios');
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    });
            });
        });
    </script>
@endsection