@extends('layouts.app')
@section('content')
    <style>
        .font-small {
            font-size: 14px;
        }

        .modal-blur {
            backdrop-filter: blur(5px);
            background-color: rgba(0, 0, 0, 0.3);
        }

        /* Estilos para FullCalendar */
        #calendar {
            background: white;
            border-radius: 8px;
            width: 100% !important;
            overflow: hidden;
        }

        .fc-event {
            cursor: move;
            /* Cambiar cursor para indicar que es arrastrable */
            border: none;
            font-size: 12px;
            padding: 2px 4px;
        }

        /* Estilo para eventos durante el drag */
        .fc-event-dragging {
            opacity: 0.7;
            transform: rotate(5deg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        /* Estilo para área de drop */
        .fc-highlight {
            background: rgba(66, 133, 244, 0.1);
            border: 2px dashed #4285f4;
        }

        .event-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content-custom {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .event-details {
            margin-top: 15px;
        }

        .detail-row {
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .action-buttons {
            margin-top: 20px;
            text-align: right;
        }

        .btn {
            padding: 8px 16px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .filters-sidebar {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .calendar-container {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            overflow: hidden;
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

        /* Estilos específicos para FullCalendar */
        .fc {
            width: 100% !important;
            max-width: 100% !important;
        }

        .fc-view-harness {
            width: 100% !important;
            max-width: 100% !important;
        }

        .fc-scrollgrid {
            width: 100% !important;
            max-width: 100% !important;
        }

        .fc-header-toolbar {
            padding: 10px;
            margin-bottom: 10px !important;
        }

        .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .fc-toolbar-title {
            font-size: 1.4em !important;
            margin: 0 10px;
        }

        .fc-button {
            padding: 6px 12px !important;
            font-size: 0.9em !important;
        }

        /* Estilos para tooltips personalizados */
        .custom-tooltip {
            position: absolute;
            z-index: 1070;
            max-width: 300px;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            border-radius: 6px;
            font-size: 12px;
            line-height: 1.4;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .custom-tooltip.show {
            opacity: 1;
        }

        .custom-tooltip .tooltip-title {
            font-weight: bold;
            margin-bottom: 4px;
            color: #fff;
        }

        .custom-tooltip .tooltip-detail {
            margin: 2px 0;
            color: #e0e0e0;
        }

        .custom-tooltip .tooltip-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
            margin: 6px 0;
        }

        /* Mejoras para eventos con tooltip */
        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        /* Notificación de actualización */
        .update-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            display: none;
        }

        .update-notification.error {
            background: #f44336;
        }

        /* Loading durante el drag */
        .drag-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
        }

        /* Bordes para eventos del calendario */
        .fc-event {
            cursor: move;
            border: none;
            font-size: 12px;
            padding: 2px 4px;
            border-left: 4px solid transparent !important;
            /* Borde izquierdo de color */
        }

        /* Bordes específicos por tipo de servicio */
        .fc-event.domestic {
            border-left-color: #B71C1C !important;
            border-top: 1px solid #B71C1C !important;
        }

        .fc-event.commercial {
            border-left-color: #1B5E20 !important;
            border-top: 1px solid #1B5E20 !important;
        }

        .fc-event.industrial {
            border-left-color: #1A237E !important;
            border-top: 1px solid #1A237E !important;
        }

        /* Bordes más gruesos para mejor visibilidad */
        .fc-event.thick-border {
            border-width: 3px !important;
        }

        /* Bordes en todos los lados */
        .fc-event.full-border {
            border: 2px solid !important;
            border-radius: 4px;
        }

        /* Efecto hover con bordes */
        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            border-width: 2px !important;
            transition: all 0.2s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .fc-toolbar {
                flex-direction: column;
                gap: 10px;
            }

            .fc-toolbar-chunk {
                justify-content: center;
                width: 100%;
            }

            .calendar-container {
                padding: 10px;
            }

            #calendar {
                height: 600px !important;
            }

            .custom-tooltip {
                max-width: 250px;
                font-size: 11px;
            }
        }
    </style>

    <div class="container-fluid font-small p-3">
        <div class="row">
            <!-- Sidebar de Filtros -->
            <div class="col-lg-3 col-md-4 mb-3">
                <div class="filters-sidebar border border-secondary">
                    <h5 class="mb-4 text-primary">
                        <i class="bi bi-funnel-fill me-2"></i>Filtros
                    </h5>

                    <form id="filter-form" action="{{ route('planning.schedule') }}" method="GET">
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
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                                <input type="text" class="form-control form-control-sm" id="customer" name="customer"
                                    value="{{ request('customer') }}" placeholder="Buscar cliente">
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
                                <input type="text" class="form-control form-control-sm date-range-picker" id="date-range"
                                    name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                    autocomplete="off">
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
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-circle-half"></i></span>
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
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-pen-fill"></i></span>
                                <select class="form-select form-select-sm" id="signature_status" name="signature_status">
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

            <!-- Calendario -->
            <div class="col-lg-9 col-md-8">
                <div class="calendar-container rounded border border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-calendar-week me-2"></i>Calendario de Actividades
                        </h5>

                        <div class="legend-container">
                            <div class="d-flex align-items-center gap-3">
                                <!-- Doméstico -->
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-color"
                                        style="background-color: #B71C1C; width: 16px; height: 16px; border-radius: 3px; margin-right: 6px;">
                                    </div>
                                    <small class="text-muted">Doméstico</small>
                                </div>

                                <!-- Comercial -->
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-color"
                                        style="background-color: #1B5E20; width: 16px; height: 16px; border-radius: 3px; margin-right: 6px;">
                                    </div>
                                    <small class="text-muted">Comercial</small>
                                </div>

                                <!-- Industrial -->
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-color"
                                        style="background-color: #1A237E; width: 16px; height: 16px; border-radius: 3px; margin-right: 6px;">
                                    </div>
                                    <small class="text-muted">Industrial</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles del evento -->
    <div id="eventModal" class="event-modal">
        <div class="modal-content-custom">
            <span class="close">&times;</span>
            <h3 id="modalTitle"></h3>
            <div id="modalContent" class="event-details"></div>
        </div>
    </div>

    <!-- Tooltip personalizado -->
    <div id="customTooltip" class="custom-tooltip"></div>

    <!-- Notificación de actualización -->
    <div id="updateNotification" class="update-notification"></div>

    <!-- Loading durante drag -->
    <div id="dragLoading" class="drag-loading">
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Actualizando...</span>
        </div>
        Actualizando evento...
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

    <script>
        let calendar; // Variable global para el calendario
        let tooltipTimeout;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            const tooltip = document.getElementById('customTooltip');
            const updateNotification = document.getElementById('updateNotification');
            const dragLoading = document.getElementById('dragLoading');

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },

                initialDate: '{{ $initial_date }}',

                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día',
                    list: 'Lista'
                },
                events: {!! $calendar_events !!},

                // Configuración para drag and drop
                editable: true, // Habilita la edición (drag and drop)
                droppable: false, // No necesitamos drop externo
                dragRevertDuration: 0, // Sin animación de revert
                dragScroll: true, // Permitir scroll durante el drag

                eventClick: function(info) {
                    showEventDetails(info.event);
                },

                // Evento cuando se completa el drag and drop
                eventDrop: function(info) {
                    updateEventDate(info.event, info.oldEvent, info.revert);
                },

                // Evento cuando se cambia la duración
                eventResize: function(info) {
                    updateEventDuration(info.event, info.oldEvent, info.revert);
                },

                eventDidMount: function(info) {
                    // Agregar tooltip personalizado a cada evento
                    addCustomTooltip(info);
                },
                eventDisplay: 'block',
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                slotMinTime: '00:00:00',
                slotMaxTime: '24:00:00',
                allDaySlot: false,
                navLinks: true,
                selectable: true,
                dayMaxEvents: true,
                height: 700,
                contentHeight: 'auto',

                // Configuraciones para evitar desbordamiento
                windowResize: function(view) {
                    calendar.updateSize();
                },

                // Asegurar que el calendario se ajuste al contenedor
                handleWindowResize: true
            });

            calendar.render();

            // Función para agregar tooltips personalizados
            function addCustomTooltip(info) {
                const eventElement = info.el;
                const extendedProps = info.event.extendedProps;

                // Crear contenido del tooltip
                let tooltipContent = `
                    <div class="tooltip-title">${info.event.title}</div>
                    <div class="tooltip-divider"></div>
                    <div class="tooltip-detail"><strong>Cliente:</strong> ${extendedProps.customer}</div>
                    <div class="tooltip-detail"><strong>Fecha:</strong> ${extendedProps.date}</div>
                    <div class="tooltip-detail"><strong>Horario:</strong> ${extendedProps.time}</div>
                    <div class="tooltip-detail"><strong>Estado:</strong> ${extendedProps.status}</div>
                `;

                if (extendedProps.services) {
                    tooltipContent +=
                        `<div class="tooltip-detail"><strong>Servicios:</strong> ${extendedProps.services}</div>`;
                }

                if (extendedProps.technicians) {
                    tooltipContent +=
                        `<div class="tooltip-detail"><strong>Técnicos:</strong> ${extendedProps.technicians}</div>`;
                }

                // Event listeners para mostrar/ocultar tooltip
                eventElement.addEventListener('mouseenter', function(e) {
                    clearTimeout(tooltipTimeout);

                    tooltipTimeout = setTimeout(() => {
                        tooltip.innerHTML = tooltipContent;
                        tooltip.classList.add('show');

                        // Posicionar el tooltip
                        const rect = eventElement.getBoundingClientRect();
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                        tooltip.style.top = (rect.top + scrollTop - tooltip.offsetHeight - 10) +
                            'px';
                        tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth /
                            2)) + 'px';
                    }, 300);
                });

                eventElement.addEventListener('mouseleave', function() {
                    clearTimeout(tooltipTimeout);
                    tooltip.classList.remove('show');
                });

                eventElement.addEventListener('mousemove', function(e) {
                    if (tooltip.classList.contains('show')) {
                        const x = e.clientX + 10;
                        const y = e.clientY + 10;
                        tooltip.style.top = y + 'px';
                        tooltip.style.left = x + 'px';
                    }
                });
            }

            // Función para actualizar la fecha del evento después del drag and drop
            function updateEventDate(event, oldEvent, revertFunction) {
                const eventId = event.id;
                const newStart = event.start;
                const newEnd = event.end || newStart;

                // Mostrar loading
                dragLoading.style.display = 'block';

                // Preparar datos para enviar al servidor
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('event_id', eventId);
                formData.append('start_date', newStart.toISOString().split('T')[0]);
                formData.append('start_time', newStart.toTimeString().split(' ')[0]);

                if (event.end) {
                    formData.append('end_date', newEnd.toISOString().split('T')[0]);
                    formData.append('end_time', newEnd.toTimeString().split(' ')[0]);
                }

                const jsonData = {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    start_date: newStart.toISOString().split('T')[0],
                    start_time: newStart.toTimeString().split(' ')[0],
                    end_date: newEnd.toISOString().split('T')[0],
                    end_time: newEnd.toTimeString().split(' ')[0]
                };

                console.log(jsonData);

                // Enviar solicitud AJAX al servidor
                fetch('{{ route('planning.updateEventDate') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        dragLoading.style.display = 'none';

                        if (data.success) {
                            showNotification('Evento actualizado correctamente', 'success');

                            // Actualizar las propiedades extendidas del evento
                            event.setExtendedProp('date', formatDate(newStart));
                            event.setExtendedProp('time', formatTime(newStart));

                        } else {
                            showNotification('Error al actualizar el evento: ' + data.message, 'error');
                            revertFunction(); // Revertir el cambio si hay error
                        }
                    })
                    .catch(error => {
                        dragLoading.style.display = 'none';
                        showNotification('Error de conexión: ' + error.message, 'error');
                        revertFunction(); // Revertir el cambio si hay error
                    });
            }

            // Función para actualizar la duración del evento
            function updateEventDuration(event, oldEvent, revertFunction) {
                const eventId = event.id;
                const newStart = event.start;
                const newEnd = event.end;

                if (!newEnd) {
                    revertFunction();
                    return;
                }

                // Mostrar loading
                dragLoading.style.display = 'block';

                const jsonData = {
                    _token: '{{ csrf_token() }}',
                    event_id: eventId,
                    start_date: newStart.toISOString().split('T')[0],
                    start_time: newStart.toTimeString().split(' ')[0],
                    end_date: newEnd.toISOString().split('T')[0],
                    end_time: newEnd.toTimeString().split(' ')[0]
                };

                console.log(jsonData);

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('event_id', eventId);
                formData.append('start_date', newStart.toISOString().split('T')[0]);
                formData.append('start_time', newStart.toTimeString().split(' ')[0]);
                formData.append('end_date', newEnd.toISOString().split('T')[0]);
                formData.append('end_time', newEnd.toTimeString().split(' ')[0]);

                fetch('{{ route('planning.updateEventDuration') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        dragLoading.style.display = 'none';

                        if (data.success) {
                            showNotification('Duración del evento actualizada', 'success');
                        } else {
                            showNotification('Error al actualizar la duración: ' + data.message, 'error');
                            revertFunction();
                        }
                    })
                    .catch(error => {
                        dragLoading.style.display = 'none';
                        showNotification('Error de conexión: ' + error.message, 'error');
                        revertFunction();
                    });
            }

            // Función para mostrar notificaciones
            function showNotification(message, type = 'success') {
                updateNotification.textContent = message;
                updateNotification.className = 'update-notification';

                if (type === 'error') {
                    updateNotification.classList.add('error');
                }

                updateNotification.style.display = 'block';

                setTimeout(() => {
                    updateNotification.style.display = 'none';
                }, 3000);
            }

            // Funciones auxiliares para formatear fechas
            function formatDate(date) {
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }

            function formatTime(date) {
                return date.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            }

            // Forzar el redimensionamiento después de la renderización
            setTimeout(() => {
                calendar.updateSize();
            }, 100);

            // DateRangePicker
            $('input[name="date_range"]').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: [
                        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                },
                opens: 'left',
                autoUpdateInput: false,
                alwaysShowCalendars: true,
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                    'Semana pasada': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1,
                        'week').endOf('week')],
                    'Este trimestre': [moment().startOf('quarter'), moment().endOf('quarter')],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                    'Año pasado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                        'year').endOf('year')]
                },
                showCustomRangeLabel: true,
                alwaysShowCalendars: true,
                linkedCalendars: false
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            // Modal functionality
            var modal = document.getElementById('eventModal');
            var span = document.getElementsByClassName('close')[0];

            span.onclick = function() {
                modal.style.display = 'none';
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }

            function showEventDetails(event) {
                const extendedProps = event.extendedProps;
                document.getElementById('modalTitle').textContent = event.title;

                let modalContent = `
                    <div class="detail-row">
                        <strong>Cliente:</strong> ${extendedProps.customer}
                    </div>
                    <div class="detail-row">
                        <strong>Fecha:</strong> ${extendedProps.date}
                    </div>
                    <div class="detail-row">
                        <strong>Horario:</strong> ${extendedProps.time}
                    </div>
                    <div class="detail-row">
                        <strong>Estado:</strong> ${extendedProps.status}
                    </div>
                `;

                if (extendedProps.products) {
                    modalContent += `
                        <div class="detail-row">
                            <strong>Productos:</strong> ${extendedProps.products}
                        </div>
                    `;
                }

                if (extendedProps.services) {
                    modalContent += `
                        <div class="detail-row">
                            <strong>Servicios:</strong> ${extendedProps.services}
                        </div>
                    `;
                }

                if (extendedProps.technicians) {
                    modalContent += `
                        <div class="detail-row">
                            <strong>Técnicos:</strong> ${extendedProps.technicians}
                        </div>
                    `;
                }

                modalContent += `
                    <div class="action-buttons">
                        <a href="${extendedProps.edit_url}" class="btn btn-primary">Editar Orden</a>
                        <a href="${extendedProps.report_url}" class="btn btn-secondary">Ver Reporte</a>
                    </div>
                `;

                document.getElementById('modalContent').innerHTML = modalContent;
                modal.style.display = 'block';
            }

            // Redimensionar calendario cuando cambie el tamaño de la ventana
            window.addEventListener('resize', function() {
                calendar.updateSize();
            });
        });
    </script>
@endsection
