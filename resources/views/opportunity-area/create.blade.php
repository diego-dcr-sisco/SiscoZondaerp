@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR AREA DE OPORTUNIDAD',
        'icon' => 'bi-lightbulb',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid">
<div class="row justify-content-center">
            <div class="col-11">
                @include('opportunity-area.create.form')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/customer.min.js') }}"></script>
    <script src="{{ asset('js/service.min.js') }}"></script>
    <script src="{{ asset('js/technician.min.js') }}"></script>
    <script src="{{ asset('js/order/functions.min.js') }}"></script>
@endsection
