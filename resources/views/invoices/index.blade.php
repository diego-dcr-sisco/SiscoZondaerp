@extends('layouts.app')
@section('content')

    <div class="row m-0">

        <!-- Contenido principal -->
        <div class="col-12 p-0" style="background-color: #FFF;">
            <div class="d-flex align-items-center border-bottom ps-4 p-2">
                <span class="text-black fw-bold fs-4">
                    FACTURAS 
                </span>
            </div>
        </div>

        <div class="col-12 p-3">
            <a href="{{ route('invoices.create') }}" class="button btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Crear Factura
            </a>
        </div>
        
        <!-- Tabla de facturas -->
        <div class="col-12 p-0">
            @include('invoices.tables.index')
        </div>
        
    </div>
@endsection

