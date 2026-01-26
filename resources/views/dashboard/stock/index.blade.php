@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <div class="w-100 h-100 m-0 p-5">
        <h1 class="fw-bold text-center mb-5"> Almacen </h1>
        <div class="d-flex justify-content-center gap-3 w-100 text-center">
            <a href="{{ route('stock.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color: #1f1f20;">
                <i class="bi bi-house-gear fs-2"></i>
                <p class="fw-bold">Almacenes</p>
            </a>

            <a href="{{ route('lot.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color:#2b4c7e;">
                <i class="bi bi-boxes fs-2"></i>
                <p class="fw-bold">Lotes</p>
            </a>

            <a href="{{ route('consumptions.pre-index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color: #567ebb">
                <i class="bi bi-clipboard-data fs-2"></i>
                <p class="fw-bold">Consumos</p>
            </a>

            <a href="{{ route('stock.movements.all') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color: #606d80">
                <i class="bi bi-arrow-left-right fs-2"></i>
                <p class="fw-bold">Movimientos</p>
            </a>

            <a href="{{ route('stock.analytics') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-dark text-decoration-none card"
                style="width: 8em; height: 8em; background-color: #dce0e6">
                <i class="bi bi-graph-up fs-2"></i>
                <p class="fw-bold">Estadisticas</p>
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
