<form class="form" action="{{ route('point.update', ['id' => $point->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('POST')
    <div class="row">
        <div class="col-4 mb-3">
            <label for="name" class="form-label">{{ __('product.product-data.name') }} punto de control: </label>
            <input type="text" class="form-control" id="name" name="name"
                value="{{ $point->name }}" />
        </div>
        <div class="col-auto mb-3">
            <label for="code" class="form-label is-required">Código: </label>
            <input type="text" class="form-control" id="code" name="code"
                value="{{ $point->code }}" placeholder="EQ" required />
        </div>
    </div>
    <div class="row">
        <div class="col-4 mb-3">
            <label for="device" class="form-label is-required"> Dispositivo asociado: </label>
            <select class="form-select " name="device_id" id="device-id" required>
                @foreach ($devices as $device)
                    <option value="{{ $device->id }}" {{ $point->product->id == $device->id ? 'selected' : '' }}>
                        {{ $device->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-1 mb-3">
            <label for="colorPicker" class="form-label">Color:</label>
            <input type="color" style="height: 40px;"
                class="form-control-file form-control" id="color" name="color"
                value="{{ $point->color }}" />
        </div>
    </div>
    <div class="row mb-3">
        <h5 class="fw-bold pb-1 border-bottom">Productos asociados:</h5>
        @if (!$products->isEmpty())
            <div class="form-text text-danger pb-1" id="basic-addon4">
                * Selecciona al menos 1 producto.
            </div>
            @foreach ($products as $product)
                @if ($product->presentation_id != 1)
                    <div class="col-3">
                        <div class="form-check">
                            <input class="product form-check-input " type="checkbox" value="{{ $product->id }}"
                                onchange="setProducts()" {{ $point->hasProduct($product->id) ? 'checked' : '' }} />
                            <label class="form-check-label" for="product-{{ $product->id }}">
                                {{ $product->name }}
                            </label>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="form-text text-danger pb-1" id="basic-addon4">
                Sin productos disponibles
            </div>
        @endif
        <input type="hidden" id="selected-products" name="selectedProducts" value="" />
    </div>

    <button type="submit" class="btn btn-primary mt-2">{{ __('modals.button.save') }}</button>
</form>

<script>
    function setProducts() {
        var input = '#selected-products'
        var productClass = '.product';
        var checkedProducts = []
        $(productClass).each(function() {
            if ($(this).is(':checked')) {
                checkedProducts.push($(this).val());
            }
        });
        $(input).val(JSON.stringify(checkedProducts));
    }
</script>
