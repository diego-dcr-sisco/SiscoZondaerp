@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-3">
            <a href="{{ route('contract.index') }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0">Contrato [{{ $contract->id }}] <span class="fw-bold">{{ $contract->customer->name }}</span> </h1>
        </div>

        <div class="row justify-content-between">
            <div class="col-auto mb-3">
                @can('write_service')
                    <a class="btn btn-primary" href="{{ route('rotation.create', ['contractId' => $contract->id]) }}">
                        <i class="bi bi-plus-lg fw-bold"></i> Crear plan de rotación
                    </a>
                @endcan
            </div>

            {{-- <div class="col-4 mb-3">
                <div type="browser" class="row mb-3">
                    @include('service.browser')
                </div>
            </div> --}}
        </div>
        @include('messages.alert')
        <div class="table-responsive">
            @include('rotation-plan.tables.index')
        </div>
        {{ $rotation_plans->links('pagination::bootstrap-5') }}
    </div>
@endsection
