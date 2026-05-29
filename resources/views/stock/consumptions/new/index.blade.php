@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => isset($customerId) && $customerId ? 'CONSUMO DE ' . $customer->name : 'GESTION DE PEDIDOS MENSUALES',
        'icon' => 'bi-clipboard-data',
        'backRoute' => isset($customerId) && $customerId ? route('consumption.show.past') : null,
        'actionRoute' => route('consumptions.create'),
        'actionText' => 'Nueva solicitud',
        'actionIcon' => 'bi-plus-lg',
    ])
    <div class="row w-100 h-100 m-0">

        <div class="col-12 p-3 m-o">
            
            <div class="row mb-3">
                <div class="col-lg-4 text-end ms-auto">
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
                            <div style="overflow-x: auto; width: 100%;">
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
