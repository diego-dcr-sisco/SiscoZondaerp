@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="{{ route('warehouse.index', ['is_active' =>  $warehouse->active ? 1 : 0]) }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0"> Lista de movimientos [ {{ $warehouse->name }} ] </h1>
        </div>
        <div class="row justify-content-center">
            <div class="table-responsive">
                <table class="table text-center table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Movimiento</th>
                            <th>Tipo de Movimiento</th>
                            <th>Almacén Origen</th>
                            <th>Almacén Destino</th>
                            <th>Producto</th>
                            <th>Creado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouse->movements as $movement)
                            <tr>
                                <td>{{ $movement->date }}</td>
                                <td>{{ $movement->time }}</td>
                                <td class="fw-bold {{ $movement->movement_id <= 4 && $movement->movement_id >= 1 ? 'text-success' : 'text-danger'}}">{{ $movement->movement_id <= 4 && $movement->movement_id >= 1 ? 'Entrada' : 'Salida'}}</td>
                                <td>{{ $movement->movementType ? $movement->movementType->name : 'N/A' }}</td>
                                <td>{{ $warehouse->name ?? 'N/A' }}</td>
                                <td>{{ $movement->destinationWarehouse ? $movement->destinationWarehouse->name : 'N/A' }}</td>
                                <td>
                                    {{ $movement->product->name }} {{ $movement->amount }} {{ $movement->product->metric->value}}
                                </td>
                                <td>{{ $movement->user ? $movement->user->name : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('warehouse.pdf', ['id' => $movement->id]) }}" class="btn btn-dark btn-sm"><i class="bi bi-file-pdf-fill"></i> Imprimir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-danger" colspan="8">No hay movimientos en este almacén.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
