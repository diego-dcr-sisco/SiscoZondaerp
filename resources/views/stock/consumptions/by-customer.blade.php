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
                                <label for="customer" class="form-label">Cliente</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input type="text" class="form-control" id="customer" name="customer"
                                        value="{{ request('customer') }}" placeholder="Nombre del cliente">
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="date-range" class="form-label">Rango de fecha</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                    <input type="text" class="form-control" id="date-range" name="date_range"
                                        value="{{ request('date_range') }}" placeholder="Fecha de las ordenes"
                                        autocomplete="off">
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

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="border rounded bg-white p-3 h-100">
                        <div class="text-muted small text-uppercase fw-semibold">Clientes</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary->customers_count ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="border rounded bg-white p-3 h-100">
                        <div class="text-muted small text-uppercase fw-semibold">Ordenes</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary->orders_count ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="border rounded bg-white p-3 h-100">
                        <div class="text-muted small text-uppercase fw-semibold">Productos</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary->products_count ?? 0) }}</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="border rounded bg-white p-3 h-100">
                        <div class="text-muted small text-uppercase fw-semibold">Cantidad total</div>
                        <div class="fs-4 fw-bold">{{ number_format($summary->total_quantity ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive bg-white border rounded">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Cliente</th>
                            <th scope="col">Grupo</th>
                            <th scope="col">Tipo de dispositivo</th>
                            <th scope="col">Producto utilizado</th>
                            <th scope="col">Lote</th>
                            <th scope="col" class="text-end">Cantidad</th>
                            <th scope="col" class="text-end">Ordenes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($consumptions as $consumption)
                            <tr>
                                <td class="fw-semibold">{{ $consumption->customer_name }}</td>
                                <td>
                                    <span class="badge text-bg-dark">{{ $divisionLabels[$division] ?? 'Grupo' }}</span>
                                    <span class="ms-1">{{ $consumption->period_label }}</span>
                                </td>
                                <td>{{ $consumption->device_type }}</td>
                                <td>{{ $consumption->product_name }}</td>
                                <td>
                                    <span class="badge text-bg-light border">{{ $consumption->lot_name }}</span>
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ number_format($consumption->quantity, 2) }}
                                    <small class="text-muted">{{ $consumption->metric_name }}</small>
                                </td>
                                <td class="text-end">{{ number_format($consumption->orders_count) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay consumos para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $consumptions->links('pagination::bootstrap-5') }}
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
    </script>
@endsection
