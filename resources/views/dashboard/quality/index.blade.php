@extends('layouts.app')
@section('content')
    <div class="container-fluid p-3">
        <ul class="nav nav-underline mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $section == 1 ? 'active' : '' }}" href="{{ route('quality.customers') }}">Clientes</a>
            </li>
            @role('SupervisorCalidad|AdministradorDireccion')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('quality.permissions') }}">Relaciones</a>
                </li>
            @endrole
        </ul>

        @include('messages.alert')
        <div class="table-responsive">
            @include('dashboard.quality.tables.customers')
        </div>
    </div>
@endsection
