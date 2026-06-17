@extends('layouts.app')

@section('content')
    @include('components.page-header', [
        'title' => 'LISTA DE PRODUCTOS POR ORDEN',
        'icon' => 'bi-list-check',
        'backRoute' => route('stock.index'),
    ])

    <div class="container-fluid h-100 p-0">
        <div class="row h-100 m-0">
            @include('dashboard.stock.navigation')
            <div class="col-11 m-0">
                <div style="overflow-x: auto; width: 100%;">
                    @include('stock.tables.technician-order')
                </div>
            </div>
        </div>
    </div>
@endsection
