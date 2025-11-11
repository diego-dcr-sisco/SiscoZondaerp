@extends('layouts.app')
@section('content')
    <div class="row w-100 h-100 m-0">

        <div class="col-12 p-3 m-o">
            

            <!-- Filtros -->
            @include('stock.consumptions.filters.pastConsumption')

            <!-- Resultados -->
             @isset($consumptions)
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Consumo por cliente </h5>
                    </div>

                    <div class="card-body">
                        @if (!empty($consumptions) && count($consumptions) > 0)
                            <div class="table-responsive">
                                @include('stock.consumptions.tables.consumption')
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