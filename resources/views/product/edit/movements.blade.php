@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Editar Producto: <span class="text-uppercase fw-bold text-primary">{{ $product->name }}</span></h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('product.index') }}" class="text-decoration-none">Productos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Movimientos de Inventario</li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge bg-secondary p-2 fs-6">SKU/Código: {{ $product->code ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 border-start border-success border-4 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Entradas</h6>
                                    <h3 class="fw-bold text-success mb-0">{{ number_format($totalEntries ?? 0, 2) }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                                    <i class="fas fa-arrow-down fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 border-start border-danger border-4 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Salidas</h6>
                                    <h3 class="fw-bold text-danger mb-0">{{ number_format($totalExits ?? 0, 2) }}</h3>
                                </div>
                                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                                    <i class="fas fa-arrow-up fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 border-start border-primary border-4 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Saldo Neto (Filtro)</h6>
                                    <h3 class="fw-bold {{ ($netBalance ?? 0) >= 0 ? 'text-primary' : 'text-warning' }} mb-0">
                                        {{ number_format($netBalance ?? 0, 2) }}
                                    </h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                    <i class="fas fa-calculator fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-dark text-white fw-bold py-2 d-flex align-items-center">
                    <i class="fas fa-filter me-2 small"></i> Filtros de Búsqueda
                </div>
                <div class="card-body bg-light">
                    <form method="GET" action="{{ route('product.edit.movements', $product->id) }}" class="row g-2">
                        <div class="col-md-3">
                            <label for="filter_start_date" class="form-label small fw-bold mb-1 text-secondary">Fecha Inicio</label>
                            <input type="date" id="filter_start_date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="filter_end_date" class="form-label small fw-bold mb-1 text-secondary">Fecha Fin</label>
                            <input type="date" id="filter_end_date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="filter_warehouse_id" class="form-label small fw-bold mb-1 text-secondary">Almacén</label>
                            <select id="filter_warehouse_id" name="warehouse_id" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="filter_lot_id" class="form-label small fw-bold mb-1 text-secondary">Lote</label>
                            <select id="filter_lot_id" name="lot_id" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach($lots as $lot)
                                    @php $lotLabel = $lot->number ?? $lot->name ?? 'Lote #'.$lot->id; @endphp
                                    <option value="{{ $lot->id }}" {{ request('lot_id') == $lot->id ? 'selected' : '' }}>
                                        {{ $lotLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="filter_type" class="form-label small fw-bold mb-1 text-secondary">Tipo</label>
                            <select id="filter_type" name="type" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="ingreso" {{ request('type') == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                                <option value="egreso" {{ request('type') == 'egreso' ? 'selected' : '' }}>Egreso</option>
                            </select>
                        </div>
                        
                        <div class="col-12 text-end mt-2">
                            <a href="{{ route('product.edit.movements', $product->id) }}" class="btn btn-sm btn-secondary px-3 me-1">
                                <i class="fas fa-undo me-1 small"></i> Limpiar
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="fas fa-search me-1 small"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-dark fw-bold d-flex align-items-center">
                        <i class="fas fa-history text-muted me-2"></i> Historial de Movimientos de Stock
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    @if($movements->isEmpty())
                        <div class="p-4 text-center">
                            <div class="alert alert-info border-0 mb-0 d-inline-block px-4 py-3 shadow-sm rounded">
                                <i class="fas fa-info-circle me-2 text-info"></i> No se registraron movimientos con los criterios seleccionados.
                            </div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0 text-nowrap">
                                <thead class="table-light border-bottom text-uppercase fs-7 text-secondary fw-bold">
                                    <tr>
                                        <th class="ps-4 py-3">Fecha / Hora</th>
                                        <th>Tipo de Movimiento</th>
                                        <th>Almacén Origen/Destino</th>
                                        <th>Lote</th>
                                        <th class="text-end">Cantidad</th>
                                        <th>Usuario / Responsable</th>
                                        <th class="pe-4">Observaciones u Referencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $mv)
                                        @php
                                            $rawType = strtolower($mv->type ?? $mv->movement->type ?? $mv->movement->name ?? $mv->warehouseMovement->type ?? '');
                                            $isEntry = str_contains($rawType, 'ingreso') || str_contains($rawType, 'entrada') || str_contains($rawType, 'compra') || ($mv->quantity > 0);
                                            $lotLabel = $mv->lot->number ?? $mv->lot->name ?? 'N/A';
                                        @endphp
                                        <tr>
                                            <td class="ps-4 py-3 text-secondary fw-medium">
                                                {{ $mv->created_at ? $mv->created_at->format('d/m/Y H:i') : 'N/A' }}
                                            </td>
                                            
                                            <td>
                                                @if($isEntry)
                                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1 text-uppercase rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fas fa-arrow-circle-down me-1"></i> Entrada
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1 text-uppercase rounded-pill fw-bold" style="font-size: 0.75rem;">
                                                        <i class="fas fa-arrow-circle-up me-1"></i> Salida
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            <td class="fw-semibold text-dark">
                                                <i class="fas fa-warehouse text-muted me-1 small"></i>
                                                {{ $mv->warehouse->name ?? 'Almacén Desconocido' }}
                                            </td>
                                            
                                            <td>
                                                <span class="bg-light border px-2 py-1 rounded text-secondary small fw-medium">
                                                    {{ $lotLabel }}
                                                </span>
                                            </td>
                                            
                                            <td class="text-end fw-bold {{ $isEntry ? 'text-success' : 'text-danger' }}">
                                                {{ $isEntry ? '+' : '-' }}{{ number_format(abs($mv->quantity), 2) }}
                                            </td>
                                            
                                            <td>
                                                <span class="small fw-medium text-dark">
                                                    {{ $mv->user->name ?? $mv->warehouseMovement->user->name ?? 'Sistema' }}
                                                </span>
                                            </td>
                                            
                                            <td class="pe-4 text-secondary small">
                                                @if($mv->warehouse_movement_id)
                                                    <a href="{{ route('stock.movements.show', $mv->warehouse_movement_id) }}" class="text-decoration-none fw-semibold">
                                                        <i class="fas fa-external-link-alt me-1 small"></i> Mov: #{{ $mv->warehouse_movement_id }}
                                                    </a>
                                                @elseif($mv->movement_id)
                                                    <a href="{{ route('stock.movements.show', $mv->movement_id) }}" class="text-decoration-none fw-semibold">
                                                        <i class="fas fa-external-link-alt me-1 small"></i> Ref: #{{ $mv->movement_id }}
                                                    </a>
                                                @endif
                                                
                                                @php $notes = $mv->notes ?? $mv->movement->description ?? ''; @endphp
                                                @if(!empty($notes))
                                                    <span class="text-muted ms-1">({{ Str::limit($notes, 40) }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="card-footer bg-white border-top d-flex justify-content-center py-3">
                            {{ $movements->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection