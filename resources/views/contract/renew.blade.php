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
                RENOVAR CONTRATO <span class="ms-2 fs-4"> {{ $contract->customer->name }} [{{ $contract->id }}]</span>
            </span>
        </div>
        @include('contract.renew.form')
        @include('contract.modals.configure-service')
        @include('contract.modals.preview')
        @include('contract.modals.service')
        @include('contract.modals.describe-service')
    </div>

    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>

    <script src="{{ asset('js/technician.min.js') }}"></script>
    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/service.min.js') }}"></script>
    <script src="{{ asset('js/contract/functions.min.js') }}"></script>

    <script>
        let contract_configurations = @json($configurations);
        let configurations = [];
        let configCounter = 0;
        let configDates = {};
        let configDescriptions = {};
        let intervals = @json($intervals);
        let frequencies = @json($frequencies);
        let can_renew = true;

        selected_services = @json($selected_services);
        displaySelectedServices();

    </script>
@endsection
