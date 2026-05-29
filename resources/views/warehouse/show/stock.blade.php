@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'VER ALMACEN - STOCK',
        'icon' => 'bi-building',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid">
<div class="row justify-content-center">
            <div class="overflow-auto w-100">
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
