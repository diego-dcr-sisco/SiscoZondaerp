@extends('layouts.app')

@section('content')
    <style>
        .bg-yellow {
            background-color: #FFA000;
        }

        .bg-blue {
            background-color: #182A41;
        }

        .bg-yellow:hover,
        .bg-blue:hover {
            filter: brightness(0.97);
        }
    </style>

    <div class="container-fluid py-5">
        <!-- Encabezado -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark mb-3">BIENVENIDO A ZONDA</h1>
            <p class="lead text-muted">MÓDULO DE CLIENTES</p>
        </div>

        <!-- Grid de tarjetas responsive -->
        <div class="d-flex flex-wrap justify-content-center gap-4 mb-4">
            <!-- Carpeta -->
            <a href="{{ route('client.system.index', ['path' => $path]) }}"
                class="card text-white text-decoration-none position-relative bg-yellow"
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
                class="card text-white text-decoration-none position-relative bg-blue"
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
                class="card text-white text-decoration-none position-relative bg-danger"
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
