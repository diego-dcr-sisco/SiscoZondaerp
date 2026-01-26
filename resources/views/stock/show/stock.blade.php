@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="{{ route('stock.index', ['is_active' => 1]) }}"
                class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0"> Lista de movimientos [ {{ $warehouse->name }} ] </h1>
        </div>
        <div class="row justify-content-center">
            <div class="table-responsive">
                <table class="table text-center table-bordered table-striped">
                    <thead>
                        <tr>
                            <td>#</td>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Unidades</th>
                            <th>Lote</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td>{{ $stock->id }}</td>
                                <td>{{ $stock->product->name }}</td>
                                <td>{{ $stock->amount }}</td>
                                <td>{{ $stock->product->metric }}</td>
                                <td>{{ $stock->registration_number }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">No hay stock disponible en este almacén.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
