@extends('layouts.app')
@section('content')
    <div class="row m-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                CREAR NUEVO COMPLEMENTO DE PAGO
                <span class="badge text-bg-warning" id="selectedInvoicesBadge">
                    SIN PAGOS AGREGADOS
                </span>
            </span>
        </div>

        <form class="form p-3" action="{{ route('invoices.payments.store') }}" method="POST" enctype="">
            @csrf
            <input type="hidden" id="selected_invoices_data" name="selected_invoices_data">

            <!-- Inputs hidden para almacenar los invoice_ids -->
            <div id="selected_invoices_ids_container">
                <!-- Se generarán dinámicamente -->
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <!-- Sección de pagos configurados -->
                    <div class="border rounded shadow p-3 mb-3" id="paymentsSection" style="display: none;">
                        <div class="row">
                            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Configuración de Pagos</h5>
                                <button type="button" class="btn btn-success btn-sm" id="btnAddPayment">
                                    <i class="fas fa-plus me-1"></i> Agregar Pago
                                </button>
                            </div>

                            <div class="col-12">
                                <div id="paymentsContainer">
                                    <!-- Los pagos se generarán dinámicamente aquí -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay pagos -->
                    <div class="border rounded shadow p-3 mb-3" id="noPaymentsSection">
                        <div class="row">
                            <div class="col-12 text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No se ha agregado ningún pago. Use el botón "Agregar Pago" para comenzar.
                                </div>
                                <button type="button" class="btn btn-success btn-sm" id="btnAddFirstPayment">
                                    <i class="fas fa-plus me-2"></i> Agregar Primer Pago
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Emisor</h5>
                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required" for="expedition_place">Lugar de expedición (Código
                                    postal)</label>
                                <input type="text" class="form-control" id="expedition_place" name="expedition_place"
                                    value="{{ $sat_config['zip_code'] }}" required>
                                <div class="form-text" id="expedition_place_help">Código postal de expedición</div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required" for="expedition_place">Razon social</label>
                                <input type="text" class="form-control" id="expedition_place" name="expedition_place"
                                    value="{{ $sat_config['business_name'] }}" required>
                                <div class="form-text" id="expedition_place_help">Código postal de expedición</div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label is-required" for="expedition_place">Registro Federal Contribuyente
                                    (RFC)</label>
                                <input type="text" class="form-control" id="expedition_place" name="expedition_place"
                                    value="{{ $sat_config['rfc'] }}" required>
                                <div class="form-text" id="expedition_place_help">Código postal de expedición</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Receptor</h5>
                            <div class="col-12 mb-3">
                                <label for="receiver_name" class="form-label is-required">Razon social</label>
                                <input type="text" class="form-control" id="receiver_name" name="receiver_name"
                                    value="" required>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="receiver_rfc" class="form-label is-required">RFC</label>
                                <input type="text" class="form-control" id="receiver_rfc" name="receiver_rfc"
                                    value="" required>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="receiver_tax_zip_code" class="form-label is-required">Código Postal</label>
                                <input type="text" class="form-control" id="receiver_tax_zip_code"
                                    name="receiver_tax_zip_code" value="" required>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="receiver_cfdi_use" class="form-label is-required">Uso CFDI</label>
                                <select name="receiver_cfdi_use" id="receiver_cfdi_use" class="form-select" required>
                                    <option value="" disabled selected>Seleccione el uso</option>
                                    @foreach ($cfdiUsages as $usage)
                                        <option value="{{ $usage['Value'] }}" {{ $usage['Value'] == 'CP01' ? 'selected' : '' }}>
                                            {{ $usage['Value'] }} - {{ $usage['Name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="receiver_fiscal_regime" class="form-label is-required">Regimen Fiscal</label>
                                <select name="receiver_fiscal_regime" id="receiver_fiscal_regime" class="form-select"
                                    required>
                                    <option value="" disabled selected>Seleccione el regimen</option>
                                    @foreach ($taxRegimes as $taxRegime)
                                        <option value="{{ $taxRegime['Value'] }}">
                                            {{ $taxRegime['Value'] }} - {{ $taxRegime['Name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" id="submitButton" disabled>
                Crear Complemento de Pago
            </button>
        </form>
    </div>

    <!-- Modal para búsqueda de facturas -->
    <div class="modal fade" id="invoicesModal" tabindex="-1" aria-labelledby="invoicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoicesModalLabel">Seleccionar Facturas para el Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Filtros de búsqueda dentro del modal -->
                    <div class="border rounded p-3 mb-3">
                        <div class="row">
                            <h6 class="fw-bold mb-3">Filtros de Búsqueda</h6>

                            <!-- Filtro por Folio -->
                            <div class="col-md-3 mb-3">
                                <label for="modal_invoice_folio" class="form-label">Folio</label>
                                <input type="text" class="form-control" id="modal_invoice_folio"
                                    placeholder="Ej: FAC-000001">
                            </div>

                            <!-- Filtro por Razón Social -->
                            <div class="col-md-3 mb-3">
                                <label for="modal_social_reason" class="form-label">Razón Social</label>
                                <input type="text" class="form-control" id="modal_social_reason"
                                    placeholder="Nombre del cliente">
                            </div>

                            <!-- Filtro por RFC -->
                            <div class="col-md-3 mb-3">
                                <label for="modal_rfc" class="form-label">RFC</label>
                                <input type="text" class="form-control" id="modal_rfc"
                                    placeholder="Ej: XAXX010101000">
                            </div>

                            <!-- Filtro por Fecha -->
                            <div class="col-md-3 mb-3">
                                <label for="modal_issued_date" class="form-label">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="modal_issued_date">
                            </div>

                            <!-- Botón de búsqueda -->
                            <div class="col-12">
                                <button type="button" id="modal_btnSearchInvoices" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i> Buscar Facturas
                                </button>
                                <button type="button" id="modal_btnClearFilters"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i> Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="modal_loadingResults" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Buscando facturas...</p>
                    </div>

                    <div id="modal_resultsContainer">
                        <table class="table table-striped table-hover" id="modal_invoicesTable" style="display: none;">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50px">Seleccionar</th>
                                    <th>Folio</th>
                                    <th>Razón Social</th>
                                    <th>RFC</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Saldo Pendiente</th>
                                </tr>
                            </thead>
                            <tbody id="modal_invoicesTableBody">
                                <!-- Los resultados se cargarán aquí via AJAX -->
                            </tbody>
                        </table>
                        <div id="modal_noResults" class="text-center py-4" style="display: none;">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron facturas con los filtros aplicados</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="modal_btnSelectInvoices" disabled>
                        <i class="fas fa-check me-1"></i> Agregar Facturas al Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const btnAddFirstPayment = $('#btnAddFirstPayment');
            const btnAddPayment = $('#btnAddPayment');
            const modal = new bootstrap.Modal($('#invoicesModal')[0]);
            const modalBtnSearch = $('#modal_btnSearchInvoices');
            const modalBtnClear = $('#modal_btnClearFilters');
            const modalBtnSelect = $('#modal_btnSelectInvoices');

            let payments = [];
            let currentPaymentId = null;

            // Definir taxObjects
            const taxObjects = {
                '01': 'No objeto de impuesto',
                '02': 'Sí objeto de impuesto (desglose)',
                '03': 'Sí objeto de impuesto (sin desglose)',
                '04': 'Sí objeto de impuesto (no causa)'
            };

            // Inicializar secciones
            $('#paymentsSection').hide();
            $('#noPaymentsSection').show();

            // Agregar primer pago
            btnAddFirstPayment.on('click', function() {
                addNewPayment();
            });

            // Agregar pago adicional
            btnAddPayment.on('click', function() {
                addNewPayment();
            });

            // Buscar facturas en el modal
            modalBtnSearch.on('click', function() {
                searchInvoices();
            });

            // Limpiar filtros del modal
            modalBtnClear.on('click', function() {
                $('#modal_invoice_folio').val('');
                $('#modal_social_reason').val('');
                $('#modal_rfc').val('');
                $('#modal_issued_date').val('');
            });

            // Buscar al presionar Enter en cualquier campo del modal
            $('#modal_invoice_folio, #modal_social_reason, #modal_rfc, #modal_issued_date').on('keypress', function(
                e) {
                if (e.which === 13) {
                    searchInvoices();
                }
            });

            function searchInvoices() {
                const loading = $('#modal_loadingResults');
                const tableBody = $('#modal_invoicesTableBody');
                const noResults = $('#modal_noResults');
                const table = $('#modal_invoicesTable');

                // Mostrar loading
                loading.show();
                table.hide();
                noResults.hide();
                tableBody.empty();

                var csrfToken = $('meta[name="csrf-token"]').attr("content");

                var form_data = new FormData();
                form_data.append("invoice_folio", $('#modal_invoice_folio').val());
                form_data.append("social_reason", $('#modal_social_reason').val());
                form_data.append("rfc", $('#modal_rfc').val());
                form_data.append("issued_date", $('#modal_issued_date').val());

                // Hacer petición AJAX con jQuery
                $.ajax({
                    url: '{{ route('invoices.ajax.search') }}',
                    method: 'POST',
                    dataType: 'json',
                    data: form_data,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    success: function(data) {
                        console.log('Datos recibidos:', data);
                        loading.hide();

                        if (data.success && data.invoices.length > 0) {
                            table.show();
                            populateTable(data.invoices);
                        } else {
                            noResults.show();
                        }
                    },
                    error: function(xhr, status, error) {
                        //console.error('Error:', error);
                        loading.hide();
                        noResults.show();
                        noResults.html('<p class="text-danger">Error al cargar los resultados</p>');
                    }
                });
            }

            function populateTable(invoices) {
                const tableBody = $('#modal_invoicesTableBody');
                tableBody.empty();

                $.each(invoices, function(index, invoice) {
                    const row = $('<tr>').html(`
                        <td>
                            <input type="checkbox" class="modal-invoice-checkbox" value="${invoice.id}" 
                                   data-folio="${invoice.serie}-${invoice.folio}"
                                   data-uuid="${invoice.UUID}"
                                   data-receiver="${invoice.receiver_name}"
                                   data-rfc="${invoice.receiver_rfc}"
                                   data-expedition-place="${invoice.expedition_place || ''}"
                                   data-receiver-zip="${invoice.receiver_tax_zip_code || ''}"
                                   data-cfdi-usage="${invoice.receiver_cfdi_use || ''}"
                                   data-fiscal-regime="${invoice.receiver_fiscal_regime || ''}"
                                   data-total="${invoice.total}"
                                   data-serie="${invoice.serie}"
                                   data-folio-num="${invoice.folio}"
                                   data-tax-rate="${invoice.tax_rate || 0.16}"
                                   data-tax-total="${invoice.tax_total || 0}"
                                   data-subtotal="${invoice.subtotal || 0}"
                                   data-tax-object="${invoice.tax_object || '02'}">
                        </td>
                        <td>${invoice.serie}-${invoice.folio}</td>
                        <td>${invoice.receiver_name}</td>
                        <td>${invoice.receiver_rfc}</td>
                        <td>${new Date(invoice.issued_date).toLocaleDateString('es-MX')}</td>
                        <td>$${parseFloat(invoice.total).toFixed(2)}</td>
                        <td>$${parseFloat(invoice.total).toFixed(2)}</td>
                    `);
                    tableBody.append(row);
                });

                // Agregar event listeners a los checkboxes
                $('.modal-invoice-checkbox').on('change', function() {
                    updateModalSelectedCount();
                });

                updateModalSelectedCount();
            }

            function updateModalSelectedCount() {
                const selectedCount = $('.modal-invoice-checkbox:checked').length;
                modalBtnSelect.prop('disabled', selectedCount === 0);

                if (selectedCount > 0) {
                    modalBtnSelect.html(
                        `<i class="fas fa-check me-1"></i> Agregar ${selectedCount} Factura(s) al Pago`);
                } else {
                    modalBtnSelect.html(`<i class="fas fa-check me-1"></i> Agregar Facturas al Pago`);
                }
            }

            function addNewPayment() {
                const paymentId = 'payment_' + Date.now();
                const paymentIndex = payments.length;

                const paymentHtml = `
                    <div class="payment-item border rounded p-3 mb-3" data-payment-id="${paymentId}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Pago ${paymentIndex + 1}</h6>
                            <div>
                                <button type="button" class="btn btn-primary btn-sm me-2 select-invoices-btn" data-payment-id="${paymentId}">
                                    <i class="fas fa-plus me-1"></i> Agregar Facturas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-payment-btn" data-payment-id="${paymentId}">
                                    <i class="fas fa-times"></i> Eliminar Pago
                                </button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label is-required">Fecha de Pago</label>
                                <input type="datetime-local" class="form-control payment-date" 
                                       value="${new Date().toISOString().slice(0, 16)}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label is-required">Forma de Pago</label>
                                <select class="form-select payment-form" required>
                                    <option value="" disabled selected>Seleccione la forma</option>
                                    @forelse ($paymentForms as $index => $form)
                                        <option value="{{ $index }}">{{ $index }} - {{ $form }}</option>
                                    @empty
                                        <option value="">Sin forma</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label is-required">Método de Pago</label>
                                <select class="form-select payment-method" required>
                                    <option value="" disabled selected>Seleccione método</option>
                                    @forelse ($paymentMethods as $index => $method)
                                        <option value="{{ $index }}">{{ $index }} - {{ $method }}</option>
                                    @empty
                                        <option value="">Sin método</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label is-required">Moneda</label>
                                <select class="form-select payment-currency" required>
                                    <option value="MXN" selected>MXN - Peso Mexicano</option>
                                    <option value="USD">USD - Dólar Americano</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Facturas a Pagar</label>
                                <div class="invoices-container mt-2" id="invoices-${paymentId}">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay facturas seleccionadas para este pago. Haga clic en "Agregar Facturas" para seleccionar.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#paymentsContainer').append(paymentHtml);
                payments.push({
                    id: paymentId,
                    invoices: []
                });

                updatePaymentsSection();
                updateBadge();
                updateSubmitButton();
            }

            function createInvoicePaymentConfig(invoice, paymentId, index) {
                const taxRate = parseFloat(invoice.tax_rate) || 0.16;
                const total = parseFloat(invoice.total);
                const taxTotal = parseFloat(invoice.tax_total) || (total * taxRate / (1 + taxRate));
                const base = parseFloat(invoice.subtotal) || (total - taxTotal);
                const taxObject = invoice.tax_object || '02';

                // Generar options para tax object
                let taxObjectOptions = '';
                for (const [key, value] of Object.entries(taxObjects)) {
                    taxObjectOptions +=
                        `<option value="${key}" ${key === taxObject ? 'selected' : ''}>${key} - ${value}</option>`;
                }

                return `
                    <div class="invoice-payment-item border rounded p-2 mb-2" data-invoice-id="${invoice.id}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">${invoice.folio}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-invoice-from-payment" 
                                    data-payment-id="${paymentId}" data-invoice-id="${invoice.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="text-muted small mb-2">UUID: ${invoice.uuid}</p>
                        
                        <div class="row">
                            <div class="col-6 mb-1">
                                <label class="form-label small">Número de Parcialidad</label>
                                <input type="number" class="form-control form-control-sm partiality-number" 
                                       value="${index + 1}" min="1" required>
                            </div>
                            <div class="col-6 mb-1">
                                <label class="form-label small">Saldo Anterior</label>
                                <input type="number" class="form-control form-control-sm previous-balance" 
                                       value="${total.toFixed(2)}" step="0.01" min="0" required>
                            </div>
                            <div class="col-6 mb-1">
                                <label class="form-label small">Monto Pagado</label>
                                <input type="number" class="form-control form-control-sm amount-paid" 
                                       value="${total.toFixed(2)}" step="0.01" min="0" required>
                            </div>
                            <div class="col-6 mb-1">
                                <label class="form-label small">Saldo Insoluto</label>
                                <input type="number" class="form-control form-control-sm outstanding-balance" 
                                       value="0" step="0.01" min="0" required>
                            </div>
                        </div>

                        <!-- Configuración de Impuestos -->
                        <div class="mt-1 p-2 bg-light rounded">
                            <label class="form-label small fw-bold">Impuestos</label>
                            <div class="row">
                                <div class="col-4 mb-1">
                                    <label class="form-label small">Objeto</label>
                                    <select class="form-control form-control-sm tax-object" required>
                                        ${taxObjectOptions}
                                    </select>
                                </div>
                                <div class="col-4 mb-1">
                                    <label class="form-label small">Tasa</label>
                                    <input type="number" class="form-control form-control-sm tax-rate" 
                                           value="${taxRate}" step="0.01" min="0" max="1">
                                </div>
                                <div class="col-4 mb-1">
                                    <label class="form-label small">Base</label>
                                    <input type="number" class="form-control form-control-sm tax-base" 
                                           value="${base.toFixed(2)}" step="0.01" min="0" readonly>
                                </div>
                                <div class="col-4 mb-1">
                                    <label class="form-label small">Total</label>
                                    <input type="number" class="form-control form-control-sm tax-total" 
                                           value="${taxTotal.toFixed(2)}" step="0.01" min="0" readonly>
                                </div>
                            </div>
                            <small class="form-text text-muted tax-object-description">
                                ${taxObjects[taxObject]}
                            </small>
                        </div>
                    </div>
                `;
            }

            // Event delegation para botones de selección de facturas
            $(document).on('click', '.select-invoices-btn', function() {
                currentPaymentId = $(this).data('payment-id');
                // Limpiar selecciones anteriores
                $('.modal-invoice-checkbox').prop('checked', false);
                updateModalSelectedCount();
                modal.show();
            });

            // Event delegation para eliminar pagos
            $(document).on('click', '.remove-payment-btn', function() {
                const paymentId = $(this).data('payment-id');
                payments = payments.filter(p => p.id !== paymentId);
                $(`.payment-item[data-payment-id="${paymentId}"]`).remove();
                updatePaymentsSection();
                updateBadge();
                updateSubmitButton();
            });

            // Event delegation para eliminar facturas de pagos
            $(document).on('click', '.remove-invoice-from-payment', function() {
                const paymentId = $(this).data('payment-id');
                const invoiceId = $(this).data('invoice-id');

                const payment = payments.find(p => p.id === paymentId);
                if (payment) {
                    payment.invoices = payment.invoices.filter(inv => inv.id != invoiceId);
                    updatePaymentInvoicesDisplay(paymentId);
                    updateBadge();
                    updateSubmitButton();
                }
            });

            function updatePaymentsSection() {
                if (payments.length === 0) {
                    $('#paymentsSection').hide();
                    $('#noPaymentsSection').show();
                } else {
                    $('#paymentsSection').show();
                    $('#noPaymentsSection').hide();
                }
            }

            function updatePaymentInvoicesDisplay(paymentId) {
                const payment = payments.find(p => p.id === paymentId);
                const container = $(`#invoices-${paymentId}`);

                if (!payment || payment.invoices.length === 0) {
                    container.html(`
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay facturas seleccionadas para este pago. Haga clic en "Agregar Facturas" para seleccionar.
                        </div>
                    `);
                    return;
                }

                let invoicesHtml = '';
                payment.invoices.forEach((invoice, index) => {
                    invoicesHtml += createInvoicePaymentConfig(invoice, paymentId, index);
                });

                container.html(invoicesHtml);
                addPaymentCalculationListeners();

                // Llenar automáticamente los campos del receptor con la primera factura
                if (payment.invoices.length > 0) {
                    const firstInvoice = payment.invoices[0];
                    $('#receiver_name').val(firstInvoice.receiver);
                    $('#receiver_rfc').val(firstInvoice.rfc);
                    $('#receiver_tax_zip_code').val(firstInvoice.receiver_zip || '');

                    /*if (firstInvoice.cfdi_usage) {
                        $('#receiver_cfdi_use').val(firstInvoice.cfdi_usage);
                    }*/

                    if (firstInvoice.fiscal_regime) {
                        $('#receiver_fiscal_regime').val(firstInvoice.fiscal_regime);
                    }
                }
            }

            function addPaymentCalculationListeners() {
                // Recalcular saldo insoluto cuando cambie monto pagado
                $(document).on('change keyup', '.amount-paid, .previous-balance', function() {
                    const item = $(this).closest('.invoice-payment-item');
                    const previousBalance = parseFloat(item.find('.previous-balance').val()) || 0;
                    const amountPaid = parseFloat(item.find('.amount-paid').val()) || 0;
                    const outstandingBalance = previousBalance - amountPaid;

                    item.find('.outstanding-balance').val(Math.max(0, outstandingBalance).toFixed(2));
                    calculateTaxes(item);
                });

                // Recalcular impuestos cuando cambie la tasa
                $(document).on('change keyup', '.tax-rate', function() {
                    const item = $(this).closest('.invoice-payment-item');
                    calculateTaxes(item);
                });

                // Actualizar descripciones cuando cambie el tax object
                $(document).on('change', '.tax-object', function() {
                    const selectedValue = $(this).val();
                    $(this).closest('.bg-light').find('.tax-object-description').text(taxObjects[
                        selectedValue]);
                    updateTaxFieldsState($(this).closest('.invoice-payment-item'));
                });
            }

            function calculateTaxes(item) {
                const amountPaid = parseFloat(item.find('.amount-paid').val()) || 0;
                const taxRate = parseFloat(item.find('.tax-rate').val()) || 0;
                const taxObject = item.find('.tax-object').val();

                if (taxObject === '02') {
                    const taxBase = amountPaid / (1 + taxRate);
                    const taxTotal = amountPaid - taxBase;

                    item.find('.tax-base').val(taxBase.toFixed(2));
                    item.find('.tax-total').val(taxTotal.toFixed(2));
                }
            }

            function updateTaxFieldsState(item) {
                const taxObject = item.find('.tax-object').val();
                const taxRateField = item.find('.tax-rate');
                const taxBaseField = item.find('.tax-base');
                const taxTotalField = item.find('.tax-total');

                if (taxObject === '01') {
                    taxRateField.prop('disabled', true).val('0.00');
                    taxBaseField.val('0.00');
                    taxTotalField.val('0.00');
                    taxRateField.addClass('bg-light text-muted');
                    taxBaseField.addClass('bg-light text-muted');
                    taxTotalField.addClass('bg-light text-muted');
                } else {
                    taxRateField.prop('disabled', false);
                    taxRateField.removeClass('bg-light text-muted');
                    taxBaseField.removeClass('bg-light text-muted');
                    taxTotalField.removeClass('bg-light text-muted');

                    if (taxObject === '02') {
                        calculateTaxes(item);
                    } else {
                        taxRateField.val('0.00');
                        taxBaseField.val('0.00');
                        taxTotalField.val('0.00');
                    }
                }
            }

            function updateBadge() {
                const badge = $('#selectedInvoicesBadge');
                const totalInvoices = payments.reduce((total, payment) => total + payment.invoices.length, 0);

                if (totalInvoices === 0) {
                    badge.text('SIN PAGOS AGREGADOS');
                    badge.removeClass('text-bg-success').addClass('text-bg-warning');
                } else {
                    badge.text(`${payments.length} PAGO(S) - ${totalInvoices} FACTURA(S)`);
                    badge.removeClass('text-bg-warning').addClass('text-bg-success');
                }
            }

            function updateSubmitButton() {
                const totalInvoices = payments.reduce((total, payment) => total + payment.invoices.length, 0);
                $('#submitButton').prop('disabled', totalInvoices === 0);
            }

            function verifyRfcConsistency(selectedInvoicesForPayment, currentRfc) {
                // Verificar si el array tiene datos
                if (!selectedInvoicesForPayment || selectedInvoicesForPayment.length === 0) {
                    console.log('Array vacío, RFC consistente (no hay facturas seleccionadas)');
                    return true;
                }

                // Verificar si todos los RFC en el array son iguales al RFC actual
                const allRfcMatch = selectedInvoicesForPayment.every(invoice => invoice.rfc === currentRfc);

                if (allRfcMatch) {
                    console.log('RFC consistente: Todas las facturas tienen el mismo RFC');
                    return true;
                } else {
                    console.log('RFC inconsistente: Hay facturas con diferentes RFC');
                    return false;
                }
            }

            // Seleccionar facturas para el pago actual
            modalBtnSelect.on('click', function() {
                if (!currentPaymentId) return;

                const currentPayment = payments.find(p => p.id === currentPaymentId);
                if (!currentPayment) return;

                const selectedInvoicesForPayment = [];

                $('.modal-invoice-checkbox:checked').each(function() {
                    if (verifyRfcConsistency(currentPayment.invoices, $(this).data('rfc'))) {
                        const invoice = {
                            id: $(this).val(),
                            folio: $(this).data('folio'),
                            uuid: $(this).data('uuid'),
                            receiver: $(this).data('receiver'),
                            rfc: $(this).data('rfc'),
                            expedition_place: $(this).data('expedition-place'),
                            receiver_zip: $(this).data('receiver-zip'),
                            cfdi_usage: $(this).data('cfdi-usage'),
                            fiscal_regime: $(this).data('fiscal-regime'),
                            total: $(this).data('total'),
                            serie: $(this).data('serie'),
                            folio_num: $(this).data('folio-num'),
                            tax_rate: $(this).data('tax-rate'),
                            tax_total: $(this).data('tax-total'),
                            subtotal: $(this).data('subtotal'),
                            tax_object: $(this).data('tax-object')
                        };
                        selectedInvoicesForPayment.push(invoice);
                    } else {
                        alert('Los RFC NO coinciden en las facturas seleccionadas');
                    }
                });

                currentPayment.invoices = selectedInvoicesForPayment;
                updatePaymentInvoicesDisplay(currentPaymentId);
                updateBadge();
                updateSubmitButton();
                modal.hide();

                // Limpiar filtros del modal
                $('#modal_invoice_folio, #modal_social_reason, #modal_rfc, #modal_issued_date').val('');
            });

            function buildPaymentsJson() {
                const expeditionPlace = $('#expedition_place').val();
                const receiverName = $('#receiver_name').val();
                const receiverRfc = $('#receiver_rfc').val();
                const receiverTaxZipCode = $('#receiver_tax_zip_code').val();
                const receiverCfdiUse = $('#receiver_cfdi_use').val();
                const receiverFiscalRegime = $('#receiver_fiscal_regime').val();

                const paymentsArray = payments.map(payment => {
                    const paymentElement = $(`.payment-item[data-payment-id="${payment.id}"]`);
                    const paymentDate = paymentElement.find('.payment-date').val();
                    const paymentForm = paymentElement.find('.payment-form').val();
                    const paymentMethod = paymentElement.find('.payment-method').val();
                    const currency = paymentElement.find('.payment-currency').val();

                    const relatedDocuments = payment.invoices.map((invoice, index) => {
                        const item = $(`.invoice-payment-item[data-invoice-id="${invoice.id}"]`);

                        const partialityNumber = item.find('.partiality-number').val();
                        const previousBalance = item.find('.previous-balance').val();
                        const amountPaid = item.find('.amount-paid').val();
                        const outstandingBalance = item.find('.outstanding-balance').val();
                        const taxObject = item.find('.tax-object').val();
                        const taxRate = item.find('.tax-rate').val();
                        const taxTotal = item.find('.tax-total').val();
                        const taxBase = item.find('.tax-base').val();

                        let taxes = [];
                        if (taxObject === '02') {
                            taxes = [{
                                "Name": "IVA",
                                "Rate": taxRate,
                                "Total": taxTotal,
                                "Base": taxBase,
                                "IsRetention": "false"
                            }];
                        }

                        return {
                            "InvoiceId": invoice.id,
                            "TaxObject": taxObject,
                            "Uuid": invoice.uuid,
                            "PartialityNumber": partialityNumber,
                            "Serie": invoice.serie,
                            "Folio": invoice.folio_num,
                            "Currency": currency,
                            "PaymentMethod": paymentMethod,
                            "PreviousBalanceAmount": previousBalance,
                            "AmountPaid": amountPaid,
                            "ImpSaldoInsoluto": outstandingBalance,
                            "Taxes": taxes
                        };
                    });

                    // Calcular monto total del pago
                    const totalAmount = payment.invoices.reduce((total, invoice, index) => {
                        const item = $(`.invoice-payment-item[data-invoice-id="${invoice.id}"]`);
                        const amountPaid = parseFloat(item.find('.amount-paid').val()) || 0;
                        return total + amountPaid;
                    }, 0);

                    return {
                        "Date": new Date(paymentDate).toISOString(),
                        "PaymentForm": paymentForm,
                        "Amount": totalAmount.toFixed(2),
                        "Currency": currency,
                        "RelatedDocuments": relatedDocuments
                    };
                });

                return {
                    "CfdiType": "P",
                    "NameId": "14",
                    "Folio": "93",
                    "ExpeditionPlace": expeditionPlace,
                    "Receiver": {
                        "Rfc": receiverRfc,
                        "CfdiUse": receiverCfdiUse,
                        "Name": receiverName,
                        "FiscalRegime": receiverFiscalRegime,
                        "TaxZipCode": receiverTaxZipCode
                    },
                    "Complemento": {
                        "Payments": paymentsArray
                    }
                };
            }

            // Generar JSON al enviar el formulario
            $('form').on('submit', function(e) {
                e.preventDefault();

                const totalInvoices = payments.reduce((total, payment) => total + payment.invoices.length,
                    0);
                if (totalInvoices === 0) {
                    alert('Debe agregar al menos una factura a los pagos');
                    return;
                }

                const paymentsJson = buildPaymentsJson();
                $('#selected_invoices_data').val(JSON.stringify(paymentsJson));

                console.log('JSON final:', paymentsJson);

                // Enviar formulario
                this.submit();
            });
        });
    </script>
@endsection                                                 