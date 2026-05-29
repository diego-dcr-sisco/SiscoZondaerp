@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR CONTRATO',
        'icon' => 'bi-file-earmark-text',
        'backRoute' => url()->previous(),
    ])
@if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="container-fluid p-0">
@include('contract.edit.form')
        @can('write_order')
            @include('components.danger-action', [
                'actionRoute' => route('contract.destroy', ['id' => $contract->id]),
                'title' => 'Zona de peligro',
                'description' => 'Elimina este contrato desde su pantalla de edición.',
                'buttonText' => 'Eliminar contrato',
            ])
        @endcan
        @include('contract.modals.configure-service')
        @include('contract.modals.preview')
        @include('contract.modals.service')
        @include('contract.modals.describe-service')
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>

    <script>
        let contract_configurations = @json($configurations);
        let configurations = [];
        let updated_services = [];
        let configCounter = 0;
        let configDates = {};
        let configDescriptions = {};
        let intervals = @json($intervals);
        let frequencies = @json($frequencies);
        const can_renew = false;
        const prefixes = @json($prefixes);
        const contain_selected_services = @json($selected_services);
        const view = @json($view);
    </script>

    <script src="{{ asset('js/technician.min.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/customer.min.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/service.min.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contract/functions.min.js') }}?v={{ time() }}"></script>
@endsection
