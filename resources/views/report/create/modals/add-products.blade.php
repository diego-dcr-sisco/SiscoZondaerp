<div class="modal" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="new-product-input" class="form-label">Producto</label>
                    <input class="form-control" list="productOptions" id="new-product-input"
                        placeholder="Escribe para buscar...">
                    <datalist id="productOptions">
                        @foreach ($products as $product)
                            <option value="{{ $product->name }}" data-id="{{ $product->id }}"></option>
                        @endforeach
                    </datalist>

                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">Cantidad</label>
                    <div class="input-group mb-3">
                        <input type="number" class="form-control handleP" id="add-product-quantity"
                            placeholder="Cantidad" value="1" min="0" disabled>
                        <span class="input-group-text" id="add-product-metric">-</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="lot">Lote</label>
                    <select class="form-select handleP" id="add-product-lot" aria-label="Default select example"
                        disabled>
                        <option selected>Selecciona un lote</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addNewProduct()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#addProductModal').on('show.bs.modal', function(e) {
        cleanAddProductForm();
    });


    function getProductId(id) {
        let option = $('#productOptions option').filter(function() {
            return $(this).data('id') === id;
        });
        return option.data('id') || null;
    }

    function getSelectedProductId() {
        const input = document.getElementById('new-product-input');
        const list = document.getElementById('productOptions');

        // Busca el option que coincide con el valor del input 
        const option = Array.from(list.options).find(opt => opt.value === input.value);

        return option ? option.getAttribute('data-id') : null;
    }

    // Maneja el cambio de producto en el input
    function handleAddProductById(product_id) {
        var product_id = getProductIdById(product_id);
        handleAddProduct(product_id);
    }


    function handleAddProduct(product_id) {
        const $select = $('#add-product-lot');

        if (product_id == "" || product_id == null) {
            cleanAddProductForm();
            return;
        }

        $('.handleP').prop('disabled', false);
        product_id = parseInt(product_id);

        const product = products.find(product => product.id == product_id);
        if (!product) {
            cleanAddProductForm();
            alert('Producto no válido. Selecciona uno de la lista.');
            return;
        }

        const found_lots = lots.filter(lot => lot.product_id == product_id);
        const metric = metrics.find(metric => metric.id == product.metric_id);

        $select.empty();

        found_lots.forEach(lot => {
            $select.append($('<option>', {
                value: lot.id,
                text: `${lot.registration_number}`
            }));
        });

        $('#add-product-metric').text(metric ? metric.value : '-');
    }


    $('#new-product-input').on('change', function() {
        const productId = getSelectedProductId();

        if (productId) {
            handleAddProduct(productId);
        } else {
            cleanAddProductForm();
            alert('Por favor selecciona un producto válido de la lista');
            this.value = '';
        }

    });

    function addNewProduct() {
        var product_id = getSelectedProductId();
        var quantity = $('#add-product-quantity').val();
        var lot_id = $('#add-product-lot').val();

        if (product_id == "" || product_id == null) {
            alert('Debes seleccionar un producto');
            return;
        }

        if (lot_id == "" || lot_id == null) {
            alert('Debes seleccionar un lote valido');
            return;
        }

        if (quantity == "" || quantity == null || quantity <= 0) {
            alert('Se debe ingresar una cantidad mayor a 0');
            return;
        }

        var index = devices.findIndex(device => device.id == device_in_review);
        if (index == -1) {
            alert('Dispositivo no encontrado');
            return;
        }

        var product_index = devices[index].products.findIndex(product => product.id == product_id);

        const lot = lots.find(lot => lot.product_id == product_id && lot.id == lot_id);
        const product = products.find(product => product.id == product_id);
        const metric = metrics.find(metric => metric.id == product.metric_id);

        const found_lots = lots.filter(lot => lot.product_id == product_id);

        if (product_index != -1) {
            devices[index].products[product_index].quantity += parseFloat(quantity);
        } else {
            devices[index].products.push({
                id: parseInt(product_id),
                name: product.name,
                lot_id: parseInt(lot_id),
                lot_number: lot ? lot.registration_number : '',
                quantity: parseFloat(quantity),
                metric: metric ? metric.value : '-',
                lots: found_lots.map(l => ({
                    id: l.id,
                    registration_number: l.registration_number
                }))
            });
        }

        showProducts();
        $('#new-product-input').val('');
        $('#addProductModal').modal('hide');
        $("#addProductModal.show").removeClass("modal-blur");
    }
</script>
