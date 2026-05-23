<!-- Modal para editar zona comercial -->
<div class="modal fade" id="editComercialZoneModal" tabindex="-1" role="dialog"
    aria-labelledby="editComercialZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editComercialZoneModalLabel">Editar Zona Comercial</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editComercialZoneForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Campo de búsqueda -->
                    <div class="form-group mb-3">
                        <label class="form-label" for="editCustomerSearch">Buscar Clientes</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="editCustomerSearch"
                                placeholder="Ingrese nombre, teléfono o dirección del cliente">
                            <button type="button" class="btn btn-outline-secondary" id="editBtnSearchCustomer">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <div id="editSearchLoading" class="spinner-border text-primary mt-2" role="status"
                            style="display:none;">
                            <span class="visually-hidden">Buscando...</span>
                        </div>
                    </div>

                    <!-- Resultados de la búsqueda -->
                    <div id="editCustomerResults" class="mt-3 mb-3 p-2 border rounded"
                        style="max-height: 200px; overflow-y: auto; display: none;">
                        <h6 class="text-muted mb-2">Resultados de búsqueda:</h6>
                        <!-- Los resultados se mostrarán aquí -->
                    </div>

                    <!-- Clientes seleccionados -->
                    <div id="editSelectedCustomers" class="mb-3 p-3 border rounded">
                        <h6 class="text-muted mb-2">Clientes seleccionados: <span class="badge bg-primary"
                                id="editSelectedCount">0</span></h6>
                        <div id="editSelectedCustomersList" class="d-flex flex-wrap gap-2">
                            <span class="text-muted">No hay clientes seleccionados</span>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="editSelectAllBtn">
                            <i class="fas fa-check-square"></i> Seleccionar todos
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="editDeselectAllBtn">
                            <i class="fas fa-times-circle"></i> Deseleccionar todos
                        </button>
                    </div>

                    <!-- Campos del formulario -->
                    <div class="form-group mb-3">
                        <label class="form-label" for="editName">Nombre de la zona comercial <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="editName"
                            name="name" placeholder="Ingrese el nombre de la zona comercial" required>
                        <div class="invalid-feedback" id="editNameError"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="editCode">Código</label>
                        <input type="text" class="form-control" id="editCode" name="code"
                            placeholder="Código de la zona comercial">
                        <div class="invalid-feedback" id="editCodeError"></div>
                    </div>

                    <div class="form-group">
                        <label for="editDescription" class="form-label">Descripción</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="4"
                            placeholder="Ingrese una descripción de la zona comercial"></textarea>
                        <div class="invalid-feedback" id="editDescriptionError"></div>
                    </div>

                    <!-- Campo oculto para almacenar los IDs de clientes -->
                    <input type="hidden" name="customer_ids" id="editCustomerIds" value="">
                    <input type="hidden" name="comercial_zone_id" id="editComercialZoneId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let editSelectedCustomers = [];
        let editCurrentSearchResults = [];
        let editDebounceTimer;

        // Función para inicializar el modal de edición
        function initializeEditModal() {
            // Limpiar selecciones anteriores
            editSelectedCustomers = [];
            editCurrentSearchResults = [];
            $('#editCustomerSearch').val('');
            $('#editCustomerResults').hide().html('');
            $('#editSelectedCustomersList').html(
                '<span class="text-muted">No hay clientes seleccionados</span>');
            $('#editSelectedCount').text('0');
            $('#editCustomerIds').val('');
            $('#editNameError, #editCodeError, #editDescriptionError').text('').hide();
            $('#editName, #editCode, #editDescription').removeClass('is-invalid');
        }

        // Abrir modal de edición
        $(document).on('click', '.edit-comercial-zone', function() {
            initializeEditModal();

            const comercialZoneId = $(this).data('id');
            const name = $(this).data('name');
            const code = $(this).data('code');
            const description = $(this).data('description');
            const customerIds = $(this).data('customers').filter(id => id !== '');

            // Llenar campos del formulario
            $('#editComercialZoneId').val(comercialZoneId);
            $('#editName').val(name);
            $('#editCode').val(code || '');
            $('#editDescription').val(description || '');

            // Establecer la ruta del formulario
            $('#editComercialZoneForm').attr('action', `/comercial-zones/${comercialZoneId}`);

            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            var formData = new FormData();

            formData.append("customer_ids", JSON.stringify(customerIds));

            // Cargar clientes seleccionados si existen
            if (customerIds.length > 0 && customerIds[0] !== '') {
                // Hacer una petición para obtener los datos completos de los clientes
                $.ajax({
                    url: "{{ route('order.search.customer') }}",
                    method: 'POST',
                    contentType: false,
                    processData: false,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    data: formData,
                    success: function(response) {
                        if (response.customers && response.customers.length > 0) {
                            editSelectedCustomers = response.customers;
                            updateEditSelectedCustomers();
                        }
                    }
                });
            }

            $('#editComercialZoneModal').modal('show');
        });

        // Función para buscar clientes en el modal de edición
        function searchEditCustomers() {
            let query = $('#editCustomerSearch').val().trim();

            if (query.length < 2) {
                $('#editCustomerResults').hide().html('');
                return;
            }

            $('#editSearchLoading').show();
            $('#editCustomerResults').hide().html('');

            $.ajax({
                url: "{{ route('order.search.customer') }}",
                method: 'GET',
                data: {
                    customer_name: query,
                    customer_phone: query,
                    customer_address: query
                },
                success: function(response) {
                    $('#editSearchLoading').hide();
                    editCurrentSearchResults = response.customers || [];

                    if (editCurrentSearchResults.length > 0) {
                        let html = '';
                        editCurrentSearchResults.forEach(function(customer) {
                            const isSelected = editSelectedCustomers.some(c => c.id ===
                                customer.id);
                            const selectedClass = isSelected ? 'customer-selected' : '';

                            html += `
                            <div class="customer-item ${selectedClass}" data-customer-id="${customer.id}">
                                <input type="checkbox" class="customer-checkbox" 
                                       data-customer='${JSON.stringify(customer)}' 
                                       ${isSelected ? 'checked' : ''}>
                                <div class="customer-info">
                                    <strong>${customer.name}</strong> (${customer.code})<br>
                                    <small>${customer.address} - ${customer.type}</small>
                                </div>
                            </div>
                        `;
                        });
                        $('#editCustomerResults').html(html).show();
                    } else {
                        $('#editCustomerResults').html(
                            '<p class="text-muted p-2">No se encontraron clientes.</p>').show();
                    }
                },
                error: function() {
                    $('#editSearchLoading').hide();
                    $('#editCustomerResults').html(
                        '<p class="text-danger p-2">Error en la búsqueda.</p>').show();
                }
            });
        }

        // Eventos de búsqueda para edición
        $('#editCustomerSearch').on('keyup', function() {
            clearTimeout(editDebounceTimer);
            editDebounceTimer = setTimeout(searchEditCustomers, 300);
        });

        $('#editBtnSearchCustomer').on('click', searchEditCustomers);

        // Función para actualizar clientes seleccionados en edición
        function updateEditSelectedCustomers() {
            const selectedList = $('#editSelectedCustomersList');
            const selectedCount = $('#editSelectedCount');

            selectedCount.text(editSelectedCustomers.length);

            if (editSelectedCustomers.length === 0) {
                selectedList.html('<span class="text-muted">No hay clientes seleccionados</span>');
                $('#editCustomerIds').val('');
                return;
            }

            let html = '';
            editSelectedCustomers.forEach((customer) => {
                html += `
                <div class="customer-badge">
                    ${customer.name}
                    <span class="remove-btn" data-customer-id="${customer.id}">×</span>
                </div>
            `;
            });

            selectedList.html(html);
            $('#editCustomerIds').val(editSelectedCustomers.map(c => c.id).join(','));
        }

        // Manejar checkboxes en edición
        $(document).on('change', '#editCustomerResults .customer-checkbox', function() {
            const customerData = $(this).data('customer');
            const isChecked = $(this).is(':checked');
            const customerId = customerData.id;

            if (isChecked) {
                if (!editSelectedCustomers.some(c => c.id === customerId)) {
                    editSelectedCustomers.push(customerData);
                    $(this).closest('.customer-item').addClass('customer-selected');
                }
            } else {
                editSelectedCustomers = editSelectedCustomers.filter(c => c.id !== customerId);
                $(this).closest('.customer-item').removeClass('customer-selected');
            }

            updateEditSelectedCustomers();
        });

        // Remover cliente en edición
        $(document).on('click', '#editSelectedCustomersList .remove-btn', function() {
            const customerId = $(this).data('customer-id');

            editSelectedCustomers = editSelectedCustomers.filter(c => c.id !== customerId);

            $(`#editCustomerResults .customer-checkbox[data-customer*='"id":${customerId}']`)
                .prop('checked', false)
                .closest('.customer-item').removeClass('customer-selected');

            updateEditSelectedCustomers();
        });

        // Botones de selección/deselección masiva en edición
        $('#editSelectAllBtn').on('click', function() {
            editCurrentSearchResults.forEach(customer => {
                if (!editSelectedCustomers.some(c => c.id === customer.id)) {
                    editSelectedCustomers.push(customer);
                }
            });

            $('#editCustomerResults .customer-checkbox').prop('checked', true);
            $('#editCustomerResults .customer-item').addClass('customer-selected');
            updateEditSelectedCustomers();
        });

        $('#editDeselectAllBtn').on('click', function() {
            const currentIds = editCurrentSearchResults.map(c => c.id);
            editSelectedCustomers = editSelectedCustomers.filter(c => !currentIds.includes(c.id));

            $('#editCustomerResults .customer-checkbox').prop('checked', false);
            $('#editCustomerResults .customer-item').removeClass('customer-selected');
            updateEditSelectedCustomers();
        });

        // Envío del formulario de edición
        $('#editComercialZoneForm').on('submit', function(e) {
            e.preventDefault();

            if (editSelectedCustomers.length === 0) {
                alert('Por favor, seleccione al menos un cliente.');
                return false;
            }

            const formData = $(this).serialize();
            const url = $(this).attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editComercialZoneModal').modal('hide');
                        location.reload(); // Recargar para ver los cambios
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $(`#edit${key.charAt(0).toUpperCase() + key.slice(1)}Error`)
                                .text(value[0]).show();
                            $(`#edit${key.charAt(0).toUpperCase() + key.slice(1)}`)
                                .addClass('is-invalid');
                        });
                    }
                }
            });
        });

        // Limpiar errores al cambiar campos
        $('#editName, #editCode, #editDescription').on('input', function() {
            const fieldName = $(this).attr('name');
            $(this).removeClass('is-invalid');
            $(`#edit${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)}Error`).hide();
        });
    });
</script>
