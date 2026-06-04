@extends('layouts.app')
@section('content')
    <style>
        .movements-shell {
            color: #212529;
        }

        .movement-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            white-space: nowrap;
        }

        .movement-products-list {
            min-width: 28rem;
        }

        .movement-products-table {
            width: 100%;
            min-width: 28rem;
            border-collapse: collapse;
            background: #fff;
        }

        .movement-products-table th,
        .movement-products-table td {
            border-bottom: 1px solid #dee2e6;
            padding: .35rem .45rem;
            vertical-align: middle;
        }

        .movement-products-table th {
            color: #6c757d;
            font-size: .75rem;
            font-weight: 700;
            background: #f8f9fa;
        }

        .movement-products-table tr:last-child td {
            border-bottom: 0;
        }

        .warehouse-summary-card {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #fff;
            padding: .9rem 1rem;
            height: 100%;
        }

        .warehouse-summary-label {
            color: #6c757d;
            font-size: .82rem;
        }

        .warehouse-summary-value {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .movement-status-badge {
            min-width: 4.5rem;
            text-align: center;
        }
    </style>

    @include('components.page-header', [
        'title' => 'MOVIMIENTOS EN LOS ALMACENES',
        'icon' => 'bi-arrow-left-right',
    ])

    <div class="container-fluid p-0 movements-shell">
        <div class="m-3">
            <form action="{{ route('stock.movements.all') }}" method="GET" class="mb-3">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0"><i class="bi bi-funnel-fill"></i> Busqueda Avanzada</h5>
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target=".movements-filter-collapse" aria-expanded="true"
                                aria-controls="movementsFilterBody movementsFilterFooter">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body collapse show movements-filter-collapse" id="movementsFilterBody">
                        <div class="row g-3 mb-3">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="warehouse" class="form-label">Almacén</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="text" class="form-control" id="warehouse" name="warehouse"
                                        value="{{ request('warehouse', $warehouse->name ?? '') }}"
                                        placeholder="Nombre del almacén">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="movement" class="form-label">Movimiento</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-arrow-left-right"></i></span>
                                    <select class="form-select" id="movement" name="movement_id">
                                        <option value="">Todos</option>
                                        @foreach ($movement_types as $movementType)
                                            <option value="{{ $movementType->id }}"
                                                {{ request('movement_id') == $movementType->id ? 'selected' : '' }}>
                                                {{ $movementType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="product" class="form-label">Producto</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-box"></i></span>
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
                                            <option value="{{ $lot->id }}" {{ request('lot_id') == $lot->id ? 'selected' : '' }}>
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
                                        value="{{ request('date_range') }}" placeholder="Rango de fechas"
                                        autocomplete="off">
                                </div>
                            </div>

                            <div class="col-lg-3 col-sm-6 col-12">
                                <label for="direction" class="form-label">Ordenar / Mostrar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction', 'DESC') == 'DESC' ? 'selected' : '' }}>
                                            DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                            ASC
                                        </option>
                                    </select>
                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select form-select-sm" id="size" name="size">
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

                    <div class="card-footer collapse show movements-filter-collapse" id="movementsFilterFooter">
                        <div class="row justify-content-end">
                            <div class="col-lg-1 col-6">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-lg-1 col-6">
                                <a href="{{ route('stock.movements.all') }}" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="warehouse-summary-card">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="warehouse-summary-label">Almacenes</div>
                                <div class="warehouse-summary-value">{{ $summary['warehouses'] ?? $warehouses->count() }}</div>
                            </div>
                            <span class="badge text-bg-light border"><i class="bi bi-building"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="warehouse-summary-card">
                        <div class="warehouse-summary-label">Movimientos filtrados</div>
                        <div class="warehouse-summary-value">{{ $summary['total'] ?? $movements->total() }}</div>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-6 col-12">
                    <div class="warehouse-summary-card">
                        <div class="warehouse-summary-label">Entradas</div>
                        <div class="warehouse-summary-value text-success">{{ $summary['entries'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-6 col-12">
                    <div class="warehouse-summary-card">
                        <div class="warehouse-summary-label">Salidas</div>
                        <div class="warehouse-summary-value text-danger">{{ $summary['exits'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-6 col-12">
                    <div class="warehouse-summary-card">
                        <div class="warehouse-summary-label">Revertidos</div>
                        <div class="warehouse-summary-value text-secondary">{{ $summary['reverted'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped caption-top">
                    <caption class="text-muted">
                        Mostrando {{ $movements->firstItem() ?? 0 }}-{{ $movements->lastItem() ?? 0 }} de {{ $movements->total() }} movimientos
                    </caption>
                    <thead class="table-light">
                        <tr>
                            <th class="fw-bold" scope="col">#</th>
                            <th class="fw-bold" scope="col">Almacén origen</th>
                            <th class="fw-bold" scope="col">Almacén destino</th>
                            <th class="fw-bold" scope="col">Productos y movimientos</th>
                            <th class="fw-bold" scope="col">Observaciones</th>
                            <th class="fw-bold" scope="col">Fecha</th>
                            <th class="fw-bold" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $index => $movement)
                            @php
                                $movementProducts = $movement->products()->get();
                                $visibleProducts = $movementProducts->take(3);
                                $hiddenProducts = $movementProducts->slice(3);
                                $collapseId = 'movement-products-' . $movement->id;
                            @endphp
                            <tr class="{{ !$movement->is_active ? 'table-light text-muted' : '' }}">
                                <th scope="row" class="fw-bold text-muted">
                                    {{ ($movements->currentPage() - 1) * $movements->perPage() + $index + 1 }}
                                    @if (!$movement->is_active)
                                        <div class="badge text-bg-secondary mt-1">Revertido</div>
                                    @endif
                                </th>
                                <td class="{{ $movement->warehouseType() == 1 ? 'text-primary fw-bold' : '' }}">
                                    {{ $movement->warehouse->name ?? '-' }}
                                </td>
                                <td class="{{ $movement->warehouseType() == 2 ? 'text-primary fw-bold' : '' }}">
                                    {{ $movement->destinationWarehouse->name ?? '-' }}
                                </td>
                                <td>
                                    <div class="movement-products-list">
                                        @if ($movementProducts->isEmpty())
                                            <span class="text-muted">Sin productos</span>
                                        @else
                                            <table class="movement-products-table">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Lote</th>
                                                        <th>Almacén</th>
                                                        <th>Tipo</th>
                                                        <th>Movimiento</th>
                                                        <th class="text-end">Cantidad</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($visibleProducts as $mp)
                                                        <tr>
                                                            <td class="fw-semibold">{{ $mp->product->name ?? '-' }}</td>
                                                            <td>{{ $mp->lot->registration_number ?? '-' }}</td>
                                                            <td>{{ $mp->warehouse->name ?? '-' }}</td>
                                                            <td>
                                                                <span class="badge {{ $mp->isEntry() ? 'text-bg-success' : 'text-bg-danger' }} movement-status-badge">
                                                                    {{ $mp->isEntry() ? 'Entrada' : 'Salida' }}
                                                                </span>
                                                            </td>
                                                            <td class="{{ $mp->movementColorClass() }} fw-bold">
                                                                {{ $mp->movement->name ?? '-' }}
                                                            </td>
                                                            <td class="text-end {{ $mp->movementColorClass() }} fw-bold">
                                                                {{ number_format((float) $mp->amount, 2) }}
                                                                <small class="text-muted fw-normal">{{ $mp->product->metric->value ?? '-' }}</small>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif

                                        @if ($hiddenProducts->isNotEmpty())
                                            <div class="collapse" id="{{ $collapseId }}">
                                                <table class="movement-products-table">
                                                    <tbody>
                                                        @foreach ($hiddenProducts as $mp)
                                                            <tr>
                                                                <td class="fw-semibold">{{ $mp->product->name ?? '-' }}</td>
                                                                <td>{{ $mp->lot->registration_number ?? '-' }}</td>
                                                                <td>{{ $mp->warehouse->name ?? '-' }}</td>
                                                                <td>
                                                                    <span class="badge {{ $mp->isEntry() ? 'text-bg-success' : 'text-bg-danger' }} movement-status-badge">
                                                                        {{ $mp->isEntry() ? 'Entrada' : 'Salida' }}
                                                                    </span>
                                                                </td>
                                                                <td class="{{ $mp->movementColorClass() }} fw-bold">
                                                                    {{ $mp->movement->name ?? '-' }}
                                                                </td>
                                                                <td class="text-end {{ $mp->movementColorClass() }} fw-bold">
                                                                    {{ number_format((float) $mp->amount, 2) }}
                                                                    <small class="text-muted fw-normal">{{ $mp->product->metric->value ?? '-' }}</small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <button class="btn btn-link btn-sm p-0 mt-1" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                                aria-expanded="false" aria-controls="{{ $collapseId }}">
                                                Ver {{ $hiddenProducts->count() }} más
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $movement->observations ?: '-' }}</td>
                                <td class="text-nowrap">
                                    {{ \Carbon\Carbon::parse($movement->date)->format('d/m/Y') }}
                                    <div class="small text-muted">{{ $movement->time }}</div>
                                </td>
                                <td>
                                    <div class="movement-actions">
                                        <a href="{{ route('stock.movement', ['id' => $movement->id]) }}"
                                            class="btn btn-dark btn-sm" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Generar voucher">
                                            <i class="bi bi-file-pdf-fill"></i>
                                        </a>

                                        @if ($movement->is_active)
                                            <form action="{{ route('stock.revertMovement', ['id' => $movement->id]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Deseas revertir este movimiento? Esta acción ajustará las existencias relacionadas.');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-warning btn-sm"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Revertir">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Movimiento revertido">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-danger fw-bold text-center" colspan="7">Sin movimientos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
            {{ $movements->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script>
        $(function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

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

            $('#date-range').on('cancel.daterangepicker', function() {
                $(this).val('');
            });
        });
    </script>
@endsection
