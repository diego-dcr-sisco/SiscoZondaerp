@extends('layouts.app')

@section('content')
    <style>
        .bg-yellow {
            background-color: #FFA000;
        }

        .bg-blue {
            background-color: #182A41;
        }

        .bg-yellow:hover {
            box-shadow: 0 10px 20px rgba(21, 101, 192, 0.3);
            transform: translateY(-5px);
        }

        .bg-blue:hover {
            box-shadow: 0 10px 20px rgba(21, 101, 192, 0.3);
            transform: translateY(-5px);
        }

        /* Animación para las tarjetas */
        .card-animate {
            opacity: 0;
            animation: fadeIn 0.3s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Retrasos para cada tarjeta */
        .card-animate:nth-child(1) {
            animation-delay: 0.3s;
        }

        .card-animate:nth-child(2) {
            animation-delay: 0.6s;
        }

        .card-animate:nth-child(3) {
            animation-delay: 0.9s;
        }

        .card-animate:nth-child(4) {
            animation-delay: 1.2s;
        }

        /* Efecto hover */
        .hover-scale {
            transition: transform 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }
    </style>

    <div class="container-fluid py-5">
        <!-- Encabezado con animación -->
        <div class="text-center mb-5 animate__animated animate__fadeIn">
            <h1 class="display-4 fw-bold text-dark mb-3">BIENVENIDO A ZONDA</h1>
            <p class="lead text-muted">MÓDULO DE CLIENTES</p>
        </div>

        <!-- Grid de tarjetas responsive con animaciones -->
        <div class="d-flex flex-wrap justify-content-center gap-4 mb-4">
            <!-- Carpeta -->
            <a href="{{ route('client.system.index', ['path' => $path]) }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-yellow card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-folder-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Carpetas</h3>
                        <p class="small opacity-75 mb-0">MIP</p>
                    </div>
                </div>
            </a>

            <!-- Reportes -->
            <a href="{{ route('client.reports') }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-blue card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-file-pdf-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">Reportes</h3>
                        <p class="small opacity-75 mb-0">Certificados de trabajo</p>
                    </div>
                </div>
            </a>

            <!-- MIP (comentado) -->
            {{--
            <a href="{{ route('client.mip.index', ['path' => $mip_path]) }}"
                class="card text-white text-decoration-none hover-scale position-relative bg-danger card-animate"
                style="width: 150px; height: 130px;">
                <div class="position-absolute top-50 start-50 translate-middle w-100 px-2" style="margin-top: -5px;">
                    <div class="text-center">
                        <i class="bi bi-gear-fill d-block fs-4 mb-2"></i>
                        <h3 class="h6 fw-bold mb-1">MIP</h3>
                        <p class="small opacity-75 mb-0">---</p>
                    </div>
                </div>
            </a>
            --}}
        </div>
    </div>
@endsection
