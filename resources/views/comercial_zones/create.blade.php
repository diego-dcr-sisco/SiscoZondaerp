@extends('layouts.app')

@section('content')
    @include('components.page-header', [
        'title' => 'CREAR ZONA COMERCIAL',
        'icon' => 'bi-geo-alt-fill',
        'iconColor' => 'text-primary',
        'backRoute' => route('comercial-zones.index'),
    ])

    <style>
        .customer-results,
        .selected-customers-box {
            min-height: 360px;
            max-height: 520px;
            overflow-y: auto;
        }

        .customer-item {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .75rem;
            border-bottom: 1px solid #f1f3f5;
            transition: background-color .2s ease;
        }

        .customer-item:hover {
            background-color: #f8f9fa;
        }

        .customer-item:last-child {
            border-bottom: 0;
        }

        .customer-selected {
            background-color: #eef7f0;
            border-left: 3px solid #198754;
        }

        .customer-badge {
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: .45rem .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: .45rem;
            background: #fff;
            width: 100%;
        }

        .customer-badge-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .remove-selected-customer {
            border: 0;
            background: transparent;
            color: #dc3545;
            padding: 0;
            line-height: 1;
        }
    </style>

    <div class="container-fluid p-0">
        <form action="{{ route('comercial-zones.store') }}" method="POST" id="createComercialZoneForm" class="m-3">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-lg-4 col-12">
                    <div class="card h-100">
                        <div class="card-header bg-light fw-bold">
                            <i class="bi bi-card-text"></i> Datos de la zona
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label is-required" for="name">Nombre</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}"
                                        placeholder="Ej. Zona Centro" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripcion</label>
                                <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="8" placeholder="Notas internas o criterio de agrupacion">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="customer_ids" id="createCustomerIds"
                                value="{{ old('customer_ids') }}">
                            @error('customer_ids')
                                <div class="alert alert-danger small py-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-xl-5 col-lg-4 col-12">
                    <div class="card h-100">
                        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center gap-2">
                            <span><i class="bi bi-search"></i> Buscar clientes</span>
                            <div id="createSearchLoading" class="spinner-border spinner-border-sm text-primary"
                                role="status" style="display:none;">
                                <span class="visually-hidden">Buscando...</span>
                            </div>
                        </div>
                        <div class="card-body">
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

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de seleccion">
                                    <button type="button" class="btn btn-outline-primary" id="createSelectAllBtn"
                                        data-bs-toggle="tooltip" title="Seleccionar clientes visibles">
                                        <i class="bi bi-check2-square"></i> Seleccionar visibles
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="createDeselectAllBtn"
                                        data-bs-toggle="tooltip" title="Quitar clientes visibles">
                                        <i class="bi bi-x-square"></i> Quitar visibles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-12">
                    <div class="card h-100">
                        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center gap-2">
                            <span><i class="bi bi-people-fill"></i> Clientes seleccionados</span>
                            <span class="badge bg-primary" id="createSelectedCount">0</span>
                        </div>
                        <div class="card-body">
                            <div id="createSelectedCustomersList"
                                class="selected-customers-box d-flex flex-column gap-2 border rounded p-2">
                                <span class="text-muted small">No hay clientes seleccionados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('comercial-zones.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2-circle"></i> Guardar zona
            </button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            let createSelectedCustomers = [];
            let createDebounceTimer;
            let createCurrentSearchResults = [];

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
                        '<div class="text-muted small p-3">Escribe al menos 2 caracteres para buscar clientes.</div>'
                        );
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

            $(document).on('change', '.create-customer-checkbox', function() {
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

            $(document).on('click', '.remove-selected-customer', function() {
                const customerId = $(this).data('customer-id');
                createSelectedCustomers = createSelectedCustomers.filter(c => c.id !== customerId);

                $(`.customer-item[data-customer-id="${customerId}"] .create-customer-checkbox`)
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

                $('.create-customer-checkbox').prop('checked', true);
                $('.customer-item').addClass('customer-selected');
                updateCreateSelectedCustomers();
            });

            $('#createDeselectAllBtn').on('click', function() {
                const currentIds = createCurrentSearchResults.map(c => c.id);
                createSelectedCustomers = createSelectedCustomers.filter(c => !currentIds.includes(c.id));

                $('.create-customer-checkbox').prop('checked', false);
                $('.customer-item').removeClass('customer-selected');
                updateCreateSelectedCustomers();
            });

            $('#createComercialZoneForm').on('submit', function(e) {
                if (createSelectedCustomers.length === 0) {
                    e.preventDefault();
                    alert('Por favor, seleccione al menos un cliente.');
                    return false;
                }
            });

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
                new bootstrap.Tooltip(element);
            });

            updateCreateSelectedCustomers();
        });
    </script>
@endsection
