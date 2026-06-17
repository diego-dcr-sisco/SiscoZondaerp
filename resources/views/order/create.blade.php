@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR ORDEN DE SERVICIO',
        'icon' => 'bi-clipboard-check',
        'backRoute' => url()->previous(),
    ])
    <div class="container-fluid p-0">
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
