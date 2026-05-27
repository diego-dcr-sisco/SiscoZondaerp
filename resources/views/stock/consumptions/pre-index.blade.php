@extends('layouts.app') @section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit(); ?>
    @endif

    @include('components.page-header', [
        'title' => 'CONSUMOS',
        'icon' => 'bi-file-earmark-text',
    ])

    <div class="w-100 h-100 m-0 p-5">
        <div class="d-flex justify-content-center gap-3 w-100 text-center">
            <a href="{{ route('consumptions.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color:rgb(3, 130, 170)">
                <i class="bi bi-file-earmark-text fs-2"></i>
                <p class="fw-bold">Historico de consumos</p>
            </a>    
            <a href="{{ route('consumptions.index') }}"
                class="d-flex justify-content-center align-items-center border rounded shadow text-white text-decoration-none card"
                style="width: 8em; height: 8em; background-color: #006064;">
                <i class="bi bi-person-fill-gear fs-2"></i>
                <p class="fw-bold">Nuevos pedidos</p>
            </a>
            
        </div>
    </div>
@endsection
