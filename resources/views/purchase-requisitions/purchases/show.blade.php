@extends('layouts.app')
@section('content')
@include('components.page-header', [
        'title' => 'VER REQUISICION',
        'icon' => 'bi-cart-check',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid mb-4">
<div class="row justify-content-center pb-5">
        <div class="col-11">
            @include('purchase-requisitions.purchases.show.data')
        </div>
    </div>
</div>

@endsection