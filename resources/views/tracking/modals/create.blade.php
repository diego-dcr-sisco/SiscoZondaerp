<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trackingModalLabel">Programación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="service" class="form-label">Servicio</label>
                    <select class="form-select" id="service" name="service_id">
                        @foreach ($services as $s)
                            <option value="{{ $s->id }}"
                                {{ isset($service) && $s->id == $service->id ? 'selected' : '' }}>
                                {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="start-date" class="form-label">Fecha de inicio</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="start-date" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="frequency" class="form-label">Frecuencia</label>
                    <div class="input-group mb-3">
                        {{-- <input type="number" class="form-control" id="reps" value="1" min="1"> --}}
                        <select class="form-select" id="frequency" name="frequency">
                            <option value="dia">Diario</option>
                            <option value="semana">Semanal</option>
                            <option value="quincena">Quincenal</option>
                            <option value="mes">Mensual</option>
                            <option value="bimestre">Bimestral</option>
                            <option value="trimestre">Trimestral</option>
                            <option value="semestre">Semestral</option>
                            <option value="año">Anual</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Título</label>
                    <input type="text" class="form-control" id="title" name="title"
                        placeholder="Título del seguimiento">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description" rows="5"
                        placeholder="Ingrese los detalles del seguimiento: acciones tomadas, estado actual del servicio, observaciones relevantes y próximos pasos"></textarea>
                </div>

                <input type="hidden" id="tracking-id" name="tracking_id">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="generateTracking()">Generar</button>
            </div>
        </div>
    </div>
</div>

<script>
    const formatToDDMMYYYY = (dateISO) => {
        const [año, mes, dia] = dateISO.split('-');
        return `${dia}-${mes}-${año}`;
    };

    function generateDates(startDate, frequency) {
        const dates = [];
        const start_date = new Date(startDate + "T00:00:00");
        const date = new Date(startDate + "T00:00:00");
        const end_date = new Date(startDate + "T00:00:00");
        end_date.setFullYear(end_date.getFullYear() + 1);

        // Asegurarnos de que las horas sean 00:00:00 local
        start_date.setHours(0, 0, 0, 0);
        date.setHours(0, 0, 0, 0);
        end_date.setHours(0, 0, 0, 0);

        while (date <= end_date) {
            // Solo agregar si no es la fecha de inicio
            if (date.toISOString() != start_date.toISOString()) {
                dates.push(date.toISOString().split('T')[0]);
            }

            switch (frequency) {
                case 'dia':
                    date.setDate(date.getDate() + 1);
                    break;
                case 'semana':
                    date.setDate(date.getDate() + 7);
                    break;
                case 'quincena':
                    date.setDate(date.getDate() + 15);
                    break;
                case 'mes':
                    // Manejar correctamente fin de meses
                    const nextMonth = date.getMonth() + 1;
                    date.setMonth(nextMonth);
                    // Ajustar si el día cambió (ej. 31 de enero → 3 de marzo)
                    if (date.getMonth() != (nextMonth % 12)) {
                        date.setDate(0); // Último día del mes anterior
                    }
                    break;
                case 'bimestre':
                    date.setMonth(date.getMonth() + 2);
                    break;
                case 'trimestre':
                    date.setMonth(date.getMonth() + 3);
                    break;
                case 'semestre':
                    date.setMonth(date.getMonth() + 6);
                    break;
                case 'año':
                    date.setFullYear(date.getFullYear() + 1);
                    break;
                default:
                    throw new Error(`Frecuencia no válida: ${frequency}`);
            }

            // Normalizar la fecha después de cada modificación
            date.setHours(0, 0, 0, 0);
        }
        return dates;
    }

    function createJsonDates(dates, title, description) {
        var data = [];
        dates.forEach(date => {
            data.push({
                tracking_id: null,
                date: date,
                title: title ?? null,
                description: description ?? null,
                status: 'active'
            });
        });
        return data;
    }


    function generateTracking() {
        const start_date = $('#start-date').val();
        const reps = 1; //parseInt($('#reps').val());
        const frequency = $('#frequency').val();
        const service_id = $('#service').val();
        const title = $('#title').val();
        const description = $('#description').val();

        if (!service_id) {
            alert('Debes seleccionar un servicio asociado a la orden');
            return;
        }

        if (!start_date) {
            alert('Debes agregar la fecha de punto de inicio de los seguimientos');
            return;
        }

        var dates = generateDates(start_date, frequency);
        var found_service = services.find(s => s.id == service_id);

        tracking = {
            service_id: service_id,
            service_name: found_service.name ?? '',
            start_date: start_date,
            frequency: frequency,
            reps: reps,
            dates: createJsonDates(dates, title, description),
        }

        tracking_data.push(tracking);
        renderTrackings()
        $('#trackingModal').modal('hide');
    }

    function handleColorStatus(status) {
        if (status == 'active') {
            return 'text-success' // #198754
        } else if (status == 'completed') {
            return 'text-primary' // #0d6efd
        } else {
            return 'text-danger' //#dc3545
        }
    }

    function handleTranslate(status) {
        if (status == 'active') {
            return 'Activo' //active
        } else if (status == 'completed') {
            return 'Completado' //completed
        } else {
            return 'Cancelado' //canceled
        }
    }

    function renderTrackings() {
        var rows = ``
        var count = 0;
        const tbody = $('#tracking-table-body');
        tbody.empty();
        tracking_data.forEach((tracking, i) => {
            tracking.dates.forEach((d, j) => {
                rows += `
                    <tr data-tracking-id="${tracking.id}">
                        <td>${++count}</td>
                        <td>${formatToDDMMYYYY(d.date)}</td>
                        <td>${tracking.service_name}</td>
                        <td>${tracking.frequency}</td>
                        <td>${d.title}</td>
                        <td>${d.description}</td>
                        <td class="${handleColorStatus(d.status)} fw-bold">${handleTranslate(d.status)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="editTracking(${i}, ${j})">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTracking(${i}, ${j})">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </td>
                    </tr>
                `
            })
        });
        tbody.html(rows)
    }

    function loadAllTrackings() {
        $('#tracking-table-body').empty();
        tracking_data.forEach(tracking => {
            paintTracking(tracking);
        });
    }

    function deleteTracking(i, j) {
        if (tracking_data[i] && tracking_data[i].dates && tracking_data[i].dates[j]) {
            tracking_data[i].dates.splice(j, 1);
            if (tracking_data[i].dates.length == 0) {
                tracking_data.splice(i, 1);
            }
            renderTrackings();
        }
    }

    function editTracking(i, j) {
        if (tracking_data[i] && tracking_data[i].dates && tracking_data[i].dates[j]) {
            $('#tracking-service').val(tracking_data[i].service_id);
            $('#tracking-date').val(tracking_data[i].dates[j].date);
            $('#tracking-status').val(tracking_data[i].dates[j].status);
            $('#tracking-title').val(tracking_data[i].dates[j].title);
            $('#tracking-description').val(tracking_data[i].dates[j].description);
        }
        edit_i = i;
        edit_j = j;

        $('#editTrackingModal').modal('show')
    }

    function updateTracking() {
        var i = edit_i;
        var j = edit_j;
        if (tracking_data[i] && tracking_data[i].dates && tracking_data[i].dates[j]) {
            tracking_data[i].service_id = $('#tracking-service').val();
            tracking_data[i].dates[j].date = $('#tracking-date').val();
            tracking_data[i].dates[j].status = $('#tracking-status').val();
            tracking_data[i].dates[j].title = $('#tracking-title').val();
            tracking_data[i].dates[j].description = $('#tracking-description').val();
        }

        renderTrackings();
        $('#editTrackingModal').modal('hide')
    }
</script>
