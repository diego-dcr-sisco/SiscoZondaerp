@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR PLAN DE ROTACION',
        'icon' => 'bi-arrow-repeat',
        'backRoute' => url()->previous(),
    ])
@php
        $time_types = ['Segundo(s)', 'Minuto(s)', 'Hora(s)'];
    @endphp

    <div class="container-fluid">
<div class="row justify-content-center">
            <div class="col-11">
                @include('messages.alert')
                @include('rotation-plan.create.form')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/product.min.js') }}"></script>
    <script>
        const found_months = @json($months);
    </script>
@endsection
