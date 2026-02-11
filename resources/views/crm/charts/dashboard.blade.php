@extends('layouts.app')
@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <div class="container-fluid">
        <div class="row p-3">
                <div class="col-lg-6 col-12 mb-4">
                    {{-- Clientes por mes (todo el año) --}}
                    @include('crm.charts.comercial.yearly-customers')
                </div>
                <div class="col-lg-6 col-12 mb-4">
                    {{-- Leads por mes (todo el año) --}}
                    @include('crm.charts.comercial.yearly-leads')
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Tipos de servicios realizados en el mes --}}
                    @include('crm.charts.comercial.services')
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Plagas más presentadas --}}
                    @include('crm.charts.comercial.pests-donut')
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Servicios programados (órdenes generadas) --}}
                    @include('crm.charts.comercial.services-programmed')
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Seguimientos programados por mes --}}
                    @include('crm.charts.comercial.trackings-by-month')
                </div>
                
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Servicios completados por mes --}}
                    @include('crm.charts.comercial.yearly-services-completed')
                </div>
                
        </div>
    </div>
@endsection
