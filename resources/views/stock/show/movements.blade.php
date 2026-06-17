@extends('layouts.app')

@section('content')
    @include('components.page-header', [
        'title' => 'LISTA DE MOVIMIENTOS - ' . $warehouse->name,
        'icon' => 'bi-arrow-left-right',
        'backRoute' => route('stock.index'),
    ])

    <div class="container-fluid h-100 p-0">
        <div class="row h-100 m-0">
            @include('stock.show.navigation')
            <div class="col-11 m-0">
                <div style="overflow-x: auto; width: 100%;">
                    @if ($type == 1)
                        @include('stock.tables.movements')
                    @else
                        @include('stock.tables.technician-order')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
