@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR ALMACEN',
        'icon' => 'bi-building',
        'backRoute' => url()->previous(),
    ])
@if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="container-fluid">
<div class="row justify-content-center">
            <div class="col-11">
                @include('warehouse.edit.form')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/user/actions.min.js') }}"></script>
    <script src="{{ asset('js/directory.min.js') }}"></script>
    <script src="{{ asset('js/customer.min.js') }}"></script>
@endsection
