@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif
    <div class="row w-100 h-100 m-0">
        @include('dashboard.stock.navigation')
        <div class="col-11 p-3 m-0">
            <div class="row border-bottom p-3 mb-3">
                <a href="{{ route('stock.index', ['is_active' => 1]) }}" class="col-auto btn-primary p-0"><i
                        class="bi bi-arrow-left m-3 fs-4"></i></a>
                <h1 class="col-auto fs-2 fw-bold m-0"> Almacén de indirectos [ {{ $warehouse->name }} ] </h1>
            </div>
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
