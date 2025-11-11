@extends('layouts.app')
@section('content')
@php
    $offset = ($branches->currentPage() - 1) * $branches->perPage();
@endphp
    <div class="container-fluid">
        <div class="py-3">
                <a class="btn btn-primary btn-sm" href="{{ route('branch.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear sucursal
                </a>
        </div>

        @include('messages.alert')
        <div class="table-responsive">
            @include('branch.tables.index')
        </div>

    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
