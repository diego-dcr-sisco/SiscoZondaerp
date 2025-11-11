<form class="form" method="POST" action="{{route('purchase-requisition.store')}}">
    @csrf
    <div class="row">
        <h5 class="fw-bold pb-1 border-bottom">Requisición de compra</h5>
        <div class="col-8 mb-3">
            <label for="customer" class="form-label is-required">Empresa destino</label>
            <select class="form-select" id="customer" name="customer_id" required>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->address . ' ' . $customer->state }}) </option>
                @endforeach
            </select>
        </div>
        <div class="col-4 mb-3">
            <label for="request_date" class="form-label is-required">Fecha a requerir</label>
            <input type="date" class="form-control" id="request_date" name="request_date" required
                min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}">
        </div>
    </div>
    <div class="row">
        <h5 class="fw-bold pb-1 border-bottom">Productos</h5>
        <div class="col-auto mb-3">
            <label for="quantity" class="form-label is-required">Cantidad</label>
            <div class="input-group">
                <input type="number" class="form-control" id="product-quantity" name="quantity" value="1"
                    min="1" required>
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

        <div class="col mb-3">
            <label for="product-description" class="form-label">Descripción del producto</label>
            <div class="input-group">
                <input type="text" class="form-control" id="product-description" name="description"
                    placeholder="Especifica las características del material, bien o servicio: incluye detalles como modelo, marca, dimensiones, especificaciones técnicas, entre otros.">
                <button type="button" class="btn btn-primary" id="add-product" onclick="addProduct()">
                    <i class="bi bi-plus-lg"></i> {{ __('buttons.add') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center" id='added-products'>
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Producto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="product-list">
                    <!-- Los productos se agregarán aquí -->
                </tbody>
            </table>
        </div>

        <div class="col-12 mb-3">
            <label for="observations" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observations" name="observations" rows="5"
                placeholder="Especifique características adicionales (e.g., color, tamaño, marca) o solicitudes opcionales (e.g., fecha de entrega preferida, embalaje especial).""></textarea>
        </div>
    </div>

    <input type="hidden" id="products" name="products" value="">

    <button type="submit" class="btn btn-primary mb-5 w-25" onclick="submitForm()">{{ __('buttons.store') }}</button>
</form>

<script>
    var products = [];

    function addProduct() {
        var quantity = $('#product-quantity').val();
        if(quantity <= 0){
            alert('La cantidad debe ser mayor a cero');
            return;
        }
        var unit = $('#product-unit').val();
        var description = $('#product-description').val();

        if(!description){
            alert('Debe agregar una descripción del producto.');
            return;
        }

        var product = {
            index: products.length,
            quantity: quantity,
            unit: unit,
            description: description
        }
        products.push(product);
        listProducts();
    }

    function listProducts() {
        var html = '';
        products.forEach(product => {
            html += `
                <tr>
                    <td>${product.quantity}</td>
                    <td>${product.unit}</td>
                    <td>${product.description}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct(${product.index})"><i class="bi bi-trash-fill"></i> Eliminar</button>
                    </td>    
                </tr>
            `
        })

        $('#product-list').html(html);
    }

    function deleteProduct(index) {
        products = products.filter(item => item.index != index);
        listProducts()
    }

    function submitForm() {
        
        if (products.length === 0) {
            alert('Debe agregar al menos un producto para crear la requisición.');
            return;
        } else {
            $('#products').val(JSON.stringify(products));
        }
    }
    
</script>
