@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-2">
            <a href="{{ route('quality.customer', ['id' => $customer->id]) }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0 fw-bold">{{ $customer->name }}</h1>
        </div>


            <div class="mb-3">
                @can('write_customer')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#areaCreateModal"><i class="bi bi-plus-lg fw-bold"></i> Crear área de aplicación</button>
                @endcan
            </div>
        
        @include('messages.alert')
        <div class="table-responsive">
            @include('dashboard.quality.zone.table')
        </div>
        {{ $zones->links('pagination::bootstrap-5') }}
    </div>
    @include('customer.modals.area.create')
    @include('customer.modals.area.edit')
@endsection
