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
            <label class="form-label" for="product_id">Fecha de expiración</label>
            <input type="date" class="form-control" id="expiration-date" name="expiration_date"
                value="{{ $lot->expiration_date }}">
        </div>

        <div class="col-3 mb-3">
            <label for="expiration_date" class="form-label is-required">Periodo de uso </label>
            <div class="input-group">
                <input type="date" class="form-control" name="start_date" id="start-date" value="{{ $lot->start_date }}" required>
                <input type="date" class="form-control" name="end_date" id="end-date" value="{{ $lot->end_date }}" required>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
</form>
