@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif
    <div class="row p-3 border-bottom">
        <a href="{{ route('rrhh', ['page' => 1]) }}" class="col-auto btn-primary p-0 fs-3"><i
                class="bi bi-arrow-left m-3"></i></a>
        @if ($va == 1)
            <h1 class="col-auto fs-2 fw-bold m-0">Por completar datos</h1>
        @else
            <h1 class="col-auto fs-2 fw-bold m-0">Por subir archivos</h1>
        @endif
        <div class="row m-0 p-3">
            @include('user.tables.index')
        </div>
    </div>
@endsection
