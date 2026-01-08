<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form class="modal-content" id="product-form" action="{{ route('report.set.product', ['orderId' => $order->id]) }}"
            method="POST">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="productModalLabel">Editar producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="op-id" name="op_id" value="" />
                <div class="mb-3">
                    <label for="service-id" class="form-label is-required">Servicio relacionado</label>
                    <select class="form-select" id="service" name="service_id" required>
                        <option value="" selected>Sin servicio</option>
                        @foreach ($order->services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product-id" class="form-label is-required">Producto</label>
                    <select class="form-select" id="product" name="product_id" onchange="onProductChange()" required>
                        <option value="" selected>Sin producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product-id" class="form-label is-required">Método de aplicación</label>
                    <select class="form-select" id="application-method" name="application_method_id"
                        onchange="completeProduct()" required>
                        <option value="" selected>Sin método de aplicación</option>
                        @foreach ($application_methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product-amount" class="form-label is-required">Cantidad usada</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="amount" name="amount" placeholder="0.00"
                            min="0" step="0.01" required>
                        <select class="form-select" id="metric" name="metric_id">
                            <option value="" selected>Sin métrica o unidades</option>
                            @foreach ($metrics as $metric)
                                <option value="{{ $metric->id }}">{{ $metric->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="product-dosage" class="form-label">Dosificación (Por litro)</label>
                    <input type="text" class="form-control" id="dosage" name="dosage" placeholder="10ml x Litro">
                </div>
                <div class="mb-3">
                    <label for="product-unit" class="form-label">Lote: </label>
                    <select class="form-select" id="lot" name="lot_id">
                        <option value="" selected>Sin lote</option>
                        @foreach ($lots as $lot)
                            <option value="{{ $lot->id }}">{{ $lot->product->name ?? '-' }} - No. {{ $lot->registration_number }}
                                </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary"
                    onclick="cleanDisabled()">{{ __('buttons.store') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Variable global con todos los lotes (debe estar definida en algún lugar)
    // var allLots = [...]; // Esto debe estar definido globalmente
    
    function loadProductLots() {
        var productId = $('#product').val();
        var lotSelect = $('#lot');
        
        // Limpiar opciones actuales
        lotSelect.empty();
        
        // Si no hay producto seleccionado
        if (!productId) {
            lotSelect.append('<option value="" selected>Seleccione un producto primero</option>');
            return;
        }
        
        // Buscar lotes del producto seleccionado en allLots
        var productLots = [];
        console.log(window.allLots)
        
        if (window.allLots && Array.isArray(window.allLots)) {
            productLots = window.allLots.filter(function(lot) {
                return lot.product_id == productId;
            });
        }
        
        // Agregar opción "Sin lote" siempre
        lotSelect.append('<option value="">Sin lote</option>');
        
        // Si hay lotes para el producto, agregarlos
        if (productLots.length > 0) {
            productLots.forEach(function(lot) {
                var lotText = '';
                
                // Construir texto del lote
                if (lot.product && lot.product.name) {
                    lotText += lot.product.name + ' - ';
                } else if (lot.product_name) {
                    lotText += lot.product_name + ' - ';
                }
                
                lotText += 'No. ' + (lot.registration_number || 'N/A');
                
                lotSelect.append($('<option>', {
                    value: lot.id,
                    text: lotText
                }));
            });
        }
        // Si no hay lotes, solo queda la opción "Sin lote"
    }
    
    function setProduct(element) {
        const productData = element.getAttribute("data-product");
        let data;

        try {
            data = JSON.parse(productData);
        } catch {
            data = productData;
        }

        console.log(data);

        $('#op-id').val(data.id);
        $('#service').val(data.service_id);
        $('#application-method').val(data.application_method_id ?? 1);
        $('#product').val(data.product_id);
        $('#metric').val(data.metric_id);
        $('#amount').val(data.amount);
        $('#dosage').val(data.dosage);

        // Cargar los lotes del producto seleccionado
        if (data.product_id) {
            loadProductLots();
            
            // Después de cargar los lotes, seleccionar el lote correspondiente
            // Usamos setTimeout para asegurar que el select se haya actualizado
            setTimeout(function() {
                $('#lot').val(data.lot_id);
            }, 50);
        } else {
            // Si no hay producto, resetear el select de lotes
            $('#lot').empty().append('<option value="" selected>Seleccione un producto primero</option>');
        }

        $('#service').prop('disabled', true);
        $('#product').prop('disabled', true);
    }

    // Función cuando cambia el producto (para el modal nuevo)
    function onProductChange() {
        var productId = $('#product').val();
        
        if (productId) {
            // Cargar lotes del producto
            loadProductLots();
            
            // Obtener datos del producto seleccionado para completar otros campos
            var selectedOption = $('#product option:selected');
            
            // Si el producto tiene datos en data attributes, completarlos
            if (selectedOption.data('metric-id')) {
                $('#metric').val(selectedOption.data('metric-id'));
            }
            
            if (selectedOption.data('dosage')) {
                $('#dosage').val(selectedOption.data('dosage'));
            }
        } else {
            // Si no hay producto seleccionado, resetear
            $('#lot').empty().append('<option value="" selected>Seleccione un producto primero</option>');
            $('#metric').val('');
            $('#dosage').val('');
        }
    }

    function cleanDisabled() {
        $('#service').prop('disabled', false);
        $('#application-method').prop('disabled', false);
        $('#product').prop('disabled', false);
    }

    function cleanForm() {
        $('#product-form').find('input[type="text"], input[type="email"], input[type="number"]').val('');
        $('#op-id').val(null);
        $('#product-form').find('select').val('');
        $('#product-form').find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
        
        // Restablecer select de lotes
        $('#lot').empty().append('<option value="" selected>Seleccione un producto primero</option>');
        
        $('#service').prop('disabled', false);
        $('#application-method').prop('disabled', false);
        $('#product').prop('disabled', false);
    }

    // Inicializar al cargar la página
    $(document).ready(function() {
        // Asegurar que el select de lotes esté vacío al inicio
        $('#lot').empty().append('<option value="" selected>Seleccione un producto primero</option>');
        
        // Si ya hay un producto seleccionado (en caso de edición), cargar sus lotes
        var initialProductId = $('#product').val();
        if (initialProductId) {
            loadProductLots();
        }
    });
</script>
