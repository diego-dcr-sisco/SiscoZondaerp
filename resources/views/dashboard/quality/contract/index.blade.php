@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="{{ route('quality.customer', ['id' => $customer->id]) }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0 fw-bold">{{ $customer->name }}</h1>
        </div>
        <div class="mb-3">
                @can('write_order')
                    <a class="btn btn-primary btn-sm" href="{{ route('contract.create') }}">
                        <i class="bi bi-plus-lg fw-bold"></i> {{ __('contract.title.create') }}
                    </a>
                @endcan
        </div>
        <div class="row">
            @include('messages.alert')
            <div class="table-responsive">
                @include('dashboard.quality.contract.table')
            </div>
            {{ $contracts->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- @include('contract.modals.renew') --}}
@endsection
