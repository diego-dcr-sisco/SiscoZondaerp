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

<div class="row w-100 justify-content-between m-0 h-100">
    <div class="col-12">
        <div class="row p-3 border-bottom">
            <a href="{{ route('lot.index') }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0">Editar lote {{$lot->id}}: [{{$lot->registration_number}}]</h1>
        </div>
        
        <div class="row p-4 m-0">
            @include('lot.edit.form')
        </div>
    </div>
</div>

@endsection
