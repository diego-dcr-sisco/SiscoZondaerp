@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'TRAZABILIDAD DEL LOTE ' . $lot->registration_number,
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
                            data-bs-target=".traceability-search-collapse" aria-expanded="false"
                            aria-controls="traceabilitySearchFilters traceabilitySearchFooter">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body collapse traceability-search-collapse" id="traceabilitySearchFilters">
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

                <div class="card-footer collapse traceability-search-collapse" id="traceabilitySearchFooter">
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

        <div class="overflow-auto w-100">
            <table class="table table-bordered table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Orden</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Metodo de aplicacion</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
                        @php
                            $traceabilityDate = $order->order->completed_date
                                ?? $order->order->programmed_date
                                ?? $order->order->created_at
                                ?? null;
                        @endphp
                        <tr>
                            <th scope="row">{{ $orders->firstItem() + $index }}</th>
                            <td class="fw-bold text-primary">
                                {{ $order->order->folio ?? $order->order->id ?? '-' }}
                            </td>
                            <td class="fw-bold">{{ $order->order->customer->name ?? 'N/A' }}</td>
                            <td>{{ $order->service->name ?? 'N/A' }}</td>
                            <td>{{ $order->appMethod->name ?? '-' }}</td>
                            <td>
                                {{ $order->amount ?? '-' }}
                                <span class="text-muted">{{ $order->metric->value ?? '' }}</span>
                            </td>
                            <td>
                                {{ $traceabilityDate ? \Carbon\Carbon::parse($traceabilityDate)->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-truck text-muted fs-1 mb-3"></i>
                                    <h5 class="text-muted">Sin trazabilidad para mostrar</h5>
                                    <p class="text-muted mb-0">No se encontraron consumos asociados a este lote.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
@endsection
