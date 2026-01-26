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
                <div class="col-12 mb-4">
                    {{-- Total de Clientes anuales --}}
                    @include('crm.charts.comercial.total-customers')
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Leads captados en el mes --}}
                    @include('crm.charts.comercial.leads')
                </div>
                <div class="col-lg-6 col-12 mb-3">
                    {{-- Tipos de servicios realizados en el mes --}}
                    @include('crm.charts.comercial.services')
                </div>

            {{--<div class="p-3 mt-4 border-top" id="graficas-calidad">
                <h2>Analíticas de calidad</h2>
                <div class="row m-3">
                    @include('crm.charts.quality.order-services')
                </div>
            </div>--}}
        </div>
    </div>
@endsection
