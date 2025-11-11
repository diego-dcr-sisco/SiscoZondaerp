<form class="form" method="POST" action="{{ route('consumptions.store') }}">
    @csrf
    <div class="row bg-light p-3 mb-3 rounded border-bottom">
        <div class="col-2 mb-3">
            <label for="zone" class="form-label is-required">Zona</label>
            <select class="form-select" id="zone" name="zone" required>
                <option value="" disabled selected>Seleccione una zona</option>
                @foreach ($zones as $zone) 
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
            </select>
            <div class="form-text text-danger" id="basic-addon4">
                *crea una zona <a href="{{ route('customer-zones.create') }}" target="_blank">aqui</a>
            </div>
        </div>
        <div class="col-6 mb-3 ">
            <label for="customer" class="form-label is-required">Planta</label>
            <select class="form-select" id="customer" name="customer_id" required>
                <option value="">Seleccione una planta</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}
                        ({{ $customer->address . ' ' . $customer->state }})
                    </option>
                @endforeach
            </select>
            <div class="form-text text-danger" id="basic-addon4">
                * Si no encuetras la planta, deberas agregarla a una zona <a href="{{ route('customer-zones.create') }}" target="_blank">aqui</a>
            </div>
        </div>
        <div class="col-2 mb-3">
            <label for="request_month" class="form-label is-required">Mes de rotación</label>
            <select class="form-select" id="request_month" name="request_month" required>
                @php
                    $months = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    $currentMonth = now()->month;
                @endphp
                @foreach ($months as $value => $name)
                    <option value="{{ $value }}" {{ $value == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <h5 class="fw-bold pb-1">Productos</h5>
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
            <label for="product-description" class="form-label">Selecciona el producto</label>
            <div class="input-group">
                <select class="form-select" id="product-catalog" name="product_catalog" onchange="updateProductId()">
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" id="product-id" name="product_id" value="{{ $products->first()->id ?? '' }}">

                <button type="button" class="btn btn-primary" id="add-product" onclick="addProduct()">
                    <i class="bi bi-plus-lg"></i> {{ __('buttons.add') }}
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center" id='added-products'>
                <thead class="table-light">
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
                placeholder="Especificaciones de servicios, comentarios, etc.."></textarea>
        </div>
    </div>

    <input type="hidden" id="products" name="products" value="">

    <div class="row justify-content-end">
        <button type="submit" class="btn btn-primary mb-5 w-25" onclick="submitForm()">{{ __('buttons.store') }}</button>
    </div>
</form>

<script>
    var products = [];

    $(document).ready(function() {
        // Filtrado dinámico de clientes por zona
        $('#zone').on('change', function() {
            const zoneId = $(this).val();
            const customerSelect = $('#customer');
            
            // Limpiar el select de clientes
            customerSelect.empty();
            
            if (zoneId) {
                customerSelect.append('<option value="">Cargando clientes...</option>');
                
                // Hacer petición AJAX para obtener clientes de la zona
                $.ajax({
                    url: '{{ route('consumptions.customers-by-zone') }}',
                    method: 'GET',
                    data: { zone_id: zoneId },
                    success: function(customers) {
                        customerSelect.empty();
                        customerSelect.append('<option value="">Seleccione un cliente</option>');
                        
                        customers.forEach(function(customer) {
                            customerSelect.append(
                                `<option value="${customer.id}">${customer.name}</option>`
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar clientes:', error);
                        customerSelect.empty();
                        customerSelect.append('<option value="">Error al cargar clientes</option>');
                        alert('Error al cargar los clientes. Por favor, intente de nuevo.');
                    }
                });
            } else {
                customerSelect.append('<option value="">Seleccione primero una zona</option>');
            }
        });
    });

    function updateProductId() {
        var selectedProductId = $('#product-catalog').val();
        $('#product-id').val(selectedProductId);
    }

    function addProduct() {
        var quantity = $('#product-quantity').val();
        if (quantity <= 0) {
            alert('La cantidad debe ser mayor a cero');
            return;
        }
        var unit = $('#product-unit').val();
        var description = $('#product-catalog option:selected').text();
        var productId = $('#product-catalog').val(); // Obtener directamente del select

        if (!productId || !description) {
            alert('Debe seleccionar un producto');
            return;
        }

        description = cleanDescription(description);

        var product = {
            index: products.length,
            product_id: productId,
            quantity: quantity,
            unit: unit,
            description: description,
        }
        products.push(product);
        listProducts();
        
        // Limpiar campos después de agregar
        $('#product-quantity').val('1');
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
