@extends('layouts.app')
@section('content')
    <!-- <ul class="nav nav-underline p-3">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('quality.customers') }}">Clientes</a>
        </li>
        @role('SupervisorCalidad|AdministradorDireccion')
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('quality.tracing') }}">Relaciones</a>
            </li>
        @endrole
    </ul> -->
    @php
        $page = $control_customers->currentPage();
        $index = $size * ($page - 1) + 1;
    @endphp
    <div class="container-fluid p-3">
        <div class="row border-bottom mb-3">
            <h4 class="">Relación calidad-cliente</h4>
        </div>
        <div class="mb-3">
            
            @can('write_customer')
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#permissionModal">
                    <i class="bi bi-plus-lg fw-bold"></i> Nueva relación
                </button>
            @endcan
        </div>

        @include('messages.alert')
        <div class="table-responsive">
            @include('dashboard.quality.tables.tracing')
        </div>
        {{ $control_customers->links('pagination::bootstrap-5') }}
    </div>

    @include('dashboard.quality.modals.tracing')
    @include('dashboard.quality.modals.performance')
@endsection
