@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="{{ route('purchase-requisition.index') }}" class="col-auto btn-primary p-0"><i
                class="bi bi-arrow-left fs-4"></i></a>
        <h1 class="col-auto fs-2 fw-bold m-0">Crear requisición de compra</h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-11">
                {{-- @include('purchase-requisitions.purchases.create.form') --}}
                @include('purchase-requisitions.purchases.create.form_2')
            </div>
        </div>
    </div>  
@endsection
