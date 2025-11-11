@extends('layouts.app')
@section('content')
    <div class="row w-100 h-100 m-0">

        <div class="col-12 p-3 m-o">
            
            <div class="row mb-3">

                <div class="col-lg-8">
                    @if (isset($customerId) && $customerId)
                        <div class="row p-1 mb-3 ">
                            <a href="{{ route('consumption.show.past') }}" class="col-auto btn-primary p-0 fs-3"><i
                                    class="bi bi-arrow-left m-3"></i></a>
                            <h1 class="col-auto fs-2 fw-bold m-0"> Consumo de {{ $customer->name }}</h1>
                        </div>
                    @else
                        <h1 class="h3 mb-0">
                            <!-- <i class="bi bi-graph-up-arrow text-primary"></i> -->
                            Gestión de Pedidos Mensuales
                        </h1>
                        <p class="text-muted">Administra los pedidos mensuales por cliente y zona</p>
                    @endif
                </div>

                <div class="col-lg-4 text-end">
                    <a href="{{ route('consumptions.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i>
                        Nueva solicitud de pedido
                    </a>
                </div>
                <div class="col-lg-4 text-end">
                    <a href="{{ route('consumptions.create-order-based-rp') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i>
                        Solicitud por plan de rotación
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            @include('stock.consumptions.filters.index')

            <!-- Resultados -->
            @isset($consumptions)
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pedidos </h5>
                    </div>

                    <div class="card-body">
                        @if (!empty($consumptions) && count($consumptions) > 0)
                            <div class="table-responsive">
                                @include('stock.consumptions.tables.index')
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No se encontraron consumos en el período seleccionado
                            </div>
                        @endif
                    </div>
                </div>
            @endisset
        </div>
    </div>


    <style>
        #customer_results {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }

        .customer-item:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
    </style>
@endsection
