<div class="row">
    {{-- Cantidad y Unidades --}}
    <div class="col-4 mb-3">
        <label for="quantity" class="form-label is-required">Cantidad</label>
        <div class="input-group">
            <input type="number" class="form-control" id="product-quantity" name="quantity" value="1" min="1"
                required>
            <select class="form-select" id="product-unit" name="product_unit">
                <option value="pza">Pieza (pza)</option>
                <option value="mg">Miligramo (mg)</option>
                <option value="g">Gramo (gr)</option>
                <option value="kg">Kilogramo (kg)</option>
                <option value="m">Metro (m)</option>
                <option value="ml">Mililitro (ml)</option>
                <option value="l">Litro (l)</option>
                <option value="paq">Paquete (paq) </option>
                <option value="doc">Docena (doc)</option>
                <option value="bulto">Bulto</option>
                <option value="rollo">Rollo</option>
                <option value="otro">Otro</option>
            </select>
        </div>
    </div>

    {{-- Descripcion del producto para clientes externos --}}
    <div class="col-8 mb-3" id="div-external">
        <label for="product-description" class="form-label">Indica producto directo</label>
        <div class="input-group">
            <select class="form-select" id="product-catalog" name="product_catalog" onchange="toggleDescriptionInput()">
                @foreach ($productsCatalog as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            {{-- <input type="text" class="form-control w-50" id="product-description" name="description"
                    placeholder="Nombre del producto o servicio; modelo, marca, etc."
                    disabled> --}}

            <button type="button" class="btn btn-primary" id="add-product" onclick="addProductExternal()">
                <i class="bi bi-plus-lg"></i> {{ __('buttons.add') }}
            </button>
        </div>
        <div class="form-text text-danger mb-2">
            * Dispositivos/pesticidas/etc.
        </div>
    </div>
</div>

<div class="row justify-content-end">
    {{-- Descripcion del producto para SISCOPLAGAS interno --}}
    <div class="col-8 mb-3" id="div-internal" style="">
        <label for="product-description" class="form-label">Indica el producto indirecto</label>
        <div class="input-group">
            <select class="form-select" id="product-catalog-internal" name="product_catalog"
                onchange="toggleDescriptionInput()">
                <option value="otro">Otro</option>
                {{-- <option value="opt">Opcion 1</option> --}}
                @foreach ($products as $product)
                    <option value="{{ $product->description }}">
                        {{ $product->description }}
                    </option>
                @endforeach
            </select>
            <input type="text" class="form-control w-50" id="product-description-internal" name="description"
                placeholder="Nombre del producto">
            <button type="button" class="btn btn-primary" id="add-product" onclick="addProductInternal()">
                <i class="bi bi-plus-lg"></i> {{ __('buttons.add') }}
            </button>
        </div>
        <div class="form-text text-danger mb-2">
            * EPP/Herramientas/Insumos oficina/etc.
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var divInternos = document.getElementById('div-internal');
        divInternos.style.display = 'none';

        console.log(divInternos.value);
    });

    function toggleDescriptionInput() {
        var selectInternal = document.getElementById('product-catalog-internal');
        var inputInternal = document.getElementById('product-description-internal');

        if (selectInternal.value === 'otro' || selectInternal.value === 'undefined' || selectInternal.value === null) {
            inputInternal.disabled = false;
            inputInternal.style.display = 'block';
        } else {
            inputInternal.disabled = true;
            inputInternal.value = ' ';
            inputInternal.style.display = 'none';
        }
    }
</script>
