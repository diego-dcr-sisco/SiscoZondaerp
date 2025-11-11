@extends('layouts.app')
@section('content')
    <div class="container-fluid mt-2 p-4">
        <form class="form" method="POST" action="{{ route('purchase-requisition.update', $requisition->id) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <h5 class="fw-bold pb-1 border-bottom">Editar requisición de compra</h5>
                <div class="col-8 mb-3">
                    <label for="customer" class="form-label is-required">Empresa destino</label>
                    <input type="text" class="form-control" id="customer" name="customer_id"
                        value="{{ $customer->name }}" disabled required>
                </div>
                <div class="col-4 mb-3">
                    <label for="request_date" class="form-label is-required">Fecha a requerir</label>
                    <input type="date" class="form-control" id="request_date" name="request_date" required
                        min="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" value="{{ $requisition->request_date }}">
                </div>
            </div>
            <div class="row">
                <h5 class="fw-bold pb-1 border-bottom">Productos</h5>
                <div class="col-auto mb-3">
                    {{-- Cantidad y unidades --}}
                    <label for="quantity" class="form-label">Cantidad</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="product-quantity" name="quantity" value="0"
                            min="0" required>
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

                <div class="col-8 mb-3" id="div-external">
                    <label for="product-description" class="form-label">Indica producto directo</label>
                    <div class="input-group">
                        <select class="form-select" id="product-catalog" name="product_catalog"
                            onchange="toggleDescriptionInput()">
                            @foreach ($productCatalog as $product)
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

                <div class="row justify-content-end">
                    @if ($requisition->customer->name == 'SISCOPLAGAS-MRO')
                        {{-- Descripcion del producto para SISCOPLAGAS interno --}}
                        <div class="col-8 mb-3" id="div-internal">
                            <label for="product-description" class="form-label">Indica el producto indirecto</label>
                            <div class="input-group">
                                <select class="form-select" id="product-catalog-internal" name="product_catalog"
                                    onchange="toggleDescriptionInput()">
                                    <option value="otro">Otro</option>
                                    {{-- <option value="opt">Opcion 1</option> --}}
                                    @foreach ($indirectProducts as $product)
                                        <option value="{{ $product->description }}">
                                            {{ $product->description }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control w-50" id="product-description-internal"
                                    name="description" placeholder="Nombre del producto">
                                <button type="button" class="btn btn-primary" id="add-product"
                                    onclick="addProductInternal()">
                                    <i class="bi bi-plus-lg"></i> {{ __('buttons.add') }}
                                </button>
                            </div>
                            <div class="form-text text-danger mb-2">
                                * EPP/Herramientas/Insumos oficina/etc.
                            </div>
                        </div>
                    @endif
                </div>


                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center" id='added-products'>
                        <thead>
                            <tr>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th>Producto</th>
                                <th>Tipo</th>
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
                        placeholder="Especifique características adicionales (e.g., color, tamaño, marca) o solicitudes opcionales (e.g., fecha de entrega preferida, embalaje especial).">{{ $requisition->observations }}</textarea>
                </div>
            </div>

            <input type="hidden" id="products" name="products" value="{{ json_encode($requisition->products) }}">

            <button type="submit" class="btn btn-primary" onclick="submitForm()">{{ __('buttons.update') }}</button>
        </form>
    </div>


    <script>
        var products = @json($requisition->products);

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


        function listProducts() {
            var html = '';
            products.forEach((product, index) => {
                html += `
                <tr>
                    <td>${product.quantity}</td>
                    <td>${product.unit}</td>
                    <td>${product.description}</td>
                    <td>${product.type === 1 ? 'directo' : 'indirecto'}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct(${index})"><i class="bi bi-trash-fill"></i> Eliminar</button>
                    </td>    
                </tr>
            `
            })

            $('#product-list').html(html);
        }

        function deleteProduct(index) {
            products.splice(index, 1);
            listProducts();
        }

        function submitForm() {

            if (products.length === 0) {
                alert('Debe agregar al menos un producto para crear la requisición.');
                return;
            } else {
                $('#products').val(JSON.stringify(products));
            }
        }

        $(document).ready(function() {
            listProducts();
        });
    </script>
@endsection
