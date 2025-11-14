    @extends('layouts.app')
    @section('content')
        <style>
            .font-small {
                font-size: 14px;
            }

            .filters-sidebar {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 20px;
                height: fit-content;
                position: sticky;
                top: 20px;
            }

            .filter-section {
                margin-bottom: 15px;
            }

            .filter-section label {
                font-weight: 500;
                margin-bottom: 5px;
                color: #495057;
            }

            .filter-actions {
                border-top: 1px solid #dee2e6;
                padding-top: 15px;
                margin-top: 15px;
            }

            /* ✅ CONTENEDOR MEJORADO PARA SCROLL HORIZONTAL */
            .table-wrapper {
                overflow-x: auto;
                overflow-y: visible;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                background: white;
                position: relative;
                max-width: 100%;
            }

            /* Indicador visual de que hay más contenido */
            .table-wrapper::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                width: 30px;
                background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1));
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .table-wrapper.scrolling::after {
                opacity: 1;
            }

            /* Estilos para la tabla de planificación */
            .planning-table {
                background: white;
                min-width: fit-content;
                /* ✅ Forzar ancho mínimo */
                width: auto;
                /* ✅ Permitir que crezca según contenido */
            }

            .table-header {
                background: #343a40;
                color: white;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .technician-header {
                background: #495057;
                color: white;
                font-weight: 600;
                text-align: center;
                padding: 12px 8px;
                min-width: 160px;
                /* ✅ Ancho reducido para más columnas */
                border-right: 1px solid #dee2e6;
                font-size: 12px;
                white-space: nowrap;
                position: sticky;
                top: 0;
            }

            .hour-cell {
                background: #e9ecef;
                font-weight: 600;
                text-align: center;
                padding: 12px 8px;
                border-bottom: 1px solid #dee2e6;
                border-right: 1px solid #dee2e6;
                min-width: 80px;
                position: sticky;
                left: 0;
                z-index: 10;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            }

            .order-card {
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                padding: 8px;
                margin: 2px;
                font-size: 11px;
                cursor: grab;
                transition: all 0.2s ease;
                min-height: 60px;
                user-select: none;
                max-width: 180px;
                /* ✅ Limitar ancho máximo */
            }

            .order-card:hover {
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                transform: translateY(-1px);
            }

            .order-card.dragging {
                opacity: 0.5;
                transform: scale(0.95);
                cursor: grabbing;
            }

            .empty-cell {
                background: #f8f9fa;
                min-height: 70px;
                min-width: 160px;
                /* ✅ Mismo ancho que los headers */
                border-right: 1px solid #dee2e6;
                border-bottom: 1px solid #dee2e6;
                transition: all 0.2s ease;
                padding: 2px;
            }

            .empty-cell.drag-over {
                background: #e8f5e8;
                border: 2px dashed #4caf50;
            }

            /* ✅ MEJORAS RESPONSIVAS */
            @media (max-width: 768px) {
                .technician-header {
                    min-width: 140px;
                    font-size: 11px;
                    padding: 10px 6px;
                }

                .hour-cell {
                    min-width: 70px;
                    font-size: 11px;
                    padding: 10px 6px;
                }

                .empty-cell {
                    min-width: 140px;
                }

                .order-card {
                    font-size: 10px;
                    padding: 6px;
                    min-height: 55px;
                }
            }

            @media (max-width: 576px) {
                .technician-header {
                    min-width: 120px;
                    font-size: 10px;
                }

                .hour-cell {
                    min-width: 60px;
                    font-size: 10px;
                }

                .empty-cell {
                    min-width: 120px;
                }
            }

            /* Loading overlay */
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            .loading-spinner {
                background: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
            }

            /* ✅ Scrollbar personalizado */
            .table-wrapper::-webkit-scrollbar {
                height: 12px;
            }

            .table-wrapper::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 0 0 8px 8px;
            }

            .table-wrapper::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 6px;
            }

            .table-wrapper::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
        </style>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 mb-0">Actualizando planificación...</p>
            </div>
        </div>

        <div class="container-fluid font-small p-3">
            <div class="row">
                <!-- Sidebar de Filtros -->
                <div class="col-lg-3 col-md-4 mb-3">
                    <div class="filters-sidebar border border-secondary">
                        <h5 class="mb-4 text-primary">
                            <i class="bi bi-funnel-fill me-2"></i>Filtros
                        </h5>

                        <form id="filter-form" action="{{ route('planning.activities') }}" method="GET">
                            <!-- No. Reporte -->
                            <div class="filter-section">
                                <label for="folio" class="form-label">No. Reporte</label>
                                <div class="input-group input-group-sm  mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-key-fill"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="folio" name="folio"
                                        value="{{ request('folio') }}" placeholder="Buscar por folio... ">
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="filter-section">
                                <label for="customer" class="form-label">Cliente</label>
                                <div class="input-group input-group-sm  mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-person-circle"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="customer"
                                        name="customer" value="{{ request('customer') }}" placeholder="Buscar cliente">
                                </div>

                            </div>

                            <!-- Servicio -->
                            <div class="filter-section">
                                <label for="service" class="form-label">Servicio</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-gear-fill"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="service" name="service"
                                        value="{{ request('service') }}" placeholder="Buscar servicio">
                                </div>
                            </div>

                            <!-- Rango de Fechas -->
                            <div class="filter-section">
                                <label for="date_range" class="form-label">Rango de Fechas</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-calendar-week-fill"></i></span>
                                    <input type="text" class="form-control form-control-sm date-range-picker"
                                        id="date-range" name="date_range" value="{{ request('date_range') }}"
                                        placeholder="Selecciona un rango" autocomplete="off">
                                </div>
                            </div>

                            <!-- Hora -->
                            <div class="filter-section">
                                <label for="time" class="form-label">Hora Programada</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-clock-fill"></i></span>
                                    <input type="time" class="form-control form-control-sm" id="time" name="time"
                                        value="{{ request('time') }}">
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="filter-section">
                                <label for="status" class="form-label">Estado</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-circle-half"></i></span>
                                    <select class="form-select form-select-sm" id="status" name="status">
                                        <option value="">Todos los estados</option>
                                        @foreach ($order_status as $status)
                                            <option value="{{ $status->id }}"
                                                {{ request('status') == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tipo de Orden -->
                            <div class="filter-section">
                                <label for="order_type" class="form-label">Tipo de Orden</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-dash-circle-fill"></i></span>
                                    <select class="form-select form-select-sm" id="order_type" name="order_type">
                                        <option value="">Todos los tipos</option>
                                        <option value="MIP" {{ request('order_type') == 'MIP' ? 'selected' : '' }}>MIP
                                        </option>
                                        <option value="Seguimiento"
                                            {{ request('order_type') == 'Seguimiento' ? 'selected' : '' }}>
                                            Seguimiento
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Firma -->
                            <div class="filter-section">
                                <label for="signature_status" class="form-label">Estado de Firma</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-pen-fill"></i></span>
                                    <select class="form-select form-select-sm" id="signature_status"
                                        name="signature_status">
                                        <option value="">Todos</option>
                                        <option value="signed"
                                            {{ request('signature_status') == 'signed' ? 'selected' : '' }}>
                                            Firmadas
                                        </option>
                                        <option value="unsigned"
                                            {{ request('signature_status') == 'unsigned' ? 'selected' : '' }}>
                                            No Firmadas
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Ordenación -->
                            <div class="filter-section">
                                <label for="direction" class="form-label">Direccion</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-arrow-down-up"></i></span>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                            Ascendente
                                        </option>
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                            Descendente
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Total de resultados -->
                            <div class="filter-section">
                                <label for="size" class="form-label">Resultados por página</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select form-select-sm" id="size" name="size">
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="filter-actions">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-funnel-fill me-1"></i> Aplicar Filtros
                                    </button>
                                    <a href="{{ route('crm.agenda') }}" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar Filtros
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Planificación -->
                <!-- Tabla de Planificación -->
                <div class="col-lg-9 col-md-8">
                    <div class="planning-table">
                        <!-- ✅ CONTENEDOR MEJORADO CON SCROLL -->
                        <div class="table-wrapper" id="tableWrapper">
                            <table class="table table-bordered mb-0">
                                <thead class="table-header">
                                    <tr>
                                        <th class="sticky-hour hour-cell">Hora</th>
                                        @foreach ($technicians as $technician)
                                            <th class="technician-header">
                                                {{ $technician['name'] }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hours as $hour)
                                        <tr>
                                            <td class="sticky-hour hour-cell">
                                                {{ $hour }}
                                            </td>
                                            @foreach ($technicians as $technician)
                                                @php
                                                    $hourData = $planning_data[$hour] ?? null;
                                                    $technicianOrders = $hourData
                                                        ? $hourData['orders']->filter(function ($orderData) use (
                                                            $technician,
                                                        ) {
                                                            return collect($orderData['technicians'])->contains(
                                                                'id',
                                                                $technician['id'],
                                                            );
                                                        })
                                                        : collect();
                                                @endphp
                                                <td class="empty-cell" data-hour="{{ $hour }}"
                                                    data-technician-id="{{ $technician['id'] }}"
                                                    data-technician-name="{{ $technician['name'] }}">
                                                    @if ($technicianOrders->count() > 0)
                                                        @foreach ($technicianOrders as $orderData)
                                                            @php
                                                                $order = $orderData['order'];
                                                                $orderTechnicians = $orderData['technicians'];
                                                            @endphp
                                                            <div class="order-card"
                                                                style="border-color: {{ $orderData['border_color'] }}; background-color: {{ $orderData['bg_color'] }};"
                                                                draggable="true" data-order-id="{{ $order->id }}"
                                                                data-current-hour="{{ $hour }}"
                                                                data-current-technician-id="{{ $technician['id'] }}"
                                                                data-border-color="{{ $orderData['border_color'] }}"
                                                                data-bg-color="{{ $orderData['bg_color'] }}"
                                                                title="Folio: {{ $order->folio ?? $order->id }}
Cliente: {{ $order->customer->name ?? 'N/A' }}
Servicio: {{ $order->service_type ?? 'N/A' }}
Técnicos: {{ implode(', ', collect($orderTechnicians)->pluck('name')->toArray()) }}">
                                                                <div class="order-folio">
                                                                    #{{ $order->folio ?? $order->id }}
                                                                </div>
                                                                <div class="order-customer">
                                                                    <span
                                                                        class="fw-bold">{{ $order->customer->name }}</span>
                                                                    <div class="order-service">
                                                                        {{ \Carbon\Carbon::parse($order->programmed_date)->format('d/m/Y') }}
                                                                        {{ \Carbon\Carbon::parse($order->start_time)->format('h:i A') }}
                                                                        -
                                                                        {{ $order->end_time ? \Carbon\Carbon::parse($order->end_time)->format('h:i A') : '' }}
                                                                    </div>
                                                                </div>
                                                                <div class="order-service">
                                                                    {{ implode(', ', $order->services->pluck('name')->toArray()) }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="text-muted text-center" style="font-size: 11px;">
                                                            Sin órdenes
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

                    <!-- Resumen -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="mb-2">Resumen de Planificación</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small><strong>Técnicos:</strong> {{ $technicians->count() }}</small>
                            </div>
                            <div class="col-md-4">
                                <small><strong>Horas con órdenes:</strong>
                                    {{ collect($planning_data)->where('order_count', '>', 0)->count() }}/24</small>
                            </div>
                            <div class="col-md-4">
                                <small><strong>Total órdenes:</strong>
                                    {{ collect($planning_data)->sum('order_count') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
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
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                        'DD/MM/YYYY'));
                });
            });


            document.addEventListener('DOMContentLoaded', function() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                let draggedOrder = null;

                // Configurar drag and drop para las órdenes
                document.querySelectorAll('.order-card').forEach(card => {
                    card.addEventListener('dragstart', handleDragStart);
                    card.addEventListener('dragend', handleDragEnd);
                });

                // Configurar zonas de drop (las celdas)
                document.querySelectorAll('.empty-cell').forEach(cell => {
                    cell.addEventListener('dragover', handleDragOver);
                    cell.addEventListener('dragenter', handleDragEnter);
                    cell.addEventListener('dragleave', handleDragLeave);
                    cell.addEventListener('drop', handleDrop);
                });

                function handleDragStart(e) {
                    draggedOrder = this;
                    this.classList.add('dragging');

                    // Guardar referencia del elemento original
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        orderId: this.getAttribute('data-order-id'),
                        currentHour: this.getAttribute('data-current-hour'),
                        currentTechnicianId: this.getAttribute('data-current-technician-id'),
                        elementId: this.id || `order-${Date.now()}`
                    }));

                    // Asignar ID único si no tiene
                    if (!this.id) {
                        this.id = `order-${Date.now()}`;
                    }

                    e.dataTransfer.effectAllowed = 'move';
                }

                function handleDragEnd(e) {
                    if (this) {
                        this.classList.remove('dragging');
                    }

                    // Remover clases de drag-over de todas las celdas
                    document.querySelectorAll('.empty-cell').forEach(cell => {
                        cell.classList.remove('drag-over');
                    });

                    // NO resetear draggedOrder aquí, lo necesitamos después
                }

                function handleDragOver(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                }

                function handleDragEnter(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                }

                function handleDragLeave(e) {
                    this.classList.remove('drag-over');
                }

                function handleDrop(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');

                    if (!draggedOrder) {
                        console.error('No hay orden arrastrada');
                        return;
                    }

                    // Obtener datos del elemento arrastrado
                    const dragData = JSON.parse(e.dataTransfer.getData('text/plain'));
                    const orderId = dragData.orderId;
                    const currentHour = dragData.currentHour;
                    const currentTechnicianId = dragData.currentTechnicianId;
                    const elementId = dragData.elementId;

                    // Buscar el elemento en el DOM usando el ID guardado
                    const orderElement = document.getElementById(elementId);
                    if (!orderElement) {
                        console.error('No se pudo encontrar el elemento de la orden en el DOM');
                        return;
                    }

                    const targetCell = this;
                    const targetHour = targetCell.getAttribute('data-hour');
                    const targetTechnicianId = targetCell.getAttribute('data-technician-id');
                    const targetTechnicianName = targetCell.getAttribute('data-technician-name');

                    // Verificar si es un movimiento válido (diferente celda)
                    if (currentHour === targetHour && currentTechnicianId === targetTechnicianId) {
                        console.log('Misma celda, no hacer nada');
                        return;
                    }

                    // Mostrar loading
                    loadingOverlay.style.display = 'flex';

                    console.log('Movimiento:', {
                        orderId,
                        currentHour,
                        currentTechnicianId,
                        targetHour,
                        targetTechnicianId,
                        elementId
                    });

                    // Enviar solicitud AJAX para actualizar la orden
                    fetch('{{ route('planning.update-order') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order_id: orderId,
                                technician_id: targetTechnicianId,
                                hour: targetHour,
                                from_technician_id: currentTechnicianId,
                                from_hour: currentHour
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Usar el elemento encontrado por ID en lugar de draggedOrder
                                moveOrderToCell(orderElement, targetCell, targetHour, targetTechnicianId);
                                showNotification('Orden actualizada correctamente', 'success');
                            } else {
                                showNotification('Error al actualizar la orden: ' + (data.message ||
                                    'Error desconocido'), 'error');
                                // Restaurar el elemento a su posición original si hay error
                                restoreOrderPosition(orderElement, currentHour, currentTechnicianId);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Error de conexión: ' + error.message, 'error');
                            // Restaurar el elemento a su posición original si hay error
                            restoreOrderPosition(orderElement, currentHour, currentTechnicianId);
                        })
                        .finally(() => {
                            loadingOverlay.style.display = 'none';
                            // Resetear draggedOrder después de completar todo
                            draggedOrder = null;
                        });
                }

                function moveOrderToCell(orderElement, targetCell, newHour, newTechnicianId) {
                    console.log('Moviendo orden:', {
                        orderElement,
                        targetCell,
                        newHour,
                        newTechnicianId
                    });

                    if (!orderElement || !targetCell) {
                        console.error('Elemento de orden o celda objetivo no encontrado');
                        return;
                    }

                    if (!document.body.contains(orderElement)) {
                        console.error('El elemento de orden ya no está en el DOM');
                        return;
                    }

                    // ✅ OBTENER colores de data attributes
                    const borderColor = orderElement.getAttribute('data-border-color');
                    const bgColor = orderElement.getAttribute('data-bg-color');

                    // Remover la orden de su celda actual
                    try {
                        if (orderElement.parentNode) {
                            orderElement.parentNode.removeChild(orderElement);
                        }
                    } catch (error) {
                        console.error('Error al remover el elemento:', error);
                        return;
                    }

                    // Actualizar los datos de la orden
                    orderElement.setAttribute('data-current-hour', newHour);
                    orderElement.setAttribute('data-current-technician-id', newTechnicianId);

                    // ✅ APLICAR estilos desde data attributes
                    if (borderColor) {
                        orderElement.style.borderColor = borderColor;
                    }
                    if (bgColor) {
                        orderElement.style.backgroundColor = bgColor;
                    }

                    // Remover la clase dragging
                    orderElement.classList.remove('dragging');

                    // Agregar la orden a la nueva celda
                    const emptyMessage = targetCell.querySelector('.text-muted');
                    if (emptyMessage) {
                        emptyMessage.remove();
                    }

                    targetCell.appendChild(orderElement);

                    updateSummary();
                }

                function restoreOrderPosition(orderElement, originalHour, originalTechnicianId) {
                    if (!orderElement) return;

                    // Buscar la celda original
                    const originalCell = document.querySelector(
                        `.empty-cell[data-hour="${originalHour}"][data-technician-id="${originalTechnicianId}"]`
                    );

                    if (originalCell && document.body.contains(orderElement)) {
                        // Remover de la posición actual si está en otra celda
                        if (orderElement.parentNode) {
                            orderElement.parentNode.removeChild(orderElement);
                        }

                        // Agregar a la celda original
                        const emptyMessage = originalCell.querySelector('.text-muted');
                        if (emptyMessage) {
                            emptyMessage.remove();
                        }

                        originalCell.appendChild(orderElement);
                        orderElement.classList.remove('dragging');
                    }
                }

                function showNotification(message, type) {
                    // Crear notificación Bootstrap
                    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    const alert = document.createElement('div');
                    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
                    alert.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
                    alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                    document.body.appendChild(alert);

                    // Auto-remover después de 5 segundos
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 5000);
                }

                function updateSummary() {
                    // Aquí puedes actualizar el resumen si es necesario
                    console.log('Resumen actualizado');
                }

                // Efectos hover para las cards
                // Efectos hover para las cards - VERSIÓN CORREGIDA
                document.querySelectorAll('.order-card').forEach(card => {
                    let originalBackgroundColor = '';
                    let originalBorderColor = '';

                    card.addEventListener('mouseenter', function() {
                        if (!this.classList.contains('dragging')) {
                            // ✅ GUARDAR colores originales antes de cambiar
                            originalBackgroundColor = this.style.backgroundColor;
                            originalBorderColor = this.style.borderColor;

                            // Aplicar efecto hover
                            this.style.backgroundColor = '#f8f9fa';
                            this.style.borderColor = '#0d6efd'; // Color azul para hover
                        }
                    });

                    card.addEventListener('mouseleave', function() {
                        if (!this.classList.contains('dragging')) {
                            // ✅ RESTAURAR colores originales
                            this.style.backgroundColor = originalBackgroundColor;
                            this.style.borderColor = originalBorderColor;
                        }
                    });
                });

                // ✅ MEJORAR EXPERIENCIA DE SCROLL
                const tableWrapper = document.getElementById('tableWrapper');

                if (tableWrapper) {
                    // Detectar cuando hay scroll horizontal
                    tableWrapper.addEventListener('scroll', function() {
                        if (this.scrollLeft > 10) {
                            this.classList.add('scrolling');
                        } else {
                            this.classList.remove('scrolling');
                        }
                    });

                    // Permitir scroll con rueda del mouse horizontalmente
                    tableWrapper.addEventListener('wheel', function(e) {
                        if (e.deltaY !== 0) {
                            e.preventDefault();
                            this.scrollLeft += e.deltaY;
                        }
                    });
                }

                // ✅ AJUSTAR ALTURA DEL CONTENEDOR
                function adjustTableHeight() {
                    const tableWrapper = document.getElementById('tableWrapper');
                    if (tableWrapper) {
                        const viewportHeight = window.innerHeight;
                        const tableTop = tableWrapper.getBoundingClientRect().top;
                        const calculatedHeight = viewportHeight - tableTop - 30; // 30px de margen

                        tableWrapper.style.maxHeight = `${Math.max(calculatedHeight, 400)}px`;
                    }
                }

                // Ajustar altura al cargar y al redimensionar
                window.addEventListener('load', adjustTableHeight);
                window.addEventListener('resize', adjustTableHeight);
            });
        </script>
    @endsection
