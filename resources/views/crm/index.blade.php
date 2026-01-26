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
    </style>

    {{-- <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a href="#" class="sidebar col-12 p-2 text-center"> Inicio
                </a>
                <a class="sidebar col-12 p-2 text-center" data-bs-toggle="collapse" href="#collapseExample" role="button"
                    aria-expanded="false" aria-controls="collapseExample">
                    Seguimiento de clientes
                </a>
                <div class="collapse" id="collapseExample" style="background-color: #495057;">
                    <div class="row">
                        <a href="{{ route('crm', ['section' => 2]) }}" class="sidebar col-12 p-2 text-center">
                            Agendados
                        </a>
                        <a href="{{ route('crm', ['section' => 3]) }}" class="sidebar col-12 p-2 text-center">
                            Potenciales
                        </a>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-11 p-3">

            @if ($section == 1)
                <div class="row mb-3">
                    @include('crm.chart')
                </div>
            @endif
            @if ($section == 2)
                @include('crm.tables.tracking')
            @endif

            @if ($section == 3)
                @include('crm.tables.leads')
            @endif
            
        </div>
    </div> --}}

    <div class="w-100 h-100 m-0 p-5">
        <h1 class="fw-bold text-center mb-5"> GESTIÓN DE RELACIÓN CON CLIENTES (CRM) </h1>
        <div class="d-flex justify-content-center gap-3 w-100 text-center">
            <a href="{{ route('customer.index', ['type' => 1]) }}"
                class="d-flex flex-column align-items-center justify-content-center card shadow text-center text-white text-decoration-none p-2 m-2"
                style="width: 8em; height: 8em; background-color: #2e7d32;">
                <i class="bi bi-people-fill fs-2"></i>
                <p class="card-text fw-bold">Clientes</p>
            </a>

            <a href="{{ route('crm.agenda') }}"
                class="d-flex flex-column align-items-center justify-content-center card shadow text-center text-white text-decoration-none p-2 m-2"
                style="width: 8em; height: 8em; background-color: #ff9800;">
                <i class="bi bi-calendar-fill fs-2"></i>
                <p class="card-text fw-bold">Agenda</p>
            </a>

            <a href="{{ route('crm.chart.dashboard') }}"
                class="d-flex flex-column align-items-center justify-content-center card shadow text-center text-white text-decoration-none p-2 m-2"
                style="width: 8em; height: 8em; background-color: #1a237e;">
                <i class="bi bi-bar-chart-fill fs-2"></i>
                <p class="card-text fw-bold">Analiticas</p>
            </a>
            <a href="{{ route('crm.tracking') }}"
                class="d-flex flex-column align-items-center justify-content-center card shadow text-center text-white text-decoration-none p-2 m-2"
                style="width: 8em; height: 8em; background-color:#d32f2f;">
                <i class="bi bi-person-fill-exclamation fs-2"></i>
                <p class="fw-bold">Seguimientos</p>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $(".card").hover(function() {
                $(this).addClass("animate__animated animate__pulse");
            }, function() {
                $(this).removeClass("animate__animated animate__pulse");
            });
        });
    </script>
@endsection