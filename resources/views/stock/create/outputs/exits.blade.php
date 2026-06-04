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
        'title' => 'MOVIMIENTO DE SALIDA - ' . $warehouse->name,
        'icon' => 'bi-box-arrow-up-left',
        'backRoute' => route('stock.index'),
    ])

    <div class="container-fluid p-0">
        @if ($errors->any())
            <div class="alert alert-danger m-3 mb-0">
                <div class="fw-bold mb-1">No se pudo registrar la salida</div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form class="m-3" id="form-stock-entry" action="{{ route('stock.exit.store') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="border rounded shadow p-3 mb-3">
                <div class="fw-bold mb-2 fs-5">Datos del movimiento</div>
                <div class="row">
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-movement" class="form-label is-required">Tipo de
                            movimiento</label>
                        <select class="form-select" id="output-movement" name="movement_id" required>
                            @foreach ($output_movements as $output_movement)
                                <option value="{{ $output_movement->id }}">{{ $output_movement->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-origin-warehouse" class="form-label is-required">Almacén de
                            origen</label>
                        <input type="hidden" id="output-origin-warehouse" name="warehouse_id" value="{{ $warehouse->id }}"
                            required>
                        <input type="text" class="form-control bg-secondary-subtle" id="output-origin-warehouse-text"
                            value="{{ $warehouse->name }}" readonly>
                    </div>
                    <div class="col-lg-3 col-12 mb-3" id="destination-warehouse-container">
                        <label for="output-origin-warehouse-text" class="form-label">Almacén
                            destino</label>
                        <select class="form-select" id="output-destination-warehouse" name="destination_warehouse_id">
                            <option value="">Sin almacén de destino</option>
                            @foreach ($all_warehouses as $warehouses)
                                <option value="{{ $warehouses->id }}">{{ $warehouses->name }}</option>
                            @endforeach
                        </select>
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
                    <span class="badge text-bg-light border">Salida de almacén</span>
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
                            @if ($errors->has('warehouse_signature'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('warehouse_signature') }}
                                </div>
                            @endif
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
            
            <input type="hidden" id="warehouse-signature" name="warehouse_signature" required />
            <input type="hidden" id="technician-signature" name="technician_signature" />
            
            <!-- Botones de acción -->

            {{-- <a href="{{ url()->previous() }}" class="btn btn-danger"
            onclick="return confirm('¿Está seguro que desea cancelar?')">
           {{ __('buttons.cancel') }}
        </a> --}} 

            <button type="submit" class="btn btn-primary" id="submit-btn">
                Registrar Salida
            </button>
        </form>

    </div>

    <script>
        $(document).ready(function() {
            function toggleDestinationWarehouse() {
                const movementType = $('#output-movement').val();
                const container = $('#output-destination-warehouse').closest('.col-6.mb-3');
                const select = $('#output-destination-warehouse');

                if (movementType == '7') {
                    select.prop('disabled', false)
                        .prop('required', true);
                    container.find('label').removeClass('text-muted');
                    container.find('.help-text').remove();
                    container.append(
                        '<small class="form-text text-primary help-text">Seleccione el almacén destino</small>');
                } else {
                    select.prop('disabled', true)
                        .prop('required', false)
                        .val('');
                    container.find('label').addClass('text-muted');
                    container.find('.help-text').remove();
                    container.append(
                        '<small class="form-text text-muted help-text">No requerido para este movimiento</small>'
                    );
                }
            }

            // Inicializar
            toggleDestinationWarehouse();
            $('#output-movement').change(toggleDestinationWarehouse);

            // Datos precargados desde el backend
            const productsData = @json($products_data);
            let movementProducts = []; // Array para almacenar los productos del movimiento
            let rowCount = 0;

            function setLotStatus(row, type = null) {
                const badge = row.find('.lot-status-badge');

                if (type === 'registered') {
                    badge.removeClass('bg-secondary').addClass('bg-warning text-dark').text('Lote registrado');
                    return;
                }

                badge.removeClass('bg-warning text-dark').addClass('bg-secondary').text('Sin lote');
            }

            function validateOutputRow(row) {
                const lotSelect = row.find('.lot-select');
                const amountInput = row.find('.amount-output');
                const amount = parseFloat(amountInput.val());
                const selectedLot = lotSelect.find('option:selected');
                const available = parseFloat(selectedLot.data('current-amount')) || 0;
                let message = '';

                if (!lotSelect.val()) {
                    message = 'Seleccione un lote disponible.';
                } else if (isNaN(amount) || amount <= 0) {
                    message = 'La cantidad debe ser mayor a 0.';
                } else if (amount > available) {
                    message = `La cantidad no puede ser mayor al disponible (${available}).`;
                }

                row.find('.amount-error').text(message);
                amountInput.toggleClass('is-invalid', message !== '');
                lotSelect.toggleClass('is-invalid', !lotSelect.val());

                return message === '';
            }

            // Función para agregar una nueva fila
            function addProductRow(selectedProduct = null, selectedLot = null) {
                rowCount++;
                const rowId = 'product-row-' + rowCount;

                // Crear la fila
                let row =
                    `
        <tr id="${rowId}">
            <td>${rowCount}</td>
            <td>
                <select class="form-control product-select" name="products[${rowCount}][product_id]" required>
                    <option value="">Seleccionar producto</option>
                    ${productsData.map(product => 
                        `<option value="${product.id}" 
                                                                                                                                        data-presentation="${product.presentation}"
                                                                                                                                        data-metric="${product.metric}"
                                                                                                                                        data-lots='${JSON.stringify(product.lots)}'>
                                                                                                                                        ${product.name}
                                                                                                                                    </option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <input type="number" class="form-control amount-output" 
                       name="products[${rowCount}][amount]" value="0" min="0" step="0.01" required>
                <div class="invalid-feedback amount-error"></div>
            </td>
            <td>
                <input type="text" class="form-control metric-output" 
                       name="products[${rowCount}][metric]" readonly>
            </td>
            <td>
                <select class="form-control lot-select" name="products[${rowCount}][lot_id]" required>
                    <option value="">Seleccionar lote</option>
                    <!-- Lotes se llenarán dinámicamente -->
                </select>
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
                            $(`#${rowId} .amount-output`).val(selectedLot.amount);
                        }, 100);
                    }
                }

                // Evento para cuando se selecciona un producto
                $(`#${rowId} .product-select`).change(function() {
                    const productId = $(this).val();
                    const selectedOption = $(this).find('option:selected');
                    const lotSelect = $(this).closest('tr').find('.lot-select');
                    const metricoutput = $(this).closest('tr').find('.metric-output');

                    // Actualizar la métrica
                    metricoutput.val(selectedOption.data('metric') || '-');
                    setLotStatus($(this).closest('tr'));

                    // Limpiar y cargar los lotes
                    lotSelect.empty().append('<option value="">Seleccionar lote</option>');

                    if (productId) {
                        const lots = selectedOption.data('lots') || [];
                        const unit = selectedOption.data('metric') || '';
                        lots.forEach(lot => {
                            lotSelect.append(
                                `<option value="${lot.id}" data-current-amount="${lot.current_amount}">
                            ${lot.registration_number} (Disp.: ${lot.current_amount} ${unit})
                        </option>`
                            );
                        });
                    }

                    validateOutputRow($(this).closest('tr'));
                });

                // Evento para cuando se selecciona un lote
                $(`#${rowId} .lot-select`).change(function() {
                    const selectedOption = $(this).find('option:selected');
                    const currentAmount = selectedOption.data('current-amount') || 0;
                    const amountoutput = $(this).closest('tr').find('.amount-output');
                    const row = $(this).closest('tr');

                    if (selectedOption.val()) {
                        setLotStatus(row, 'registered');
                        // Si se seleccionó un lote (no es NULL)
                        amountoutput.attr('max', currentAmount);
                    } else {
                        setLotStatus(row);
                        // Si se seleccionó NULL, quitar cualquier restricción
                        amountoutput.removeAttr('max');
                    }

                    validateOutputRow(row);
                });

                $(`#${rowId} .amount-output`).on('input change', function() {
                    validateOutputRow($(this).closest('tr'));
                });

                // Evento para actualizar movementProducts cuando cambian los valores
                $(`#${rowId} select, #${rowId} input`).change(function() {
                    updateMovementProducts();
                });
            }

            // Función para actualizar el array movementProducts
            function updateMovementProducts() {
                movementProducts = [];

                $('#products-container tr').each(function() {
                    const productId = $(this).find('.product-select').val();
                    const lotId = $(this).find('.lot-select').val();
                    const amount = $(this).find('.amount-output').val();

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

            window.stockOutputState = {
                updateMovementProducts,
                getMovementProducts: () => movementProducts,
                validateRows: function() {
                    let isValid = true;
                    let firstInvalid = null;
                    const lotTotals = {};
                    const lotRows = {};

                    $('#products-container tr').each(function() {
                        const row = $(this);
                        if (!validateOutputRow(row)) {
                            isValid = false;
                            firstInvalid = firstInvalid || row;
                        }

                        const lotId = row.find('.lot-select').val();
                        const amount = parseFloat(row.find('.amount-output').val());
                        const available = parseFloat(row.find('.lot-select option:selected').data('current-amount')) || 0;

                        if (lotId && !isNaN(amount) && amount > 0) {
                            lotTotals[lotId] = (lotTotals[lotId] || 0) + amount;
                            lotRows[lotId] = lotRows[lotId] || {
                                rows: [],
                                available,
                            };
                            lotRows[lotId].rows.push(row);
                        }
                    });

                    Object.keys(lotTotals).forEach(function(lotId) {
                        const total = lotTotals[lotId];
                        const available = lotRows[lotId].available;

                        if (total > available) {
                            isValid = false;

                            lotRows[lotId].rows.forEach(function(row) {
                                row.find('.amount-error').text(
                                    `La suma de este lote (${total}) supera el disponible (${available}).`
                                );
                                row.find('.amount-output').addClass('is-invalid');
                                firstInvalid = firstInvalid || row;
                            });
                        }
                    });

                    if (firstInvalid) {
                        firstInvalid.find('.is-invalid:first').focus();
                    }

                    return isValid;
                },
            };

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
            window.validateForm = function() {
                // Validar firma del almacenista (obligatoria)
                const warehouseSignature = $('#warehouse-signature').val();
                if (!warehouseSignature || warehouseSignature.trim() === '') {
                    alert('Error: La firma del almacenista es obligatoria para registrar la salida.');
                    return false;
                }

                // Actualizar el array movementProducts por última vez
                window.stockOutputState?.updateMovementProducts();
                const movementProducts = window.stockOutputState?.getMovementProducts() || [];

                // Validar que haya al menos un producto
                if (movementProducts.length === 0) {
                    alert('Debe agregar al menos un producto');
                    return false;
                }

                if (!window.stockOutputState?.validateRows()) {
                    alert('Revise las cantidades: deben ser mayores a 0 y no superar el disponible del lote.');
                    return false;
                }

                // Crear o actualizar el input hidden con los datos
                let $hiddenInput = $('input[name="products"]');
                if ($hiddenInput.length === 0) {
                    $hiddenInput = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'products')
                        .appendTo('#form-stock-entry');
                }

                // Convertir a JSON y asignar al input
                $hiddenInput.val(JSON.stringify(movementProducts));

                return confirm('¿Está seguro de registrar la salida?');
            };

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
