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
    </style>

    <div class="container-fluid font-small p-3">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $nav == 'c' ? 'active' : '' }}" aria-current="page" href="{{ route('crm.agenda') }}">Calendario</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $nav == 't' ? 'active' : '' }}" href="{{ route('crm.tracking') }}">Seguimientos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $nav == 'q' ? 'active' : '' }}" href="{{ route('crm.quotation') }}">Cotizaciones</a>
            </li>
        </ul>
        <div class="card">
            <div class="card-header fw-bold">
                Calendario de actividades
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Añade esto en tu vista -->
    <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @include('dashboard.crm.tracking.modals.complete')
    @include('dashboard.crm.tracking.modals.cancel')

    <script>
        // Mapeo de estados
        const statusMap = {
            'active': 'Activo',
            'completed': 'Completado',
            'pending': 'Pendiente',
            'cancelled': 'Cancelado'
        };

        $(document).ready(function() {
            // Inicializar el calendario
            var calendarEl = $('#calendar')[0];
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                events: {!! $calendar_events !!},
                eventClick: function(info) {
                    // Mostrar detalles del pedido en un modal
                    showOrderDetails(info.event);
                },
                eventDidMount: function(info) {
                    console.log(info);
                    $(info.el).tooltip({
                        title: info.event.title +
                            '<br>Fecha: ' + info.event.start.toLocaleDateString() +
                            '<br>Status: ' + info.event.extendedProps.status,
                        html: true,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                headerToolbar: {
                    //left: 'prev,next today',
                    //center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                initialDate: new Date().toISOString().split('T')[0]
            });
            calendar.render();

            // Función para mostrar detalles del pedido
            function showOrderDetails(order) {
                // Procesar observaciones solo si es tipo tracking
                const description = (order.extendedProps.type == 'tracking' && order.extendedProps.description) ?
                    order.extendedProps.description.replace(/\\n/g, '\n') :
                    order.extendedProps.description || '-';

                $('#orderModal .modal-title').text(order.title);
                $('#orderModal .modal-body').html(`
                    <ol class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Cliente:</strong> ${order.extendedProps.customer || '-'}</li>

                        ${order.extendedProps.type == 'order' ? `
                                                <li class="list-group-item"><strong>Estado:</strong> ${order.extendedProps.status || '-'}</li>
                                                <li class="list-group-item"><strong>Fecha:</strong> ${order.extendedProps.date || '-'}</li>
                                                <li class="list-group-item"><strong>Hora:</strong> ${order.extendedProps.time || '-'}</li>
                                                <li class="list-group-item"><strong>Servicio(s):</strong> ${order.extendedProps.services || '-'}</li>
                                                <li class="list-group-item"><strong>Tecnico(s):</strong> ${order.extendedProps.technicians || '-'}</li>
                                                <li class="list-group-item"><strong>Producto(s):</strong> ${order.extendedProps.products || '-'}</li>
                                            ` : `
                                                <li class="list-group-item"><strong>Estado:</strong>
                                                    <span class="text-${order.extendedProps.status == 'active' ? 'success' :
                                                                    (order.extendedProps.status == 'completed' ? 'primary' : 'danger')}">
                                                        ${statusMap[order.extendedProps.status] || order.extendedProps.status}
                                                    </span>
                                                </li>
                                                <li class="list-group-item"><strong>Fecha:</strong> ${order.extendedProps.date || '-'}</li>
                                                <li class="list-group-item"><strong>Titulo:</strong> ${order.extendedProps.title || '-'}</li>
                                                ${order.extendedProps.type == 'tracking' ? `
                                <li class="list-group-item">
                                    <strong>Observaciones:</strong> ${description}
                                </li>
                            ` : ''}
                                                                                                                                                        `}
                    </ol>
                    <div class="p-3 pb-0">
                        <div class="float-end">
                            <a href="${order.extendedProps.edit_url || '#'}">Ver detalles</a>
                        </div>
                        <div class="btn-group" role="group">
                            ${order.extendedProps.type == 'order' ? `
                                                    <a class="btn btn-dark btn-sm" href="${order.extendedProps.report_url || '#'}">Ver reporte</a>
                                                ` : `
                                                    <a class="btn btn-warning btn-sm" href="${order.extendedProps.auto_url || '#'}"
                                                    onclick="return confirm('La reprogramación se realiza entorno a la frecuencia configurada, ¿Estas seguro de continuar?')">
                                                        Reprogramar
                                                    </a>
                                                    <button class="btn btn-sm btn-primary" onclick="completedModal(${order.id})">Completar</button>
                                                    <button class="btn btn-sm btn-danger" onclick="cancelModal(${order.id})">Cancelar</button>
                                                `}
                        </div>
                    </div>
                `);
                $('#orderModal').modal('show');
            }

            // Opcional: Botón para refrescar el calendario
            $('#refreshCalendar').click(function() {
                calendar.refetchEvents();
            });
        });
    </script>
@endsection
