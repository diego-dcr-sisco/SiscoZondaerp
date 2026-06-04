<style>
    #createComercialZoneModal .commercial-zone-panel {
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        background: #fff;
    }

    #createComercialZoneModal .commercial-zone-panel-header {
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        padding: .75rem 1rem;
        font-weight: 700;
    }

    #createComercialZoneModal .customer-results,
    #createComercialZoneModal .selected-customers-box {
        min-height: 220px;
        max-height: 320px;
        overflow-y: auto;
    }

    #createComercialZoneModal .customer-item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .75rem;
        border-bottom: 1px solid #f1f3f5;
        transition: background-color .2s ease;
    }

    #createComercialZoneModal .customer-item:hover {
        background-color: #f8f9fa;
    }

    #createComercialZoneModal .customer-item:last-child {
        border-bottom: 0;
    }

    #createComercialZoneModal .customer-selected {
        background-color: #eef7f0;
        border-left: 3px solid #198754;
    }

    #createComercialZoneModal .customer-badge {
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        padding: .45rem .6rem;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: #fff;
        max-width: 100%;
    }

    #createComercialZoneModal .customer-badge-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #createComercialZoneModal .remove-selected-customer {
        border: 0;
        background: transparent;
        color: #dc3545;
        padding: 0;
        line-height: 1;
    }
</style>

