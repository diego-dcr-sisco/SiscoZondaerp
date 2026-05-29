@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'REGISTRAR SOLICITUD POR PLAN DE ROTACION',
        'icon' => 'bi-clipboard-plus',
        'backRoute' => route('consumptions.index'),
    ])

    <div class="row w-100 h-100 m-0">
        @include('dashboard.stock.navigation')

        <div class="col-11 p-3 m-0">
            
            <div class="row">

                <div class="row mb-3 p-3">
                    @include('stock.consumptions.create.form-order-based-rp')
                </div>

            </div>

        </div>
    </div>
@endsection
