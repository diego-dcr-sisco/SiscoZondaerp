@extends('layouts.app')
@section('content')
    <!-- <ul class="nav nav-underline p-3">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('quality.customers') }}">Clientes</a>
        </li>
        @role('SupervisorCalidad|AdministradorDireccion')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('quality.tracing') }}">Relaciones</a>
            </li>
        @endrole
    </ul> -->
    <div class="container-fluid p-3">
        <div class="row border-bottom mb-3">
            <h4 class="">Clientes Industriales/Plantas</h4>
        </div>
        <div class="table-responsive">
            @include('dashboard.quality.tables.customers')
        </div>
        @include('messages.alert')
    </div>
@endsection
