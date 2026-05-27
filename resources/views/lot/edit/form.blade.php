<form method="POST" class="form" action="{{ route('lot.update', $lot->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-4 mb-3">
            <label class="form-label is-required" for="product">Producto</label>
            <select name="product_id" id="product" class="form-select" required>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ $lot->product_id == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto mb-3">
            <label class="form-label is-required" for="registration_number">Número de Lote</label>
            <input type="text" class="form-control" id="registration-number" name="registration_number"
                value="{{ $lot->registration_number }}" required>
        </div>
        <div class="col-auto mb-3">
            <label class="form-label is-required" for="amount">Cantidad</label>
            <input type="text" class="form-control" id="amount" name="amount" value="{{ $lot->amount }}"
                required>
        </div>
    </div>
    <div class="row">
        <div class="col-4 mb-3">
            <label class="form-label is-required" for="warehouse">Almacen destino</label>
            <select class="form-select" name="warehouse_id" id="warehouse" required>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ $lot->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-2 mb-3">
            <label class="form-label" for="expiration-date">Fecha de expiración</label>
            <input type="date" class="form-control" id="expiration-date" name="expiration_date"
                value="{{ $lot->expiration_date }}">
        </div>

        <div class="col-2 mb-3">
            <label for="start-date" class="form-label is-required">Fecha de fabricación</label>
            <input type="date" class="form-control" name="start_date" id="start-date"
                value="{{ $lot->start_date }}" required>
        </div>

        <div class="col-auto mb-3">
            <input type="hidden" name="is_active" value="0">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="is_active" id="is-active"
                    value="1" {{ $lot->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is-active">
                    Lote activo para captura
                </label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
</form>
