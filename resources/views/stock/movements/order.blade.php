@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CONSUMOS EN ORDENES',
        'icon' => 'bi-clipboard-data',
    ])

    <div class="container-fluid p-0">
        <div class="m-3">
            <form action="{{ route('stock.movements.orders') }}" method="GET" class="mb-3">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                            </h5>
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target=".order-consumption-search-collapse" aria-expanded="true"
                                aria-controls="orderConsumptionSearchFilters orderConsumptionSearchFooter">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body collapse show order-consumption-search-collapse"
                        id="orderConsumptionSearchFilters">
                        <div class="row g-3 mb-3">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="order" class="form-label">Orden</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                    <input type="text" class="form-control" id="order" name="order_folio"
                                        value="{{ request('order_folio') }}" placeholder="Folio de la orden.">
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="warehouse" class="form-label">Almacen</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <input type="text" class="form-control" id="warehouse" name="warehouse"
                                        value="{{ request('warehouse') }}" placeholder="Nombre del almacen.">
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="technician" class="form-label">Técnico</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" class="form-control" id="technician" name="technician"
                                        value="{{ request('technician') }}" placeholder="Nombre del usuario/técnico.">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="product" class="form-label">Producto</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <select class="form-select" id="product" name="product_id">
                                        <option value="">Todos los productos</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="lot" class="form-label">Lote</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <select class="form-select" id="lot" name="lot_id">
                                        <option value="">Todos los lotes</option>
                                        @foreach ($lots as $lot)
                                            <option value="{{ $lot->id }}"
                                                {{ request('lot_id') == $lot->id ? 'selected' : '' }}>
                                                {{ $lot->registration_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="date-range" class="form-label">Fecha</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                    <input type="text" class="form-control" id="date-range" name="date_range"
                                        value="{{ request('date-range') }}"
                                        placeholder="Rango de fecha de los movimientos" autocomplete="off">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="direction" class="form-label">Ordenar / Mostrar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                                    <select class="form-select" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                            DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                            ASC
                                        </option>
                                    </select>
                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer collapse show order-consumption-search-collapse"
                        id="orderConsumptionSearchFooter">
                        <div class="row justify-content-end">
                            <div class="col-lg-1 col-6">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-lg-1 col-6">
                                <a href="{{ route('stock.movements.orders') }}" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <table class="table table-bordered table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Orden</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Fecha de orden</th>
                        <th scope="col">Almacen</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Lote</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Usuario/Técnico</th>
                        <th scope="col">Fecha realizado</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wos as $wo)
                        <tr>
                            <th scope="row">{{ $wo->id }}</th>
                            <td>
                                <a class="fw-bold" href="{{ route('order.edit', ['id' => $wo->order_id]) }}">{{ $wo->order->folio ?? '-' }}
                                    [{{ $wo->order_id ?? '-' }}]</a>
                            </td>
                            <td>{{ $wo->order->customer->name ?? '-' }}</td>
                            <td>{{ $wo->order->programmed_date ?? '-' }}</td>
                            <td>{{ $wo->warehouse->name ?? '-' }}</td>
                            <td>{{ $wo->product->name ?? '-' }}</td>
                            <td>{{ $wo->lot->registration_number ?? '-' }}</td>
                            <td class="text-danger fw-bold">
                                {{ $wo->amount ?? '-' }}<br>
                                <small class="text-muted">{{ $wo->product->metric->value }}</small>
                            </td>
                            <td>{{ $wo->user->name ?? '-' }}</td>
                            <td>{{ $wo->created_at->format('d-m-Y H:i:s') }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $wos->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
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

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });
    </script>
@endsection
