@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('stock.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                @isset($warehouse)
                    MOVIMIENTOS DEL ALMACEN <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $warehouse->name }}</span>
                @else
                    MOVIMIENTOS EN LOS ALMACENES
                    @endif
                </span>
            </div>
            <div class="m-3">
                <table class="table table-bordered table-hover table-sm align-middle caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">
                        <form action="{{ route('stock.movements.all', ['id' => 0]) }}" method="GET">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4 col-12 mb-3">
                                    <label for="warehouse" class="form-label">Almacen</label>
                                    <input type="text" class="form-control form-control-sm" id="warehouse" name="warehouse"
                                        value="{{ $warehouse->name ?? '' }}" placeholder="Nombre del almacen."
                                        {{ isset($warehouse) ? 'readonly' : '' }} />
                                </div>
                                <div class="col-lg-2 col-12 mb-3">
                                    <label for="movement" class="form-label">Movimiento</label>
                                    <select class="form-select form-select-sm" id="movement" name="movement_id">
                                        <option value="">Todos los movimientos</option>
                                        @foreach ($movement_types as $movement)
                                            <option value="{{ $movement->id }}"
                                                {{ request('movement_id') == $movement->id ? 'selected' : '' }}>
                                                {{ $movement->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-12 mb-3">
                                    <label for="movement" class="form-label">Producto</label>
                                    <select class="form-select form-select-sm" id="product" name="product_id">
                                        <option value="">Todos los productos</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-12 mb-3">
                                    <label for="lot" class="form-label">Lote</label>
                                    <select class="form-select form-select-sm" id="lot" name="lot_id">
                                        <option value="">Todos los lotes</option>
                                        @foreach ($lots as $lot)
                                            <option value="{{ $lot->id }}"
                                                {{ request('lot_id') == $lot->id ? 'selected' : '' }}>
                                                {{ $lot->registration_number ?? '-' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-12 mb-3">
                                    <label for="lot" class="form-label">Fecha</label>
                                    <input type="text" class="form-control form-control-sm" id="date-range" name="date_range"
                                        value="{{ request('date-range') }}" placeholder="Rango de fecha de los movimientos"
                                        autocomplete="off">
                                </div>
                                <div class="col-auto mb-3">
                                    <label for="signature_status" class="form-label">Dirección</label>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                        </option>
                                    </select>
                                </div>

                                <div class="col-auto mb-3">
                                    <label for="order_type" class="form-label">Total</label>
                                    <select class="form-select form-select-sm" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="col-12 d-flex justify-content-end m-0">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>

                                </div>
                            </div>
                        </form>
                    </caption>
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
                                                    <td
                                                        class="{{ $mp->movement && $mp->movement->type == 'in' ? 'text-success' : 'text-danger' }} fw-bold">
                                                        {{ $mp->movement->name ?? '-' }}</td>
                                                    <td
                                                        class="{{ $mp->movement && $mp->movement->type == 'in' ? 'text-success' : 'text-danger' }}">
                                                        {{ $mp->amount }}</td>
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
