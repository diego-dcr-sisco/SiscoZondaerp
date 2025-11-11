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
                CREAR CONTRATO
            </span>
        </div>
        @include('contract.create.form')
        @include('contract.modals.configure-service')
        @include('contract.modals.preview')
        @include('contract.modals.service')
        @include('contract.modals.describe-service')
    </div>

    <script src="{{ asset('js/technician.min.js') }}"></script>
    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/service.min.js') }}"></script>
    <script src="{{ asset('js/contract/functions.min.js') }}"></script>
@endsection
