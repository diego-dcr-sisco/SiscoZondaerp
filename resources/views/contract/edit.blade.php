@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR CONTRATO <span class="ms-2 fs-4"> {{ $contract->customer->name }} [{{ $contract->id }}]</span>
            </span>
        </div>
        @include('contract.edit.form')
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
