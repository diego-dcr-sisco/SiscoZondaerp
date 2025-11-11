@extends('layouts.app') @section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit(); ?>
    @endif

    <style>
        .sidebar {
            color: white;
            text-decoration: none;
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .directory:hover {
            text-decoration: underline !important;
            color: #0d6efd !important;
        }
    </style>

    <div class="w-100 h-100 m-0 p-5">
        <h1 class="fw-bold text-center mb-5">
            SISTEMA DE REQUISICIONES DE COMPRA
        </h1>

        <div class="d-flex justify-content-center gap-3 w-100 text-center">
            <a href="{{ route('purchase-requisition.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color:rgb(3, 130, 170)">
                <i class="bi bi-file-earmark-text fs-2"></i>
                <p class="fw-bold">Compras</p>
            </a>
            <a href="{{ route('supplier.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color: #006064;">
                <i class="bi bi-person-fill-gear fs-2"></i>
                <p class="fw-bold">Proveedores</p>
            </a>
            <a href="{{ route('consumptions.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color:rgb(0, 41, 43);">
                <i class="bi bi-file-earmark-text-fill fs-2"></i>
                <p class="fw-bold">Consumos</p>
            </a>
            
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $(".card").hover(
                function() {
                    $(this).addClass("animate__animated animate__pulse");
                },
                function() {
                    $(this).removeClass("animate__animated animate__pulse");
                },
            );
        });
    </script>
@endsection