<div class="modal fade" id="createComercialZoneModal" tabindex="-1"
    aria-labelledby="createComercialZoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="{{ route('comercial-zones.store') }}" method="POST" id="createComercialZoneForm"
            class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createComercialZoneModalLabel">
                    <i class="bi bi-geo-alt-fill"></i> Crear zona comercial
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-4 col-12">
                        <div class="commercial-zone-panel h-100">
                            <div class="commercial-zone-panel-header">
                                Datos de la zona
                            </div>
                            <div class="p-3">
                                <div class="mb-3">
                                    <label class="form-label is-required" for="name">Nombre</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <input type="text"
                                            class="form-control @error('name') is-invalid @enderror" id="name"
                                            name="name" value="{{ old('name') }}"
                                            placeholder="Ej. Zona Centro" required>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="description" class="form-label">Descripcion</label>
                                    <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" id="description"
                                        name="description" rows="7" placeholder="Notas internas o criterio de agrupacion">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <div class="commercial-zone-panel h-100">
                            <div class="commercial-zone-panel-header d-flex justify-content-between align-items-center gap-2">
                                <span>Buscar clientes</span>
                                <div id="createSearchLoading" class="spinner-border spinner-border-sm text-primary"
                                    role="status" style="display:none;">
                                    <span class="visually-hidden">Buscando...</span>
                                </div>
                            </div>
                            <div class="p-3">
                                <label class="form-label" for="createCustomerSearch">Cliente</label>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="createCustomerSearch"
                                        placeholder="Nombre, telefono o direccion">
                                    <button type="button" class="btn btn-outline-secondary" id="createBtnSearchCustomer"
                                        data-bs-toggle="tooltip" title="Buscar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>

                                <div id="createCustomerResults" class="customer-results border rounded">
                                    <div class="text-muted small p-3">
                                        Escribe al menos 2 caracteres para buscar clientes.
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm flex-fill"
                                        id="createSelectAllBtn">
                                        <i class="bi bi-check2-square"></i> Seleccionar visibles
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm flex-fill"
                                        id="createDeselectAllBtn">
                                        <i class="bi bi-x-square"></i> Quitar visibles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <div class="commercial-zone-panel h-100">
                            <div class="commercial-zone-panel-header d-flex justify-content-between align-items-center gap-2">
                                <span>Clientes seleccionados</span>
                                <span class="badge bg-primary" id="createSelectedCount">0</span>
                            </div>
                            <div class="p-3">
                                <div id="createSelectedCustomersList"
                                    class="selected-customers-box d-flex flex-column gap-2 border rounded p-2">
                                    <span class="text-muted small">No hay clientes seleccionados</span>
                                </div>
                                @error('customer_ids')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="customer_ids" id="createCustomerIds" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check2-circle"></i> Guardar zona
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        let createSelectedCustomers = [];
        let createDebounceTimer;
        let createCurrentSearchResults = [];
        const createModal = $('#createComercialZoneModal');

        function renderCreateCustomerResults(customers) {
            if (customers.length === 0) {
                $('#createCustomerResults').html(
                    '<div class="text-muted small p-3">No se encontraron clientes.</div>');
                return;
            }

            let html = '';
            customers.forEach(function(customer) {
                const isSelected = createSelectedCustomers.some(c => c.id === customer.id);
                const selectedClass = isSelected ? 'customer-selected' : '';

                html += `
                    <label class="customer-item ${selectedClass}" data-customer-id="${customer.id}">
                        <input type="checkbox" class="form-check-input mt-1 create-customer-checkbox"
                            data-customer='${JSON.stringify(customer)}' ${isSelected ? 'checked' : ''}>
                        <span class="flex-grow-1">
                            <span class="fw-semibold d-block">${customer.name}</span>
                            <span class="text-muted small">${customer.code || '-'} - ${customer.type || '-'}</span>
                            <span class="text-muted small d-block">${customer.address || 'Sin direccion'}</span>
                        </span>
                    </label>
                `;
            });

            $('#createCustomerResults').html(html);
        }

        function searchCreateCustomers() {
            const query = $('#createCustomerSearch').val().trim();

            if (query.length < 2) {
                $('#createCustomerResults').html(
                    '<div class="text-muted small p-3">Escribe al menos 2 caracteres para buscar clientes.</div>');
                createCurrentSearchResults = [];
                return;
            }

            $('#createSearchLoading').show();

            const formData = new FormData();
            formData.append('customer_name', query);
            formData.append('customer_phone', '');
            formData.append('customer_address', '');

            $.ajax({
                url: "{{ route('order.search.customer') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    createCurrentSearchResults = response.customers || [];
                    renderCreateCustomerResults(createCurrentSearchResults);
                },
                error: function() {
                    $('#createCustomerResults').html(
                        '<div class="text-danger small p-3">Error en la busqueda.</div>');
                },
                complete: function() {
                    $('#createSearchLoading').hide();
                }
            });
        }

        function updateCreateSelectedCustomers() {
            const selectedList = $('#createSelectedCustomersList');
            $('#createSelectedCount').text(createSelectedCustomers.length);

            if (createSelectedCustomers.length === 0) {
                selectedList.html('<span class="text-muted small">No hay clientes seleccionados</span>');
                $('#createCustomerIds').val('');
                return;
            }

            selectedList.html(createSelectedCustomers.map(function(customer) {
                return `
                    <div class="customer-badge">
                        <span class="customer-badge-name">${customer.name}</span>
                        <button type="button" class="remove-selected-customer"
                            data-customer-id="${customer.id}" aria-label="Quitar cliente">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                `;
            }).join(''));

            $('#createCustomerIds').val(JSON.stringify(createSelectedCustomers.map(c => c.id)));
        }

        $('#createCustomerSearch').on('keyup', function() {
            clearTimeout(createDebounceTimer);
            createDebounceTimer = setTimeout(searchCreateCustomers, 300);
        });

        $('#createBtnSearchCustomer').on('click', searchCreateCustomers);

        $(document).on('change', '#createComercialZoneModal .create-customer-checkbox', function() {
            const customerData = $(this).data('customer');
            const isChecked = $(this).is(':checked');
            const customerId = customerData.id;

            if (isChecked && !createSelectedCustomers.some(c => c.id === customerId)) {
                createSelectedCustomers.push(customerData);
                $(this).closest('.customer-item').addClass('customer-selected');
            }

            if (!isChecked) {
                createSelectedCustomers = createSelectedCustomers.filter(c => c.id !== customerId);
                $(this).closest('.customer-item').removeClass('customer-selected');
            }

            updateCreateSelectedCustomers();
        });

        $(document).on('click', '#createComercialZoneModal .remove-selected-customer', function() {
            const customerId = $(this).data('customer-id');
            createSelectedCustomers = createSelectedCustomers.filter(c => c.id !== customerId);

            $(`#createComercialZoneModal .customer-item[data-customer-id="${customerId}"] .create-customer-checkbox`)
                .prop('checked', false)
                .closest('.customer-item').removeClass('customer-selected');

            updateCreateSelectedCustomers();
        });

        $('#createSelectAllBtn').on('click', function() {
            createCurrentSearchResults.forEach(function(customer) {
                if (!createSelectedCustomers.some(c => c.id === customer.id)) {
                    createSelectedCustomers.push(customer);
                }
            });

            $('#createComercialZoneModal .create-customer-checkbox').prop('checked', true);
            $('#createComercialZoneModal .customer-item').addClass('customer-selected');
            updateCreateSelectedCustomers();
        });

        $('#createDeselectAllBtn').on('click', function() {
            const currentIds = createCurrentSearchResults.map(c => c.id);
            createSelectedCustomers = createSelectedCustomers.filter(c => !currentIds.includes(c.id));

            $('#createComercialZoneModal .create-customer-checkbox').prop('checked', false);
            $('#createComercialZoneModal .customer-item').removeClass('customer-selected');
            updateCreateSelectedCustomers();
        });

        createModal.on('hidden.bs.modal', function() {
            $('#createCustomerSearch').val('');
            $('#createCustomerResults').html(
                '<div class="text-muted small p-3">Escribe al menos 2 caracteres para buscar clientes.</div>');
            createCurrentSearchResults = [];
        });

        $('#createComercialZoneForm').on('submit', function(e) {
            if (createSelectedCustomers.length === 0) {
                e.preventDefault();
                alert('Por favor, seleccione al menos un cliente.');
                return false;
            }
        });

        updateCreateSelectedCustomers();
    });
</script>
