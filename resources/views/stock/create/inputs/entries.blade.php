@extends('layouts.app')
@section('content')
    <style>
        .table-scroll-container {
            max-height: 100vh;
            overflow-y: auto;
            border: 1px solid #dee2e6;
        }
    </style>

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('stock.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                MOVIMIENTO DE ENTRADA <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $warehouse->name }}</span>
            </span>
        </div>

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
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Unidades</th>
                                    <th>Lote</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="products-container">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <!-- Firma del Almacenista -->
                <div class="col-lg-6 col-12 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Firma del almacenista <span class="text-warning">*</span></h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <label for="almacenistaFileInput" class="form-label small text-muted">Subir imagen de
                                    firma</label>
                                <input type="file" id="almacenistaFileInput" class="form-control form-control-sm"
                                    accept="image/*">
                            </div>

                            <div class="mb-3 position-relative bg-light rounded d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <img id="almacenistaSignatureImg" src=""
                                    class="img-fluid d-none w-100 h-100 object-fit-contain border rounded">
                                <canvas id="almacenistaCanvas"
                                    class="position-absolute w-100 h-100 border border-2 border-primary rounded d-none"
                                    style="background-color: #f8f9fa; cursor: crosshair;"></canvas>
                                <div id="almacenistaPlaceholder"
                                    class="text-muted">Seleccione o
                                    dibuje su firma</div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-auto">
                                <div>
                                    <button type="button" id="clearAlmacenista"
                                        class="btn btn-outline-danger btn-sm me-2" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Limpiar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <button type="button" id="drawAlmacenista" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Dibujar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                </div>
                                <button type="button" id="saveAlmacenista" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Firma del Técnico/Receptor -->
                <div class="col-lg-6 col-12 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Firma del técnico/receptor</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3">
                                <label for="tecnicoFileInput" class="form-label small text-muted">Subir imagen de
                                    firma</label>
                                <input type="file" id="tecnicoFileInput" class="form-control form-control-sm"
                                    accept="image/*">
                            </div>

                            <div class="mb-3 position-relative bg-light rounded d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <img id="tecnicoSignatureImg" src=""
                                    class="img-fluid d-none w-100 h-100 object-fit-contain border rounded">
                                <canvas id="tecnicoCanvas"
                                    class="position-absolute w-100 h-100 border border-2 border-success rounded d-none"
                                    style="background-color: #f8f9fa; cursor: crosshair;"></canvas>
                                <div id="tecnicoPlaceholder"
                                    class="text-muted">Seleccione o
                                    dibuje su firma</div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-auto">
                                <div>
                                    <button type="button" id="clearTecnico" class="btn btn-outline-danger btn-sm me-2"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Limpiar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <button type="button" id="drawTecnico" class="btn btn-outline-success btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Dibujar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                </div>
                                <button type="button" id="saveTecnico" class="btn btn-success btn-sm">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </button>
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
    </div>

    <script>
        $(document).ready(function() {
            // Datos precargados desde el backend
            const productsData = @json($products_data);
            let movementProducts = []; // Array para almacenar los productos del movimiento
            let rowCount = 0;

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
                <select class="form-control lot-select" name="products[${rowCount}][lot_id]">
                    <option value="">Sin lote (0.00)</option>
                    <!-- Lotes se llenarán dinámicamente -->
                </select>
                <small class="form-text text-muted null-lot-message" style="display:none;">
                    Este producto permite registro sin lote
                </small>
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

                    // Limpiar y cargar los lotes
                    lotSelect.empty().append('<option value="">Sin lote (0.00)</option>');

                    if (productId) {
                        const lots = selectedOption.data('lots') || [];
                        lots.forEach(lot => {
                            lotSelect.append(
                                `<option value="${lot.id}" data-current-amount="${lot.current_amount}">
                            ${lot.registration_number} (Disponible: ${lot.current_amount})
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

                    if (selectedOption.val()) {
                        // Si se seleccionó un lote (no es NULL)
                        /*amountInput.attr('max', currentAmount);

                        if (parseInt(amountInput.val()) > currentAmount) {
                            amountInput.val(currentAmount);
                        }*/
                    } else {
                        // Si se seleccionó NULL, quitar cualquier restricción
                        amountInput.removeAttr('max');
                    }
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