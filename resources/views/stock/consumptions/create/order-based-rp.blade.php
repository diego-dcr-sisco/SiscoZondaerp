@extends('layouts.app')
@section('content')
    <div class="row w-100 h-100 m-0">
        @include('dashboard.stock.navigation')

        <div class="col-11 p-3 m-0">
            
            <div class="row">

                <div class="row mb-3">
                    <a href="{{ route('consumptions.index') }}" class="col-auto btn-primary p-0 fs-3"><i
                            class="bi bi-arrow-left m-3"></i></a>
                    <h2 class="col-auto m-0"> Registrar solicitud de consumo en base a plan de rotación </h2>
                </div>

                <div class="row mb-3 p-3">
                    @include('stock.consumptions.create.form-order-based-rp')
                </div>

            </div>

        </div>
    </div>
@endsection
