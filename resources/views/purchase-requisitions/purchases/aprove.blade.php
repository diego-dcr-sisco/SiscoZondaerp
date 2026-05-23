@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row p-3">
        <div class="col-lg-2 text-left">
            <a href="{{ route('purchase-requisition.index') }}" class="btn btn-secondary">
                Volver
            </a>        
        </div>
        <div class="col-lg-10 text-left">
            <h1>Requisicion de Compra Insumos</h1>      
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            Folio de Solicitud {{ $requisition->folio_id }}
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <p class="card-text"><strong>Solicitante:</strong> {{ $requisition->user->name }}</p>
                </div>
                <div class="col-lg-6">
                    <p class="card-text"><strong>Empresa Destino:</strong> {{ $requisition->customer->name }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <p class="card-text"><strong>Departamento Solicitante:</strong> {{ $requisition->user->workDepartment->name }}</p>
                </div>
                <div class="col-lg-6">
                    <p class="card-text"><strong>Dirección Empresa Destino:</strong> {{ $requisition->customer->address }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <p class="card-text"><strong>Fecha de Solicitud:</strong> {{ $requisition->created_at }}</p>
                </div>
                <div class="col-lg-6">
                    <p class="card-text"><strong>Fecha a Requerir:</strong> {{ $requisition->required_by_date }}</p>
                </div>
            </div>
            <p class="card-text"><strong>Estado:</strong> {{ $requisition->state }}</p>
            <h5 class="card-title">Productos</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 30px;">Cantidad</th>
                            <th style="width: 30px;">Unidad</th>
                            <th>Producto</th>
                            <th>Proveedor 1</th>
                            <th>Costo 1</th>
                            <th>Proveedor 2</th>
                            <th>Costo 2</th>
                        </tr>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->quantity }}</td>
                                <td>{{ $product->unit }}</td>
                                <td>{{ $product->description }}</td>
                                <td>{{ $product->supplier1->name }}</td>
                                <td>{{ $product->supplier1_cost }}</td>
                                <td>{{ $product->supplier2->name }}</td>
                                <td>{{ $product->supplier2_cost }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <form action="{{ route('purchase-requisition.approve', $requisition->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-success" onclick="return confirm('¿Estás seguro de que deseas aprobar esta solicitud de compra?')">
                    Aprobar
                </button>
            </form>
            <form action="{{ route('purchase-requisition.reject', $requisition->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas rechazar esta solicitud de compra?')">
                    Rechazar
                </button>
            </form>
            <a href="{{ route('purchase-requisition.index') }}" class="btn btn-primary">Volver</a>
        </div>
    </div>
</div>
@endsection