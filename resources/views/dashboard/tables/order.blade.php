@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif
    <div class="row p-3 border-bottom">
        <a href="{{ route('crm', ['status'=> 1,'page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i class="bi bi-arrow-left m-3"></i></a>
        @if($va == 1)
            <h1 class="col-auto fs-2 fw-bold m-0">Ordenes completadas</h1>
        @else
            <h1 class="col-auto fs-2 fw-bold m-0">Ordenes canceladas</h1>
        @endif
        <div class="col" style="text-align: right;">
            <a class="btn btn-info" href="{{ route('reportServs.index',[ 'va' => $va ]) }}">
                <i class="bi bi-file-earmark-bar-graph-fill"></i> Exportar
            </a>
        </div>
    </div>
    <div class="row m-0 p-3">
            @include('order.tables.index')
    </div>
   
@endsection