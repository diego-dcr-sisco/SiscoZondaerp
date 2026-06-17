@extends('layouts.app')

@section('content')
    @php
        $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    @endphp

    @include('components.page-header', [
        'title' => 'EDITAR ZONA COMERCIAL',
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
        <form action="{{ route('comercial-zones.update', ['id' => $comercialZone->id]) }}" method="POST"
            id="editComercialZoneForm" class="m-3">
            @csrf
            @method('PUT')

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
                                        id="name" name="name" value="{{ old('name', $comercialZone->name) }}"
                                        placeholder="Ej. Zona Centro" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="code">Codigo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-upc"></i></span>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                        id="code" name="code" value="{{ old('code', $comercialZone->code) }}"
                                        placeholder="Codigo interno">
                                </div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripcion</label>
                                <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="7" placeholder="Notas internas o criterio de agrupacion">{{ old('description', $comercialZone->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="customer_ids" id="editCustomerIds"
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
                            <div id="editSearchLoading" class="spinner-border spinner-border-sm text-primary"
                                role="status" style="display:none;">
                                <span class="visually-hidden">Buscando...</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <label class="form-label" for="editCustomerSearch">Cliente</label>
                            <div class="input-group input-group-sm mb-3">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="editCustomerSearch"
                                    placeholder="Nombre, telefono o direccion">
                                <button type="button" class="btn btn-outline-secondary" id="editBtnSearchCustomer"
                                    data-bs-toggle="tooltip" title="Buscar">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>

                            <div id="editCustomerResults" class="customer-results border rounded">
                                <div class="text-muted small p-3">
                                    Escribe al menos 2 caracteres para buscar clientes.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de seleccion">
                                    <button type="button" class="btn btn-outline-primary" id="editSelectAllBtn"
                                        data-bs-toggle="tooltip" title="Seleccionar clientes visibles">
                                        <i class="bi bi-check2-square"></i> Seleccionar visibles
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="editDeselectAllBtn"
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
                            <span class="badge bg-primary" id="editSelectedCount">0</span>
                        </div>
                        <div class="card-body">
                            <div id="editSelectedCustomersList"
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
                <i class="bi bi-check2-circle"></i> Actualizar zona
            </button>
        </form>
    </div>

    @php
        $selectedCustomers = $comercialZone->customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'code' => $customer->code ?? null,
                'type' => $customer->type ?? null,
                'address' => $customer->address ?? null,
            ];
        })->values();
    @endphp

    <script>
        $(document).ready(function() {
            let editSelectedCustomers = @json($selectedCustomers);
            let editDebounceTimer;
            let editCurrentSearchResults = [];

            function renderEditCustomerResults(customers) {
                if (customers.length === 0) {
                    $('#editCustomerResults').html(
                        '<div class="text-muted small p-3">No se encontraron clientes.</div>');
                    return;
                }

                let html = '';
                customers.forEach(function(customer) {
                    const isSelected = editSelectedCustomers.some(c => c.id === customer.id);
                    const selectedClass = isSelected ? 'customer-selected' : '';

                    html += `
                        <label class="customer-item ${selectedClass}" data-customer-id="${customer.id}">
                            <input type="checkbox" class="form-check-input mt-1 edit-customer-checkbox"
                                data-customer='${JSON.stringify(customer)}' ${isSelected ? 'checked' : ''}>
                            <span class="flex-grow-1">
                                <span class="fw-semibold d-block">${customer.name}</span>
                                <span class="text-muted small">${customer.code || '-'} - ${customer.type || '-'}</span>
                                <span class="text-muted small d-block">${customer.address || 'Sin direccion'}</span>
                            </span>
                        </label>
                    `;
                });

                $('#editCustomerResults').html(html);
            }

            function searchEditCustomers() {
                const query = $('#editCustomerSearch').val().trim();

                if (query.length < 2) {
                    $('#editCustomerResults').html(
                        '<div class="text-muted small p-3">Escribe al menos 2 caracteres para buscar clientes.</div>'
                    );
                    editCurrentSearchResults = [];
                    return;
                }

                $('#editSearchLoading').show();

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
                        editCurrentSearchResults = response.customers || [];
                        renderEditCustomerResults(editCurrentSearchResults);
                    },
                    error: function() {
                        $('#editCustomerResults').html(
                            '<div class="text-danger small p-3">Error en la busqueda.</div>');
                    },
                    complete: function() {
                        $('#editSearchLoading').hide();
                    }
                });
            }

            function updateEditSelectedCustomers() {
                const selectedList = $('#editSelectedCustomersList');
                $('#editSelectedCount').text(editSelectedCustomers.length);

                if (editSelectedCustomers.length === 0) {
                    selectedList.html('<span class="text-muted small">No hay clientes seleccionados</span>');
                    $('#editCustomerIds').val('');
                    return;
                }

                selectedList.html(editSelectedCustomers.map(function(customer) {
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

                $('#editCustomerIds').val(JSON.stringify(editSelectedCustomers.map(c => c.id)));
            }

            $('#editCustomerSearch').on('keyup', function() {
                clearTimeout(editDebounceTimer);
                editDebounceTimer = setTimeout(searchEditCustomers, 300);
            });

            $('#editBtnSearchCustomer').on('click', searchEditCustomers);

            $(document).on('change', '.edit-customer-checkbox', function() {
                const customerData = $(this).data('customer');
                const isChecked = $(this).is(':checked');
                const customerId = customerData.id;

                if (isChecked && !editSelectedCustomers.some(c => c.id === customerId)) {
                    editSelectedCustomers.push(customerData);
                    $(this).closest('.customer-item').addClass('customer-selected');
                }

                if (!isChecked) {
                    editSelectedCustomers = editSelectedCustomers.filter(c => c.id !== customerId);
                    $(this).closest('.customer-item').removeClass('customer-selected');
                }

                updateEditSelectedCustomers();
            });

            $(document).on('click', '.remove-selected-customer', function() {
                const customerId = $(this).data('customer-id');
                editSelectedCustomers = editSelectedCustomers.filter(c => c.id !== customerId);

                $(`.customer-item[data-customer-id="${customerId}"] .edit-customer-checkbox`)
                    .prop('checked', false)
                    .closest('.customer-item').removeClass('customer-selected');

                updateEditSelectedCustomers();
            });

            $('#editSelectAllBtn').on('click', function() {
                editCurrentSearchResults.forEach(function(customer) {
                    if (!editSelectedCustomers.some(c => c.id === customer.id)) {
                        editSelectedCustomers.push(customer);
                    }
                });

                $('.edit-customer-checkbox').prop('checked', true);
                $('.customer-item').addClass('customer-selected');
                updateEditSelectedCustomers();
            });

            $('#editDeselectAllBtn').on('click', function() {
                const currentIds = editCurrentSearchResults.map(c => c.id);
                editSelectedCustomers = editSelectedCustomers.filter(c => !currentIds.includes(c.id));

                $('.edit-customer-checkbox').prop('checked', false);
                $('.customer-item').removeClass('customer-selected');
                updateEditSelectedCustomers();
            });

            $('#editComercialZoneForm').on('submit', function(e) {
                if (editSelectedCustomers.length === 0) {
                    e.preventDefault();
                    alert('Por favor, seleccione al menos un cliente.');
                    return false;
                }
            });

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
                new bootstrap.Tooltip(element);
            });

            updateEditSelectedCustomers();
        });
    </script>
@endsection
