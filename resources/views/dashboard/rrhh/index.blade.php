@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .flat-btn {
            background-color: #55ff00;
        }
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">

        <!-- Contenido -->
        <div class="col-12 p-3">
            <div class="row align-items-center border-bottom pb-1 mb-3">
                <a href="javascript:history.back()" class="fs-2 col-1 m-0">
                    <i class="bi bi-arrow-left fs-2"></i>
                </a>
                <h1 class="col-11 m-0">Recursos Humanos</h1>
            </div>


            <div class="row">
                <div class="row mb-3 justify-content-between">
                    <!-- Crear usuario -->
                    <div class="col-auto">
                        <a href="{{ route('user.create', ['type' => 1]) }}" class="btn btn-primary">
                            <i class="bi bi-person-plus-fill"></i> Crear nuevo usuario
                        </a>
                    </div>
                    
                    <!-- Barra de Busqueda -->
                    <div class="col-4 d-flex">
                        <form class="input-group d-flex" action="{{ route('rrhh', ['section' => $section]) }}"
                            method="get">
                            @csrf
                            <input type="search" class="form-control rounded-0 rounded-start-2" id="search"
                                name="search" placeholder="Buscar por nombre" autocomplete="off">
                            <button type="submit" class="btn btn-success rounded-0 rounded-end-2" data-bs-toggle="tooltip"
                                data-bs-placement="bottom" data-bs-title=" {{ __('customer.tips.search') }}">
                                {{ __('buttons.search') }} </button>
                        </form>
                    </div>
                </div>
                

                <!-- Tabla de usuarios pendientes -->
                @if ($section == 1)
                    @include('dashboard.rrhh.tables.waiting')
                @else
                    @include('dashboard.rrhh.tables.files')
                @endif
            </div>
        </div>
    </div>
@endsection
