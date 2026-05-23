@extends('layouts.app')
@section('content')
@php
    $offset = ($branches->currentPage() - 1) * $branches->perPage();
@endphp
    <div class="container-fluid p-0">
        <div class="d-flex flex-wrap align-items-center justify-content-between border-bottom px-4 py-3 mb-3 bg-white">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3 fs-4 text-primary"></i>
                <span class="text-black fw-bold fs-4 mb-0">SUCURSALES</span>
            </div>
            <a class="btn btn-primary btn-sm" href="{{ route('branch.create') }}">
                <i class="bi bi-plus-lg fw-bold"></i> Crear sucursal
            </a>
        </div>

        @include('messages.alert')
        <div class="px-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="overflow-auto w-100">
                        @include('branch.tables.index')
                    </div>
                </div>
            </div>
            <div class="mt-3">
                {{ $branches->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
