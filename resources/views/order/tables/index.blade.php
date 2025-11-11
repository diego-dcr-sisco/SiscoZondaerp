@php
    $offset = ($orders->currentPage() - 1) * $orders->perPage();
@endphp

<table class="table table-bordered table-striped table-sm caption-top">
    <caption class="border rounded-top p-3 text-dark bg-light">
        <form id="filter-form" action="{{ route('order.filter') }}" method="GET">
            <div class="row g-2 mb-0">
                <!-- Cliente -->
                <div class="col-lg-4">
                    <label for="customer" class="form-label">Cliente</label>
                    <input type="text" class="form-control form-control-sm" id="customer" name="customer"
                        value="{{ request('customer') }}" placeholder="Buscar cliente">
                </div>

                <!-- Rango de Fechas -->
                <div class="col-auto">
                    <label for="date_range" class="form-label">Rango de Fechas</label>
                    <input type="text" class="form-control form-control-sm date-range-picker" id="date-range"
                        name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                        autocomplete="off">
                </div>

                <!-- Hora -->
                <div class="col-auto">
                    <label for="time" class="form-label">Hora Programada</label>
                    <input type="time" class="form-control form-control-sm" id="time" name="time"
                        value="{{ request('time') }}">
                </div>

                <!-- Servicio -->
                <div class="col-lg-4">
                    <label for="service" class="form-label">Servicio</label>
                    <input type="text" class="form-control form-control-sm" id="service" name="service"
                        value="{{ request('service') }}" placeholder="Buscar servicio">
                </div>

                <!-- Estado -->
                <div class="col-auto">
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
                <div class="col-auto">
                    <label for="order_type" class="form-label">Tipo de Orden</label>
                    <select class="form-select form-select-sm" id="order_type" name="order_type">
                        <option value="">Todos</option>
                        <option value="MIP" {{ request('order_type') == 'MIP' ? 'selected' : '' }}>MIP
                        </option>
                        <option value="Seguimiento" {{ request('order_type') == 'Seguimiento' ? 'selected' : '' }}>
                            Seguimiento</option>
                    </select>
                </div>

                <!-- Firma -->
                <div class="col-auto">
                    <label for="signature_status" class="form-label">Estado de Firma</label>
                    <select class="form-select form-select-sm" id="signature_status" name="signature_status">
                        <option value="">Todos</option>
                        <option value="signed" {{ request('signature_status') == 'signed' ? 'selected' : '' }}>
                            Firmadas</option>
                        <option value="unsigned" {{ request('signature_status') == 'unsigned' ? 'selected' : '' }}>
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
                    </select>
                </div>

                <input type="hidden" id="direction" name="direction" value="{{ request('direction', 'ASC') }}"
                    readonly>

                <!-- Botones -->
                <div class="col-lg-12 d-flex justify-content-end">
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
                <input class="form-check-input border-secondary" type="checkbox" value="" id="all-checkboxes"
                    onclick="selectAllOrders()" />
            </th>
            <th scope="col">#</th>
            <th scope="col">{{ __('order.data.customer') }}</th>
            <th scope="col">Hora</th>
            <th scope="col">
                <div class="d-flex justify-content-between">
                    <span>Fecha</span>
                    <button class="btn btn-sm p-0 m-0" onclick="directionTable('{{ request('direction') }}')">
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
            <th scope="col">{{ __('buttons.actions') }}</th>
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
                <th class="text-decoration-underline" scope="row">{{ $offset + $index + 1 }}</th>
                <td><span class="fw-bold text-decoration-underline">{{ $order->customer->name }}</span>
                    ({{ $order->folio }})</td>
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
                    @foreach ($order->getNameTechnicians() as $technician)
                        {{ $technician->name }} <br>
                    @endforeach
                </td>
                <td>
                    {{ $order->closeUser->name ?? '-' }}
                </td>
                <td>{{ $order->signature_name ?? 'Sin firma' }}</td>
                <td> <img class="border" style="width: 75px;" src="{{ $signature }}" alt="img_firma"> </td>
                <td class="fw-bold {{ $statusColors[$order->status_id] ?? $statusColors['default'] }}">
                    {{ $order->status->name ?? '' }}
                </td>
                <td>
                    @can('write_order')
                        <button class="btn btn-warning btn-sm" data-order="{{ $order }}"
                            onclick="openModal(this)" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Firmar orden">
                            <i class="bi bi-pen-fill"></i>
                        </button>
                        <a href="{{ Route('crm.tracking.create.order', ['customerId' => $order->customer_id, 'orderId' => $order->id]) }}"
                            class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-title="Seguimiento de la orden">
                            <i class="bi bi-person-fill-gear"></i>
                        </a>
                        <a class="btn btn-secondary btn-sm" href="{{ route('order.edit', ['id' => $order->id]) }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar orden">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a class="btn btn-dark btn-sm" href="{{ route('report.review', ['id' => $order->id]) }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Generar reporte">
                            <i class="bi bi-file-pdf-fill"></i>
                        </a>
                        @if ($order->status->id != 6)
                            <a href="{{ route('order.destroy', ['id' => $order->id]) }}" class="btn btn-danger btn-sm"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cancelar orden"
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

@include('order.modals.signature')

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    $(document).ready(function() {
        $('input[name="date_range"]').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
            },
            opens: 'left',
            autoUpdateInput: false
        });

        $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                'DD/MM/YYYY'));
        });
    });

    function directionTable(direction) {
        $('#direction').val(direction == 'ASC' ? 'DESC' : 'ASC');
        $('#filter-form').submit();
    }
</script>
