@extends('layouts.app')
@section('content')
<div class="container-fluid p-0">

    
    <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h1 class="col-auto fs-2 fw-bold m-0">{{ $lot->registration_number }}</h1>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <caption class="border rounded-top p-2 text-dark bg-light caption-top">
                <form action="{{ route('order.search') }}" method="GET">
                    @csrf
                    <div class="row g-3 mb-0">
                        <div class="col-lg-3 col-md-6 col-12">
                            <label for="order_id" class="form-label">ID Orden</label>
                            <input type="text" class="form-control form-control-sm" name="order_id" value="{{ request('order_id') }}"/>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <label for="service" class="form-label">Servicio</label>
                            <input type="text" class="form-control form-control-sm" name="service" value="{{ request('service') }}"/>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <label for="quantity" class="form-label">Cantidad</label>
                            <select class="form-select form-select-sm" name="quantity_filter">
                                <option value="">Todos</option>
                                <option value="min" {{ request('quantity_filter') == 'min' ? 'selected' : '' }}>Mínimo</option>
                                <option value="max" {{ request('quantity_filter') == 'max' ? 'selected' : '' }}>Máximo</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <label for="size" class="form-label">Registros por página</label>
                            <select class="form-select form-select-sm" id="size" name="size">
                                <option value="10" {{ request('size') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-lg-12 d-flex justify-content-end m-0 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                            <a href="{{ route('order.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </caption>
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Servicio</th>
                    <th scope="col">Método de aplicación</th>
                    <th scope="col">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $index => $order)
                    <tr>
                        <th scope="row">{{ $index + 1 }}</th>
                        <td class="fw-bold">{{ $order->order->customer->name ?? 'N/A' }}</td>
                        <td>{{ $order->service->name ?? 'N/A' }}</td>
                        <td>{{ $order->appMethod->name ?? '-' }}</td>
                        <td>{{ $order->amount ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        {{-- <div class="text-muted">
            Mostrando {{ $orders->firstItem() }} - {{ $orders->lastItem() }} de {{ $orders->total() }} registros
        </div> --}}
        
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.1);
    }
    .badge {
        font-size: 0.85em;
    }
    .caption-top {
        background-color: #f8f9fa !important;
    }
</style>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
</script>
@endsection