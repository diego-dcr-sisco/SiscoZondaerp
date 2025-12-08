@extends('layouts.app')
@section('content')
    <style>
        #zoom-image {
            user-select: none;
        }

        #zoom-wrapper {
            width: 100%;
            height: 100%;
        }

        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }
    </style>

    @php
        $areaNames = [];
        $pointNames = [];
        $productNames = [];
    @endphp
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.show.sede.floorplans', ['id' => $floorplan->customer_id]) }}"
                class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                PLANO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $floorplan->filename }}</span>
            </span>
        </div>

        @if (!$floorplan->service)
            <div class="alert alert-danger alert-dismissible fade show m-2" role="alert">
                Por favor, selecciona un servicio para continuar con la configuración. NO se podra crear o actualizar la
                configuraion del plano
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </div>
        @endif

        <div class="m-3">
            @csrf
            <div class="row">
                <div class="col-6">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="fw-bold mb-2 fs-5">Seleccionar una version</div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Versión del plano: </label>
                                <div class="input-group">
                                    <select class="form-select " id="version" name="version">
                                        <option value=""> Sin version </option>
                                        @foreach ($floorplan->versions()->latest()->get() as $floorVersion)
                                            <option value="{{ $floorVersion->version }}"
                                                {{ $floorVersion->version == $f_version?->version ? 'selected' : '' }}>
                                                {{ $floorVersion->version }} -
                                                ({{ $floorVersion->updated_at }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-success" type="button" onclick="searchVersion()">Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="fw-bold mb-2 fs-5">Exportar plano</div>

                        <!-- Leyenda informativa -->
                        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Importante:</strong> Antes de exportar, asegúrate de haber guardado todos los cambios
                            realizados en el plano. La exportación se realizará sobre la versión seleccionada.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <button type="button" class="btn btn-success btn-sm" onclick="captureCanvas()">
                            <i class="bi bi-image"></i> Generar archivo
                        </button>

                        <!-- Información adicional sobre la versión actual -->
                        <div class="mt-2 p-2 bg-light rounded">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Versión actual: <strong>{{ $f_version->version ?? '---' }}</strong> |
                                Última actualización: {{ $f_version?->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>

                        <div id="image-preview" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <form action="{{ route('floorplan.update.devices', ['id' => $floorplan->id]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="border rounded shadow p-3 mb-3">
                    <div class="fw-bold mb-2 fs-5">Generar dispositivos</div>
                    <div class="row">
                        <div class="col-lg-6 col-12 mb-3">
                            <label class="form-label">Versión seleccionada: </label>
                            @if ($f_version)
                                <div class="input-group">
                                    <input type="number" class="form-control" name="version"
                                        value="{{ $f_version?->version }}" disabled>
                                    <input type="date" class="form-control" name="version_updated_at"
                                        value="{{ $f_version?->updated_at->format('Y-m-d') }}">
                                </div>
                            @else
                                <input type="text" class="form-control" value="Sin versión" disabled>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-3">
                            <label class="form-label">Servicio: </label>
                            <input type="text" class="form-control" value="{{ $floorplan->service->name ?? '' }}"
                                disabled>
                        </div>

                        <div class="col-lg-4 col-12 mb-3">
                            <label class="form-label">
                                Área de aplicación
                            </label>
                            @if (!$customer->applicationAreas->isEmpty())
                                <select class="form-select" id="area" name="area">
                                    @foreach ($customer->applicationAreas as $area)
                                        @php
                                            $areaNames[] = [
                                                'id' => $area->id,
                                                'name' => $area->name,
                                            ];
                                        @endphp
                                        <option value="{{ $area->id }}">
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-control" value="Sin zonas o áreas disponibles"
                                    id="area" disabled>
                            @endif
                        </div>

                        <div class="col-lg-4 col-12 mb-3">
                            <label class="form-label">Puntos de control
                                asociados:
                            </label>
                            <select class="form-select " id="control-points" name="control_points">
                                @foreach ($ctrlPoints as $point)
                                    @php
                                        $pointNames[] = [
                                            'id' => $point->id,
                                            'name' => $point->name,
                                        ];
                                    @endphp
                                    <option value="{{ $point->id }}">
                                        {{ $point->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4 col-12 mb-3">
                            <label class="form-label">
                                Rango:</label>
                            <div class="input-group">
                                <input class="form-control" id="min-range" type="number" placeholder="Mín"
                                    min="0" />
                                <input class="form-control" id="max-range" type="number" placeholder="Máx"
                                    min="0" />
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="generate-points"
                        onclick="generatePoints()">{{ __('buttons.generate') }}</button>
                </div>

                <div class="border rounded shadow p-3 mb-3">
                    <div class="fw-bold mb-2 fs-5">Simbologia</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead>
                                <tr>
                                    <th class="col-1" scope="col">Color</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Código</th>
                                    <th class="col-2" scope="col">Área</th>
                                    <th class="col-2" scope="col">Producto</th>
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Rangos</th>
                                </tr>
                            </thead>
                            <tbody id="table-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="border rounded shadow p-3 mb-3">
                    <div class="fw-bold mb-2 fs-5">Layout (Plano) dinamico</div>
                    <div class="row">
                        <div class="col-12">
                            <div class="border border-dark rounded bg-info-subtle p-2 mb-3">
                                <span class="fw-bold" id="count-points">Puntos generados: 0</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded bg-secondary-subtle p-2 mb-3">
                                <p class="fw-bold mb-1 fs-5 border">INSTRUCCIONES DE USO</p>
                                <ul>
                                    <li><strong>ZOOM</strong>: <strong>[+]</strong> para acercar, <strong>[-]</strong>
                                        para
                                        alejar.
                                    </li>
                                    <li><strong>MOVER PLANO</strong>: Pulsa la tecla <strong>Alt</strong>, haz click
                                        sobre
                                        el
                                        plano
                                        y
                                        <strong>arrastra</strong>.
                                    </li>
                                    <li><strong>EDITAR PUNTO</strong>: Seleccionalo y pulsa la tecla <strong>E</strong>
                                        o
                                        <strong>e</strong>.
                                    </li>
                                    <li><strong>ELIMINAR PUNTO</strong>: Seleccionalo y pulsa la tecla
                                        <strong>D</strong> o
                                        <strong>d</strong>.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 p-2 border rounded bg-secondary-subtle">
                        <div class="form-check">
                            <input class="form-check-input border-dark" type="checkbox" id="create-version"
                                name="create_version" {{ count($floorplan->versions) > 0 ? '' : 'checked' }}>
                            <label class="form-check-label fw-bold ms-1" for="create-version">
                                Nueva versión
                            </label>
                        </div>
                        <small class="d-block mt-1">Al marcar esta opción se generará una nueva revisión del
                            plano</small>
                    </div>
                    <div class="row">
                        <div class="col-auto mb-1">
                            <div class="input-group input-group-sm">
                                <button type="button" class="btn btn-success" id="zoomIn"><i
                                        class="bi bi-plus-lg"></i></button>
                                <span class="input-group-text">Zoom</span>
                                <button type="button" class="btn btn-danger" id="zoomOut"><i
                                        class="bi bi-dash-lg"></i></button>
                            </div>
                        </div>
                        <div class="col-auto mb-1">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="point-label">Tamaño del punto</span>
                                <select class="form-select" id="point-size" name="point_size">
                                    <option value="6">Muy Pequeño (6px)</option>
                                    <option value="8">Pequeño (8px)</option>
                                    <option value="10" selected>Normal (10px)</option>
                                    <option value="12">Grande (12px)</option>
                                    <option value="16">Muy Grande (16px)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="canvas-container" style="position: relative; overflow: auto;">
                        <canvas class="border-1 border-dark rounded" id="myCanvas"></canvas>
                    </div>
                    <input type="hidden" id="points" name="points" value="">
                </div>

                @if ($floorplan->service)
                    <button type="submit" class="btn btn-primary my-3 me-2" onclick="return submitForm();">
                        {{ __('buttons.update') }}
                    </button>
                @endif
            </form>

            <a id="download-link" style="display: none;"></a>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="pointModal" tabindex="-1" aria-labelledby="pointModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pointModalLabel">Información del Punto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Revisiones: </label>
                        <ul class="list-group list-group list-group-numbered" id="reviews"></ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label is-required">Punto(s) de control
                        </label>
                        <select class="form-select " id="update-control-point" name="update_control_point">
                            @foreach ($ctrlPoints as $point)
                                <option value="{{ $point->id }}">
                                    {{ $point->name }} ({{ $point->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label is-required"> rea </label>
                        <select class="form-select " id="update-area" name="update_area">
                            @foreach ($customer->applicationAreas as $area)
                                <option value="{{ $area->id }}">
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label is-required"> Producto asociado </label>
                        <select class="form-select " id="update-product" name="update_product">
                            <option value="" selected>
                                Sin producto
                            </option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" id="point-index" value="" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                        onclick="setDevice()">{{ __('buttons.store') }}</button>
                    <button type="button" class="btn btn-danger"
                        onclick="deleteDevice()">{{ __('buttons.delete') }}</button>
                </div>
            </div>
        </div>
    </div>



    @if (!$floorplan->service_id == null)
        <script>
            let points = [];
            let zoomLevel = count = 1;
            let index = countPoints = 0;
            let isDragging = hasPoints = false;
            let lastX, lastY;

            const zoomFactor = 0.2;
            const maxZoom = 3;
            const minZoom = 0.5;

            const imgURL = "{{ route('image.show', ['path' => $floorplan->path]) }}";

            var data = @json($ctrlPoints);
            var devices = @json($devices);
            var nplans = @json($nplans);
            var pointNames = @json($pointNames);
            var areaNames = @json($areaNames);
            var productNames = @json($productNames);
            var reviews = @json($reviews);
            var img_sizes = @json($img_sizes);
            var legendPDF = @json($legend);
            var printData = @json($print_data);

            let currentPointSize = 10;
            let currentBase64 = '';
            let can_resize = @json($can_resize);


            const img = new Image();
            img.src = imgURL;

            //const img_scale = ~~(window.innerWidth / img_sizes[0]);
            const img_scale = Math.round(1080 / img_sizes[0]);

            var canvas = new fabric.Canvas('myCanvas', {
                //width: img_sizes[0] > img_sizes[1] ? 1100 : 800,
                //height: img_sizes[0] > img_sizes[1] ? 800 : 1100,
                width: img_sizes[0] * img_scale,
                height: img_sizes[1] * img_scale,
                selection: false,
            });


            $(document).ready(function() {
                resetInputs();
                setDevices();
                if (can_resize) {
                    resizePointsToNewCanvas();
                }
            });

            function resetInputs() {
                $('#min-range').val(0);
                $('#max-range').val(0);
                $('#count-points').text(`Puntos generados: 0`);
            }

            function sortPoints() {
                points = points
                    .sort((a, b) => a.count - b.count);
                //.map((point, index) => ({ ...point, index: index }));
            }

            function submitForm() {
                var element = $('#create-version');
                element.val(Boolean(element.is(':checked')));

                var message = element.is(':checked') ?
                    'Se creará una nueva versión del plano.' :
                    'Los dispositivos se actualizarán utilizando la versión actual del plano.';

                if (confirm(message)) {
                    if (points.length > 0) {
                        sortPoints();
                        $('#points').val(JSON.stringify(points));
                    }
                    $('#create-version').prop('disabled', false);
                    return true;
                }
                return false;
            }

            function findColor(id) {
                var foundObject = []
                if (points.some(obj => obj.pointID == id)) {
                    foundObject = points.find(obj => obj.pointID == id);
                } else {
                    foundObject = data.find(obj => obj.id == id);
                }
                return foundObject ? foundObject.color : null;
            }

            function findPointName(id) {
                return pointNames.find(obj => obj.id == id)?.name ?? null;
            }

            function findAreaName(id) {
                return points.find(obj => obj.pointID == id)?.areaID ?
                    areaNames.find(obj => obj.id == points.find(obj => obj.pointID == id).areaID)?.name ?? null :
                    null;
            }

            function findZone(id) {
                return points.find(item => item.pointID == id)?.areaID ?
                    areaNames.find(item => item.id == points.find(item => item.pointID == id)?.areaID)?.name ?? '' :
                    '';
            }

            function findProduct(point_id, area_id) {
                const foundPoint = points.find(item => item.pointID == point_id && item.areaID == area_id);
                return foundPoint.productID ?? false;
            }

            function findArea(id) {
                const foundPoint = points.find(item => item.pointID == id);
                return foundPoint.areaID ?? false;
            }

            function findCode(id) {
                return data.find(item => item.id == id)?.code ?
                    data.find(item => item.id == id).code : 'Sin código';
            }

            function deleteDevice() {
                const i = parseInt($('#point-index').val());
                const point_index = points.findIndex(item => item.index == i);
                const reviewList = points[i] && reviews[point_index] ? reviews[point_index][points[i].point_id] : [];
                var aux = count;
                var message = reviewList && reviewList.length > 0 ?
                    'La existencia de revisiones en el dispositivo exige la creación obligatoria de una nueva versión, ' : '';

                if (i != -1) {
                    message += '¿Deseas eliminar el dispositivo?';
                    if (reviewList && reviewList.length > 0) {
                        $('#create-version').val(1);
                        $('#create-version').prop('checked', true).prop('disabled', true);
                    }
                    if (confirm(message)) {
                        points = points.filter(item => item.index != i);
                        nplans = points.map(item => item.count);

                        canvas.getObjects().forEach(obj => {
                            if (obj.type == 'group') {
                                const metadata = obj.metadata;
                                if (metadata && metadata.index == i) {
                                    canvas.remove(obj);
                                }
                            }
                        });

                        canvas.renderAll();
                        createLegend();

                        //countPoints++;
                        //count = --aux;
                        //$('#count-points').text(`Puntos generados: ${countPoints}`);
                    }
                }
                $('#pointModal').modal('hide');
            }

            function addPoint(x, y, pointId, areaId, productId, color, code) {
                x = parseFloat(x);
                y = parseFloat(y);
                const point = new fabric.Circle({
                    left: x, // Posición absoluta inicial (ajustaremos el grupo luego)
                    top: y,
                    radius: currentPointSize,
                    fill: color || 'black',
                    selectable: true,
                    hasControls: false,
                    hasBorders: false,
                    originX: 'center', // Importante: El círculo se posiciona desde su centro
                    originY: 'center',
                });

                const baseFontSize = 10; // Tamaño base para texto
                const pointText = new fabric.Text(`${count}`, {
                    fontSize: baseFontSize * (currentPointSize / 8),
                    fill: getContrastColor(color || '#fff'),
                    selectable: false,
                    fontWeight: 'bold',
                    fontFamily: 'Courier New',
                    originX: 'center', // Centrado horizontal
                    originY: 'center', // Centrado vertical
                });

                // Calculamos el tamaño del texto (Fabric.js necesita renderizar primero)
                const textWidth = pointText.getBoundingRect().width;
                const textHeight = pointText.getBoundingRect().height;

                // Posición del texto (centrado sobre el punto)
                pointText.set({
                    left: x,
                    top: y,
                });

                // Creamos el grupo (ajustando posición absoluta)
                const pointGroup = new fabric.Group([point, pointText], {
                    left: x,
                    top: y,
                    originX: 'center',
                    originY: 'center',
                    metadata: {
                        index: index
                    },
                });

                // Añadimos el grupo al canvas
                canvas.add(pointGroup);

                // Resto del código (eventos, array `points`, etc.)
                var newPoint = {
                    index: index,
                    point_id: pointId,
                    area_id: areaId,
                    product_id: productId,
                    color: color,
                    code: code,
                    x: x,
                    y: y,
                    img_tamx: 0,
                    img_tamy: 0,
                    count: count,
                    size: currentPointSize
                };

                points.push(newPoint);

                if (!nplans.includes(count)) {
                    nplans.push(count);
                }

                pointGroup.on('mousedown', function(event) {
                    if (event.e.detail == 1) {
                        const i = points.findIndex(p => p.code == code);
                        if (i != -1) {
                            $('#point-index').val(points[i].index);
                        }
                    }
                });

                pointGroup.on('moving', function(event) {
                    const i = points.findIndex(p => p.index == newPoint.index);
                    if (i != -1) {
                        points[i].x = pointGroup.left;
                        points[i].y = pointGroup.top;
                    }
                });

                index = points.length;
                count++;
            }

            function getContrastColor(hexColor) {
                // Convierte color HEX a RGB
                const r = parseInt(hexColor.substr(1, 2), 16) / 255;
                const g = parseInt(hexColor.substr(3, 2), 16) / 255;
                const b = parseInt(hexColor.substr(5, 2), 16) / 255;

                // Fórmula de luminancia (estándar WCAG)
                const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;

                // Retorna blanco para fondos oscuros, negro para claros
                return luminance > 0.5 ? '#000000' : '#FFFFFF';
            }

            function inRange(min, max) {
                return !nplans.some(i => i >= min && i <= max);
            }

            function generateRanges(array) {
                array.sort((a, b) => a - b); // Ordenar los números
                const ranges = [];
                let start = array[0];
                let end = array[0];

                for (let i = 1; i < array.length; i++) {
                    if (array[i] == end + 1) {
                        end = array[i];
                    } else {
                        ranges.push(start == end ? `${start}` : `${start}-${end}`);
                        start = array[i];
                        end = array[i];
                    }
                }
                ranges.push(start == end ? `${start}` : `${start}-${end}`);
                return ranges.join(', ');
            }

            function generatePoints() {
                const min_range = parseInt($('#min-range').val());
                const max_range = parseInt($('#max-range').val());
                const area_id = parseInt($('#area').val());
                const control_point_id = parseInt($('#control-points').val());

                if (min_range <= 0 && max_range <= 0) {
                    alert('Ambos valores deben ser mayores a 0')
                    return
                }

                if (!area_id) {
                    alert('Debes seleccionar una zona o área de la empresa');
                    return;
                }

                if (!control_point_id) {
                    alert('Debes seleccionar un tipo de punto de control');
                    return;
                }

                var type_point_name = pointNames.find(pn => pn.id == control_point_id).name ?? '';

                if (!inRange(min_range, max_range)) {
                    countPoints = 0;
                    var ranges = generateRanges(nplans);
                    $('#count-points').text(`Puntos generados: ${countPoints} ${type_point_name}`);
                    alert(`No se permiten valores entre: ${ranges}`);
                    return
                } else {
                    countPoints = max_range - min_range + 1;
                    count = min_range;
                    $('#count-points').text(`Puntos generados: ${countPoints} ${type_point_name}`);
                }
            }

            function editDevice() {
                const point_index = $('#point-index').val();
                const i = points.findIndex(item => item.index == point_index);
                if (i != -1) {
                    var html = '';
                    $('#pointModalLabel').text(points[i].code);
                    $('#update-control-point').val(points[i].point_id);
                    $('#update-area').val(points[i].area_id);
                    $('#update-product').val(points[i].product_id);
                    $('#point-index').val(points[i].index);

                    const reviewList = reviews[points[i].index] ? reviews[points[i].index][points[i].point_id] : [];

                    html = reviewList && reviewList.length > 0 ?
                        reviewList.map(review =>
                            `<li class="list-group-item">(${review.updated_at}) <strong>${review.answer}</strong></li>`).join(
                            '') :
                        `<li class="list-group-item text-danger fw-bold">Sin revisiones</li>`;


                    $('#reviews').html(html);

                    if (confirm(`Estas seguro de editar el punto: ${points[i].code} (${points[i].index})`)) {
                        $('#pointModal').modal('show');
                    }
                }
            }

            fabric.Image.fromURL(imgURL, function(img) {
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: canvas.width / img.width,
                    scaleY: canvas.height / img.height
                });
            });

            canvas.on('mouse:dblclick', function(event) {
                const pointer = canvas.getPointer(event.e);
                const point_id = parseInt($('#control-points').val());
                const area_id = parseInt($('#area').val());
                const min_range = parseInt($('#min-range').val());
                const max_range = parseInt($('#max-range').val());
                var color = findColor(point_id);
                var product_id = null;

                if (points.length > 0) {
                    var fetched_colors = points.filter(item => item.point_id == point_id && item.area_id == area_id)
                        .map(elem => elem.color);

                    if (fetched_colors.length) {
                        color = fetched_colors[0];
                    }
                }

                if (countPoints > 0) {
                    var code = data.find(item => item.id == point_id)?.code ?
                        `${data.find(item => item.id == point_id).code}-${count}` : null;
                    var total = max_range - min_range + 1;
                    countPoints--;
                    index = count - 1;
                    addPoint(pointer.x, pointer.y, point_id, area_id, product_id, color, code);
                    createLegend();
                    //sortPoints()

                    $('#count-points').text(`Puntos generados: ${countPoints}`);
                }
            });

            $(document).on('mouseup', function() {
                isDragging = false;
            });

            $(document).on('keydown', function(event) {
                if (event.key == 'D' || event.key == 'd') {
                    deleteDevice();
                }

                if (event.key == 'E' || event.key == 'e') {
                    editDevice();
                }
            });

            function applyZoom() {
                canvas.setViewportTransform([zoomLevel, 0, 0, zoomLevel, 0, 0]);
                canvas.renderAll();
            }

            // Eventos de botones de zoom
            $('#zoomIn').on('click', function() {
                if (zoomLevel < maxZoom) {
                    zoomLevel += zoomFactor;
                    applyZoom();
                }
            });

            $('#zoomOut').on('click', function() {
                if (zoomLevel > minZoom) {
                    zoomLevel -= zoomFactor;
                    applyZoom();
                }
            });

            canvas.on('mouse:down', function(event) {
                if (event.e.altKey) { // Mantén presionada la tecla ALT para activar el pan
                    isDragging = true;
                    const pointer = canvas.getPointer(event.e);
                    lastPosX = pointer.x;
                    lastPosY = pointer.y;
                    canvas.setCursor('grab');
                    canvas.renderAll();
                }

                const {
                    x,
                    y
                } = canvas.getPointer(event.e);

                selectedElement = points.find(el =>
                    x >= el.x && x <= el.x + (el.img_tamx || 10) &&
                    y >= el.y && y <= el.y + (el.img_tamy || 10)
                );
            });

            // Evento para mover el canvas
            canvas.on('mouse:move', function(event) {
                if (isDragging) {
                    const pointer = canvas.getPointer(event.e);
                    const deltaX = pointer.x - lastPosX;
                    const deltaY = pointer.y - lastPosY;
                    const currentTransform = canvas.viewportTransform || [1, 0, 0, 1, 0, 0];

                    // Actualizar la transformación del viewport
                    currentTransform[4] += deltaX;
                    currentTransform[5] += deltaY;
                    canvas.setViewportTransform(currentTransform);

                    lastPosX = pointer.x;
                    lastPosY = pointer.y;
                }
            });

            // Evento para detener el desplazamiento
            canvas.on('mouse:up', function() {
                isDragging = false;
                canvas.setCursor('default');
            });

            function setDevice() {
                const point_index = $('#point-index').val();
                const i = points.findIndex(item => item.index == point_index);
                var indexs = [];
                if (i != -1) {
                    var selected_point_id = parseInt($('#update-control-point').val() ?? '0');
                    var selected_area_id = parseInt($('#update-area').val() ?? '0');
                    var selected_product_id = parseInt($('#update-product').val() ?? '0');
                    var color = findColor(selected_point_id);

                    if (points[i].point_id != selected_point_id) {
                        var code = data.find(item => item.id == selected_point_id)?.code ?
                            `${data.find(item => item.id == selected_point_id).code}-${points[i].count}` : null;
                        points[i].code = code;
                        points[i].color = color;
                        points[i].point_id = selected_point_id;
                    }
                    points[i].area_id = selected_area_id
                    points[i].product_id = selected_product_id
                    indexs.push(points[i].index);

                    canvas.getObjects().forEach(obj => {
                        if (obj.type == 'group') {
                            const circle = obj._objects[0];
                            const text = obj._objects[1];
                            const metadata = obj.metadata;

                            if (
                                metadata &&
                                indexs.includes(metadata.index)
                            ) {
                                circle.set('fill', color);
                                text.set('fill', color);
                            }
                        }
                    });
                    canvas.renderAll();
                    createLegend();
                }
                $('#pointModal').modal('hide');
            }

            function getLegend() {
                const countMap = {};
                points.forEach(point => {
                    // Crear una clave única con las propiedades deseadas
                    const key = `${point.point_id}-${point.area_id}-${point.color}`;

                    // Si la clave ya existe, incrementa el contador
                    if (countMap[key]) {
                        countMap[key].count += 1;
                    } else {
                        // Si la clave no existe, inicializa el contador
                        countMap[key] = {
                            point_id: point.point_id,
                            area_id: point.area_id,
                            product_id: point.product_id,
                            color: point.color,
                            count: 1
                        };
                    }
                });
                const countedPoints = Object.values(countMap);
                return countedPoints;
            }

            function createLegend() {
                var legend = getLegend();
                var html = '';

                legend.forEach(s => {
                    const count_points = points.filter(point => s.point_id == point.point_id && s.area_id == point
                        .area_id).map(item => item.count);
                    var ranges = generateRanges(count_points.flat());

                    html += `
                <tr>
                    <td>
                        <input type="color" class="form-control" style="height: 2em !important;" value="${s.color}" data-legend='${JSON.stringify(s)}' onchange="updateColor(this)" />
                    </td>
                    <td>${findPointName(s.point_id)}</td>
                    <td class="fw-bold text-primary">${findCode(s.point_id)}</td>
                    <td>
                        <select class="form-select form-select-sm" data-legend='${JSON.stringify(s)}' onchange="updateArea(this)">
                        ${
                            areaNames.map(item => `<option value="${item.id}" ${s.area_id == item.id ? 'selected' : ''}>${item.name}</option>`).join('')
                        }
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" data-legend='${JSON.stringify(s)}' onchange="updateProduct(this)">
                            <option value="" ${!s.product_id ? 'selected' : ''}>Sin producto</option>
                        ${
                            productNames.map(item => `<option value="${item.id}" ${s.product_id == item.id ? 'selected' : ''}>${item.name}</option>`).join('')
                        }
                        </select>
                    </td>
                    <td>${s.count}</td>
                    <td>${ranges}</td>
                </tr>
                `;
                });

                $('#table-body').html(html);
            }

            function updateColor(input) {
                const color = input.value;
                const legendData = JSON.parse(input.getAttribute('data-legend'));
                const {
                    point_id,
                    area_id,
                    product_id
                } = legendData;

                var indexs = [];

                points.forEach(point => {
                    if (point.point_id == point_id && point.area_id == area_id && point.product_id == product_id) {
                        point.color = color;
                        indexs.push(point.index);
                    }
                });

                canvas.getObjects().forEach(obj => {
                    if (obj.type == 'group') {
                        const circle = obj._objects[0];
                        const text = obj._objects[1];
                        const metadata = obj.metadata;

                        if (
                            metadata &&
                            indexs.includes(metadata.index)
                        ) {
                            circle.set('fill', color);
                            text.set('fill', getContrastColor(color || '#fff'));
                        }
                    }
                });

                canvas.renderAll();
            }


            function updateArea(input) {
                const areaId = parseInt(input.value);
                const legendData = JSON.parse(input.getAttribute('data-legend'));

                const {
                    point_id,
                    area_id
                } = legendData;

                // Obtener los colores y productos de los puntos en la nueva área
                const color_replicate = points
                    .filter(point => parseInt(point.area_id) === areaId)
                    .map(point => point.color);

                const product_replicate = points
                    .filter(point => parseInt(point.area_id) === areaId)
                    .map(point => point.product_id);

                var indexs = [];

                points.forEach((point, i) => {
                    if (
                        point.point_id == point_id &&
                        parseInt(point.area_id) === parseInt(area_id)
                    ) {
                        points[i] = {
                            ...points[i],
                            area_id: areaId,
                            product_id: product_replicate.length ? product_replicate[0] : point
                                .product_id, // Evitar undefined
                            color: color_replicate.length ? color_replicate[0] : point.color // Evitar undefined
                        };

                        indexs.push(i);
                    }
                });

                // Actualizar colores en el canvas
                canvas.getObjects().forEach(obj => {
                    if (obj.type == 'group') {
                        const circle = obj._objects[0];
                        const metadata = obj.metadata;

                        if (metadata && indexs.includes(metadata.index)) {
                            circle.set('fill', points[metadata.index].color);
                        }
                    }
                });

                canvas.renderAll();
                createLegend();
            }

            function updateProduct(input) {
                const productId = parseInt(input.value);
                const legendData = JSON.parse(input.getAttribute('data-legend'));
                const {
                    point_id,
                    area_id,
                    product_id
                } = legendData;

                points.forEach(point => {
                    if (point.point_id == point_id && point.area_id == area_id && point.product_id == product_id) {
                        point.product_id = productId;
                    }
                });
            }

            function setDevices() {
                if (devices) {
                    devices.forEach(device => {
                        count = device.nplan;
                        index = device.itemnumber;
                        currentPointSize = device.size ?? 10;
                        addPoint(
                            device.map_x,
                            device.map_y,
                            device.type_control_point_id,
                            device.application_area_id,
                            device.product_id,
                            device.color,
                            device.code,
                        );
                    })
                    createLegend();
                }
            }

            function searchVersion() {
                var formData = new FormData();
                var csrfToken = $('meta[name="csrf-token"]').attr("content");
                var version = $('#version').val();

                formData.append('version', version);
                $.ajax({
                    url: "{{ route('floorplan.search.device.version', ['id' => $floorplan->id]) }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    success: function(response) {
                        if (response.redirect) {
                            // Redirigir a la URL
                            window.location.href = response.redirect;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error in AJAX request:', status, error);
                    }
                });
            }

            /*function changePointSize(newSize) {
                currentPointSize = parseInt(newSize);

                canvas.getObjects().forEach(obj => {
                    if (obj.type === 'group') {
                        const circle = obj._objects[0];
                        if (circle && circle.type === 'circle') {
                            circle.set('radius', currentPointSize);

                            // Ajustar el tamaño del texto proporcionalmente
                            const text = obj._objects[1];
                            if (text && text.type === 'text') {
                                const baseFontSize = text.count < 100 ? 10 : text.count < 1000 ? 10 : 8;
                                text.set('fontSize', baseFontSize * (currentPointSize / 8));
                            }
                        }
                    }
                });

                canvas.renderAll();
            }*/

            // Event listener para el select de tamaño
            document.getElementById('point-size').addEventListener('change', function() {
                changePointSize(this.value);
            });
        </script>

        <script>
            function changePointSize(newSize) {
                currentPointSize = parseInt(newSize);

                // Guardar el nuevo tamaño en todos los puntos existentes
                points.forEach(point => {
                    point.size = currentPointSize;
                });

                canvas.getObjects().forEach(obj => {
                    if (obj.type === 'group') {
                        const circle = obj._objects[0];
                        if (circle && circle.type === 'circle') {
                            circle.set('radius', currentPointSize);

                            // Actualizar el tamaño en los metadatos del punto
                            const metadata = obj.metadata;
                            if (metadata) {
                                const pointIndex = points.findIndex(p => p.index === metadata.index);
                                if (pointIndex !== -1) {
                                    points[pointIndex].size = currentPointSize;
                                }
                            }

                            // Ajustar el tamaño del texto proporcionalmente
                            const text = obj._objects[1];
                            if (text && text.type === 'text') {
                                const baseFontSize = 10; // Tamaño base para texto
                                text.set('fontSize', baseFontSize * (currentPointSize / 8));
                            }
                        }
                    }
                });

                canvas.renderAll();
            }

            // FUNCIÓN PARA OBTENER DIMENSIONES ORIGINALES DEL CANVAS
            function getOriginalCanvasDimensions() {
                const isWide = img_sizes[0] > img_sizes[1];
                const width = isWide ? 1100 : 800;
                const height = isWide ? 800 : 1100;

                return [width, height];
            }

            // FUNCIÓN PARA REAJUSTAR PUNTOS A NUEVAS DIMENSIONES
            function resizePointsToNewCanvas() {
                // Obtener dimensiones originales y actuales
                const [originalWidth, originalHeight] = getOriginalCanvasDimensions();
                const currentWidth = canvas.getWidth();
                const currentHeight = canvas.getHeight();

                // Si las dimensiones no han cambiado, no hacer nada
                if (originalWidth === currentWidth && originalHeight === currentHeight) {
                    console.log('Las dimensiones del canvas no han cambiado');
                    return;
                }

                // Calcular factores de escala
                const scaleX = currentWidth / originalWidth;
                const scaleY = currentHeight / originalHeight;

                console.log(`Reajustando puntos de ${originalWidth}x${originalHeight} a ${currentWidth}x${currentHeight}`);
                console.log(`Factores de escala: X=${scaleX.toFixed(4)}, Y=${scaleY.toFixed(4)}`);

                // Actualizar todos los puntos en el array y en el canvas
                points.forEach(point => {
                    // Escalar las coordenadas
                    const newX = point.x * scaleX;
                    const newY = point.y * scaleY;

                    // Actualizar el punto en el array
                    point.x = newX;
                    point.y = newY;

                    // Buscar y actualizar el objeto visual en el canvas
                    canvas.getObjects().forEach(obj => {
                        if (obj.type === 'group' && obj.metadata && obj.metadata.index === point.index) {
                            obj.set({
                                left: newX,
                                top: newY
                            });
                            obj.setCoords(); // Actualizar coordenadas para interacción
                        }
                    });
                });

                // Renderizar los cambios
                canvas.renderAll();
            }
        </script>

        <script>
            function captureCanvas() {
                try {
                    // Asegurarse de que el canvas esté completamente renderizado
                    canvas.renderAll();

                    // Pequeña pausa para asegurar el renderizado completo
                    setTimeout(() => {
                        // Obtener el canvas como data URL (base64) con la mejor calidad
                        const dataURL = canvas.toDataURL({
                            format: 'png',
                            quality: 1
                        });

                        // Guardar en variable global para uso posterior
                        currentBase64 = dataURL;

                        // Mostrar previsualización
                        const preview = document.getElementById('image-preview');
                        preview.innerHTML = `
                            <div class="alert alert-success">
                                <strong>¡Imagen generada!</strong>
                                <div class="mt-2">
                                    <img src="${dataURL}" class="img-fluid rounded border" style="max-height: 200px;">
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="downloadImage()">
                                        <i class="bi bi-download"></i> Descargar imagen
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="copyToClipboard()">
                                        <i class="bi bi-clipboard"></i> Copiar base64
                                    </button>
                                    <form id="pdfForm" method="POST" action="{{ route('floorplan.print.version') }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" id="pdfJsonData" name="pdf_json_data">
                                        <button type="button" class="btn btn-success btn-sm" onclick="generatePDF()">
                                            <i class="bi bi-file-pdf-fill"></i> Descargar PDF
                                        </button>
                                    </form>
                                </div>
                            </div>
                        `;
                    }, 100);


                } catch (error) {
                    console.error('Error al capturar el canvas:', error);
                    alert('Error al generar la imagen: ' + error.message);
                }
            }

            function downloadImage() {
                if (!currentBase64) {
                    alert('Primero genera la imagen');
                    return;
                }

                const link = document.createElement('a');
                link.download = `plano-{{ $floorplan->filename }}-${new Date().toISOString().slice(0,10)}.png`;
                link.href = currentBase64;
                link.click();
            }

            function copyToClipboard() {
                if (!currentBase64) {
                    alert('Primero genera la imagen');
                    return;
                }

                navigator.clipboard.writeText(currentBase64).then(() => {
                    alert('Base64 copiado al portapapeles');
                }).catch(err => {
                    console.error('Error al copiar: ', err);
                    alert('Error al copiar al portapapeles');
                });
            }

            function groupByColorAndCode(points) {
                return points.reduce((acc, point) => {
                    const key = `${point.color}`;
                    if (!acc[key]) {
                        acc[key] = [];
                    }
                    acc[key].push(point);
                    return acc;
                }, {});
            }

            async function generatePDF() {
                const button = event.target;
                const floorplan = '{{ $floorplan }}';

                try {
                    const groupedPoints = groupByColorAndCode(points);

                    // Obtener la imagen en base64
                    //const imageBase64 = await generateImageBase64();

                    // Extraer solo la parte de datos base64 (sin el prefix)
                    const base64Data = currentBase64.replace(/^data:image\/\w+;base64,/, '');

                    // Preparar datos para el JSON
                    const pdfData = {
                        image: base64Data,
                        customer: printData.customer,
                        service: printData.service,
                        filename: printData.name,
                        version: printData.floorplan_version,
                        date_version: printData.date_version,
                        device_count: printData.count,
                        font_family: 'Helvetica', //font_family ?? null,
                        font_color: '#000000', //font_color ?? null,
                        groupedPoints: legendPDF
                    };

                    $('#pdfJsonData').val(JSON.stringify(pdfData));

                    // Aquí puedes proceder con el envío del formulario
                    // Por ejemplo:
                    $('#pdfForm').submit();

                } catch (error) {
                    console.error('Error al preparar datos para PDF:', error);
                    alert('Error al preparar datos para PDF: ' + error.message);
                }
            }

            function showLoading(message) {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'alert alert-info';
                loadingDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <strong>${message}</strong>
        </div>
    `;

                const preview = document.getElementById('image-preview');
                if (preview) {
                    preview.innerHTML = '';
                    preview.appendChild(loadingDiv);
                }

                return loadingDiv;
            }

            function formatNplansCompact(nplansArray) {
                // Validaciones iniciales
                if (!nplansArray || !Array.isArray(nplansArray) || nplansArray.length === 0) {
                    return 'N/A';
                }

                // Si solo hay un número, retornarlo directamente
                if (nplansArray.length === 1) {
                    return nplansArray[0].toString();
                }

                // Ordenar los números de forma ascendente
                const sorted = [...nplansArray].sort((a, b) => a - b);

                const ranges = [];
                let start = sorted[0];
                let end = sorted[0];

                // Recorrer el array para identificar rangos consecutivos
                for (let i = 1; i < sorted.length; i++) {
                    if (sorted[i] === end + 1) {
                        // Números consecutivos, extender el rango
                        end = sorted[i];
                    } else {
                        // Fin del rango consecutivo, agregar al resultado
                        if (start === end) {
                            ranges.push(start.toString());
                        } else {
                            ranges.push(`${start}-${end}`);
                        }
                        // Iniciar nuevo rango
                        start = sorted[i];
                        end = sorted[i];
                    }
                }

                // Agregar el último rango
                if (start === end) {
                    ranges.push(start.toString());
                } else {
                    ranges.push(`${start}-${end}`);
                }

                // Unir todos los rangos con comas
                const result = ranges.join(', ');

                // Limitar la longitud si es muy extensa
                const maxLength = 35; // Caracteres máximos permitidos
                if (result.length > maxLength) {
                    return result.substring(0, maxLength - 3) + '...';
                }

                return result;
            }
        </script>
    @endif
@endsection
