@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR PLAN DE ROTACION',
        'icon' => 'bi-arrow-repeat',
        'backRoute' => url()->previous(),
    ])
@php
        $time_types = ['Segundo(s)', 'Minuto(s)', 'Hora(s)'];
    @endphp

    <div class="container-fluid">
<div class="row justify-content-center">
            <div class="col-11">
                
                @include('rotation-plan.edit.form')
            </div>
        </div>
    </div>

    <script>
        const found_months = @json($months);
        const fetched_changes = @json($changes);
    </script>

    <script src="{{ asset('js/product.min.js') }}"></script>
@endsection
