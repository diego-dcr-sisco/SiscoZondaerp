@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    @include('components.page-header', [
        'title' => 'EDITAR ALMACEN ' . $warehouse->name,
        'icon' => 'bi-building-gear',
        'backRoute' => route('stock.index', ['is_active' => 1]),
    ])

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-11">
                @include('stock.edit.form')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/user/actions.min.js') }}"></script>
    <!-- <script src="{{ asset('js/directory.min.js') }}"></script> -->
    <script src="{{ asset('js/customer.min.js') }}"></script>
@endsection
