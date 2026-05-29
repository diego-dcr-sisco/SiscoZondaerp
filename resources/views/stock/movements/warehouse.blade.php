@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => isset($warehouse) ? 'MOVIMIENTOS DEL ALMACEN ' . $warehouse->name : 'MOVIMIENTOS EN LOS ALMACENES',
        'icon' => 'bi-arrow-left-right',
    ])

    <div class="container-fluid p-0">
            <div class="m-3">
                <form action="{{ route('stock.movements.all', ['id' => 0]) }}" method="GET" class="mb-3">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h5 class="card-title fw-bold mb-0">
                                    <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                                </h5>
                                <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target=".warehouse-movement-search-collapse" aria-expanded="true"
                                    aria-controls="warehouseMovementSearchFilters warehouseMovementSearchFooter">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body collapse show warehouse-movement-search-collapse"
                            id="warehouseMovementSearchFilters">
                            <div class="row g-3 mb-3">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <label for="warehouse" class="form-label">Almacen</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input type="text" class="form-control" id="warehouse" name="warehouse"
                                            value="{{ $warehouse->name ?? '' }}" placeholder="Nombre del almacen."
                                            {{ isset($warehouse) ? 'readonly' : '' }}>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-sm-6 col-12">
                                    <label for="movement" class="form-label">Movimiento</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-arrow-left-right"></i></span>
                                        <select class="form-select" id="movement" name="movement_id">
                                            <option value="">Todos los movimientos</option>
                                            @foreach ($movement_types as $movement)
                                                <option value="{{ $movement->id }}"
                                                    {{ request('movement_id') == $movement->id ? 'selected' : '' }}>
                                                    {{ $movement->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                                    {{ $lot->registration_number ?? '-' }}
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
                        <div class="card-footer collapse show warehouse-movement-search-collapse"
                            id="warehouseMovementSearchFooter">
                            <div class="row justify-content-end">
                                <div class="col-lg-1 col-6">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                </div>
                                <div class="col-lg-1 col-6">
                                    <a href="{{ isset($warehouse) ? route('stock.movements.warehouse', ['id' => $warehouse->id]) : route('stock.movements.all') }}"
                                        class="btn btn-secondary btn-sm w-100">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Almacen origen</th>
                            <th scope="col">Almacen destino</th>
                            <th scope="col">Productos y movimientos</th>
                            {{-- <th scope="col">Lote</th>
                        <th scope="col">Existencia previa</th>
                        <th scope="col">Cantidad del movimiento</th> --}}
                            <th scope="col">Observaciones</th>
                            <th scope="col">Fecha</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $movement)
                            <tr>
                                <th scope="row">{{ $movement->id }}</th>
                                <td class="{{ $movement->warehouseType() == 1 ? 'text-primary fw-bold' : '' }}">
                                    {{ $movement->warehouse->name ?? '-' }}</td>
                                <td class="{{ $movement->warehouseType() == 2 ? 'text-primary fw-bold' : '' }}">
                                    {{ $movement->destinationWarehouse->name ?? '-' }}</td>
                                <td class="p-0">
                                    <table class="table m-0 table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th class="fw-bold" scope="col">Producto</th>
                                                <th class="fw-bold" scope="col">Lote</th>
                                                <th class="fw-bold" scope="col">Movimiento</th>
                                                <th class="fw-bold" scope="col">Cantidad del movimiento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($movement->warehouseProducts($warehouse->id) as $mp)
                                                <tr>
                                                    <th scope="row">{{ $mp->product->name }}</th>
                                                    <td>{{ $mp->lot->registration_number ?? '-' }}</td>
                                                    <td class="{{ $mp->movementColorClass() }} fw-bold">
                                                        {{ $mp->movement->name ?? '-' }}</td>
                                                    <td class="{{ $mp->movementColorClass() }}">
                                                        {{ number_format((float) $mp->amount, 2) }}
                                                        <small class="text-muted">{{ $mp->product->metric->value ?? '-' }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                                <td>{{ $movement->observations ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($movement->date)->format('d/m/Y') }} -
                                    {{ $movement->time }}</td>
                                <td>
                                    <a href="{{ route('stock.movement', ['id' => $movement->id]) }}"
                                        class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Generar voucher">
                                        <i class="bi bi-file-pdf-fill"></i>
                                    </a>
                                    <a href="" class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Revertir">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $movements->links('pagination::bootstrap-5') }}
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

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    @endsection
