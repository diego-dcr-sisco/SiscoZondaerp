@extends('layouts.app')

@section('content')
<div class="row w-100 h-100 m-0">
    @include('dashboard.stock.navigation')

    <div class="col-11 p-3 m-0">
        
        
        <div class="row mb-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <a href="{{ route('consumptions.index') }}" class="col-auto btn-primary p-0 fs-3">
                        <i class="bi bi-arrow-left m-3"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">Detalles del Consumo</h1>
                    </div>
                </div>
            </div>
            <!-- <div class="col-lg-4 text-end">
                <span class="badge bg-{{ $groupedConsumption->status == 'approved' ? 'success' : ($groupedConsumption->status == 'rejected' ? 'danger' : 'warning') }} fs-6">
                    {{ $groupedConsumption->status_formatted }}
                </span>
            </div> -->
        </div>

        <!-- Información del Consumo -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                            <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Cliente:</td>
                                <td>{{ $groupedConsumption->customer->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Zona:</td>
                                <td>
                                    <span class="text-center">{{ $groupedConsumption->zone->name ?? 'Sin zona' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Período:</td>
                                <td>{{ $groupedConsumption->period_formatted }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Registrado por:</td>
                                <td>{{ $groupedConsumption->user->name ?? 'Usuario desconocido' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Fecha de registro:</td>
                                <td>{{ $groupedConsumption->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @if($groupedConsumption->updated_at != $groupedConsumption->created_at)
                            <tr>
                                <td class="fw-bold">Última actualización:</td>
                                <td>{{ $groupedConsumption->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="mb-5"> Observaciones:</h5>
                                <p class="mb-0">{{ $groupedConsumption->observation }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Productos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos del Consumo</h5>
                <div>
                    @if(auth()->user()->role_id == '1' || auth()->user()->role_id == '5')
                        <a href="{{ route('consumptions.supply-grouped', [
                            'customer_id' => $groupedConsumption->customer->id,
                            'zone_id' => $groupedConsumption->zone->id,
                            'month' => $groupedConsumption->month,
                            'year' => $groupedConsumption->year
                        ]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-seam"></i> Surtir
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <!-- <th>Fecha de registro</th> -->
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedConsumption->products as $product)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $product->product->name }}</div>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $product->amount }} {{ $product->units }}</span>
                                </td>
                                <!-- <td>
                                    <div>{{ $product->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $product->created_at->format('H:i') }}</small>
                                </td> -->
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('consumptions.edit', $product->id) }}" 
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Editar este producto">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('consumptions.destroy', $product->id) }}" 
                                              class="d-inline" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este producto del consumo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Eliminar este producto">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="fw-bold">Total de productos:</td>
                                <td class="text-center fw-bold">{{ $groupedConsumption->products_count }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .sidebar, .card-header .btn {
        display: none !important;
    }
}
</style>
@endsection 