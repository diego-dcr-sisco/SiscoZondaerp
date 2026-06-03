@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR PRODUCTO - TRATAMIENTOS',
        'icon' => 'bi-box-seam',
        'backRoute' => url()->previous(),
    ])
    @include('product.edit.navigation-tabs')
<span class="m-3"> En desarollo </span>
@endsection
