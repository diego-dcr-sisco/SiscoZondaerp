<?php
use Carbon\Carbon;
?>

<div class="container-fluid px-3">
    <form action="{{ route('stock.storeExit') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <!-- Datos de movimiento -->
            <div class="col-md-6">
                <div class="card shadow-sm border-1 h-100">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0 fw-bold">
                            Datos del movimiento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <label class="form-label is-required">Almacén de salida</label>
                                <input type="hidden" class="form-control" id="output-warehouse" name="origin_warehouse_id" value="{{ $warehouse->id }}" required />
                                <input type="text" class="form-control-plaintext bg-light px-2 rounded" id="output-warehouse-text" name="origin_warehouse_text" value="{{ $warehouse->name }}" disabled readonly />
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label is-required">Almacén destino</label>
                                <select class="form-select" id="output-destination-warehouse" name="destination_warehouse_id" required>
                                    <option value="" selected disabled>Seleccione almacén destino</option>
                                    @foreach ($all_warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-12 col-lg-6">
                                <label class="form-label is-required">Tipo de movimiento</label>
                                <select class="form-select" id="output-movement" name="movement_id" required>
                                    @foreach ($output_movements as $output_movement)
                                        <option value="{{ $output_movement->id }}">{{ $output_movement->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label is-required">Fecha</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="output-date" name="date" value="{{ Carbon::now()->toDateString() }}" required />
                                    <button type="button" class="btn btn-outline-secondary" onclick="setToday()">Hoy</button>
                                </div>
                                <script>
                                    function setToday() {
                                        document.getElementById('output-date').value = new Date().toISOString().split('T')[0];
                                    }
                                </script>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3" placeholder="Ingrese detalles sobre el traspaso de salida, motivo, condiciones o instrucciones especiales."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Productos -->
            <div class="col-md-6">
                <div class="card shadow-sm border-1" id="products-card" style="height: 60vh; display: flex; flex-direction: column;">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0 fw-bold">
                            Productos
                        </h5>
                    </div>
                    <div class="card-body" style="flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0;">
                        <div class="d-flex align-items-center justify-content-end mb-2">
                            <button type="button" class="btn btn-success btn-sm" onclick="addProduct()">
                                <i class="bi bi-plus-lg"></i> Agregar producto
                            </button>
                        </div>
                        <div id="products-container" style="flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 10px;">
                            <div class="product-entry row g-2 mb-4 mt-2 bg-light shadow-sm rounded-3 pb-3">
                                <div class="col-lg-3">
                                    <label class="form-label is-required">Cantidad</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-sm" name="products[0][amount]" min="1" required />
                                        <span class="input-group-text" id="exits-amount-unit" name="exits-amount-unit"></span>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label is-required">Producto</label>
                                    <select class="form-select form-select-sm" name="products[0][product_id]" onchange="refreshLots(this, 0)" required>
                                        <option value="" selected disabled>Seleccionar producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label is-required">Lote</label>
                                    <select class="form-select form-select-sm" name="products[0][lot_id]" onchange="setMaxAmount(this, 0)" required>
                                        {{-- Aqui se actualizan los lotes  --}}
                                    </select>
                                    <small class="form-text text-muted">Si no hay lotes, crear uno <a href="{{ route('lot.index') }}">aquí</a>.</small>
                                </div>

                                <div class="col-12 col-lg-1 d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeProduct(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="form-text text-muted">
                            Puede agregar varios productos a la salida.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botones de acción -->
        <div class="row mt-4">
            <div class="col-12 d-flex flex-column flex-md-row gap-2 justify-content-between">
                <a href="{{ url()->previous() }}" class="btn btn-outline-danger flex-grow-1" onclick="return confirm('¿Está seguro que desea cancelar?')">
                    <i class="bi bi-x-circle"></i> {{ __('buttons.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary flex-grow-1 w-50">
                    <i class="bi bi-check-circle"></i> Registrar Salida
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    let productIndex = 1;
    const lots = @json($lots);
    const lotAmounts = @json($lot_amounts);

    function addProduct() {
        const container = document.getElementById('products-container');
        const newProductEntry = document.createElement('div');
        newProductEntry.classList.add('product-entry', 'row', 'g-2', 'mb-4', 'mt-2', 'bg-light', 'shadow-sm', 'rounded-3', 'pb-3');
        newProductEntry.innerHTML = `
            <div class="col-lg-4">
                <label class="form-label is-required">Producto</label>
                <select class="form-select form-select-sm" name="products[${productIndex}][product_id]" onchange="refreshLots(this, ${productIndex})" required>
                    <option value="" selected disabled>Seleccionar producto</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Si no encuentra el producto, debe crear un lote <a href="{{ route('lot.index') }}">aquí</a>.</small>
            </div>
            <div class="col-lg-4">
                <label class="form-label is-required">Lote</label>
                <select class="form-select form-select-sm" name="products[${productIndex}][lot_id]" onchange="setMaxAmount(this, ${productIndex})" required>
                    {{-- Aqui se actualizan los lotes  --}}
                </select>
                <small class="form-text text-muted">Si no hay lotes disponibles, debe crearlo <a href="{{ route('lot.index') }}">aquí</a>.</small>
            </div>
            <div class="col-lg-3">
                <label class="form-label is-required">Cantidad</label>
                <input type="number" class="form-control form-control-sm" name="products[${productIndex}][amount]" min="1" required />
                <div class="form-text">Mililitros (ml)/Unidades (uds) <span></span></div>
            </div>
            <div class="col-lg-1 d-flex align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm mt-4" onclick="removeProduct(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newProductEntry);
        productIndex++;
    }

    function removeProduct(button) {
        const productEntry = button.closest('.product-entry');
        productEntry.remove();
    }

    function refreshLots(selectElement, index) {
        var productId = selectElement.value;
        var productEntry = selectElement.closest('.product-entry');
        var lotSelect = productEntry.querySelector(`select[name="products[${index}][lot_id]"]`);

        // Limpiar las opciones actuales
        lotSelect.innerHTML = '<option value="" selected disabled>Seleccione un lote</option>';

        // Filtrar los lotes por producto seleccionado
        var filteredLots = lots.filter(lot => lot.product_id == productId);

        // Agregar las opciones de lotes filtrados
        filteredLots.forEach(lot => {
            var option = document.createElement('option');
            option.value = lot.id;
            option.textContent = lot.registration_number;
            lotSelect.appendChild(option);
        });
    }

    function setMaxAmount(selectElement, index) {
        const lotId = selectElement.value;
        const productEntry = selectElement.closest('.product-entry');
        const amountInput = productEntry.querySelector(`input[name="products[${index}][amount]"]`);
        const maxAmountSpan = productEntry.querySelector('.form-text span');

        // Obtener la cantidad máxima del lote seleccionado
        const maxAmount = lotAmounts[lotId];
        if (maxAmount !== undefined) {
            amountInput.max = maxAmount;
            maxAmountSpan.textContent = `Cantidad máxima: ${maxAmount}`;
        }
    }

    // Modificar el script para deshabilitar el botón al enviar el formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Procesando...';
    });
    
</script>
