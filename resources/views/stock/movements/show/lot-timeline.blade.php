@extends('layouts.app')

@section('content')
    <div class="row w-100 h-100 m-0">

        <div class="col-12 p-3">
            <div class="row border-bottom p-3 mb-3">
                <a href="{{ route('stock.movements.all') }}" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 fw-bold m-0"> Registro de movimientos</h1> 
            </div>
            
            {{-- Datos del Movimiento --}}
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-light text-start align-middle">
                    <h5 class="mb-0">Datos del producto</h5>
                </div>
                <div class="card-body fs-6">
                    <div class="row text-center align-middle my-1 text-primary">
                        <span style="font-size: large; font-weight:600">
                            {{ $lot->product->name }} [ {{$lot->registration_number}} ]
                        </span>
                    </div>
                    <div class="row">
                        <div class="col">
                            <strong>Almacén de origen:</strong>
                            <br>
                            <p>{{ $lot->warehouse->name }}</p>
                        </div>
                        <div class="col">
                            <strong>Cantidad en almacén:</strong>
                            <br>
                            <p>{{ $lot->amount }}</p>
                        </div>
                        <div class="col">
                            <strong>Fecha de expiración:</strong>
                            <br>
                            <p>{{ $lot->expiration_date ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Movimientos --}}
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-light text-start align-middle">
                    <h5 class="mb-0">Movimientos</h5>
                </div>
                <div class="card-body fs-6">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Ver</th>
                                    <th>Almacén Origen</th>
                                    <th></th>
                                    <th>Almacén Destino</th>
                                    <th>Fecha</th>
                                    <th>Cant. Movimiento</th>
                                    <th>Cant. Previa</th>
                                    <th>Cant. Restante</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $movement)
                                    @foreach($movement->products as $product)
                                        @if($product->product->lot->id == $lot->id)
                                            <tr class="text-center align-middle">
                                                <td>{{ $movement->id }}</td>
                                                <td>
                                                    <a href="{{ route('stock.movement', ['id' => $movement->id, 1]) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-eye-fill"></i> 
                                                    </a>
                                                </td>
                                                <td>{{ $warehouses->find($movement->warehouse_id)->name ?? 'sin almacen de origen' }}</td>
                                                <td> <i class="bi bi-arrow-right m-3"></i> </td>
                                                <td>{{ $warehouses->find($movement->destination_warehouse_id)->name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($movement->date)->format('d-m-Y') }}</td>
                                                <td class="text-center" >{{ $product->amount }}  </td>
                                                <td class="text-center" >{{ $product->previous_amount ?? '' }} </td>
                                                <td class="text-center" >{{ $product->result_amount ?? '' }} </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $movements->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection