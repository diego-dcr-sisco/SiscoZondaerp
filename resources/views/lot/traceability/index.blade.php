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
            $traceabilityNodes = $orders->getCollection()->groupBy('order_id');
        @endphp

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">
                    <i class="bi bi-diagram-3-fill"></i> Recorrido del producto
                </h5>
            </div>
            <div class="card-body">
                @if ($traceabilityNodes->isNotEmpty())
                    <div class="row align-items-center justify-content-between g-3 traceability-flow">
                        @foreach ($traceabilityNodes as $orderId => $items)
                            @php
                                $firstItem = $items->first();
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

                            <div class="traceability-node col-lg-5 p-0 card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <h6 class="card-title fw-bold mb-0">
                                            <i class="bi bi-circle-fill text-primary"></i>
                                            Nodo {{ $loop->iteration }}
                                        </h6>
                                        <div class="small text-muted">
                                            {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <label class="form-label">Cantidad</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                                <div class="form-control bg-light">
                                                    {{ number_format($items->sum('amount'), 2) }}
                                                    <span class="text-muted">{{ $metric }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <label class="form-label">Cliente</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                                <div class="form-control bg-light">
                                                    {{ $nodeOrder->customer->name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <label class="form-label">Order</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                                                <div class="form-control bg-light">
                                                    {{ $nodeOrder->folio ?? $nodeOrder->id ?? '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-6 col-12">
                                            <label class="form-label">Creado en</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                                <div class="form-control bg-light">
                                                    {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 mt-1">
                                        <div class="col-lg-6 col-12">
                                            <label class="form-label">Servicio(s)</label>
                                            <div class="traceability-list-box">
                                                @forelse ($services as $service)
                                                    <div class="traceability-list-item">
                                                        <i class="bi bi-chevron-right"></i> {{ $service }}
                                                    </div>
                                                @empty
                                                    <div class="text-muted">-</div>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-12">
                                            <label class="form-label">Metodo de aplicacion</label>
                                            <div class="traceability-list-box">
                                                @forelse ($methods as $method)
                                                    <div class="traceability-list-item">
                                                        <i class="bi bi-chevron-right"></i> {{ $method }}
                                                    </div>
                                                @empty
                                                    <div class="text-muted">-</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (!$loop->last)
                                <div class="traceability-arrow col-lg-1 col-12">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                            @else
                                <div class="traceability-end col-lg-1 col-12">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>
                        No hay nodos de trazabilidad para mostrar.
                    </div>
                @endif
            </div>
        </div>

        {{ $orders->links('pagination::bootstrap-5') }}
    </div>

    <style>
        .traceability-node {
            border-radius: 0.375rem;
        }

        .traceability-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d6efd;
            font-size: 1.75rem;
            line-height: 1;
        }

        .traceability-end {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #198754;
            font-size: 1.5rem;
            line-height: 1;
        }

        .traceability-node .form-control {
            min-height: 31px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .traceability-list-box {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background: #f8f9fa;
            min-height: 31px;
            padding: 0.25rem 0.5rem;
        }

        .traceability-list-item {
            font-size: 0.875rem;
            line-height: 1.5;
        }

        @media (max-width: 991.98px) {
            .traceability-end {
                padding-top: 0.25rem;
            }

            .traceability-arrow i {
                transform: rotate(90deg);
            }
        }
    </style>
@endsection
