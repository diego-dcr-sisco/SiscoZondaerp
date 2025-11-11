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

<div class="container-fluid">
    <div class="row border-bottom p-3 mb-3">
        <a href="{{ route('warehouse.index', ['is_active' =>  $warehouse->active ? 1 : 0]) }}" class="col-auto btn-primary p-0 fs-3"><i
                class="bi bi-arrow-left m-3"></i></a>
        <h1 class="col-auto fs-2 fw-bold m-0"> Ver almacen: {{ $warehouse->name }} </h1>
    </div>
    <div class="row justify-content-center">
        <div class="col-11">
            <div class="row">
                <span class="col fw-bold">ID</span>
                <span class="col fw-normal">{{ $warehouse->id }}</span>
            </div>
            <div class="row">
                <span class="col fw-bold">¿Está activo?</span>
                <span class="col fw-bold {{ $warehouse->active ? 'text-success' : 'text-danger' }}">
                    {{ $warehouse->active ? 'Sí' : 'No' }}
                </span>
            </div>            

            <div class="row mb-3">
                <span class="col fw-bold">Permitir recepciones de material en este almacén? </span>
                <span class="col fw-bold {{ $warehouse->receive_material == 1 ? 'text-success' : 'text-danger' }}">
                    {{ $warehouse->receive_material == 1 ? 'Si' : 'No' }}
                </span>
            </div>
            
            <div class="row">
                <span class="col fw-bold">Telefono:</span>
                <span class="col fw-normal">{{ $warehouse->phone }}</span>
            </div>
            
            <div class="row">
                <span class="col fw-bold">Delegacion:</span>
                <span class="col fw-normal">{{ $warehouse->branch->name }}</span>
            </div>
            <div class="row">
                <span class="col fw-bold">Direccion:</span>
                <span class="col fw-normal">{{ $warehouse->address }}</span>
            </div>
            <div class="row">
                <span class="col fw-bold">Código postal:</span>
                <span class="col fw-normal">{{ $warehouse->zip_code }}</span>
            </div>
            <div class="row mb-3">
                <span class="col fw-bold">Ciudad/Estado:</span>
                <span class="col fw-normal">{{ $warehouse->city }}, {{ $warehouse->state }}</span>
            </div>
                        

            <div class="row">
                <span class="col fw-bold">Observaciones:</span>
                <span class="col fw-normal">{{ $warehouse->observations }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
