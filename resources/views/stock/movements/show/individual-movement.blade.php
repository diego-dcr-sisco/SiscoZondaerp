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
                VISTA PREVIA DEL VOUCHER
            </span>
        </div>

        <form class="m-3" id="form-stock-entry" action="{{ route('stock.movement.update', ['id' => $movement->id]) }}"
            method="POST">
            @csrf
            <div class="border rounded shadow p-3 mb-3">
                <div class="fw-bold mb-2 fs-5">Datos del movimiento</div>
                <div class="row">
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-movement" class="form-label is-required">Tipo de
                            movimiento</label>
                        <input type="hidden" id="movement" value="{{ $movement->movement_id }}" required>
                        <input type="text" class="form-control px-2 rounded" id="movement-name"
                            value="{{ $movement->movement->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-origin-warehouse" class="form-label is-required">Almacén de
                            origen</label>
                        <input type="hidden" id="warehouse" value="{{ $movement->warehouse_id }}" required>
                        <input type="text" class="form-control px-2 rounded" id="warehouse-text"
                            value="{{ $movement->warehouse->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-destination-warehouse-text" class="form-label">Almacén
                            destino</label>
                        <input type="hidden" id="movement" value="{{ $movement->destination_warehouse_id }}" required>
                        <input type="text" class="form-control px-2 rounded" id="destination-warehouse-text"
                            value="{{ $movement->destinationWarehouse->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="output-date" class="form-label is-required">Fecha y tiempo</label>
                        <div class="input-group mb-3">
                            <input type="date" class="form-control" id="date" value="{{ $movement->date }}"
                                readonly>
                            <input type="time" class="form-control" id="time" value="{{ $movement->time }}"
                                readonly>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="observations" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3"
                            placeholder="Ingrese detalles sobre el traspaso, motivo, condiciones o instrucciones especiales.">{{ $movement->observations }}</textarea>
                    </div>
                </div>
            </div>

            <div class="border rounded shadow p-3 mb-3">
                <div class="fw-bold mb-2 fs-5">Productos</div>

                @if ($movement->hasWarehouseProducts($movement->warehouse_id))
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th class="fw-bold" scope="col">Almacen origen</th>
                                    <th class="fw-bold" scope="col">Producto</th>
                                    <th class="fw-bold" scope="col">Lote</th>
                                    <th class="fw-bold" scope="col">Movimiento</th>
                                    <th class="fw-bold" scope="col">Cantidad del movimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movement->warehouseProducts($movement->warehouse_id) as $mp)
                                    <tr>
                                        <th scope="row"> {{ $mp->warehouse->name }} </th>
                                        <td>{{ $mp->product->name }}</td>
                                        <td>{{ $mp->lot->registration_number ?? '-' }}</td>
                                        <td
                                            class="{{ $mp->movement && $mp->movement->type == 'in' ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ $mp->movement->name ?? '-' }}</td>
                                        <td
                                            class="{{ $mp->movement && $mp->movement->type == 'in' ? 'text-success' : 'text-danger' }}">
                                            {{ $mp->amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($movement->hasWarehouseProducts($movement->destination_warehouse_id))
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th class="fw-bold" scope="col">Almacen origen</th>
                                    <th class="fw-bold" scope="col">Producto</th>
                                    <th class="fw-bold" scope="col">Lote</th>
                                    <th class="fw-bold" scope="col">Movimiento</th>
                                    <th class="fw-bold" scope="col">Cantidad del movimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movement->warehouseProducts($movement->destination_warehouse_id) as $mp)
                                    <tr>
                                        <th scope="row"> {{ $mp->warehouse->name ?? '' }} </th>
                                        <td>{{ $mp->product->name }}</td>
                                        <td>{{ $mp->lot->registration_number }}</td>
                                        <td
                                            class="{{ $mp->movement && $mp->movement->type == 'in' ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ $mp->movement->name ?? '-' }}</td>
                                        <td class="text-success">{{ $mp->amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="row justify-content-center">
                <!-- Firma del Almacenista (Solo Lectura) -->
                <div class="col-lg-6 col-12 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Firma del almacenista</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3 position-relative bg-light rounded d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                @if ($movement->warehouse_signature)
                                    <img src="{{ $movement->warehouse_signature }}"
                                        class="img-fluid w-100 h-100 object-fit-contain border rounded">
                                @else
                                    <div class="text-muted">No hay firma registrada</div>
                                @endif
                            </div>
                            <div class="text-center">
                                <small class="text-muted">Firma del almacenista (solo lectura)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Firma del Técnico/Receptor (Editable) -->
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
                                <img id="tecnicoSignatureImg" src="{{ $movement->technician_signature }}"
                                    class="img-fluid {{ $movement->technician_signature ? '' : 'd-none' }} w-100 h-100 object-fit-contain border rounded">
                                <canvas id="tecnicoCanvas"
                                    class="position-absolute w-100 h-100 border border-2 border-success rounded d-none"
                                    style="background-color: #f8f9fa; cursor: crosshair;"></canvas>
                                <div id="tecnicoPlaceholder"
                                    class="text-muted {{ $movement->technician_signature ? 'd-none' : '' }}">Seleccione o
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
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="warehouse_signature" name="warehouse_signature" value="{{ $movement->warehouse_signature }}" readonly />
            <input type="hidden" id="technician_signature" name="technician_signature" value="{{ $movement->technician_signature }}" />

            <button type="submit" class="btn btn-primary me-2 mb-3">
               <i class="bi bi-pencil"></i> Actualizar
            </button>

            <a href="{{ route('stock.voucherPdfPreview', ['id' => $movement->id]) }}" class="btn btn-dark mb-3" target="_blank">
                Generar voucher
            </a>
        </form>
    </div>

    <script>
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
                            prefix == 'tecnico' ? `#technician_signature` : `#warehouse_signature`
                        ).val(imageData);
                    } else if (!canvas.hasClass('d-none')) {
                        // La firma es dibujada
                        const dataURL = canvas[0].toDataURL('image/png');
                        signatureImg.attr('src', dataURL).removeClass('d-none');
                        canvas.addClass('d-none');
                        console.log('Firma guardada (dibujo):', dataURL);
                        alert('Firma del ' + prefix + ' guardada como dibujo');
                        $(
                            prefix == 'tecnico' ? `#technician_signature` : `#warehouse_signature`
                        ).val(dataURL);

                    } else {
                        alert('Por favor, sube una imagen o dibuja tu firma primero.');
                    }
                });
            }

            // Configurar solo la sección de firma del técnico (la del almacenista es solo lectura)
            setupSignatureSection('tecnico', '#198754');
        });
    </script>
@endsection
