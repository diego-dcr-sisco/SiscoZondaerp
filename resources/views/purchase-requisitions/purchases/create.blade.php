@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CREAR REQUISICION',
        'icon' => 'bi-cart-check',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid">
<div class="row justify-content-center">
            <div class="col-11">
                {{-- @include('purchase-requisitions.purchases.create.form') --}}
                @include('purchase-requisitions.purchases.create.form_2')
            </div>
        </div>
    </div>  
@endsection
