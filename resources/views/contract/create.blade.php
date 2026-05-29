@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR CONTRATO',
        'icon' => 'bi-file-earmark-text',
        'backRoute' => url()->previous(),
    ])
@if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="container-fluid p-0">
@include('contract.create.form')
        @include('contract.modals.configure-service')
        @include('contract.modals.preview')
        @include('contract.modals.service')
        @include('contract.modals.describe-service')
    </div>

    <script>
        let contract_configurations = [];
        let configurations = [];
        let updated_services = [];
        let configCounter = 0;
        let configDates = {};
        let configDescriptions = {};
        let intervals = @json($intervals);
        let frequencies = @json($frequencies);
        const can_renew = false;
        const prefixes = @json($prefixes);
        const contain_selected_services = [];
        const view = @json($view);

        // Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>   

    <script src="{{ asset('js/technician.min.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/customer.min.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/service.min.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contract/functions.min.js') }}?v={{ time() }}"></script>
@endsection
