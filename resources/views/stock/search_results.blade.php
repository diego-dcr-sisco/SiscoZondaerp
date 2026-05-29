@extends('layouts.app')

@section('content')
@include('components.page-header', [
    'title' => 'RESULTADOS DE LA BUSQUEDA',
    'icon' => 'bi-search',
    'backRoute' => route('movements.search_view'),
])
<div>
    <div class="col-11">
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
