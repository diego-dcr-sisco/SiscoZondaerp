<div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="inputModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" id="input-form" action="{{ route('stock.input') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="inputModalLabel">Entrada de almacen </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Almacen destino</label>
                    <input type="hidden" class="form-control" id="input-warehouse" name="warehouse_id"
                        value="" required/>
                    <input type="text" class="form-control" id="input-warehouse-text" name="warehouse_text"
                        value="" disabled/>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Almacen de origen</label>
                    <select class="form-select" id="input-destination-warehouse" name="destination_warehouse_id">
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Tipo de movimiento</label>
                    <select class="form-select" id="input-movement" name="movement_id" required>
                        @foreach ($input_movements as $input_movement)
                            <option value="{{ $input_movement->id }}">{{ $input_movement->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Fecha actual</label>
                    <input type="date" class="form-control" id="input-date" name="date" value="{{ now() }}" required/>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observations" name="observations" rows="4" placeholder="Ingrese detalles sobre el traspaso de salida, como el motivo, condiciones de los productos o instrucciones especiales."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Producto</label>
                    <select class="form-select" id="input-product" name="product_id" onchange="limitLots(this.value, 'input')" required>
                        <option value="" selected>Sin producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Lote</label>
                    <select class="form-select" id="input-lot" name="lot_id" onchange="limitAmount(this.value, 'input')" required>
                        <option value="" selected>Sin lote</option>
                        {{--@foreach ($lots as $lot)
                            <option value="{{ $lot->id }}">{{ $lot->registration_number }}</option>
                        @endforeach--}}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Cantidad</label>
                    <input type="number" class="form-control" id="input-amount" name="amount" onblur="checkMaxValue(this)" min="0" step="0.01" onblur="checkMaxValue(this)" required/>
                    <div class="form-text" id="basic-addon4">Mililitros (ml)/Unidades (uds)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __('buttons.add') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>
