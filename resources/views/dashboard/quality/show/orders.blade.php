@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'VER DASHBOARD - ORDENES',
        'icon' => 'bi-speedometer2',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid">
<div class="row justify-content-between p-3 m-0">
            <div class="col-auto">
                @can('write_order')
                    <a class="btn btn-primary" href="{{ route('order.create') }}">
                        <i class="bi bi-plus-lg fw-bold"></i> {{ __('order.title.create') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="container-fluid">
            @include('messages.alert')
            <div style="overflow-x: auto; width: 100%;">
                @include('order.tables.index')
            </div>
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
