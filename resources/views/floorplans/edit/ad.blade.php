<script>
    // =============================================
    // 1. VARIABLES GLOBALES Y CONFIGURACIÓN INICIAL
    // =============================================
    let points = [];
    let zoomLevel = count = 1;
    let index = countPoints = 0;
    let isDragging = hasPoints = false;
    let lastX, lastY;

    const zoomFactor = 0.2;
    const maxZoom = 3;
    const minZoom = 0.5;
    const imgURL = "{{ route('image.show', ['path' => $floorplan->path]) }}";

    // Datos del backend
    var data = @json($ctrlPoints);
    var devices = @json($devices);
    var nplans = @json($nplans);
    var pointNames = @json($pointNames);
    var areaNames = @json($areaNames);
    var productNames = @json($productNames);
    var reviews = @json($reviews);
    var img_sizes = @json($img_sizes);
    var img_scale = 1;

    // Inicialización del canvas
    var canvas = new fabric.Canvas('myCanvas', {
        selection: false,
        preserveObjectStacking: true,
        backgroundColor: '#f8f9fa'
    });

    // Configuración inicial del contenedor
    $('#canvas-container').css({
        'width': '1000px',
        'height': '1000px',
        'overflow': 'auto'
    });

    // =============================================
    // 2. FUNCIONES DE INICIALIZACIÓN Y CONFIGURACIÓN
    // =============================================

    $(document).ready(function () {
        loadImage();
        resetInputs();
        setupEventListeners();
        if (devices && devices.length > 0) {
            setDevices();
        }
    });

    function loadImage() {
        fabric.Image.fromURL(imgURL, function (img) {
            img_scale = ~~(window.innerWidth / img.width);

            canvas.setWidth(img.width * img_scale);
            canvas.setHeight(img.height * img_scale);

            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                originX: 'left',
                originY: 'top',
                scaleX: img_scale,
                scaleY: img_scale
            });

        }, {
            crossOrigin: 'anonymous',
            error: function (err) {
                //console.error('Error loading image:', err);
                showErrorOnCanvas('Error al cargar la imagen');
            }
        });
    }

    function showErrorOnCanvas(message) {
        const text = new fabric.Text(message, {
            left: 20,
            top: 30,
            fontSize: 20,
            fill: 'red'
        });
        canvas.add(text);
        canvas.setWidth(1000);
        canvas.setHeight(1000);
    }

    function setupEventListeners() {
        // Eventos de zoom
        $('#zoomIn').on('click', zoomIn);
        $('#zoomOut').on('click', zoomOut);

        // Eventos de canvas
        canvas.on('mouse:dblclick', handleCanvasDoubleClick);
        canvas.on('mouse:down', handleCanvasMouseDown);
        canvas.on('mouse:move', handleCanvasMouseMove);
        canvas.on('mouse:up', handleCanvasMouseUp);

        // Eventos de teclado
        $(document).on('keydown', handleKeyDown);
        $(document).on('mouseup', function () {
            isDragging = false;
        });
    }

    // =============================================
    // 3. FUNCIONES DE MANEJO DE PUNTOS/DISPOSITIVOS
    // =============================================

    function addPoint(x, y, pointId, areaId, productId, color, code) {
        x = parseFloat(x);
        y = parseFloat(y);

        // Ajustar coordenadas según la escala de la imagen
        const scaledX = x * img_scale;
        const scaledY = y * img_scale;

        // Ajustar tamaño del punto según la escala
        const pointRadius = 7 / img_scale;
        const fontSize = (count < 100 ? 9 : count < 1000 ? 7 : 5) / img_scale;

        const point = new fabric.Circle({
            left: scaledX,
            top: scaledY,
            radius: pointRadius,
            fill: color || 'black',
            selectable: true,
            hasControls: false,
            hasBorders: false,
            originX: 'center',
            originY: 'center',
        });

        const pointText = new fabric.Text(`${count}`, {
            fontSize: fontSize,
            fill: getContrastColor(color || '#fff'),
            selectable: false,
            fontWeight: 'bold',
            fontFamily: 'Courier New',
            originX: 'center',
            originY: 'center',
        });

        const pointGroup = new fabric.Group([point, pointText], {
            left: scaledX,
            top: scaledY,
            originX: 'center',
            originY: 'center',
            metadata: {
                index: index,
                originalX: x,  // Guardamos las coordenadas originales
                originalY: y   // para referencia futura
            },
        });

        canvas.add(pointGroup);

        const newPoint = {
            index: index,
            point_id: pointId,
            area_id: areaId,
            product_id: productId,
            color: color,
            code: code,
            x: x,  // Almacenamos las coordenadas originales
            y: y,  // sin escalar para consistencia
            img_tamx: 0,
            img_tamy: 0,
            count: count,
        };

        points.push(newPoint);

        if (!nplans.includes(count)) {
            nplans.push(count);
        }

        pointGroup.on('mousedown', function (event) {
            if (event.e.detail == 1) {
                const i = points.findIndex(p => p.code == code);
                if (i != -1) {
                    $('#point-index').val(points[i].index);
                }
            }
        });

        pointGroup.on('moving', function (event) {
            const i = points.findIndex(p => p.index == newPoint.index);
            if (i != -1) {
                // Actualizamos con las coordenadas escaladas
                points[i].x = pointGroup.left / img_scale;
                points[i].y = pointGroup.top / img_scale;
            }
        });

        index = points.length;
        count++;
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
                    if (obj.type == 'group' && obj.metadata && obj.metadata.index == i) {
                        canvas.remove(obj);
                    }
                });

                canvas.renderAll();
                createLegend();

                countPoints++;
                count = --aux;
                $('#count-points').text(`Puntos generados: ${countPoints}`);
            }
        }
        $('#pointModal').modal('hide');
    }

    function setDevices() {
        devices.forEach(device => {
            count = device.nplan;
            index = device.itemnumber;
            addPoint(
                device.map_x,
                device.map_y,
                device.type_control_point_id,
                device.application_area_id,
                device.product_id,
                device.color,
                device.code
            );
        });
        createLegend();
    }

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

            points[i].area_id = selected_area_id;
            points[i].product_id = selected_product_id;
            indexs.push(points[i].index);

            canvas.getObjects().forEach(obj => {
                if (obj.type == 'group') {
                    const circle = obj._objects[0];
                    const text = obj._objects[1];
                    const metadata = obj.metadata;

                    if (metadata && indexs.includes(metadata.index)) {
                        circle.set('fill', color);
                        text.set('fill', getContrastColor(color || '#fff'));
                    }
                }
            });

            canvas.renderAll();
            createLegend();
        }
        $('#pointModal').modal('hide');
    }

    // =============================================
    // 4. FUNCIONES DE BÚSQUEDA Y ACTUALIZACIÓN
    // =============================================

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
                "X-CSRF-TOKEN": csrfToken
            },
            success: function (response) {
                devices = response.devices;
                points = [];
                canvas.clear();
                fabric.Image.fromURL(imgURL, function (img) {
                    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                        scaleX: canvas.width / img.width,
                        scaleY: canvas.height / img.height
                    });
                    setDevices();
                    createLegend();
                });
            },
            error: function (xhr, status, error) {
                //console.error('Error in AJAX request:', status, error);
            }
        });
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

                if (metadata && indexs.includes(metadata.index)) {
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

        const color_replicate = points
            .filter(point => parseInt(point.area_id) === areaId)
            .map(point => point.color);

        const product_replicate = points
            .filter(point => parseInt(point.area_id) === areaId)
            .map(point => point.product_id);

        var indexs = [];

        points.forEach((point, i) => {
            if (point.point_id == point_id && parseInt(point.area_id) === parseInt(area_id)) {
                points[i] = {
                    ...points[i],
                    area_id: areaId,
                    product_id: product_replicate.length ? product_replicate[0] : point.product_id,
                    color: color_replicate.length ? color_replicate[0] : point.color
                };
                indexs.push(i);
            }
        });

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

    // =============================================
    // 5. FUNCIONES DE INTERFAZ Y UTILIDADES
    // =============================================

    function createLegend() {
        var legend = getLegend();
        var html = '';

        if (legend.length <= 0) {
            html = `<tr class="text-center"><td class="text-danger" colspan="7">No se tienen dispositivos</td></tr>`;
        }

        legend.forEach(s => {
            const count_points = points.filter(point => s.point_id == point.point_id && s.area_id == point
                .area_id)
                .map(item => item.count);
            var ranges = generateRanges(count_points.flat());

            html += `
            <tr>
                <td>
                    <input type="color" class="form-control" style="height: 2em;" value="${s.color}" data-legend='${JSON.stringify(s)}' onchange="updateColor(this)" />
                </td>
                <td>${findPointName(s.point_id)}</td>
                <td class="fw-bold text-primary">${findCode(s.point_id)}</td>
                <td>
                    <select class="form-select" data-legend='${JSON.stringify(s)}' onchange="updateArea(this)">
                    ${areaNames.map(item => `<option value="${item.id}" ${s.area_id == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <select class="form-select" data-legend='${JSON.stringify(s)}' onchange="updateProduct(this)">
                        <option value="" ${!s.product_id ? 'selected' : ''}>Sin producto</option>
                    ${productNames.map(item => `<option value="${item.id}" ${s.product_id == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </td>
                <td>${s.count}</td>
                <td>${ranges}</td>
            </tr>`;
        });

        $('#table-body').html(html);
    }

    function getLegend() {
        const countMap = {};
        points.forEach(point => {
            const key = `${point.point_id}-${point.area_id}-${point.color}`;
            if (countMap[key]) {
                countMap[key].count += 1;
            } else {
                countMap[key] = {
                    point_id: point.point_id,
                    area_id: point.area_id,
                    product_id: point.product_id,
                    color: point.color,
                    count: 1
                };
            }
        });
        return Object.values(countMap);
    }

    function resetInputs() {
        $('#min-range').val(0);
        $('#max-range').val(0);
        $('#count-points').text(`Puntos generados: 0`);
    }

    function sortPoints() {
        points = points.sort((a, b) => a.count - b.count);
    }

    // =============================================
    // 6. FUNCIONES DE ZOOM Y NAVEGACIÓN
    // =============================================

    function zoomIn() {
        if (zoomLevel < maxZoom) {
            zoomLevel += zoomFactor;
            applyZoom();
        }
    }

    function zoomOut() {
        if (zoomLevel > minZoom) {
            zoomLevel -= zoomFactor;
            applyZoom();
        }
    }

    function applyZoom() {
        canvas.setViewportTransform([zoomLevel, 0, 0, zoomLevel, 0, 0]);
        canvas.renderAll();
    }

    function handleCanvasMouseDown(event) {
        if (event.e.altKey) {
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
    }

    function handleCanvasMouseMove(event) {
        if (isDragging) {
            const pointer = canvas.getPointer(event.e);
            const deltaX = pointer.x - lastPosX;
            const deltaY = pointer.y - lastPosY;
            const currentTransform = canvas.viewportTransform || [1, 0, 0, 1, 0, 0];

            currentTransform[4] += deltaX;
            currentTransform[5] += deltaY;
            canvas.setViewportTransform(currentTransform);

            lastPosX = pointer.x;
            lastPosY = pointer.y;
        }
    }

    function handleCanvasMouseUp() {
        isDragging = false;
        canvas.setCursor('default');
    }

    function handleCanvasDoubleClick(event) {
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

        // Convertir coordenadas del puntero a coordenadas originales
        const originalX = pointer.x / img_scale;
        const originalY = pointer.y / img_scale;


        if (countPoints > 0) {
            var code = data.find(item => item.id == point_id)?.code ?
                `${data.find(item => item.id == point_id).code}-${count}` : null;
            var total = max_range - min_range + 1;
            countPoints--;
            index = count - 1;
            addPoint(originalX, originalY, point_id, area_id, product_id, color, code);
            createLegend();
            $('#count-points').text(`Puntos generados: ${countPoints}`);
        }
    }

    function handleKeyDown(event) {
        if (event.key == 'D' || event.key == 'd') {
            deleteDevice();
        }

        if (event.key == 'E' || event.key == 'e') {
            editDevice();
        }
    }

    // =============================================
    // 7. FUNCIONES DE BÚSQUEDA Y FORMULARIOS
    // =============================================

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

        if (!inRange(min_range, max_range)) {
            countPoints = 0;
            var ranges = generateRanges(nplans);
            $('#count-points').text(`Puntos generados: ${countPoints}`);
            alert(`No se permiten valores entre: ${ranges}`);
            return
        } else {
            countPoints = max_range - min_range + 1;
            count = min_range;
            $('#count-points').text(`Puntos generados: ${countPoints}`);
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

    // =============================================
    // 8. FUNCIONES DE UTILIDAD (HELPERS)
    // =============================================

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

    function getContrastColor(hexColor) {
        const r = parseInt(hexColor.substr(1, 2), 16) / 255;
        const g = parseInt(hexColor.substr(3, 2), 16) / 255;
        const b = parseInt(hexColor.substr(5, 2), 16) / 255;

        const luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
        return luminance > 0.5 ? '#000000' : '#FFFFFF';
    }

    function inRange(min, max) {
        return !nplans.some(i => i >= min && i <= max);
    }

    function generateRanges(array) {
        array.sort((a, b) => a - b);
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
</script>