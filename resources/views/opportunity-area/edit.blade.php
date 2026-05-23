@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="{{ Route('quality.opportunity-area', ['id' => $opportunity_area->customer_id]) }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0"> Editar area de oportunidad </h1>
        </div>
        <div class="row justify-content-center">
            <div class="col-11">
                @include('opportunity-area.edit.form')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/service.min.js') }}"></script>
    <script src="{{ asset('js/technician.min.js') }}"></script>
    <script src="{{ asset('js/order/functions.min.js') }}"></script>
@endsection
