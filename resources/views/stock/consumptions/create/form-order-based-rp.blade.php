<form class="form" method="POST" action="{{ route('consumptions.store') }}">
    @csrf
    <div class="row bg-light p-3 mb-3 rounded border-bottom">
        <div class="col-2 mb-3">
            <label for="zone" class="form-label is-required">Zona</label>
            <select class="form-select" id="zone" name="zone" required>
                <option value="">Seleccione una zona</option>
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
            <select class="form-select" id="customer" name="customer_id" required disabled>
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
            <label for="request_rp" class="form-label is-required">Plan de rotación</label>
            <select class="form-select" id="request_rp" name="request_rp" required>
            <option value="">Seleccione un plan</option>
            @foreach ($rotationPlans as $plan)
                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
            @endforeach
            </select>
        </div>
        <div class="col-2 mb-3 d-flex align-items-end">
            <button type="button" class="btn btn-primary w-100" id="load-plan-products">
                <i class="bi bi-filter"></i> Obtener productos
            </button>
        </div>
        </div>

        <div class="row">
            <h5 class="fw-bold pb-1">Productos</h5>
            <div class="table-responsive">
            <table class="table table-bordered table-striped text-center" id="added-products">
                <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody id="product-list">
                <!-- Los productos del plan se cargarán aquí -->
                </tbody>
            </table>
            </div>
        </div>

    <script>
        // Unidades disponibles
        const units = [
        { value: "pza", label: "Pieza (pza)" },
        { value: "mg", label: "Miligramo (mg)" },
        { value: "g", label: "Gramo (gr)" },
        { value: "kg", label: "Kilogramo (kg)" },
        { value: "m", label: "Metro (m)" },
        { value: "ml", label: "Mililitro (ml)" },
        { value: "l", label: "Litro (l)" },
        { value: "paq", label: "Paquete (paq)" },
        { value: "doc", label: "Docena (doc)" },
        { value: "bulto", label: "Bulto" },
        { value: "rollo", label: "Rollo" },
        { value: "otro", label: "Otro" }
        ];

        // Actualiza la cantidad de un producto
        function updateProductQuantity(index, value) {
        const qty = parseInt(value, 10);
        if (!isNaN(qty) && qty > 0) {
            products[index].quantity = qty;
        }
        }

        // Actualiza la unidad de un producto
        function updateProductUnit(index, value) {
        products[index].unit = value;
        }

        // Modifica listProducts para mostrar inputs/selects editables
        function listProducts() {
        var html = '';
        products.forEach((product, idx) => {
            html += `
            <tr>
                <td>${product.description}</td>
                <td>
                <input type="number" class="form-control" min="1" value="${product.quantity || 1}" 
                    onchange="updateProductQuantity(${idx}, this.value)" required>
                </td>
                <td>
                <select class="form-select" onchange="updateProductUnit(${idx}, this.value)">
                    ${units.map(unit => `
                    <option value="${unit.value}" ${product.unit === unit.value ? 'selected' : ''}>${unit.label}</option>
                    `).join('')}
                </select>
                </td>
                <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct(${product.index})">
                    <i class="bi bi-trash-fill"></i> Eliminar
                </button>
                </td>
            </tr>
            `;
        });

        $('#product-list').html(html);
        }
    </script>

        <script>
            // Cargar productos del plan de rotación seleccionado
            $('#load-plan-products').on('click', function(e) {
                e.preventDefault();
                var planId = $('#request_rp').val();
                if (!planId) {
                    alert('Seleccione un plan de rotación');
                    return;
                }
              $.ajax({
                    url: '{{ route('consumptions.products-by-plan') }}',
                    method: 'GET',
                    data: { id: planId },
                    success: function(response) {
                        // Limpiar productos actuales
                        products = [];
                    
                        response.forEach(function(item, idx) {
                            products.push({
                                index: idx,
                                product_id: item.product_id, 
                                 description: item.product_name 
                                
                            });
                        });
                        listProducts();
                    },
                    error: function(xhr, status, error) {
                        alert('Error al cargar productos del plan');
                    }
                });
            });

            
            
        </script>

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
                customerSelect.prop('disabled', false);
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
        products.forEach((product, idx) => {
            html += `
                <tr>
                    <td>${product.description}</td>
                    <td>
                        <input type="number" class="form-control" min="1" value="${product.quantity || 1}" 
                            onchange="updateProductQuantity(${idx}, this.value)" required>
                    </td>
                    <td>
                        <select class="form-select" onchange="updateProductUnit(${idx}, this.value)">
                            ${units.map(unit => `
                                <option value="${unit.value}" ${product.unit === unit.value ? 'selected' : ''}>${unit.label}</option>
                            `).join('')}
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteProduct(${product.index})">
                            <i class="bi bi-trash-fill"></i> Eliminar
                        </button>
                    </td>       
                </tr>
            `;
        });

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
