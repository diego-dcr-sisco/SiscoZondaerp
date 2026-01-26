@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <!-- <a href="{{ route('order.index') }}" class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a> -->
            <a href="#" onclick="window.history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR ORDEN DE SERVICIO
            </span>
        </div>
        @include('order.create.form')
        @include('order.modals.service')
        @include('order.modals.configure-service')
    </div>
    
    <script>
        let services_configuration = [];
        let contract_configurations = [];
        const contain_selected_services = [];
        const view = @json($view);
    </script>

    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/service.min.js') }}"></script>
    <script src="{{ asset('js/technician.min.js') }}"></script>
    <script src="{{ asset('js/order/functions.min.js') }}"></script>

@endsection
