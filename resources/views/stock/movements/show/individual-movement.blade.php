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
        'title' => 'VISTA PREVIA DEL VOUCHER',
        'icon' => 'bi-file-pdf-fill',
        'iconColor' => 'text-danger',
        'backRoute' => route('stock.index'),
    ])

    <div class="container-fluid p-0">
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
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark">Salidas</span>
                        <span class="text-muted small">Partidas descontadas del almacén origen</span>
                    </div>
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th class="fw-bold" scope="col">Almacén afectado</th>
                                    <th class="fw-bold" scope="col">Producto</th>
                                    <th class="fw-bold" scope="col">Lote</th>
                                    <th class="fw-bold" scope="col">E/S</th>
                                    <th class="fw-bold" scope="col">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movement->warehouseProducts($movement->warehouse_id) as $mp)
                                    @php
                                        $isEntry = $mp->isEntry();
                                        $movementColorClass = $mp->movementColorClass();
                                    @endphp
                                    <tr>
                                        <th scope="row"> {{ $mp->warehouse->name }} </th>
                                        <td>{{ $mp->product->name }}</td>
                                        <td>{{ $mp->lot->registration_number ?? '-' }}</td>
                                        <td class="{{ $movementColorClass }} fw-bold">
                                            {{ $isEntry ? 'Entrada' : 'Salida' }}
                                            <div class="small text-muted fw-normal">{{ $mp->movement->name ?? '-' }}</div>
                                        </td>
                                        <td class="{{ $movementColorClass }} fw-bold">
                                            {{ number_format((float) $mp->amount, 2) }}
                                            <span class="text-muted fw-normal">{{ $mp->product->metric->value ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($movement->hasWarehouseProducts($movement->destination_warehouse_id))
                    <div class="d-flex align-items-center gap-2 mb-2 {{ $movement->hasWarehouseProducts($movement->warehouse_id) ? 'mt-3' : '' }}">
                        <span class="badge bg-success">Entradas</span>
                        <span class="text-muted small">Partidas agregadas al almacén destino</span>
                    </div>
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th class="fw-bold" scope="col">Almacén afectado</th>
                                    <th class="fw-bold" scope="col">Producto</th>
                                    <th class="fw-bold" scope="col">Lote</th>
                                    <th class="fw-bold" scope="col">E/S</th>
                                    <th class="fw-bold" scope="col">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movement->warehouseProducts($movement->destination_warehouse_id) as $mp)
                                    @php
                                        $isEntry = $mp->isEntry();
                                        $movementColorClass = $mp->movementColorClass();
                                    @endphp
                                    <tr>
                                        <th scope="row"> {{ $mp->warehouse->name ?? '' }} </th>
                                        <td>{{ $mp->product->name }}</td>
                                        <td>{{ $mp->lot->registration_number }}</td>
                                        <td class="{{ $movementColorClass }} fw-bold">
                                            {{ $isEntry ? 'Entrada' : 'Salida' }}
                                            <div class="small text-muted fw-normal">{{ $mp->movement->name ?? '-' }}</div>
                                        </td>
                                        <td class="{{ $movementColorClass }} fw-bold">
                                            {{ number_format((float) $mp->amount, 2) }}
                                            <span class="text-muted fw-normal">{{ $mp->product->metric->value ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="signature-section p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                    <div>
                        <div class="fw-bold fs-5">Firmas del movimiento</div>
                        <div class="text-muted small">Validación del almacenista y confirmación del técnico/receptor.</div>
                    </div>
                    <span class="badge text-bg-light border">Voucher #{{ $movement->id }}</span>
                </div>

                <div class="row g-3">
                    <!-- Firma del Almacenista (Solo Lectura) -->
                    <div class="col-lg-6 col-12">
                        <div class="signature-card">
                            <div class="signature-card-header">
                                <h5 class="signature-title">
                                    <span class="signature-icon"><i class="fas fa-user-shield"></i></span>
                                    Almacenista
                                </h5>
                                <span class="badge text-bg-secondary">Solo lectura</span>
                            </div>
                            <div class="p-3">
                                <div class="signature-stage mb-3">
                                    @if ($movement->warehouse_signature)
                                        <img src="{{ $movement->warehouse_signature }}" alt="Firma del almacenista">
                                    @else
                                        <div class="signature-placeholder">
                                            <i class="bi bi-file-earmark-x d-block fs-2 mb-2"></i>
                                            No hay firma registrada
                                        </div>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    Esta firma se conserva como evidencia original del movimiento.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Firma del Técnico/Receptor (Editable) -->
                    <div class="col-lg-6 col-12">
                        <div class="signature-card">
                            <div class="signature-card-header">
                                <h5 class="signature-title">
                                    <span class="signature-icon"><i class="fas fa-user-tie"></i></span>
                                    Técnico / receptor
                                </h5>
                                <span class="badge text-bg-success">Editable</span>
                            </div>
                            <div class="p-3">
                                <div class="signature-toolbar mb-3">
                                    <div class="signature-file">
                                        <label for="tecnicoFileInput" class="form-label small fw-semibold mb-1">Imagen de firma</label>
                                        <input type="file" id="tecnicoFileInput" class="form-control form-control-sm" accept="image/*">
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de firma">
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
                                    <img id="tecnicoSignatureImg" src="{{ $movement->technician_signature }}"
                                        class="{{ $movement->technician_signature ? '' : 'd-none' }}" alt="Firma del técnico/receptor">
                                    <canvas id="tecnicoCanvas" class="d-none"></canvas>
                                    <div id="tecnicoPlaceholder"
                                        class="signature-placeholder {{ $movement->technician_signature ? 'd-none' : '' }}">
                                        <i class="bi bi-pen d-block fs-2 mb-2"></i>
                                        Seleccione una imagen o dibuje la firma
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <small id="tecnicoSignatureStatus" class="text-muted">
                                        {{ $movement->technician_signature ? 'Firma cargada.' : 'Firma pendiente.' }}
                                    </small>
                                    <button type="button" id="saveTecnico" class="btn btn-success btn-sm">
                                        <i class="bi bi-check2-circle"></i> Aplicar firma
                                    </button>
                                </div>
                                <div id="tecnicoSignatureAlert" class="alert alert-success alert-dismissible fade d-none mt-3 mb-0"
                                    role="alert">
                                    <i class="bi bi-check-circle-fill"></i> Firma guardada correctamente.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Cerrar"></button>
                                </div>
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
                const statusText = $(`#${prefix}SignatureStatus`);
                const alertBox = $(`#${prefix}SignatureAlert`);
                const signatureField = $(prefix == 'tecnico' ? '#technician_signature' : '#warehouse_signature');
                const signatureUpdateUrl = "{{ route('stock.movement.signature.update', ['id' => $movement->id]) }}";

                let isDrawing = false;
                let hasDrawing = false;
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
                    canvas[0].height = container.height();
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
                    hasDrawing = true;
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
                    hasDrawing = true;
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
                            statusText.removeClass('text-danger text-muted').addClass('text-success')
                                .text('Imagen seleccionada. Aplica la firma para guardarla.');
                        };
                        reader.readAsDataURL(file);
                    }
                });

                function persistSignature(imageData) {
                    if (prefix !== 'tecnico') {
                        signatureField.val(imageData);
                        return;
                    }

                    saveBtn.prop('disabled', true);
                    alertBox.addClass('d-none').removeClass('show');
                    statusText.removeClass('text-danger text-success text-muted').addClass('text-muted')
                        .text('Guardando firma...');

                    $.ajax({
                        url: signatureUpdateUrl,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            technician_signature: imageData
                        },
                        success: function() {
                            signatureField.val(imageData);
                            statusText.removeClass('text-muted text-danger').addClass('text-success')
                                .text('Firma guardada correctamente.');
                            alertBox.removeClass('d-none').addClass('show');
                            setTimeout(function() {
                                alertBox.removeClass('show').addClass('d-none');
                            }, 3500);
                        },
                        error: function() {
                            statusText.removeClass('text-muted text-success').addClass('text-danger')
                                .text('No se pudo guardar la firma. Intenta nuevamente.');
                        },
                        complete: function() {
                            saveBtn.prop('disabled', false);
                        }
                    });
                }

                // Botón de dibujar
                drawBtn.on('click', function(e) {
                    e.preventDefault();
                    canvas.removeClass('d-none');
                    signatureImg.addClass('d-none');
                    placeholder.addClass('d-none');
                    fileInput.val('');
                    hasDrawing = false;
                    ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);
                    statusText.removeClass('text-danger text-success').addClass('text-muted')
                        .text('Modo dibujo activo. Traza la firma dentro del recuadro.');
                });

                // Botón de limpiar
                clearBtn.on('click', function(e) {
                    e.preventDefault();
                    ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);
                    signatureImg.addClass('d-none').attr('src', '#');
                    canvas.addClass('d-none');
                    placeholder.removeClass('d-none');
                    fileInput.val('');
                    hasDrawing = false;
                    statusText.removeClass('text-success text-danger').addClass('text-muted')
                        .text('Firma pendiente.');
                });

                // Botón de guardar
                saveBtn.on('click', function(e) {
                    e.preventDefault();
                    if (!signatureImg.hasClass('d-none')) {
                        // La firma es una imagen cargada
                        const imageData = signatureImg.attr('src');
                        persistSignature(imageData);
                    } else if (!canvas.hasClass('d-none')) {
                        if (!hasDrawing) {
                            statusText.removeClass('text-muted text-success').addClass('text-danger')
                                .text('Dibuja la firma antes de aplicarla.');
                            return;
                        }

                        // La firma es dibujada
                        const dataURL = canvas[0].toDataURL('image/png');
                        signatureImg.attr('src', dataURL).removeClass('d-none');
                        canvas.addClass('d-none');
                        persistSignature(dataURL);

                    } else {
                        statusText.removeClass('text-muted text-success').addClass('text-danger')
                            .text('Sube una imagen o dibuja la firma antes de aplicarla.');
                    }
                });
            }

            // Configurar solo la sección de firma del técnico (la del almacenista es solo lectura)
            setupSignatureSection('tecnico', '#198754');
        });
    </script>
@endsection
