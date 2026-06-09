@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'TRAZABILIDAD DEL LOTE ' . $lot->registration_number . ' - ' . ($lot->product->name ?? 'Producto desconocido'),
        'icon' => 'bi-truck',
    ])

    <div class="container-fluid">
        <form action="{{ route('lot.traceability', $lot->id) }}" method="GET" class="mb-3">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                        </h5>
                        <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target=".traceability-search-collapse" aria-expanded="true"
                            aria-controls="traceabilitySearchFilters traceabilitySearchFooter">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body collapse show traceability-search-collapse" id="traceabilitySearchFilters">
                    <div class="row g-3 mb-3">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <label for="order_id" class="form-label">Orden</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                <input type="text" class="form-control" id="order_id" name="order_id"
                                    value="{{ request('order_id') }}" placeholder="ID o folio de orden">
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-12">
                            <label for="service" class="form-label">Servicio</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-briefcase-fill"></i></span>
                                <input type="text" class="form-control" id="service" name="service"
                                    value="{{ request('service') }}" placeholder="Buscar servicio">
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-12">
                            <label for="quantity_filter" class="form-label">Cantidad</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-sort-numeric-down"></i></span>
                                <select class="form-select" id="quantity_filter" name="quantity_filter">
                                    <option value="">Todos</option>
                                    <option value="min" {{ request('quantity_filter') == 'min' ? 'selected' : '' }}>
                                        Minimo
                                    </option>
                                    <option value="max" {{ request('quantity_filter') == 'max' ? 'selected' : '' }}>
                                        Maximo
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-12">
                            <label for="size" class="form-label">Mostrar</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                <select class="form-select" id="size" name="size">
                                    <option value="10" {{ request('size') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('size', 25) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer collapse show traceability-search-collapse" id="traceabilitySearchFooter">
                    <div class="row justify-content-end">
                        <div class="col-lg-1 col-6">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-lg-1 col-6">
                            <a href="{{ route('lot.traceability', $lot->id) }}" class="btn btn-secondary btn-sm w-100">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @php
            $traceabilityTableRows = $orders->getCollection()->sortByDesc(function ($item) {
                return optional($item->order)->created_at ?? $item->created_at;
            })->values();
            $traceabilityRowOffset = ($orders->currentPage() - 1) * $orders->perPage();
            $traceabilityNodeNumbers = collect();
            $traceabilityTableRows->each(function ($record, $index) use ($traceabilityNodeNumbers, $traceabilityRowOffset) {
                if (!$traceabilityNodeNumbers->has($record->order_id)) {
                    $traceabilityNodeNumbers->put($record->order_id, $traceabilityRowOffset + $index + 1);
                }
            });
            $traceabilityNodes = $traceabilityTableRows->groupBy('order_id');
            $traceabilityNodeChunks = $traceabilityNodes->values()->chunk(4);
            $carouselId = 'traceabilityCarousel' . $lot->id;
        @endphp

        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="bi bi-diagram-3-fill"></i> Recorrido del producto
                    </h5>
                    @if ($traceabilityNodeChunks->count() > 1)
                        <div class="btn-group btn-group-sm" role="group" aria-label="Navegacion de nodos">
                            <button class="btn btn-outline-secondary" type="button" data-bs-target="#{{ $carouselId }}"
                                data-bs-slide="prev" title="Anterior">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" data-bs-target="#{{ $carouselId }}"
                                data-bs-slide="next" title="Siguiente">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if ($traceabilityNodes->isNotEmpty())
                    <div id="{{ $carouselId }}" class="carousel slide traceability-animated-carousel" data-bs-ride="false">
                        <div class="carousel-inner">
                            @foreach ($traceabilityNodeChunks as $chunkIndex => $chunk)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <div class="row g-3 traceability-flow">
                                        @foreach ($chunk as $items)
                                            @php
                                                $firstItem = $items->first();
                                                $nodeNumber =
                                                    $traceabilityNodeNumbers->get($firstItem->order_id) ??
                                                    $traceabilityRowOffset + $chunkIndex * 4 + $loop->iteration;
                                                $nodeOrder = $firstItem->order;
                                                $createdAt = $nodeOrder->created_at ?? $firstItem->created_at ?? null;
                                                $metric = $firstItem->metric->value ?? '';
                                                $services = $items
                                                    ->map(fn($item) => $item->service->name ?? null)
                                                    ->filter()
                                                    ->unique()
                                                    ->values();
                                                $methods = $items
                                                    ->map(fn($item) => $item->appMethod->name ?? null)
                                                    ->filter()
                                                    ->unique()
                                                    ->values();
                                            @endphp

                                            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                                                <div class="traceability-node card h-100">
                                                    <div class="card-header py-2 bg-light">
                                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                                            <h6 class="card-title fw-bold mb-0 small">
                                                                <i class="bi bi-circle-fill text-primary"></i>
                                                                Nodo {{ $nodeNumber }}
                                                            </h6>
                                                            <div class="traceability-node-amount text-success text-end">
                                                                {{ number_format($items->sum('amount'), 2) }}
                                                                <span>{{ $metric }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-body p-2">
                                                        <div class="traceability-row">
                                                            <span class="traceability-label">
                                                                <i class="bi bi-person-fill"></i> Cliente
                                                            </span>
                                                            <span class="traceability-value" title="{{ $nodeOrder->customer->name ?? 'N/A' }}">
                                                                {{ $nodeOrder->customer->name ?? 'N/A' }}
                                                            </span>
                                                        </div>

                                                        <div class="traceability-row">
                                                            <span class="traceability-label">
                                                                <i class="bi bi-file-earmark-text"></i> Orden
                                                            </span>
                                                            <span class="traceability-value">
                                                                {{ $nodeOrder->folio ?? $nodeOrder->id ?? '-' }}
                                                            </span>
                                                        </div>

                                                        <div class="traceability-row">
                                                            <span class="traceability-label">
                                                                <i class="bi bi-calendar-event"></i> Fecha
                                                            </span>
                                                            <span class="traceability-value">
                                                                {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') : '-' }}
                                                            </span>
                                                        </div>

                                                        <div class="traceability-compact-section">
                                                            <div class="traceability-section-title">Servicio(s)</div>
                                                            <div class="traceability-list-box">
                                                                @forelse ($services->take(3) as $service)
                                                                    <div class="traceability-list-item" title="{{ $service }}">
                                                                        {{ $service }}
                                                                    </div>
                                                                @empty
                                                                    <div class="text-muted">-</div>
                                                                @endforelse
                                                                @if ($services->count() > 3)
                                                                    <div class="text-muted small">+{{ $services->count() - 3 }} mas</div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="traceability-compact-section">
                                                            <div class="traceability-section-title">Metodo</div>
                                                            <div class="traceability-list-box">
                                                                @forelse ($methods->take(2) as $method)
                                                                    <div class="traceability-list-item" title="{{ $method }}">
                                                                        {{ $method }}
                                                                    </div>
                                                                @empty
                                                                    <div class="text-muted">-</div>
                                                                @endforelse
                                                                @if ($methods->count() > 2)
                                                                    <div class="text-muted small">+{{ $methods->count() - 2 }} mas</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card-footer py-1 bg-white">
                                                        <div class="d-flex align-items-center justify-content-between small text-muted">
                                                            <span>Registro</span>
                                                            <span>
                                                                #{{ $nodeNumber }} / {{ $orders->total() }}
                                                                @if ($nodeNumber === $orders->total())
                                                                    <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($traceabilityNodeChunks->count() > 1)
                            <div class="carousel-indicators traceability-carousel-indicators">
                                @foreach ($traceabilityNodeChunks as $chunk)
                                    <button type="button" data-bs-target="#{{ $carouselId }}"
                                        data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"
                                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-label="Grupo {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>
                        No hay nodos de trazabilidad para mostrar.
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">
                    <i class="bi bi-table"></i> Registros del lote
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Orden</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Servicio</th>
                                <th scope="col">Metodo</th>
                                <th scope="col" class="text-end">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($traceabilityTableRows as $record)
                                @php
                                    $recordOrder = $record->order;
                                    $recordDate = $recordOrder->created_at ?? $record->created_at ?? null;
                                @endphp
                                <tr>
                                    <td class="fw-bold text-muted">
                                        {{ $traceabilityRowOffset + $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $recordDate ? \Carbon\Carbon::parse($recordDate)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $recordOrder->folio ?? $recordOrder->id ?? '-' }}</span>
                                        <span class="text-muted small d-block">ID {{ $recordOrder->id ?? '-' }}</span>
                                    </td>
                                    <td>{{ $recordOrder->customer->name ?? 'N/A' }}</td>
                                    <td>{{ $record->service->name ?? '-' }}</td>
                                    <td>{{ $record->appMethod->name ?? '-' }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($record->amount, 2) }}
                                        <span class="text-muted">{{ $record->metric->value ?? '' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay registros para mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{ $orders->links('pagination::bootstrap-5') }}
    </div>

    <style>
        .traceability-node {
            border-radius: 0.375rem;
            border-color: #dee2e6;
        }

        .traceability-flow {
            padding-bottom: 1.5rem;
        }

        .traceability-animated-carousel .carousel-inner {
            overflow: hidden;
        }

        .traceability-animated-carousel .carousel-item {
            transition: transform .75s cubic-bezier(.22, .61, .36, 1), opacity .55s ease-in-out;
        }

        .traceability-animated-carousel .carousel-item.active .traceability-node {
            animation: traceability-node-enter .65s cubic-bezier(.22, .61, .36, 1) both;
        }

        .traceability-animated-carousel .carousel-item.active .traceability-flow > :nth-child(2) .traceability-node {
            animation-delay: .1s;
        }

        .traceability-animated-carousel .carousel-item.active .traceability-flow > :nth-child(3) .traceability-node {
            animation-delay: .2s;
        }

        .traceability-animated-carousel .carousel-item.active .traceability-flow > :nth-child(4) .traceability-node {
            animation-delay: .3s;
        }

        @keyframes traceability-node-enter {
            from {
                opacity: 0;
                transform: translateY(24px) scale(.93);
            }

            65% {
                opacity: 1;
                transform: translateY(-3px) scale(1.02);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .traceability-carousel-indicators {
            position: static;
            margin: .75rem 0 0;
        }

        .traceability-carousel-indicators [data-bs-target] {
            width: .55rem;
            height: .55rem;
            border-radius: 50%;
            background-color: #6c757d;
        }

        .traceability-row {
            display: grid;
            grid-template-columns: minmax(76px, auto) minmax(0, 1fr);
            gap: .5rem;
            align-items: center;
            padding: .25rem 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .traceability-label {
            color: #6c757d;
            font-size: .75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .traceability-value {
            color: #212529;
            font-size: .82rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .traceability-node-amount {
            flex: 0 0 auto;
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .traceability-node-amount span {
            font-size: .8rem;
            font-weight: 700;
        }

        .traceability-compact-section {
            margin-top: .5rem;
        }

        .traceability-section-title {
            color: #6c757d;
            font-size: .75rem;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .traceability-list-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            min-height: 2rem;
        }

        .traceability-list-item {
            font-size: .8rem;
            line-height: 1.35;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (prefers-reduced-motion: reduce) {
            .traceability-animated-carousel .carousel-item {
                transition: none;
            }

            .traceability-animated-carousel .carousel-item.active .traceability-node {
                animation: none;
            }
        }
    </style>
@endsection
