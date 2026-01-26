@extends('layouts.app')
@section('content')

@if (!auth()->check())
    <?php header("Location: /login"); exit; ?>
@endif

<div class="border rounded shadow m-3 p-2 bg-white">
    <h1 class="fs-2 fw-bold m-0">
        <a href="{{ route('crm', ['status' => 1, 'page' => 1 ]) }}"><i class="bi bi-arrow-left m-3"></i></a>
        @if($va == 1)
            Crea un reporte de los clientes registrados
        @else
            Crea un reporte de los clientes potenciales registrados
        @endif
        </h1>
    <form class="form m-3" method="POST" action="{{ route('reportcustomer.create', ['va' => $va]) }}" enctype="multipart/form-data">
        @csrf
        <div style="color:red;" class="form-text" id="basic-addon4">Llena los campos requeridos</div>
        <div class="row">
            <div class="col-sm">
                <label class="form-label is-required" for="fechaI">Fecha Inicio:</label>
                <input class="form-control "  type="date" name="fechaI" required>
            </div>
            <div class="col-sm">
                <label class="form-label is-required" for="fechaF">Fecha Fin:</label>
                <input class="form-control " type="date" name="fechaF" required>
            </div>
            
            <div class="col-sm" style="padding-top: 30px;">
                <button class="btn btn-primary w-100" type="submit">Exportar a Excel</button>
            </div>
        </div>
        
    </form>
</div>
@endsection