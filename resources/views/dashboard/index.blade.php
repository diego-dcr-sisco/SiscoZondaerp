@extends('layouts.app')

@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    @php
        $statusMap = [
            'active' => [
                'color' => 'success',
                'icon' => 'check-circle',
                'text' => 'Activo',
            ],
            'completed' => [
                'color' => 'primary',
                'icon' => 'flag',
                'text' => 'Completado',
            ],
            'canceled' => [
                'color' => 'danger',
                'icon' => 'times-circle',
                'text' => 'Cancelado',
            ],
        ];
    @endphp

    <style>
        /* Colores y estilos base */
        .bg-blue {
            background-color: #182A41;
        }

        .bg-blue:hover {
            box-shadow: 0 10px 20px rgba(21, 101, 192, 0.3);
            transform: translateY(-5px);
        }

        .bg-purple {
            background-color: #192A59;
        }

        .bg-purple:hover {
            box-shadow: 0 10px 20px rgba(106, 27, 154, 0.3);
            transform: translateY(-5px);
        }

        .bg-red {
            background-color: #1D2D83;
        }

        .bg-red:hover {
            box-shadow: 0 10px 20px rgba(198, 40, 40, 0.3);
            transform: translateY(-5px);
        }

        .bg-brown {
            background-color: #4A2E84;
        }

        .bg-brown:hover {
            box-shadow: 0 10px 20px rgba(78, 52, 46, 0.3);
            transform: translateY(-5px);
        }

        .bg-pink {
            background-color: #6C3A75;
        }

        .bg-pink:hover {
            box-shadow: 0 10px 20px rgba(173, 20, 87, 0.3);
            transform: translateY(-5px);
        }

        .bg-green {
            background-color: #9E4653;
        }

        .bg-green:hover {
            box-shadow: 0 10px 20px rgba(46, 125, 50, 0.3);
            transform: translateY(-5px);
        }

        .bg-gray {
            background-color: #C2523F;
        }

        .bg-gray:hover {
            box-shadow: 0 10px 20px rgba(55, 71, 79, 0.3);
            transform: translateY(-5px);
        }

        /* Animaciones personalizadas */
        .card-animate {
            opacity: 0;
            animation: fadeIn 0.3s ease-out forwards;
            transition: all 0.3s ease;
        }

        .table-row-animate {
            opacity: 0;
            animation: fadeInRight 0.5s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Retrasos para animaciones */
        .card-animate:nth-child(1) {
            animation-delay: 0.2s;
        }

        .card-animate:nth-child(2) {
            animation-delay: 0.4s;
        }

        .card-animate:nth-child(3) {
            animation-delay: 0.6s;
        }

        .card-animate:nth-child(4) {
            animation-delay: 0.8s;
        }

        .card-animate:nth-child(5) {
            animation-delay: 1.0s;
        }

        .card-animate:nth-child(6) {
            animation-delay: 1.2s;
        }

        .card-animate:nth-child(7) {
            animation-delay: 1.4s;
        }

        .table-row-animate:nth-child(1) {
            animation-delay: 0.2s;
        }

        .table-row-animate:nth-child(2) {
            animation-delay: 0.3s;
        }

        .table-row-animate:nth-child(3) {
            animation-delay: 0.4s;
        }

        .table-row-animate:nth-child(4) {
            animation-delay: 0.5s;
        }

        .table-row-animate:nth-child(5) {
            animation-delay: 0.6s;
        }

        /* Efecto hover para tarjetas */
        .hover-scale {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .crm-modal .modal-header {
            background: linear-gradient(135deg, #3f51b5, #7986cb);
        }

        .crm-modal .tracking-count {
            font-size: 2.2rem;
            font-weight: 700;
            color: #3f51b5;
        }

        .crm-modal .agenda-link {
            background: linear-gradient(135deg, #3f51b5, #7986cb);
            transition: transform 0.3s ease;
        }

        .crm-modal .agenda-link:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .priority-badge {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .priority-high {
            background-color: #f44336;
        }

        .priority-medium {
            background-color: #ff9800;
        }

        .priority-low {
            background-color: #4caf50;
        }

        /* Ajustes responsive */
        @media (max-width: 576px) {
            .card {
                width: 140px !important;
                height: 120px !important;
            }

            .card i.bi {
                font-size: 1.25rem !important;
            }

            h3.h6 {
                font-size: 0.9rem !important;
            }
        }
    </style>

    <div class="container-fluid py-5">
        <!-- Encabezado con animación -->
        <div class="text-center mb-5 animate__animated animate__fadeIn">
            <h1 class="display-4 fw-bold text-dark mb-3">BIENVENIDO A ZONDA</h1>
            <p class="lead text-muted">Sistema para el Manejo Integral de Plagas (SMIP)</p>
        </div>

        <!-- Grid de tarjetas con animación secuencial -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
            <!-- CRM -->
            <a href="{{ route('crm.agenda') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-blue card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-people-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">CRM</h3>
                        <p class="small opacity-75 mb-0">Clientes</p>
                    </div>
                </div>
            </a>

            <!-- Planificación -->
            <a href="{{ route('planning.schedule') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-purple card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-calendar-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Planificación</h3>
                        <p class="small opacity-75 mb-0">Agenda</p>
                    </div>
                </div>
            </a>

            <!-- Calidad -->
            <a href="{{ route('quality.customers') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-red card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-gear-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Calidad</h3>
                        <p class="small opacity-75 mb-0">Control</p>
                    </div>
                </div>
            </a>

            <!-- Almacén -->
            <a href="{{ route('stock.index') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-brown card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-box-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Almacén</h3>
                        <p class="small opacity-75 mb-0">Inventario</p>
                    </div>
                </div>
            </a>

            <!-- RRHH -->
            <a href="{{ route('rrhh', ['section' => 1]) }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-pink card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-file-person-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">RRHH</h3>
                        <p class="small opacity-75 mb-0">Personal</p>
                    </div>
                </div>
            </a>

            <!-- facturación -->
            <a href="{{ route('invoices.dashboard') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-green card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-stack d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Facturación</h3>
                        <p class="small opacity-75 mb-0">Pagos</p>
                    </div>
                </div>
            </a>

            <!-- Clientes -->
            <a href="{{ route('client.index') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-gray card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-person-workspace d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Clientes</h3>
                        <p class="small opacity-75 mb-0">Gestión</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tabla con animación secuencial -->
        {{--<div class="row justify-content-md-center table-card animate__animated animate__fadeIn">
            <div class="col-lg-9">
                <div class="border rounded shadow p-3 mb-3">
                    <div class="fw-bold mb-3">
                        Cantidad de pendientes: <span class="text-danger fw-bold"
                            id="count-trackings">{{ $count_trackings }}</span>
                    </div>

                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-striped table-hover table-sm caption-top">
                            <caption class="border rounded-top p-2 text-dark bg-warning sticky-top">
                                <span class="fw-bold">Seguimientos del mes</span>
                            </caption>
                            <thead class="sticky-top" style="top: 48px; background-color: white; z-index: 1;">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Orden</th>
                                    <th>Próxima Fecha</th>
                                    <th>Título</th>
                                    <th>Descripción</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trackings_data as $tracking)
                                    <tr id="tracking-row{{ $tracking['id'] }}" class="table-row-animate">
                                        <td>{{ $tracking['customer'] ?? '-' }}</td>
                                        <td>
                                            @if ($tracking['order'])
                                                <a href="{{ route('order.edit', ['id' => $tracking['order']['id']]) }}"
                                                    class="text-primary"> {{ $tracking['order']['folio'] }}</a>
                                            @else
                                                '-'
                                            @endif
                                        </td>
                                        <td id="tracking{{ $tracking['id'] }}-date">{{ $tracking['next_date'] ?? '-' }}
                                        </td>
                                        <td id="tracking{{ $tracking['id'] }}-title">{{ $tracking['title'] ?? '-' }}</td>
                                        <td id="tracking{{ $tracking['id'] }}-description">
                                            {{ $tracking['description'] ?? '-' }}</td>
                                        <td id="tracking{{ $tracking['id'] }}-status"
                                            class="text-{{ $statusMap[$tracking['status']]['color'] }} fw-bold">
                                            {{ $statusMap[$tracking['status']]['text'] }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="updateTrackingStatus({{ $tracking['id'] }}, 'active')"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Activar Seguimiento">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="updateTrackingStatus({{ $tracking['id'] }}, 'completed')"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Completar Seguimiento">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="updateTrackingStatus({{ $tracking['id'] }}, 'canceled')"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Cancelar Seguimiento">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                onclick="editTracking({{ $tracking['id'] }})" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Editar Seguimiento">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="deleteTracking({{ $tracking['id'] }})" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Eliminar Seguimiento">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>--}}
    </div>


    {{--<div class="modal fade" id="crmModal" tabindex="-1" aria-labelledby="crmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <!-- Header con gradiente -->
                <div class="modal-header bg-warning">
                    <h1 class="modal-title fs-5" id="crmModalLabel">
                        <i class="bi bi-calendar-check me-2"></i>
                        CRM Seguimientos y Actividades
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body del modal -->
                <div class="modal-body">
                    <!-- Alerta de seguimientos pendientes -->
                    <div class="alert alert-warning border-0 shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-4"></i>
                            <h4 class="mb-0 text-dark">{{ $count_trackings }} Pendientes</h4>
                        </div>
                        <p class="mb-0 text-dark">Seguimientos que requieren tu atención inmediata</p>
                    </div>

                    <!-- Información y enlace -->
                    <div class="mb-4">
                        <p class="text-muted mb-3">
                            Por favor dirígete a la agenda o revisa la tabla de pendientes para completar las acciones
                            necesarias.
                        </p>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('crm.agenda') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-calendar-week me-2"></i>
                                Ir a la Agenda
                            </a>
                        </div>
                    </div>

                    <!-- Sugerencias rápidas -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Sugerencias rápidas
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex align-items-center border-0">
                                    <span class="badge bg-danger me-2">•</span>
                                    Revisar seguimientos de alta prioridad
                                </li>
                                <li class="list-group-item d-flex align-items-center border-0">
                                    <span class="badge bg-warning me-2">•</span>
                                    Programar recordatorios para actividades
                                </li>
                                <li class="list-group-item d-flex align-items-center border-0">
                                    <span class="badge bg-success me-2">•</span>
                                    Actualizar estado de clientes en CRM
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Footer del modal -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <a href="{{ route('crm.tracking') }}" class="btn btn-success">
                        Ver pendientes
                    </a>
                </div>
            </div>
        </div>
    </div>--}}

    <script>
        var trackings_data = @json($trackings_data);
        var statusMap = @json($statusMap);

        $(document).ready(function() {
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            //$('#crmModal').modal('show');
        });

        function editTracking(tracking_id) {
            const trackingData = trackings_data.find(t => t.id == tracking_id)
            $('#editTrackingModal').find('#tracking-id').val(trackingData.id || '');
            $('#editTrackingModal').find('#tracking-service').val(trackingData.service || '');
            $('#editTrackingModal').find('#tracking-date').val(trackingData.next_date || '');
            $('#editTrackingModal').find('#tracking-status').val(trackingData.status || '');
            $('#editTrackingModal').find('#tracking-title').val(trackingData.title || '');
            $('#editTrackingModal').find('#tracking-description').val(trackingData.description || '');
            $('#editTrackingModal').modal('show');
        }

        function updateTracking() {
            var update_tracking = {
                id: $('#tracking-id').val(),
                service_id: $('#tracking-service').val(),
                date: $('#tracking-date').val(),
                status: $('#tracking-status').val(),
                title: $('#tracking-title').val(),
                description: $('#tracking-description').val(),
            }

            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            formData.append('tracking', JSON.stringify(update_tracking));

            $.ajax({
                url: "{{ route('tracking.update') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    if (response.success) {
                        $(`#tracking${update_tracking.id}-date`).text(update_tracking.date);
                        $(`#tracking${update_tracking.id}-title`).text(update_tracking.title);
                        $(`#tracking${update_tracking.id}-description`).text(update_tracking.description);
                        $(`#tracking${update_tracking.id}-status`).text(statusMap[update_tracking.status].text);
                        $(`#tracking${update_tracking.id}-status`).removeClass();
                        $(`#tracking${update_tracking.id}-status`).addClass(
                            `text-${statusMap[update_tracking.status].color} fw-bold`);

                        $('#count-trackings').text(response.count)

                        var found_tracking = trackings_data.find(t => t.id == update_tracking.id);
                        if (found_tracking) {
                            found_tracking.service = update_tracking.service_id;
                            found_tracking.next_date = update_tracking.date;
                            found_tracking.title = update_tracking.title;
                            found_tracking.description = update_tracking.description;
                            found_tracking.status = update_tracking.status;
                        }
                    }
                    $('#editTrackingModal').modal('hide');
                },
                error: function(error) {},
            });
        }

        function updateTrackingStatus(tracking_id, status) {
            var update_tracking = {
                id: tracking_id,
                status: status
            }

            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            formData.append('tracking', JSON.stringify(update_tracking));

            $.ajax({
                url: "{{ route('tracking.update.status') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    if (response.success) {
                        $(`#tracking${update_tracking.id}-status`).text(statusMap[update_tracking.status].text);
                        $(`#tracking${update_tracking.id}-status`).removeClass();
                        $(`#tracking${update_tracking.id}-status`).addClass(
                            `text-${statusMap[update_tracking.status].color} fw-bold`);

                        $('#count-trackings').text(response.count)

                        var found_tracking = trackings_data.find(t => t.id == update_tracking.id);
                        if (found_tracking) {
                            found_tracking.status = update_tracking.status;
                        }
                    }
                    $('#editTrackingModal').modal('hide');
                },
                error: function(error) {},
            });
        }

        function deleteTracking(tracking_id) {
            var update_tracking = {
                id: tracking_id,
            }
            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            formData.append('tracking', JSON.stringify(update_tracking));

            $.ajax({
                url: "{{ route('tracking.destroy') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    if (response.success) {
                        $(`#tracking-row${update_tracking.id}`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        $('#count-trackings').text(response.count)
                    }
                    $('#editTrackingModal').modal('hide');
                },
                error: function(error) {},
            });
        }
    </script>
@endsection