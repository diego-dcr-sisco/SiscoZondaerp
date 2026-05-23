    <!-- Modal para crear zona comercial -->
    <style>
        .customer-badge {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 15px;
            padding: 5px 12px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .customer-badge .remove-btn {
            color: #f44336;
            cursor: pointer;
            font-weight: bold;
            margin-left: 5px;
        }

        .customer-badge .remove-btn:hover {
            color: #d32f2f;
        }

        .customer-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .customer-item:hover {
            background-color: #f8f9fa;
        }

        .customer-item:last-child {
            border-bottom: none;
        }

        .customer-checkbox {
            cursor: pointer;
        }

        .customer-info {
            flex: 1;
        }

        .customer-selected {
            background-color: #e8f5e8;
            border-left: 3px solid #28a745;
        }
    </style>

    <!-- Modal para crear zona comercial -->
    <div class="modal fade" id="createComercialZoneModal" tabindex="-1" role="dialog"
        aria-labelledby="createComercialZoneModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Zonas comerciales</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('comercial-zones.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Campo de búsqueda -->
                        <div class="form-group mb-3">
                            <label class="form-label" for="customerSearch">Buscar Clientes</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="customerSearch"
                                    placeholder="Ingrese nombre, teléfono o dirección del cliente">
                                <button type="button" class="btn btn-outline-secondary" id="btnSearchCustomer">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="searchLoading" class="spinner-border text-primary mt-2" role="status"
                                style="display:none;">
                                <span class="visually-hidden">Buscando...</span>
                            </div>
                        </div>

                        <!-- Resultados de la búsqueda -->
                        <div id="customerResults" class="mt-3 mb-3 p-2 border rounded"
                            style="max-height: 200px; overflow-y: auto; display: none;">
                            <h6 class="text-muted mb-2">Resultados de búsqueda:</h6>
                            <!-- Los resultados se mostrarán aquí -->
                        </div>

                        <!-- Clientes seleccionados -->
                        <div id="selectedCustomers" class="mb-3 p-3 border rounded">
                            <h6 class="text-muted mb-2">Clientes seleccionados: <span class="badge bg-primary"
                                    id="selectedCount">0</span></h6>
                            <div id="selectedCustomersList" class="d-flex flex-wrap gap-2">
                                <span class="text-muted">No hay clientes seleccionados</span>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                                <i class="fas fa-check-square"></i> Seleccionar todos
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="deselectAllBtn">
                                <i class="fas fa-times-circle"></i> Deseleccionar todos
                            </button>
                        </div>

                        <!-- Campos originales del formulario -->
                        <div class="form-group mb-3">
                            <label class="form-label is-required" for="name">Nombre de la zona comercial</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}"
                                placeholder="Ingrese el nombre de la zona comercial" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="4" placeholder="Ingrese una descripción de la zona comercial">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campo oculto para almacenar los IDs de clientes -->
                        <input type="hidden" name="customer_ids" id="customerIds" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" onclick="submitForm(event)">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let selectedCustomers = [];
            let debounceTimer;
            let currentSearchResults = [];

            // Función para buscar clientes
            function searchCustomers() {
                let query = $('#customerSearch').val().trim();

                if (query.length < 2) {
                    $('#customerResults').hide().html('');
                    return;
                }

                $('#searchLoading').show();
                $('#customerResults').hide().html('');

                var csrfToken = $('meta[name="csrf-token"]').attr("content");

                var formData = new FormData();
                if (query == "") {
                    alert("Debes introducir al menos 1 dato de busqueda.");
                    return;
                }

                //deleteCustomer();

                formData.append("customer_name", query);
                formData.append("customer_phone", '');
                formData.append("customer_address", '');

                $.ajax({
                    url: "{{ route('order.search.customer') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    data: formData,
                    success: function(response) {
                        $('#searchLoading').hide();
                        currentSearchResults = response.customers || [];

                        if (currentSearchResults.length > 0) {
                            let html = '';
                            currentSearchResults.forEach(function(customer) {
                                // Verificar si el cliente ya está seleccionado
                                const isSelected = selectedCustomers.some(c => c.id === customer
                                    .id);
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
                            $('#customerResults').html(html).show();
                        } else {
                            $('#customerResults').html(
                                '<p class="text-muted p-2">No se encontraron clientes.</p>').show();
                        }
                    },
                    error: function() {
                        $('#searchLoading').hide();
                        $('#customerResults').html(
                            '<p class="text-danger p-2">Error en la búsqueda.</p>').show();
                    }
                });
            }

            // Evento de búsqueda con debounce
            $('#customerSearch').on('keyup', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(searchCustomers, 300);
            });

            $('#btnSearchCustomer').on('click', searchCustomers);

            // Función para actualizar la lista de clientes seleccionados
            function updateSelectedCustomers() {
                const selectedList = $('#selectedCustomersList');
                const selectedCount = $('#selectedCount');

                selectedCount.text(selectedCustomers.length);

                if (selectedCustomers.length === 0) {
                    selectedList.html('<span class="text-muted">No hay clientes seleccionados</span>');
                    $('#customerIds').val('');
                    return;
                }

                let html = '';
                selectedCustomers.forEach((customer, index) => {
                    html += `
                <div class="customer-badge">
                    ${customer.name}
                    <span class="remove-btn" data-customer-id="${customer.id}">×</span>
                </div>
            `;
                });

                selectedList.html(html);
                $('#customerIds').val(
                    JSON.stringify(selectedCustomers.map(c => c.id))
                );
            }

            // Manejar selección/deselección de checkboxes
            $(document).on('change', '.customer-checkbox', function() {
                const customerData = $(this).data('customer');
                const isChecked = $(this).is(':checked');
                const customerId = customerData.id;

                if (isChecked) {
                    // Agregar cliente si no está ya seleccionado
                    if (!selectedCustomers.some(c => c.id === customerId)) {
                        selectedCustomers.push(customerData);
                        $(this).closest('.customer-item').addClass('customer-selected');
                    }
                } else {
                    // Remover cliente
                    selectedCustomers = selectedCustomers.filter(c => c.id !== customerId);
                    $(this).closest('.customer-item').removeClass('customer-selected');
                }

                updateSelectedCustomers();
            });

            // Remover cliente seleccionado desde los badges
            $(document).on('click', '.remove-btn', function() {
                const customerId = $(this).data('customer-id');

                // Remover de la lista de seleccionados
                selectedCustomers = selectedCustomers.filter(c => c.id !== customerId);

                // Desmarcar checkbox correspondiente
                $(`.customer-checkbox[data-customer*='"id":${customerId}']`).prop('checked', false)
                    .closest('.customer-item').removeClass('customer-selected');

                updateSelectedCustomers();
            });

            // Seleccionar todos los clientes visibles
            $('#selectAllBtn').on('click', function() {
                currentSearchResults.forEach(customer => {
                    if (!selectedCustomers.some(c => c.id === customer.id)) {
                        selectedCustomers.push(customer);
                    }
                });

                // Marcar todos los checkboxes y actualizar UI
                $('.customer-checkbox').prop('checked', true);
                $('.customer-item').addClass('customer-selected');
                updateSelectedCustomers();
            });

            // Deseleccionar todos los clientes
            $('#deselectAllBtn').on('click', function() {
                // Solo remover los clientes que están en los resultados actuales
                const currentIds = currentSearchResults.map(c => c.id);
                selectedCustomers = selectedCustomers.filter(c => !currentIds.includes(c.id));

                // Desmarcar todos los checkboxes y actualizar UI
                $('.customer-checkbox').prop('checked', false);
                $('.customer-item').removeClass('customer-selected');
                updateSelectedCustomers();
            });

            // Limpiar búsqueda al cerrar el modal
            $('#createComercialZoneModal').on('hidden.bs.modal', function() {
                $('#customerSearch').val('');
                $('#customerResults').hide().html('');
                currentSearchResults = [];
            });

            // Validación del formulario
            function submitForm(e) {
                if (selectedCustomers.length === 0) {
                    e.preventDefault();
                    alert('Por favor, seleccione al menos un cliente.');
                    return false;
                }
            }

            // Inicializar contador
            updateSelectedCustomers();
        });
    </script>
