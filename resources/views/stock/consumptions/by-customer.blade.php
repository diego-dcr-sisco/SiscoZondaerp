@extends('layouts.app')
@section('content')
    @php
        $divisionLabels = [
            'day' => 'Dia',
            'week' => 'Semana',
            'month' => 'Mes',
            'year' => 'Anio',
        ];
    @endphp

    @include('components.page-header', [
        'title' => 'CONSUMOS POR CLIENTE',
        'icon' => 'bi-people',
    ])

    <div class="container-fluid p-0">
        <div class="m-3">
            <form action="{{ route('stock.consumptions.by-customer') }}" method="GET" class="mb-3">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                            </h5>
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target=".customer-consumption-search-collapse" aria-expanded="true"
                                aria-controls="customerConsumptionSearchFilters customerConsumptionSearchFooter">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body collapse show customer-consumption-search-collapse"
                        id="customerConsumptionSearchFilters">
                        <div class="row g-3">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="customer" class="form-label is-required">Cliente</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input type="text" class="form-control" id="customer" name="customer"
                                        value="{{ request('customer') }}" placeholder="Nombre del cliente" required>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="date-range" class="form-label is-required">Rango de fecha</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                    <input type="text" class="form-control" id="date-range" name="date_range"
                                        value="{{ request('date_range') }}" placeholder="Fecha de las ordenes"
                                        autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-lg-2 col-sm-6 col-12">
                                <label for="division" class="form-label">Division</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <select class="form-select" id="division" name="division">
                                        @foreach ($divisionLabels as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $division == $value ? 'selected' : '' }}
                                                {{ !in_array($value, $availableDivisions, true) ? 'disabled' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2 col-sm-6 col-12">
                                <label for="size" class="form-label">Mostrar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size', 50) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer collapse show customer-consumption-search-collapse"
                        id="customerConsumptionSearchFooter">
                        <div class="row justify-content-end">
                            <div class="col-lg-1 col-6">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-lg-1 col-6">
                                <a href="{{ route('stock.consumptions.by-customer') }}"
                                    class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="bg-white border rounded p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                    <div>
                        <div class="fw-bold">
                            <i class="bi bi-people-fill"></i> Clientes con coincidencias
                        </div>
                        <div class="text-muted small">
                            Clientes que tuvieron consumo en el rango y busqueda aplicada.
                        </div>
                    </div>
                    <span class="badge text-bg-light border">
                        {{ $matchedCustomers->count() }} cliente{{ $matchedCustomers->count() === 1 ? '' : 's' }}
                    </span>
                </div>

                @if ($matchedCustomers->isNotEmpty())
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ($matchedCustomers as $matchedCustomer)
                            <span class="badge text-bg-light border text-start py-2 px-3">
                                <span class="fw-semibold">{{ $matchedCustomer->customer_name }}</span>
                                <span class="text-muted">({{ $matchedCustomer->customer_id }})</span>
                                <span class="text-muted">- Matriz: {{ $matchedCustomer->matrix_name }}
                                    ({{ $matchedCustomer->matrix_id }})</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small">
                        {{ $hasAppliedFilters ? 'No hubo clientes con coincidencias para los filtros aplicados.' : 'Aplica filtros para ver los clientes encontrados.' }}
                    </div>
                @endif
            </div>

            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                    data-bs-target="#customerProductExcelModal">
                    <i class="bi bi-file-earmark-excel-fill"></i> Excel por cliente y producto
                </button>
            </div>

            <div class="modal fade" id="customerProductExcelModal" tabindex="-1"
                aria-labelledby="customerProductExcelModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <form action="{{ route('stock.consumptions.by-customer.export') }}" method="GET"
                        class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="customerProductExcelModalLabel">
                                <i class="bi bi-file-earmark-excel-fill"></i> Excel por cliente y producto
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="date-range-export" class="form-label is-required">Rango de fecha</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                        <input type="text" class="form-control" id="date-range-export" name="date_range"
                                            value="{{ request('date_range') }}" placeholder="Fecha de las ordenes"
                                            autocomplete="off" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Tipo de cliente</label>
                                    <div class="d-flex align-items-center gap-3 flex-wrap border rounded p-2 bg-white">
                                        @php
                                            $selectedServiceTypes = collect(request('service_type_ids', [1, 2, 3]))->map(fn ($value) => (string) $value);
                                            $serviceTypeOptions = [
                                                1 => 'Domestico',
                                                2 => 'Comercial',
                                                3 => 'Industrial/Planta',
                                            ];
                                        @endphp
                                        @foreach ($serviceTypeOptions as $typeId => $typeLabel)
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="service_type_ids[]"
                                                    id="service-type-{{ $typeId }}" value="{{ $typeId }}"
                                                    data-label="{{ $typeLabel }}"
                                                    {{ $selectedServiceTypes->contains((string) $typeId) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-type-{{ $typeId }}">
                                                    {{ $typeLabel }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="small text-muted mt-2">
                                        Clientes filtrados:
                                        <span id="selected-service-types-summary"
                                            class="d-inline-flex gap-1 flex-wrap"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-excel-fill"></i> Generar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-5 col-12">
                    <div class="table-responsive bg-white border rounded h-100">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Tipo de dispositivo</th>
                                    <th scope="col" class="text-end">Dispositivos</th>
                                    <th scope="col" class="text-end">Registros</th>
                                    <th scope="col" class="text-end">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($deviceSummaries as $deviceSummary)
                                    @php
                                        $deviceModalId = 'device-type-modal-' . md5($deviceSummary->device_type);
                                        $deviceDetails = $deviceDetailsByType->get($deviceSummary->device_type, collect());
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">
                                            <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none"
                                                data-bs-toggle="modal" data-bs-target="#{{ $deviceModalId }}">
                                                {{ $deviceSummary->device_type }}
                                            </button>
                                        </td>
                                        <td class="text-end">{{ number_format($deviceSummary->devices_count) }}</td>
                                        <td class="text-end">{{ number_format($deviceSummary->consumptions_count) }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($deviceSummary->total_quantity, 2) }}
                                            <small class="text-muted">{{ $deviceSummary->metric_name }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aplica un filtro para consultar dispositivos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-xl-7 col-12">
                    <div class="table-responsive bg-white border rounded h-100">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Producto utilizado</th>
                                    <th scope="col">Lote</th>
                                    <th scope="col" class="text-end">Cantidad total</th>
                                    <th scope="col" class="text-end">Ordenes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productSummaries as $productSummary)
                                    @php
                                        $productOrderKey = implode('|', [
                                            $productSummary->product_id,
                                            $productSummary->lot_name,
                                            $productSummary->metric_name,
                                        ]);
                                        $productOrdersModalId = 'product-orders-modal-' . md5($productOrderKey);
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">
                                            <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none"
                                                data-bs-toggle="modal" data-bs-target="#{{ $productOrdersModalId }}">
                                                {{ $productSummary->product_name }}
                                            </button>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-light border">{{ $productSummary->lot_name }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ number_format($productSummary->total_quantity, 2) }}
                                            <small class="text-muted">{{ $productSummary->metric_name }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($productSummary->orders_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            {{ $hasAppliedFilters ? 'No hay productos utilizados para los filtros seleccionados.' : 'Aplica un filtro para consultar los productos utilizados.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @foreach ($productSummaries as $productSummary)
                @php
                    $productOrderKey = implode('|', [
                        $productSummary->product_id,
                        $productSummary->lot_name,
                        $productSummary->metric_name,
                    ]);
                    $productOrdersModalId = 'product-orders-modal-' . md5($productOrderKey);
                    $productOrderDetails = $productOrderDetailsByKey->get($productOrderKey, collect());
                @endphp
                <div class="modal fade" id="{{ $productOrdersModalId }}" tabindex="-1"
                    aria-labelledby="{{ $productOrdersModalId }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title" id="{{ $productOrdersModalId }}Label">
                                    <i class="bi bi-box-seam-fill"></i> {{ $productSummary->product_name }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <span class="badge text-bg-light border">Lote: {{ $productSummary->lot_name }}</span>
                                    <span class="badge text-bg-light border">Unidad: {{ $productSummary->metric_name }}</span>
                                    <span class="badge text-bg-light border">
                                        Ordenes: {{ number_format($productSummary->orders_count) }}
                                    </span>
                                </div>

                                <div class="table-responsive border rounded">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Orden</th>
                                                <th scope="col">Fecha programada</th>
                                                <th scope="col">Cliente</th>
                                                <th scope="col" class="text-end">Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($productOrderDetails as $orderDetail)
                                                <tr>
                                                    <td class="text-muted">#{{ $orderDetail->order_id }}</td>
                                                    <td class="fw-semibold">
                                                        <a href="{{ route('order.edit', ['id' => $orderDetail->order_id]) }}"
                                                            target="_blank" class="text-decoration-none">
                                                            {{ $orderDetail->order_folio ?? ('Orden #' . $orderDetail->order_id) }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $orderDetail->programmed_date }}</td>
                                                    <td>
                                                        {{ $orderDetail->customer_name }}
                                                        <span class="text-muted">({{ $orderDetail->customer_id }})</span>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">
                                                        {{ number_format((float) $orderDetail->quantity, 2) }}
                                                        <small class="text-muted">{{ $productSummary->metric_name }}</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        No hay ordenes para este producto.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @foreach ($deviceSummaries as $deviceSummary)
                @php
                    $deviceModalId = 'device-type-modal-' . md5($deviceSummary->device_type);
                    $deviceDetails = $deviceDetailsByType->get($deviceSummary->device_type, collect());
                @endphp
                <div class="modal fade" id="{{ $deviceModalId }}" tabindex="-1"
                    aria-labelledby="{{ $deviceModalId }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title" id="{{ $deviceModalId }}Label">
                                    <i class="bi bi-router-fill"></i> {{ $deviceSummary->device_type }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Codigo de dispositivo</th>
                                                <th scope="col">Tipo de punto de control</th>
                                                <th scope="col">Version del dispositivo</th>
                                                <th scope="col">Plano</th>
                                                <th scope="col" class="text-end">Ordenes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($deviceDetails as $deviceDetail)
                                                <tr>
                                                    <td class="fw-semibold">
                                                        {{ $deviceDetail->device_code }}
                                                        @if ($deviceDetail->device_nplan !== '-' && $deviceDetail->device_nplan !== $deviceDetail->device_code)
                                                            <div class="small text-muted">N. plano: {{ $deviceDetail->device_nplan }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $deviceDetail->control_point_type }}
                                                        <span class="badge text-bg-light border">
                                                            {{ $deviceDetail->control_point_code }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $deviceDetail->device_version }}</td>
                                                    <td>{{ $deviceDetail->floorplan_name }}</td>
                                                    <td class="text-end">{{ number_format($deviceDetail->orders_count) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        No hay dispositivos para este tipo.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

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

            $('#date-range, #date-range-export').daterangepicker(commonOptions);

            $('#date-range, #date-range-export').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            function updateSelectedServiceTypesSummary() {
                const selectedTypes = $('input[name="service_type_ids[]"]:checked').map(function() {
                    return $(this).data('label');
                }).get();

                const summary = $('#selected-service-types-summary');

                if (selectedTypes.length === 0) {
                    summary.html('<span class="badge text-bg-warning">Sin tipo seleccionado</span>');
                    return;
                }

                summary.html(selectedTypes.map(function(label) {
                    return '<span class="badge text-bg-light border">' + label + '</span>';
                }).join(''));
            }

            $('input[name="service_type_ids[]"]').on('change', updateSelectedServiceTypesSummary);
            updateSelectedServiceTypesSummary();
        });
    </script>
@endsection
