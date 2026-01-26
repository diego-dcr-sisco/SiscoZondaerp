<form class="form" method="POST" action="{{ route('purchase-requisition.store') }}">
    @csrf
    <div class="row">
        <h5 class="fw-bold pb-1 border-bottom">Requisición de compra</h5>
        <div class="col-8 mb-3">
            <label for="customer" class="form-label is-required">Empresa destino</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" id="cliente-externo" name="destination_type"
                    value="externo" onchange="toggleCustomerSelect(this)" checked>
                <label class="form-check-label" for="cliente-externo">Cliente externo</label>
                <select class="form-select" id="customer" name="customer_id" required>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}
                            ({{ $customer->address . ' ' . $customer->state }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text text-danger mb-3">
                    * Para compras destinadas a otros clientes.
                </div>
            </div>

            <div class="form-check mt-3" id="cb-sisco">
                <input class="form-check-input" type="radio" id="siscoplagas-interno" name="destination_type"
                    value="interno" onchange="toggleCustomerSelect(this)">
                <label class="form-check-label" for="siscoplagas-interno" id="siscoplagas-label">
                    SISCOPLAGAS interno
                </label>
                <div class="form-text text-danger mb-3">
                    * Para insumos internos de la misma compañia.
                </div>
            </div>
        </div>
        <div class="col-4 mb-3">
            <label for="request_date" class="form-label is-required">Fecha a requerir</label>
            <div class="form-text text-danger mb-2">
                * Selecciona una fecha a partir de mañana.
            </div>
            <input type="date" class="form-control" id="request_date" name="request_date" required
                min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}">
        </div>
    </div>
    <div class="row">
        <h5 class="fw-bold pb-1 border-bottom">Productos</h5>
        @include('purchase-requisitions.purchases.create.products')

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center" id='added-products'>
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Producto</th>
                        <th>tipo</th>
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
                placeholder="Especifique características adicionales (e.g., color, tamaño, marca) o solicitudes opcionales (e.g., fecha de entrega preferida, embalaje especial)."></textarea>
        </div>
    </div>

    <input type="hidden" id="products" name="products" value="">

    <button type="submit" class="btn btn-primary mb-5 w-25" onclick="submitForm()">{{ __('buttons.store') }}</button>
</form>

<script>
    var products = [];

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('cliente-externo').checked = true;
        toggleCustomerSelect(document.getElementById('cliente-externo'));
    });

    function toggleCustomerSelect(radio) {
        var customerSelect = document.getElementById('customer');
        var divInternos = document.getElementById('div-internal');

        if (radio.value === 'externo') {
            customerSelect.disabled = false;
            divInternos.style.display = 'none';
        } else {
            customerSelect.disabled = true;
            divInternos.style.display = 'block';
        }
    }

    function addProductExternal() {
        var quantity = $('#product-quantity').val();
        if (quantity <= 0) {
            alert('La cantidad debe ser mayor a cero');
            return;
        }
        var unit = $('#product-unit').val();
        var description = $('#product-catalog option:selected').text();

        if (!description) {
            alert('Debe agregar un producto');
            return;
        }

        description = cleanDescription(description);

        var product = {
            index: products.length,
            quantity: quantity,
            unit: unit,
            description: description,
            type: 1
        }
        products.push(product);
        listProducts();
    }

    function addProductInternal() {
        var quantity = $('#product-quantity').val();
        if (quantity <= 0) {
            alert('La cantidad debe ser mayor a cero');
            return;
        }
        var unit = $('#product-unit').val();

        if (!$('#product-description-internal').is(':disabled')) {
            var description = $('#product-description-internal').val();
        } else {
            var description = $('#product-catalog-internal option:selected').val();
        }

        if (!description || description === 'undefined' || description === ' ') {
            alert('Debe agregar un producto');
            return;
        }

        if ($('#product-catalog-internal').val() === 'otro') {
            alert('Está seguro de agregar el producto \n' + description + '\n' +
                'Se almacenará en el inventario para su selección en futuras compras. \n\n' +
                'Verifique la ortografía y la descripción del producto antes de agregarlo'
            );
        }
        
        description = cleanDescription(description);

        var product = {
            index: products.length,
            quantity: quantity,
            unit: unit,
            description: description,
            type: 2
        }
        products.push(product);
        listProducts();
    }

    function cleanDescription(description) {
        return description.replace(/\s+/g, ' ').trim();
    }

    function listProducts() {
        var html = '';
        products.forEach(product => {
            html += `
                <tr>
                    <td>${product.quantity}</td>
                    <td>${product.unit}</td>
                    <td>${product.description}</td>
                    <td>${product.type === 1 ? 'directo' : 'indirecto'}</td>
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
