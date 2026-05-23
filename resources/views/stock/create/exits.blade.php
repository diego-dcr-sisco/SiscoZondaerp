<?php
use Carbon\Carbon;
?>

@extends('layouts.app')

@section('content')
    <div class="col-11 m-0">
        <div class="row border-bottom p-3 justify-content-start ">
            <a href="{{ route('stock.index') }}" class="col-auto btn-primary fs-3"><i
                    class="bi bi-arrow-left m-3"></i></a>
            <h1 class="col-auto fs-2 fw-bold m-0">Movimiento de almacen: Salida. </h1>
        </div>
    </div>
    <div class="container-fluid p-5">

        <div class="container">
            <form action="{{ route('stock.storeExit') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label is-required">Almacen de salida</label>
                    <input type="hidden" class="form-control" id="output-warehouse" name="warehouse_id"
                        value="{{ $warehouse->id }}" required />
                    <input type="text" class="form-control" id="output-warehouse-text" name="warehouse_text"
                        value="{{ $warehouse->name }}" disabled />
                </div>
                <div class="mb-3">
                    <label class="form-label">Almacen destino</label>
                    <select class="form-select" id="output-destination-warehouse" name="destination_warehouse_id">
                        <option value=" ">Sin almacen de destino</option>
                        @foreach ($all_warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="mb-3">
                            <label class="form-label is-required">Tipo de movimiento</label>
                            <select class="form-select" id="output-movement" name="movement_id" required>
                                @foreach ($output_movements as $output_movement)
                                    <option value="{{ $output_movement->id }}">{{ $output_movement->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="mb-3">
                            <label class="form-label is-required">Fecha actual</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="output-date" name="date"
                                    value="{{ Carbon::now() }}" required />
                                <button type="button" class="btn btn-secondary" onclick="setToday()">Hoy</button>
                                <script>
                                    function setToday() {
                                        document.getElementById('output-date').value = new Date().toISOString().split('T')[0];
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observations" name="observations" rows="4"
                        placeholder="Ingrese detalles sobre el traspaso de salida, como el motivo, condiciones de los productos o instrucciones especiales."></textarea>
                </div>

                <div class="row">
                    <div class="col-8">
                        <div class="mb-3">
                            <label class="form-label is-required">Producto</label>
                            <select class="form-select" id="product" name="product_id" onchange="refreshLots()" required>
                                <option value="" selected disabled>Seleccione un producto</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="mb-3">
                            <label class="form-label is-required">Lote</label>
                            <select class="form-select" id="lot" name="lot_id" onchange="setMaxAmount()" required>
                                {{-- Aqui se actalizan los lotes  --}}
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label is-required">Cantidad</label>
                    <input type="number" class="form-control" id="amount" name="amount" min="0" required />
                    <div class="form-text" id="basic-addon4">Mililitros (ml)/Unidades (uds). <span id="cant-max"></span>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary px-5" style="width: 90%">Registrar Salida</button>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-danger"
                        onclick="return confirm('¿Está seguro que desea cancelar?')">{{ __('buttons.cancel') }}</a>
                </div>
            </form>
        </div>

    </div>

    <script>
        function refreshLots() {
            let product = document.getElementById('product').value;
            let lots = @json($lots);
            let select = document.getElementById('lot');
            select.innerHTML = '';
            lots.forEach(lot => {
                if (lot.product_id == product) {
                    let option = document.createElement('option');
                    option.value = lot.id;
                    option.text = lot.registration_number;
                    select.appendChild(option);
                }
            });

            setMaxAmount();
        }

        function setMaxAmount() {
            let selectedLotId = document.getElementById('lot').value; // Obtener el valor seleccionado del select
            let lots = @json($lots); // Obtener el array de lotes desde el backend
            let amount = 0;

            // Iterar sobre cada lote
            lots.forEach(lot => {
                if (lot.id == selectedLotId) { // Comparar el id del lote con el valor seleccionado
                    amount = lot.amount; // Asignar el amount del lote seleccionado
                }
            });

            // Actualizar el max y placeholder del input amount
            document.getElementById('amount').max = amount;
            document.getElementById('amount').placeholder = 'Max: ' + amount;
            document.getElementById('cant-max').innerHTML = 'cantidad en stock: ' + amount;
        }
    </script>
@endsection
