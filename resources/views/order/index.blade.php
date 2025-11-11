@extends('layouts.app')
@section('content')
    <style>
        #fullscreen-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .spinner-overlay {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
    </style>

    <div id="fullscreen-spinner" class="d-none">
        <div class="spinner-overlay">
            <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="text-light mt-2">Procesando...</div>
        </div>
    </div>

    <div class="container-fluid p-3">
        <div class="d-flex">
            <div class="mb-3">
                @can('write_order')
                    <a class="btn btn-primary btn-sm me-2" href="{{ route('order.create') }}">
                        <i class="bi bi-plus-lg fw-bold"></i> {{ __('order.title.create') }}
                    </a>
                @endcan
            </div>

            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Funciones especiales
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a style="cursor: pointer;" class="dropdown-item" onclick="setCheckboxOrder()">Generar ZIP de
                            ordenes</a>
                    </li>
                    <li>
                        <a style="cursor: pointer;" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#technicianModal">Asignar técnicos en rango</a>
                    </li>
                </ul>
            </div>
        </div>

        @include('messages.alert')
        @include('order.modals.technicians')
        @include('order.modals.signature')

        <div class="table-responsive">
            @php
                $offset = ($orders->currentPage() - 1) * $orders->perPage();
            @endphp

            <table class="table table-bordered table-striped table-sm caption-top">
                <caption class="border rounded-top p-3 text-dark bg-light">
                    <form id="filter-form" action="{{ route('order.filter') }}" method="GET">
                        <div class="row g-2 mb-0">
                            <!-- Cliente -->
                            <div class="col-lg-2">
                                <label for="customer" class="form-label">No. Reporte</label>
                                <input type="text" class="form-control form-control-sm" id="customer" name="folio"
                                    value="{{ request('folio') }}" placeholder="Buscar cliente">
                            </div>

                            <div class="col-lg-4">
                                <label for="customer" class="form-label">Cliente</label>
                                <input type="text" class="form-control form-control-sm" id="customer" name="customer"
                                    value="{{ request('customer') }}" placeholder="Buscar cliente">
                            </div>

                            <!-- Servicio -->
                            <div class="col-lg-4">
                                <label for="service" class="form-label">Servicio</label>
                                <input type="text" class="form-control form-control-sm" id="service" name="service"
                                    value="{{ request('service') }}" placeholder="Buscar servicio">
                            </div>

                            <!-- Rango de Fechas -->
                            <div class="col-2">
                                <label for="date_range" class="form-label">Rango de Fechas</label>
                                <input type="text" class="form-control form-control-sm date-range-picker" id="date-range"
                                    name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                    autocomplete="off">
                            </div>

                            <!-- Hora -->
                            <div class="col-lg-2 col-6">
                                <label for="time" class="form-label">Hora Programada</label>
                                <input type="time" class="form-control form-control-sm" id="time" name="time"
                                    value="{{ request('time') }}">
                            </div>

                            <!-- Estado -->
                            <div class="col-lg-2 col-6">
                                <label for="status" class="form-label">Estado</label>
                                <select class="form-select form-select-sm" id="status" name="status">
                                    <option value="">Todos</option>
                                    @foreach ($order_status as $status)
                                        <option value="{{ $status->id }}"
                                            {{ request('status') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Orden -->
                            <div class="col-lg-2 col-6">
                                <label for="order_type" class="form-label">Tipo de Orden</label>
                                <select class="form-select form-select-sm" id="order_type" name="order_type">
                                    <option value="">Todos</option>
                                    <option value="MIP" {{ request('order_type') == 'MIP' ? 'selected' : '' }}>MIP
                                    </option>
                                    <option value="Seguimiento"
                                        {{ request('order_type') == 'Seguimiento' ? 'selected' : '' }}>
                                        Seguimiento</option>
                                </select>
                            </div>

                            <!-- Firma -->
                            <div class="col-lg-2 col-6">
                                <label for="signature_status" class="form-label">Estado de Firma</label>
                                <select class="form-select form-select-sm" id="signature_status" name="signature_status">
                                    <option value="">Todos</option>
                                    <option value="signed" {{ request('signature_status') == 'signed' ? 'selected' : '' }}>
                                        Firmadas</option>
                                    <option value="unsigned"
                                        {{ request('signature_status') == 'unsigned' ? 'selected' : '' }}>
                                        No Firmadas</option>
                                </select>
                            </div>

                            <!-- Direccion y orden -->
                            <div class="col-auto">
                                <label for="signature_status" class="form-label">Dirección</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ request('direction', 'ASC') }}" readonly>
                            </div>

                            <div class="col-auto">
                                <label for="order_type" class="form-label">Total</label>
                                <select class="form-select form-select-sm" id="size" name="size">
                                    <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                    <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                </select>
                            </div>

                            <input type="hidden" id="direction" name="direction"
                                value="{{ request('direction', 'ASC') }}" readonly>

                            <!-- Botones -->
                            <div class="col-lg-12 d-flex justify-content-end m-0 mt-3">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                                <a href="{{ route('order.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">
                            <input class="form-check-input border-secondary" type="checkbox" value=""
                                id="all-checkboxes" onclick="selectAllOrders()" />
                        </th>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('order.data.customer') }}</th>
                        <th scope="col">ID</th>
                        <th scope="col">Hora</th>
                        <th scope="col">
                            <div class="d-flex justify-content-between">
                                <span>Fecha</span>
                                <button class="btn btn-sm p-0 m-0"
                                    onclick="directionTable('{{ request('direction') }}')">
                                    @if (request('direction') == 'ASC')
                                        <i class="bi bi-arrow-up-circle"></i>
                                    @else
                                        <i class="bi bi-arrow-down-circle"></i>
                                    @endif
                                </button>
                            </div>
                        </th>
                        <th scope="col">Tipo</th>
                        <th scope="col">{{ __('order.data.service') }} </th>
                        <th scope="col">Tecnicos</th>
                        <th scope="col">Cerrado por</th>
                        <th> Firmado por </th>
                        <th> Firma </th>
                        <th scope="col">{{ __('order.data.status') }}</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $index => $order)
                        @php
                            // Asegurarte que la cadena tiene el prefijo correcto
                            $signature =
                                strpos($order->customer_signature, 'data:image') === 0
                                    ? $order->customer_signature
                                    : 'data:image/png;base64,' . $order->customer_signature;

                            $statusColors = [
                                1 => 'text-warning', // Amarillo (ej: Pendiente)
                                2 => 'text-primary', // Azul (ej: En Proceso)
                                3 => 'text-primary', // Azul (ej: En Revisión)
                                4 => 'text-info', // Celeste (ej: En Camino)
                                5 => 'text-success', // Verde (ej: Completado)
                                'default' => 'text-danger', // Rojo (ej: Cancelado/Error)
                            ];
                        @endphp
                        <tr id="order-{{ $order->id }}">
                            <td>
                                <input class="form-check-input border-secondary checkbox-order" type="checkbox"
                                    value="{{ $order->id }}" id="checkbox-order-{{ $order->id }}" />
                            </td>
                            <td class="text-decoration-underline" scope="row">{{ $offset + $index + 1 }}</td>
                            <td><span class="fw-bold text-decoration-underline">{{ $order->customer->name }}</span>
                                ({{ $order->folio }})
                            </td>
                            <td class="fw-bold text-decoration-underline">{{ $order->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->start_time)->format('H:i') }} -
                                {{ $order->end_time ? \Carbon\Carbon::parse($order->end_time)->format('H:i') : '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->programmed_date)->format('d/m/Y') }} -
                                {{ $order->completed_date ? \Carbon\Carbon::parse($order->completed_date)->format('d/m/Y') : '' }}
                            </td>
                            <td>{{ $order->contract_id > 0 ? 'MIP' : 'Seguimiento' }}</td>
                            <td>
                                @foreach ($order->services as $service)
                                    {{ $service->name }} <br>
                                @endforeach
                            </td>
                            <td>
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($order->getNameTechnicians() as $technician)
                                        <li>{{ $technician->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $order->closeUser->name ?? '-' }}
                            </td>
                            <td>{{ $order->signature_name ?? 'Sin firma' }}</td>
                            <td> <img class="border" style="width: 75px;" src="{{ $signature }}" alt="img_firma">
                            </td>
                            <td class="fw-bold {{ $statusColors[$order->status_id] ?? $statusColors['default'] }}">
                                {{ $order->status->name ?? '' }}
                            </td>
                            <td class="text-center">
                                @can('write_order')
                                    <button class="btn btn-warning btn-sm" data-order="{{ $order }}"
                                        onclick="openModal(this)" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Firmar orden">
                                        <i class="bi bi-pen-fill"></i>
                                    </button>
                                    <a href="{{ Route('tracking.create.order', ['id' => $order->id]) }}"
                                        class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Seguimiento de la orden">
                                        <i class="bi bi-person-fill-gear"></i>
                                    </a>
                                    <a class="btn btn-secondary btn-sm"
                                        href="{{ route('order.edit', ['id' => $order->id]) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-title="Editar orden">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a class="btn btn-dark btn-sm" href="{{ route('report.review', ['id' => $order->id]) }}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Generar reporte">
                                        <i class="bi bi-file-pdf-fill"></i>
                                    </a>
                                    @if($order->invoice)
                                        <a class="btn btn-success btn-sm" href="{{ route('invoices.show', ['id' => $order->invoice->id]) }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Ver factura">
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </a>
                                    @else
                                        <a class="btn btn-success btn-sm" href="{{ route('invoices.create', ['order_id' => $order->id]) }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Crear factura">
                                            <i class="bi bi-file-earmark-plus-fill"></i>
                                        </a>    
                                    @endif
                                    @if ($order->status->id != 6)
                                        <a href="{{ route('order.destroy', ['id' => $order->id]) }}"
                                            class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Cancelar orden"
                                            onclick="return confirm('{{ __('messages.are_you_sure') }}')">
                                            <i class="bi bi-x-lg"></i>
                                        </a>

                                        {{-- <a href="{{ route('order.destroy', ['id' => $order->id]) }}"
                                    class="btn btn-outline-danger "
                                    onclick="return confirm('{{ __('messages.are_you_sure') }}')">
                                    <i class="bi bi-trash-fill"></i>
                                </a> --}}
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- @include('layouts.pagination.orders') --}}
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>

    <script>
        var order_ids = [];

        $(document).ready(function() {
            $('.form-check-input').prop('checked', false);
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        $(function() {
            // Configuración común para ambos datepickers
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
            $('#date-range-technician').daterangepicker(commonOptions);

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            $('#date-range-technician').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });

        function createTracking() {
            $('#trackingModal').modal('show');
        }

        function selectAllOrders() {
            const isChecked = $('#all-checkboxes').is(':checked');
            $('.checkbox-order').prop('checked', isChecked);
        }

        function setCheckboxOrder() {
            const checkboxes = $('.checkbox-order:checked');

            if (checkboxes.length == 0) {
                alert('Por favor, selecciona al menos una orden.');
                return;
            }

            var selected_orders = [];
            checkboxes.each(function() {
                selected_orders.push(parseInt($(this).val()));
            });

            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            formData.append('selectedOrders', JSON.stringify(selected_orders));

            showSpinner();

            $.ajax({
                url: "{{ route('report.bulk') }}",
                type: 'POST',
                data: formData,
                processData: false, // Importante para FormData
                contentType: false, // Importante para FormData
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        if (confirm('ZIP listo, ¿Deseas descargar los reportes?')) {
                            window.location.href = response.download_url;
                        } else {
                            window.location.href = response.delete_url;
                        }
                    }
                },
                complete: function() {
                    hideSpinner();
                },
                error: function(xhr, status, error) {
                    console.error('Error al descargar los reportes:', error);
                }
            });
        }

        function showSpinner() {
            $("#fullscreen-spinner").removeClass("d-none");
        }

        function hideSpinner() {
            $("#fullscreen-spinner").addClass("d-none");
        }
    </script>
@endsection
