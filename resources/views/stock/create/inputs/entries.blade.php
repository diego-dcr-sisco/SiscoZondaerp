@extends('layouts.app')
@section('content')
    <style>
        .table-scroll-container {
            max-height: 100vh;
            overflow-y: auto;
            border: 1px solid #dee2e6;
        }

        .signature-section {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            box-shadow: 0 .35rem 1rem rgba(33, 37, 41, .06);
        }

        .signature-card {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            background: #fff;
            height: 100%;
            overflow: hidden;
        }

        .signature-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .signature-title {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #212529;
        }

        .signature-icon {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e9ecef;
            color: #495057;
        }

        .signature-stage {
            min-height: 220px;
            border: 1px dashed #adb5bd;
            border-radius: .5rem;
            background: linear-gradient(180deg, #fff 0%, #f8f9fa 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .signature-stage img,
        .signature-stage canvas {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .signature-stage canvas {
            position: absolute;
            inset: 0;
            background-color: #fff;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-placeholder {
            text-align: center;
            color: #6c757d;
            padding: 1rem;
        }

        .signature-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .signature-file {
            max-width: 18rem;
        }

        @media (max-width: 767.98px) {
            .signature-toolbar,
            .signature-card-header {
                align-items: stretch;
                flex-direction: column;
            }

            .signature-file {
                max-width: 100%;
            }
        }
    </style>

    @include('components.page-header', [
        'title' => 'MOVIMIENTO DE ENTRADA - ' . $warehouse->name,
        'icon' => 'bi-box-arrow-in-down-right',
        'backRoute' => route('stock.index'),
    ])

    <div class="container-fluid p-0">
        <form class="m-3" id="form-stock-entry" action="{{ route('stock.entry.store') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="border rounded shadow p-3 mb-3">
                <div class="fw-bold mb-2 fs-5">Datos del movimiento</div>
                <div class="row">
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-movement" class="form-label is-required">Tipo de
                            movimiento</label>
                        <select class="form-select" id="output-movement" name="movement_id" required>
                            @foreach ($input_movements as $input_movement)
                                <option value="{{ $input_movement->id }}">{{ $input_movement->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-origin-warehouse" class="form-label is-required">Almacén de
                            origen</label>
                        <select class="form-select" id="output-origin-warehouse" name="warehouse_id">
                            <option value="">Sin almacén de origen</option>
                            @foreach ($all_warehouses as $warehouses)
                                <option value="{{ $warehouses->id }}">{{ $warehouses->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-destination-warehouse-text" class="form-label">Almacén
                            destino</label>
                        <input type="hidden" id="output-destination-warehouse" name="destination_warehouse_id"
                            value="{{ $warehouse->id }}" required>
                        <input type="text" class="form-control bg-secondary-subtle px-2 rounded"
                            id="output-destination-warehouse-text" value="{{ $warehouse->name }}" disabled readonly>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-date" class="form-label is-required">Fecha</label>
                        <input type="date" class="form-control" id="output-date" name="date"
                            value="{{ \Carbon\Carbon::now()->toDateString() }}" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="observations" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3"
                            placeholder="Ingrese detalles sobre el traspaso, motivo, condiciones o instrucciones especiales."></textarea>
                    </div>
                </div>
            </div>

            <div class="border rounded shadow p-3 mb-3">
                <div class="fw-bold mb-2 fs-5">Productos</div>
                <div class="mb-3">
                    <button type="button" id="add-product-row" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Agregar
                        producto</button>
                </div>
                <div class="table-responsive-container">
                    <div class="overflow-auto w-100">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Unidades</th>
                                    <th>Lote</th>
                                    <th>Estado lote</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="products-container">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="signature-section p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                    <div>
                        <div class="fw-bold fs-5">Firmas del movimiento</div>
                        <div class="text-muted small">Validación del almacenista y confirmación del técnico/receptor.</div>
                    </div>
                    <span class="badge text-bg-light border">Entrada de almacén</span>
                </div>

                <div class="row g-3">
                <!-- Firma del Almacenista -->
                <div class="col-lg-6 col-12">
                    <div class="signature-card">
                        <div class="signature-card-header">
                            <h5 class="signature-title">
                                <span class="signature-icon"><i class="fas fa-user-shield"></i></span>
                                Almacenista
                            </h5>
                            <span class="badge text-bg-warning">Obligatoria</span>
                        </div>
                        <div class="p-3">
                            <div class="signature-toolbar mb-3">
                                <div class="signature-file">
                                    <label for="almacenistaFileInput" class="form-label small fw-semibold mb-1">Imagen de firma</label>
                                    <input type="file" id="almacenistaFileInput" class="form-control form-control-sm"
                                        accept="image/*">
                                </div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de firma del almacenista">
                                    <button type="button" id="drawAlmacenista" class="btn btn-outline-primary"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Dibujar firma">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button type="button" id="clearAlmacenista"
                                        class="btn btn-outline-danger" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Limpiar firma">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="signature-stage mb-3">
                                <img id="almacenistaSignatureImg" src="" class="d-none" alt="Firma del almacenista">
                                <canvas id="almacenistaCanvas" class="d-none"></canvas>
                                <div id="almacenistaPlaceholder" class="signature-placeholder">
                                    <i class="bi bi-pen d-block fs-2 mb-2"></i>
                                    Seleccione una imagen o dibuje la firma
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <small class="text-muted">Firma pendiente.</small>
                                <button type="button" id="saveAlmacenista" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check2-circle"></i> Aplicar firma
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Firma del Técnico/Receptor -->
                <div class="col-lg-6 col-12">
                    <div class="signature-card">
                        <div class="signature-card-header">
                            <h5 class="signature-title">
                                <span class="signature-icon"><i class="fas fa-user-tie"></i></span>
                                Técnico / receptor
                            </h5>
                            <span class="badge text-bg-light border">Opcional</span>
                        </div>
                        <div class="p-3">
                            <div class="signature-toolbar mb-3">
                                <div class="signature-file">
                                    <label for="tecnicoFileInput" class="form-label small fw-semibold mb-1">Imagen de firma</label>
                                    <input type="file" id="tecnicoFileInput" class="form-control form-control-sm"
                                        accept="image/*">
                                </div>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de firma del técnico">
                                    <button type="button" id="drawTecnico" class="btn btn-outline-success"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Dibujar firma">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button type="button" id="clearTecnico" class="btn btn-outline-danger"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Limpiar firma">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="signature-stage mb-3">
                                <img id="tecnicoSignatureImg" src="" class="d-none" alt="Firma del técnico/receptor">
                                <canvas id="tecnicoCanvas" class="d-none"></canvas>
                                <div id="tecnicoPlaceholder" class="signature-placeholder">
                                    <i class="bi bi-pen d-block fs-2 mb-2"></i>
                                    Seleccione una imagen o dibuje la firma
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <small class="text-muted">Firma pendiente.</small>
                                <button type="button" id="saveTecnico" class="btn btn-success btn-sm">
                                    <i class="bi bi-check2-circle"></i> Aplicar firma
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            
            <input type="hidden" id="warehouse-signature" name="warehouse_signature" required/>
            <input type="hidden" id="technician-signature" name="technician_signature" />

            <!-- Botones de acción -->

            {{-- <a href="{{ url()->previous() }}" class="btn btn-danger"
            onclick="return confirm('¿Está seguro que desea cancelar?')">
           {{ __('buttons.cancel') }}
        </a> --}}
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('¿Está seguro de registrar la entrada?')">
                Registrar Entrada
            </button>
        </form>

        <div class="modal fade" id="quickLotModal" tabindex="-1" aria-labelledby="quickLotModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quickLotModalLabel">Crear lote rápido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="quick-lot-product-id">
                        <input type="hidden" id="quick-lot-warehouse-id" value="{{ $warehouse->id }}">
                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <input type="text" class="form-control" id="quick-lot-product-name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label is-required" for="quick-lot-registration">Número de lote</label>
                            <input type="text" class="form-control" id="quick-lot-registration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label is-required" for="quick-lot-amount">Cantidad de registro del lote</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="quick-lot-amount" min="0" step="0.01" required>
                                <span class="input-group-text" id="quick-lot-unit">Unidad</span>
                            </div>
                            <div class="form-text">
                                El stock se afectará hasta guardar esta entrada; esta cantidad solo identifica cómo se registró el lote.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="quick-lot-expiration">Fecha de expiración</label>
                            <input type="date" class="form-control" id="quick-lot-expiration">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Periodo de uso</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="quick-lot-start">
                                <input type="date" class="form-control" id="quick-lot-end">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                        <button type="button" class="btn btn-primary" id="quick-lot-save">Crear y seleccionar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Datos precargados desde el backend
            const productsData = @json($products_data);
            let movementProducts = []; // Array para almacenar los productos del movimiento
            let rowCount = 0;
            let currentLotRow = null;
            const quickLotModal = new bootstrap.Modal(document.getElementById('quickLotModal'));

            function escapeHtml(value) {
                return $('<div>').text(value ?? '').html();
            }

            function appendLotToProduct(productId, lot) {
                const product = productsData.find(p => p.id == productId);
                if (!product) {
                    return;
                }

                product.lots = product.lots || [];
                product.lots.push(lot);

                $(`.product-select option[value="${productId}"]`).each(function() {
                    $(this).data('lots', product.lots);
                });
            }

            function getProductMetric(productId) {
                const product = productsData.find(p => p.id == productId);
                return product?.metric || 'Unidad';
            }

            function setLotStatus(row, type = null) {
                const badge = row.find('.lot-status-badge');

                if (type === 'new') {
                    badge.removeClass('bg-secondary bg-warning text-dark').addClass('bg-success').text('Nuevo lote');
                    return;
                }

                if (type === 'registered') {
                    badge.removeClass('bg-secondary bg-success').addClass('bg-warning text-dark').text('Lote registrado');
                    return;
                }

                badge.removeClass('bg-success bg-warning text-dark').addClass('bg-secondary').text('Sin lote');
            }

            // Función para agregar una nueva fila
            function addProductRow(selectedProduct = null, selectedLot = null) {
                rowCount++;
                const rowId = 'product-row-' + rowCount;

                // Crear la fila
                let row = `
        <tr id="${rowId}">
            <td>${rowCount}</td>
            <td>
                <select class="form-control product-select" name="products[${rowCount}][product_id]" required>
                    <option value="">Seleccionar producto</option>
                    ${productsData.map(product => 
                        `<option value="${product.id}" 
                                                                                                                        data-presentation="${product.presentation}"
                                                                                                                        data-metric="${product.metric}"
                                                                                                                        data-lots='${JSON.stringify(product.lots)}'
                                                                                                                        data-allow-null-lot="${product.allow_null_lot || false}">
                                                                                                                        ${product.name}
                                                                                                                    </option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <input type="number" class="form-control amount-input" 
                       name="products[${rowCount}][amount]" value="0" min="0" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control metric-input" 
                       name="products[${rowCount}][metric]" readonly>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <select class="form-control lot-select" name="products[${rowCount}][lot_id]">
                        <option value="">Sin lote (0.00)</option>
                        <!-- Lotes se llenarán dinámicamente -->
                    </select>
                    <button type="button" class="btn btn-outline-primary quick-lot-btn" disabled>
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <small class="form-text text-muted null-lot-message" style="display:none;">
                    Este producto permite registro sin lote
                </small>
            </td>
            <td class="text-center">
                <span class="badge bg-secondary lot-status-badge">Sin lote</span>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row" data-row="${rowId}">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </td>
        </tr>`;

                $('#products-container').append(row);

                // Si se proporciona un producto seleccionado (para edición)
                if (selectedProduct) {
                    $(`#${rowId} .product-select`).val(selectedProduct.id).trigger('change');
                    if (selectedLot) {
                        setTimeout(() => {
                            $(`#${rowId} .lot-select`).val(selectedLot.id);
                            $(`#${rowId} .amount-input`).val(selectedLot.amount);
                        }, 100);
                    }
                }

                // Evento para cuando se selecciona un producto
                $(`#${rowId} .product-select`).change(function() {
                    const productId = $(this).val();
                    const selectedOption = $(this).find('option:selected');
                    const lotSelect = $(this).closest('tr').find('.lot-select');
                    const metricInput = $(this).closest('tr').find('.metric-input');
                    const quickLotButton = $(this).closest('tr').find('.quick-lot-btn');
                    const allowNullLot = selectedOption.data('allow-null-lot') === true;
                    const nullLotMessage = $(this).closest('tr').find('.null-lot-message');

                    // Mostrar/ocultar mensaje de lote nulo permitido
                    if (allowNullLot) {
                        nullLotMessage.show();
                        //lotSelect.prop('required', false);
                    } else {
                        nullLotMessage.hide();
                        //lotSelect.prop('required', true);
                    }

                    // Actualizar la métrica
                    metricInput.val(selectedOption.data('metric') || '-');
                    quickLotButton.prop('disabled', !productId);
                    setLotStatus($(this).closest('tr'));

                    // Limpiar y cargar los lotes
                    lotSelect.empty().append('<option value="">Sin lote (0.00)</option>');

                    if (productId) {
                        const lots = selectedOption.data('lots') || [];
                        const unit = selectedOption.data('metric') || '';
                        lots.forEach(lot => {
                            const label = lot.entry_amount !== undefined && lot.entry_amount !== null
                                ? `A ingresar: ${lot.entry_amount} ${unit}`
                                : `Actual: ${lot.current_amount} ${unit}`;
                            lotSelect.append(
                                `<option value="${lot.id}" data-current-amount="${lot.current_amount}" data-is-new="${lot.is_new ? 1 : 0}">
                            ${lot.registration_number} (${label})
                        </option>`
                            );
                        });
                    }
                });

                // Evento para cuando se selecciona un lote
                $(`#${rowId} .lot-select`).change(function() {
                    const selectedOption = $(this).find('option:selected');
                    const currentAmount = selectedOption.data('current-amount') || 0;
                    const amountInput = $(this).closest('tr').find('.amount-input');
                    const row = $(this).closest('tr');

                    if (selectedOption.val()) {
                        setLotStatus(row, selectedOption.data('is-new') == 1 ? 'new' : 'registered');
                        // Si se seleccionó un lote (no es NULL)
                        /*amountInput.attr('max', currentAmount);

                        if (parseInt(amountInput.val()) > currentAmount) {
                            amountInput.val(currentAmount);
                        }*/
                    } else {
                        setLotStatus(row);
                        // Si se seleccionó NULL, quitar cualquier restricción
                        amountInput.removeAttr('max');
                    }
                });

                // Evento para actualizar movementProducts cuando cambian los valores
                $(`#${rowId} select, #${rowId} input`).change(function() {
                    updateMovementProducts();
                });
            }

            $(document).on('click', '.quick-lot-btn', function() {
                currentLotRow = $(this).closest('tr');
                const productSelect = currentLotRow.find('.product-select');
                const productId = productSelect.val();

                if (!productId) {
                    alert('Seleccione un producto antes de crear el lote.');
                    return;
                }

                $('#quick-lot-product-id').val(productId);
                $('#quick-lot-product-name').val(productSelect.find('option:selected').text().trim());
                $('#quick-lot-unit').text(getProductMetric(productId));
                $('#quick-lot-registration, #quick-lot-amount, #quick-lot-expiration, #quick-lot-start, #quick-lot-end').val('');
                const rowAmount = currentLotRow.find('.amount-input').val();
                if (rowAmount && parseFloat(rowAmount) > 0) {
                    $('#quick-lot-amount').val(rowAmount);
                }
                quickLotModal.show();
            });

            $('#quick-lot-save').click(function() {
                const productId = $('#quick-lot-product-id').val();
                const registrationNumber = $('#quick-lot-registration').val().trim();
                const amount = $('#quick-lot-amount').val();

                if (!productId || !registrationNumber || amount === '') {
                    alert('Complete producto, número de lote y cantidad.');
                    return;
                }

                $.ajax({
                    url: "{{ route('stock.lot.quickStore') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        product_id: productId,
                        warehouse_id: $('#quick-lot-warehouse-id').val(),
                        registration_number: registrationNumber,
                        amount: amount,
                        expiration_date: $('#quick-lot-expiration').val(),
                        start_date: $('#quick-lot-start').val(),
                        end_date: $('#quick-lot-end').val(),
                        create_initial_stock: 0
                    },
                    success: function(response) {
                        const lot = response.lot;
                        lot.entry_amount = amount;
                        lot.is_new = true;
                        appendLotToProduct(productId, lot);

                        if (currentLotRow) {
                            const lotSelect = currentLotRow.find('.lot-select');
                            const amountInput = currentLotRow.find('.amount-input');
                            const unit = getProductMetric(productId);
                            lotSelect.append(
                                `<option value="${lot.id}" data-current-amount="${lot.current_amount}" data-entry-amount="${amount}" data-is-new="1">
                                    ${escapeHtml(lot.registration_number)} (A ingresar: ${amount} ${escapeHtml(unit)})
                                </option>`
                            );
                            amountInput.val(amount);
                            lotSelect.val(lot.id).trigger('change');
                            updateMovementProducts();
                        }

                        quickLotModal.hide();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'No se pudo crear el lote.');
                    }
                });
            });

            // Función para actualizar el array movementProducts
            function updateMovementProducts() {
                movementProducts = [];

                $('#products-container tr').each(function() {
                    const productId = $(this).find('.product-select').val();
                    const lotId = $(this).find('.lot-select').val();
                    const amount = $(this).find('.amount-input').val();

                    if (productId) {
                        // Buscar el producto completo en productsData
                        const product = productsData.find(p => p.id == productId);

                        if (product) {
                            let lotInfo = {};
                            if (lotId) {
                                // Buscar el lote completo si fue seleccionado
                                const lot = product.lots.find(l => l.id == lotId);
                                if (lot) {
                                    lotInfo = {
                                        lot_id: lotId,
                                        lot_registration: lot.registration_number,
                                        current_amount: lot.current_amount
                                    };
                                }
                            }

                            movementProducts.push({
                                product_id: productId,
                                product_name: product.name,
                                presentation: product.presentation,
                                metric: product.metric,
                                amount: amount,
                                ...lotInfo
                            });
                        }
                    }
                });

                console.log('Movement Products:', movementProducts);
            }

            // Evento para agregar una nueva fila
            $('#add-product-row').click(function() {
                addProductRow();
            });

            // Evento para eliminar una fila
            $(document).on('click', '.remove-row', function() {
                const rowId = $(this).data('row');
                $('#' + rowId).remove();

                // Renumerar las filas
                $('#products-container tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });

                updateMovementProducts();
            });

            // Si necesitas precargar datos (para edición)
            function loadInitialProducts(initialProducts) {
                initialProducts.forEach(item => {
                    addProductRow({
                            id: item.product_id,
                            name: item.product_name
                        },
                        item.lot_id ? {
                            id: item.lot_id,
                            amount: item.amount
                        } : null
                    );
                });
            }

            // Ejemplo de cómo cargar productos iniciales
            // loadInitialProducts([]);


            function confirmAndSubmit(event) {
                // Actualizar el array movementProducts por última vez
                updateMovementProducts();

                // Validar que haya al menos un producto
                if (movementProducts.length === 0) {
                    alert('Debe agregar al menos un producto');
                    return false;
                }

                // Validar que todos los productos tengan cantidad válida
                let isValid = true;
                $('.amount-input').each(function() {
                    const amount = parseInt($(this).val());
                    if (isNaN(amount)) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    alert('Por favor complete todas las cantidades correctamente');
                    return false;
                }


                if (!confirm('¿Está seguro de registrar el movimiento con ' + movementProducts.length +
                        ' producto(s)?')) {
                    return false;
                }

                // Crear o actualizar el input hidden con los datos
                let $hiddenInput = $('input[name="products"]');
                if ($hiddenInput.length === 0) {
                    $hiddenInput = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'products')
                        .appendTo('form');
                }

                // Convertir a JSON y asignar al input
                $hiddenInput.val(JSON.stringify(movementProducts));

                // Continuar con el envío del formulario
                return true;
            }

            $('form').on('submit', confirmAndSubmit);
        });

        $(document).ready(function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Configuración común para ambos canvas
            function setupSignatureSection(prefix, color) {
                const canvas = $(`#${prefix}Canvas`);
                const ctx = canvas[0].getContext('2d');
                const fileInput = $(`#${prefix}FileInput`);
                const signatureImg = $(`#${prefix}SignatureImg`);
                const placeholder = $(`#${prefix}Placeholder`);
                const clearBtn = $(`#clear${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`);
                const drawBtn = $(`#draw${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`);
                const saveBtn = $(`#save${prefix.charAt(0).toUpperCase() + prefix.slice(1)}`);

                let isDrawing = false;
                let lastX = 0;
                let lastY = 0;

                // Configuración inicial
                ctx.strokeStyle = color || '#000';
                ctx.lineWidth = 2.5;
                ctx.lineJoin = 'round';
                ctx.lineCap = 'round';

                // Ajustar tamaño del canvas
                function resizeCanvas() {
                    const container = canvas.parent();
                    canvas[0].width = container.width();
                    canvas[0].height = 200;
                }

                resizeCanvas();
                $(window).on('resize', resizeCanvas);

                // Función para obtener posición
                function getPosition(e, canvasEl) {
                    const rect = canvasEl.getBoundingClientRect();
                    return [
                        e.clientX - rect.left,
                        e.clientY - rect.top
                    ];
                }

                // Eventos para ratón
                canvas.on('mousedown', function(e) {
                    isDrawing = true;
                    [lastX, lastY] = getPosition(e, this);
                });

                canvas.on('mousemove', function(e) {
                    if (!isDrawing) return;
                    const [x, y] = getPosition(e, this);
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(x, y);
                    ctx.stroke();
                    [lastX, lastY] = [x, y];
                });

                canvas.on('mouseup mouseout', function() {
                    isDrawing = false;
                });

                // Eventos para pantallas táctiles
                canvas.on('touchstart', function(e) {
                    e.preventDefault();
                    isDrawing = true;
                    const touch = e.originalEvent.touches[0];
                    [lastX, lastY] = getPosition(touch, this);
                });

                canvas.on('touchmove', function(e) {
                    e.preventDefault();
                    if (!isDrawing) return;
                    const touch = e.originalEvent.touches[0];
                    const [x, y] = getPosition(touch, this);
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(x, y);
                    ctx.stroke();
                    [lastX, lastY] = [x, y];
                });

                canvas.on('touchend', function() {
                    isDrawing = false;
                });

                // Manejar carga de imagen
                fileInput.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            signatureImg.attr('src', event.target.result).removeClass('d-none');
                            canvas.addClass('d-none');
                            placeholder.addClass('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // Botón de dibujar
                drawBtn.on('click', function(e) {
                    e.preventDefault();
                    canvas.removeClass('d-none');
                    signatureImg.addClass('d-none');
                    placeholder.addClass('d-none');
                    fileInput.val('');
                });

                // Botón de limpiar
                clearBtn.on('click', function(e) {
                    e.preventDefault();
                    ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);
                    signatureImg.addClass('d-none').attr('src', '#');
                    canvas.addClass('d-none');
                    placeholder.removeClass('d-none');
                    fileInput.val('');
                });

                // Botón de guardar
                saveBtn.on('click', function(e) {
                    e.preventDefault();
                    if (!signatureImg.hasClass('d-none')) {
                        // La firma es una imagen cargada
                        const imageData = signatureImg.attr('src');
                        console.log('Firma guardada (imagen):', imageData);
                        alert('Firma del ' + prefix + ' guardada como imagen');
                        $(
                            prefix == 'tecnico' ? `#technician-signature` : `#warehouse-signature`
                        ).val(imageData);
                    } else if (!canvas.hasClass('d-none')) {
                        // La firma es dibujada
                        const dataURL = canvas[0].toDataURL('image/png');
                        signatureImg.attr('src', dataURL).removeClass('d-none');
                        canvas.addClass('d-none');
                        console.log('Firma guardada (dibujo):', dataURL);
                        alert('Firma del ' + prefix + ' guardada como dibujo');
                        $(
                            prefix == 'tecnico' ? `#technician-signature` : `#warehouse-signature`
                        ).val(dataURL);

                    } else {
                        alert('Por favor, sube una imagen o dibuja tu firma primero.');
                    }
                });
            }

            // Configurar ambas secciones de firma
            setupSignatureSection('almacenista', '#0d6efd');
            setupSignatureSection('tecnico', '#198754');

            // Función de validación del formulario
            function validateForm() {
                // Validar firma del almacenista (obligatoria)
                const warehouseSignature = $('#warehouse-signature').val();
                if (!warehouseSignature || warehouseSignature.trim() === '') {
                    alert('Error: La firma del almacenista es obligatoria para registrar la entrada.');
                    return false;
                }

                // Actualizar el array movementProducts por última vez
                updateMovementProducts();

                // Validar que haya al menos un producto
                if (movementProducts.length === 0) {
                    alert('Debe agregar al menos un producto');
                    return false;
                }

                // Validar que todos los productos tengan cantidad válida
                let isValid = true;
                $('.amount-input').each(function() {
                    const amount = parseInt($(this).val());
                    if (isNaN(amount)) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    alert('Por favor complete todas las cantidades correctamente');
                    return false;
                }

                // Crear o actualizar el input hidden con los datos
                let $hiddenInput = $('input[name="products"]');
                if ($hiddenInput.length === 0) {
                    $hiddenInput = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'products')
                        .appendTo('form');
                }

                // Convertir a JSON y asignar al input
                $hiddenInput.val(JSON.stringify(movementProducts));

                return confirm('¿Está seguro de registrar la entrada?');
            }

            // Reemplazar el onclick del botón de envío
            $('form').on('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection
