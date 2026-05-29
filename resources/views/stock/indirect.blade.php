@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif
    @include('components.page-header', [
        'title' => 'ALMACEN DE INDIRECTOS - ' . $warehouse->name,
        'icon' => 'bi-boxes',
        'backRoute' => route('stock.index', ['is_active' => 1]),
    ])

    <div class="row w-100 h-100 m-0">
        @include('dashboard.stock.navigation')
        <div class="col-11 p-3 m-0">
            <div class="row justify-content-center">
                <div class="col-11">
                    <div class="row">
                        <div class="container-fluid">
                            
                            @if ($newProducts->isEmpty())
                                <p>No hay productos nuevos para mostrar.</p>
                            @else
                                @include('stock.tables.new-indirect-products')
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="container-fluid">
                            @if ($products->isEmpty())
                                <p>No hay productos en almacén </p>
                            @else
                                @include('stock.tables.indirect-products')
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
