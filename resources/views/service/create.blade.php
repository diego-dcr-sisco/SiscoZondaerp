@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('service.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR SERVICIO
            </span>
        </div>
        @include('service.create.form')
    </div>
    <script src="{{ asset('js/service/functions.min.js') }}"></script>
@endsection
