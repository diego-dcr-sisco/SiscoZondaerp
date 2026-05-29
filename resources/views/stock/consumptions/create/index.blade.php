@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'REGISTRAR SOLICITUD DE CONSUMO',
        'icon' => 'bi-clipboard-plus',
        'backRoute' => route('consumptions.index'),
    ])

    <div class="row w-100 h-100 m-0">

        <div class="col-12 p-3 m-0">
            
            <div class="row">

                <div class="row mb-3 p-3">
                    @include('stock.consumptions.create.form')
                </div>

            </div>

        </div>
    </div>
@endsection
