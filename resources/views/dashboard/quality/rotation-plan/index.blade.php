@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row border-bottom p-3 mb-2">
            <a href="{{ route('quality.customer', ['id' => $customer->id]) }}" class="col-auto btn-primary p-0"><i
                    class="bi bi-arrow-left m-3 fs-4"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0 fw-bold">{{ $customer->name }}</h1>
        </div>

        <div class="row justify-content-between p-3 m-0">
            <div class="col-auto">
                @can('write_order')
                    @if($customer->contracts->isNotEmpty())
                        <a class="btn btn-primary btn-sm me-2" href="{{ route('quality.rotation-plan.create', ['id' => $customer->id]) }}">
                            <i class="bi bi-plus-lg fw-bold me-1"></i> Crear plan de rotación
                        </a>
                    @else
                        <button class="btn btn-primary btn-sm me-2" disabled >
                            <i class="bi bi-plus-lg fw-bold me-1"></i> Crear plan de rotación
                        </button>
                        <a href="{{ route('quality.contracts', ['id' => $customer->id]) }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-plus-lg fw-bold me-1"></i> Agregar contrato
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <div class="container-fluid">
            @include('messages.alert')
            <div class="table-responsive">
                @include('dashboard.quality.rotation-plan.tables')
            </div>
          
        </div>
    </div>
@endsection