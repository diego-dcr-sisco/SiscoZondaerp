<div class="modal fade" id="newDeviceModal" tabindex="-1" aria-labelledby="newDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="newDeviceModalLabel">Nuevo dispositivo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selecciona un servicio</label>
                    <select class="form-select" id="serviceSelect" aria-label="Seleccionar servicio">
                        @foreach ($order->services as $service)
                            <option value="{{ $service->id }}"> {{ $service->name }} </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Selecciona un floorplan</label>
                    <select class="form-select" id="floorplanSelect" aria-label="Seleccionar floorplan">
                        <option value="">-- Seleccione un floorplan --</option>
                        @foreach ($order->customer->floorplans as $floorplan)
                            <option value="{{ $floorplan->id }}" data-service="{{ $floorplan->service_id }}">
                                {{ $floorplan->filename }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" id="device-search-add-btn">Buscar</button>
                </div>

                <div class="devices-container mt-4" id="devicesContainer" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Dispositivos asociados al plano:</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllDevices">
                            <label class="form-check-label small" for="selectAllDevices">
                                Seleccionar todos
                            </label>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead class="sticky-top bg-light">
                                <tr>
                                    <th></th>
                                    <th>No. Plano</th>
                                    <th>Nombre</th>
                                    <th>Código</th>
                                    <th>Plano</th>
                                    <th>Version</th>
                                    <th>Área</th>
                                </tr>
                            </thead>
                            <tbody id="devicesList">
                                <!-- Los dispositivos se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted" id="selectedCount">
                            Ningún dispositivo seleccionado
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="addDeviceBtn" disabled>Agregar dispositivos</button>
            </div>
        </div>
    </div>
</div>

<script>
    const customer_id = {{ $order->customer_id }};

    document.addEventListener('DOMContentLoaded', function() {
        const serviceSelect = document.getElementById('serviceSelect');
        const floorplanSelect = document.getElementById('floorplanSelect');
        const devicesContainer = document.getElementById('devicesContainer');
        const devicesList = document.getElementById('devicesList');
        const addDeviceBtn = document.getElementById('addDeviceBtn');
        const selectAllCheckbox = document.getElementById('selectAllDevices');
        const selectedCount = document.getElementById('selectedCount');

        let selectedDevices = new Set();

        // Filtrar floorplans cuando cambia el servicio
        serviceSelect.addEventListener('change', function() {
            const serviceId = this.value;
            const options = floorplanSelect.querySelectorAll('option');

            options.forEach(option => {
                if (option.value === '') return;

                if (option.dataset.service === serviceId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });

            // Resetear selección de floorplan
            floorplanSelect.value = '';
            devicesContainer.style.display = 'none';
            resetSelection();
        });

        // Cargar dispositivos cuando se selecciona un floorplan
        floorplanSelect.addEventListener('change', function() {
            const floorplanId = this.value;

            if (!floorplanId) {
                devicesContainer.style.display = 'none';
                resetSelection();
                return;
            }

            // Aquí harías una llamada AJAX para obtener los dispositivos del floorplan
            fetchDevicesByFloorplan(floorplanId);
        });

        // Seleccionar/deseleccionar todos los dispositivos
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const checkboxes = $('.device-checkbox');

            // Aplicar el estado a todos los checkboxes
            checkboxes.prop('checked', isChecked);

            // Ejecutar las funciones necesarias
            checkboxes.each(function() {
                updateDeviceSelection(this);
            });

            if (!isChecked) {
                resetSelection();
            }
        });

        $('#device-search-add-btn').on('click', function() {
            const floorplanSelect = document.getElementById('floorplanSelect');
            const selectedFloorplan = floorplanSelect.value;
            fetchDevicesByFloorplan(selectedFloorplan);
        });

        // Función para obtener dispositivos (simulada)
        function fetchDevicesByFloorplan(floorplanId) {
            // Simulamos una respuesta con datos de ejemplo
            var mockDevices = [];

            const formData = new FormData();
            formData.append('customer_id', customer_id);
            formData.append('floorplan_id', floorplanId);
            formData.append('service_id', serviceSelect.value)

            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            $.ajax({
                url: "{{ route('report.search.devices') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    mockDevices = response.devices;

                    // Limpiar lista anterior
                    devicesList.innerHTML = '';
                    resetSelection();
                    console.log(mockDevices);
                    // Llenar la tabla con los dispositivos
                    response.devices.forEach(device => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                <td>
                    <input type="checkbox" class="device-checkbox" value="${device.id}" 
                           data-device-name="${device.name}" onchange="updateDeviceSelection(this)">
                </td>
                <td>${device.nplan}</td>
                <td>${device.name}</td>
                <td>${device.code}</td>
                <td>${device.floorplan.name}</td>
                <td>${device.version}</td>
                <td>${device.area}</td>
            `;
                        devicesList.appendChild(row);
                    });

                    // Mostrar el contenedor de dispositivos
                    devicesContainer.style.display = 'block';
                    selectAllCheckbox.checked = false;
                },
                error: function(response) {},
                complete: function() {}
            });
        }

        // Función para actualizar la selección de dispositivos
        window.updateDeviceSelection = function(checkbox) {
            console.log('holi')
            console.log(checkbox)
            const deviceId = checkbox.value;
            const deviceName = checkbox.dataset.deviceName;

            if (checkbox.checked) {
                selectedDevices.add({
                    id: deviceId,
                    name: deviceName
                });
            } else {
                selectedDevices.delete(deviceId);
                selectAllCheckbox.checked = false;
            }

            updateSelectionUI();
        };

        // Actualizar la interfaz de selección
        function updateSelectionUI() {
            const count = selectedDevices.size;

            if (count === 0) {
                selectedCount.textContent = 'Ningún dispositivo seleccionado';
                addDeviceBtn.disabled = true;
            } else {
                selectedCount.textContent = `${count} dispositivo(s) seleccionado(s)`;
                addDeviceBtn.disabled = false;

                // Verificar si todos están seleccionados
                const totalCheckboxes = devicesList.querySelectorAll('.device-checkbox').length;
                selectAllCheckbox.checked = count === totalCheckboxes && totalCheckboxes > 0;
            }
        }

        // Resetear selección
        function resetSelection() {
            selectedDevices.clear();
            selectAllCheckbox.checked = false;
            updateSelectionUI();
            addDeviceBtn.disabled = true;
        }

        // Event listener para el botón de agregar
        addDeviceBtn.addEventListener('click', function() {
            if (selectedDevices.size === 0) {
                alert('Por favor, selecciona al menos un dispositivo');
                return;
            }

            const selectedFloorplan = floorplanSelect.value;
            if (!selectedFloorplan) {
                alert('Por favor, selecciona un floorplan primero');
                return;
            }

            // Convertir Set a Array para enviar
            const devicesArray = Array.from(selectedDevices);

            // Aquí implementarías la lógica para enviar los datos al servidor
            var formData = new FormData();
            console.log('Dispositivos seleccionados:', devicesArray);
            console.log('Floorplan seleccionado:', selectedFloorplan);

            formData.append('order_id', '{{ $order->id }}');
            formData.append('devices', JSON.stringify(devicesArray.map(d => d.id)));

            var csrfToken = $('meta[name="csrf-token"]').attr("content");


            $.ajax({
                url: "{{ route('report.assign.devices') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    $("#table-row-empty").hide();
                    loadDevices(response);
                },
                error: function(response) {},
                complete: function(response) {}
            });
        });

        function loadDevices(response) {
            //$('#loading').remove();

            $.each(response.devices, function(index, device) {
                const pestsText = device.pests.map(pest => `${pest.name} (${pest.total})`).join(', ');
                const productsText = device.products.map(product =>
                    `${product.name} (${product.quantity})`).join(', ');

                const isCheckedClass = device.states.is_checked ? 'text-success' : 'text-danger';
                const isScannedClass = device.states.is_scanned ? 'text-success' : 'text-danger';

                const isCheckedTitle = device.states.is_checked ? 'Revisado' : 'No revisado';
                const isScannedTitle = device.states.is_scanned ? 'Escaneado' : 'No escaneado';

                const row = `
                    <tr id="device-${device.id}" class="highlight">
                        <th scope="row">${device.nplan}</th>
                        <td class="fw-bold text-primary">${device.code}</td>
                        <td>${device.control_point.name ?? ''}</td>
                        <td>${device.floorplan.name}</td>
                        <td>${device.application_area.name}</td>
                        <td>${pestsText}</td>
                        <td>${productsText}</td>
                        <td>
                            <span class="${isCheckedClass} m-1 status-icon" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${isCheckedTitle}">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                            <span class="${isScannedClass} m-1 status-icon" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${isScannedTitle}">
                                <i class="bi bi-qr-code"></i>
                            </span>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary btn-sm btn-action" data-device='${JSON.stringify(device)}' onclick="openReviewModal(this, ${ device.service.id })">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;

                $('#table-body-devices').append(row);
            });

            // Inicializar tooltips de Bootstrap
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Agregar evento de clic para las filas
            $('tr[id^="device-"]').click(function() {
                $('tr[id^="device-"]').removeClass('table-active');
                $(this).addClass('table-active');
            });
        }

        // Simular búsqueda en tiempo real
        $('#search-input').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('tr[id^="device-"]').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
