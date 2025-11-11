<div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="inputModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" action="{{ route('warehouse.input') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="inputModalLabel">Movimiento de almacen: Entrada</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label is-required">Almacen de origen</label>
                    <input type="hidden" class="form-control" id="input-warehouse" name="warehouse_id"
                        value="" required/>
                    <input type="text" class="form-control" id="input-warehouse-text" name="warehouse_text"
                        value="" disabled/>
                </div>
                <div class="mb-3">
                    <label class="form-label">Almacen destino</label>
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
                    <input type="date" class="form-control" id="input-date" name="date" required/>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Observaciones</label>
                    <textarea class="form-control" id="observations" name="observations" rows="4" required> </textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Producto</label>
                    <select class="form-select" id="product" name="product_id" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Lote</label>
                    <select class="form-select" id="lot" name="lot_id" required>
                        @foreach ($lots as $lot)
                            <option value="{{ $lot->id }}">{{ $lot->registration_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Cantidad</label>
                    <input type="number" class="form-control" id="amount" name="amount" min="0"
                        value="0" required/>
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
