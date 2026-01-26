@extends('layouts.app')

@section('content')
    <div class="container-fluid h-100 p-0">
        <div class="row h-100 m-0">
            @include('dashboard.stock.navigation')
            <div class="col-11 m-0">
                <div class="row border-bottom p-3 mb-3">
                    <a href="{{ route('stock.index') }}" class="col-auto btn-primary p-0 fs-3"><i
                            class="bi bi-arrow-left m-3"></i></a>
                    <h1 class="col-auto fs-2 fw-bold m-0"> Lista de productos por orden </h1>
                </div>
                <div class="table-responsive">
                    @include('stock.tables.technician-order')
                </div>
            </div>
        </div>
    </div>
@endsection
