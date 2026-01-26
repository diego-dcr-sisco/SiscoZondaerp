@extends('layouts.app')

@section('content')
<div>
    <div class="col-11">
        <div class="row p-3 border-bottom">
            <a href="{{ route('movements.search_view') }}" class="col-auto btn-primary p-0 fs-3">
            <i class="bi bi-arrow-left m-3"></i>
            </a>
            <h1  class="col-auto fs-2 fw-bold m-0">Resultados de la Búsqueda</h1>
        </div>
    </div>

    
    
    @if($movements->isEmpty())
        <p class="text-center">No se encontraron movimientos para los filtros seleccionados.</p>
    @else
        <table class="table text-center table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo de Movimiento</th>
                    <th>Almacén Origen</th>
                    <th>Almacén Destino</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->id }}</td>
                    <td>{{ $movement->date }}</td>
                    <td>{{ $movement->time }}</td>
                    <td>{{ $movement->movement_type_id }}</td>
                    <td>{{ optional($movement->sourceWarehouse)->name }}</td>
                    <td>{{ optional($movement->destinationWarehouse)->name }}</td>
                    <td>{{ optional($movement->user)->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
