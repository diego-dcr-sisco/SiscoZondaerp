@extends('layouts.app')
@section('content')
    <div class="row m-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                CREAR NUEVA NOTA DE CREDITO
                <span class="badge text-bg-warning" id="selectedInvoiceBadge">
                    {{ isset($invoice->folio) ? $invoice->serie . '-' . $invoice->folio : 'SIN FACTURA SELECCIONADA' }}
                </span>
            </span>
        </div>

        <form class="form p-3" action="{{ route('invoices.credit-notes.store') }}" method="POST" enctype="">
            @csrf
            <input type="hidden" id="selected_invoice_id" name="invoice_id">
            <input type="hidden" id="selected_uuid" name="cfdi_uuid">

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Búsqueda de Facturas</h5>

                            <!-- Filtro por Folio -->
                            <div class="col-md-3 mb-3">
                                <label for="invoice_folio" class="form-label">Folio</label>
                                <input type="text" class="form-control" id="invoice_folio" name="invoice_folio"
                                    placeholder="Ej: FAC-000001">
                            </div>

                            <!-- Filtro por Razón Social -->
                            <div class="col-md-3 mb-3">
                                <label for="social_reason" class="form-label">Razón Social</label>
                                <input type="text" class="form-control" id="social_reason" name="social_reason"
                                    placeholder="Nombre del cliente">
                            </div>

                            <!-- Filtro por RFC -->
                            <div class="col-md-3 mb-3">
                                <label for="rfc" class="form-label">RFC</label>
                                <input type="text" class="form-control" id="rfc" name="rfc"
                                    placeholder="Ej: XAXX010101000">
                            </div>

                            <!-- Filtro por Fecha -->
                            <div class="col-md-3 mb-3">
                                <label for="issued_date" class="form-label">Fecha de Emisión</label>
                                <input type="date" class="form-control" id="issued_date" name="issued_date">
                            </div>

                            <!-- Botones de acción -->
                            <div class="col-12">
                                <button type="button" id="btnSearchInvoices" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i> Buscar Facturas
                                </button>
                                <button type="button" id="btnChangeInvoice" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-sync me-1"></i> Cambiar Factura
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de factura seleccionada -->
                    <div class="border rounded shadow p-3 mb-3" id="selectedInvoiceSection" style="display: none;">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Factura Seleccionada</h5>

                            <div class="col-12 mb-3">
                                <label for="selected_invoice_display" class="form-label">Factura</label>
                                <input type="text" class="form-control bg-light" id="selected_invoice_display" readonly
                                    placeholder="Selecciona una factura de la búsqueda">
                            </div>

                            <div class="col-6 mb-3">
                                <label for="uuid_display" class="form-label">UUID</label>
                                <input type="text" class="form-control bg-light" id="uuid_display" readonly>
                            </div>

                            <div class="col-6 mb-3">
                                <div class="alert alert-success mt-4">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Factura seleccionada correctamente
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay factura seleccionada -->
                    <div class="border rounded shadow p-3 mb-3" id="noInvoiceSelectedSection">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No se ha seleccionado ninguna factura. Use el botón "Buscar Facturas" para seleccionar
                                    una.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Emisor</h5>
                            <div class="col-12 mb-3">
                                <label class="form-label is-required" for="expedition_place">Lugar de expedición (Código
                                    postal)</label>
                                <input type="text" class="form-control" id="expedition_place" name="expedition_place"
                                    value="{{ $sat_config['zip_code'] }}" required>
                                <div class="form-text" id="expedition_place_help">Código postal de expedición</div>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="payment_method" class="form-label fw-bold is-required">Método de Pago</label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="" disabled selected>Seleccione método</option>
                                    @forelse ($paymentMethods as $index => $method)
                                        <option value="{{ $index }}">{{ $index }} - {{ $method }}
                                        </option>
                                    @empty
                                        <option value="">Sin método</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="col-lg-6 col-12 mb-3">
                                <label for="payment_form" class="form-label fw-bold is-required">Forma de Pago</label>
                                <select name="payment_form" id="payment_form" class="form-select" required>
                                    <option value="" disabled selected>Seleccione la forma</option>
                                    @forelse ($paymentForms as $index => $form)
                                        <option value="{{ $index }}">{{ $index }} - {{ $form }}
                                        </option>
                                    @empty
                                        <option value="">Sin forma</option>
                                    @endforelse
                                </select>
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
                                        <option value="{{ $usage['Value'] }}">
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

                <!-- Sección de Items de la Factura -->
                <div class="col-12 mb-3" id="invoiceItemsSection" style="display: none;">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Conceptos de la Factura</h5>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th class="is-required">Cantidad</th>
                                                <th>Clave Prod/Serv</th>
                                                <th>Descripción</th>
                                                <th>Clave Unidad</th>
                                                <th class="is-required">Precio Unitario</th>
                                                <th>Subtotal unitario</th>
                                                <th class="is-required">Descuento (%)</th>
                                                <th>Descuento ($)</th>
                                                <th class="is-required">Impuesto (%)</th>
                                                <th>Subtotal</th>
                                                <th>Impuesto total</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoiceItemsBody">
                                            <!-- Los items se cargarán aquí via JavaScript -->
                                        </tbody>
                                        <!-- Fila de totales -->
                                        <tr class="table-primary fw-bold">
                                            <td colspan="9" class="text-end">TOTALES:</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" id="total_subtotal" value="0"
                                                    step="0.01" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" id="total_tax" value="0"
                                                    step="0.01" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" id="total_grand" value="0"
                                                    step="0.01" readonly>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" id="submitButton" disabled>
                Crear Nota de Crédito
            </button>
        </form>
    </div>

    <!-- Modal para resultados -->
    <div class="modal fade" id="invoicesModal" tabindex="-1" aria-labelledby="invoicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoicesModalLabel">Seleccionar Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loadingResults" class="text-center" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Buscando facturas...</p>
                    </div>
                    <div id="resultsContainer">
                        <table class="table table-striped table-hover" id="invoicesTable" style="display: none;">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50px">Seleccionar</th>
                                    <th>Folio</th>
                                    <th>Razón Social</th>
                                    <th>RFC</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTableBody">
                                <!-- Los resultados se cargarán aquí via AJAX -->
                            </tbody>
                        </table>
                        <div id="noResults" class="text-center py-4" style="display: none;">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron facturas con los filtros aplicados</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnSelectInvoice" disabled>Seleccionar
                        Factura</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const btnSearch = $('#btnSearchInvoices');
            const btnSelect = $('#btnSelectInvoice');
            const modal = new bootstrap.Modal($('#invoicesModal')[0]);
            let selectedInvoice = null;

            // Inicializar secciones
            $('#selectedInvoiceSection').hide();
            $('#noInvoiceSelectedSection').show();
            $('#invoiceItemsSection').hide();

            // Buscar facturas
            btnSearch.on('click', function() {
                searchInvoices();
            });

            // Buscar al presionar Enter en cualquier campo
            $('#invoice_folio, #social_reason, #rfc, #issued_date').on('keypress', function(e) {
                if (e.which === 13) {
                    searchInvoices();
                }
            });

            // Cambiar factura
            $('#btnChangeInvoice').on('click', function() {
                searchInvoices();
            });

            function searchInvoices() {
                const loading = $('#loadingResults');
                const tableBody = $('#invoicesTableBody');
                const noResults = $('#noResults');
                const table = $('#invoicesTable');

                // Mostrar loading
                loading.show();
                table.hide();
                noResults.hide();
                tableBody.empty();

                var csrfToken = $('meta[name="csrf-token"]').attr("content");

                var form_data = new FormData();
                form_data.append("invoice_folio", $('#invoice_folio').val());
                form_data.append("social_reason", $('#social_reason').val());
                form_data.append("rfc", $('#rfc').val());
                form_data.append("issued_date", $('#issued_date').val());

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

                        modal.show();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        loading.hide();
                        noResults.show();
                        noResults.html('<p class="text-danger">Error al cargar los resultados</p>');
                    }
                });
            }

            function populateTable(invoices) {
                const tableBody = $('#invoicesTableBody');
                tableBody.empty();

                $.each(invoices, function(index, invoice) {
                    console.log('Factura:', invoice);
                    
                    // Verificar y formatear los items
                    let itemsData = [];
                    if (invoice.items && Array.isArray(invoice.items)) {
                        itemsData = invoice.items;
                    }
                    
                    // Escapar correctamente el JSON para el data attribute
                    const itemsJson = JSON.stringify(itemsData).replace(/"/g, '&quot;');

                    const row = $('<tr>').html(`
                    <td>
                        <input type="radio" name="selected_invoice" value="${invoice.id}" 
                               data-folio="${invoice.serie}-${invoice.folio}"
                               data-uuid="${invoice.UUID}"
                               data-receiver="${invoice.receiver_name}"
                               data-rfc="${invoice.receiver_rfc}"
                               data-expedition-place="${invoice.expedition_place || ''}"
                               data-receiver-zip="${invoice.receiver_tax_zip_code || ''}"
                               data-cfdi-usage="${invoice.receiver_cfdi_use || ''}"
                               data-fiscal-regime="${invoice.receiver_fiscal_regime || ''}"
                               data-items="${itemsJson}">
                    </td>
                    <td>${invoice.serie}-${invoice.folio}</td>
                    <td>${invoice.receiver_name}</td>
                    <td>${invoice.receiver_rfc}</td>
                    <td>${new Date(invoice.issued_date).toLocaleDateString('es-MX')}</td>
                    <td>$${parseFloat(invoice.total).toFixed(2)}</td>
                `);
                    tableBody.append(row);
                });

                // Agregar event listeners a los radio buttons
                $('input[name="selected_invoice"]').on('change', function() {
                    btnSelect.prop('disabled', !this.checked);
                    if (this.checked) {
                        try {
                            // Obtener los items del data attribute
                            const itemsData = $(this).data('items');
                            console.log('Items data:', itemsData);
                            
                            // Si itemsData es un string, parsearlo, si ya es un objeto, usarlo directamente
                            const items = typeof itemsData === 'string' ? JSON.parse(itemsData) : (itemsData || []);
                            
                            selectedInvoice = {
                                id: $(this).val(),
                                folio: $(this).data('folio'),
                                uuid: $(this).data('uuid'),
                                receiver: $(this).data('receiver'),
                                rfc: $(this).data('rfc'),
                                expedition_place: $(this).data('expedition-place'),
                                receiver_zip: $(this).data('receiver-zip'),
                                cfdi_usage: $(this).data('cfdi-usage'),
                                fiscal_regime: $(this).data('fiscal-regime'),
                                items: items
                            };
                            console.log('Factura seleccionada:', selectedInvoice);
                        } catch (error) {
                            console.error('Error al parsear items:', error);
                            selectedInvoice = {
                                id: $(this).val(),
                                folio: $(this).data('folio'),
                                uuid: $(this).data('uuid'),
                                receiver: $(this).data('receiver'),
                                rfc: $(this).data('rfc'),
                                expedition_place: $(this).data('expedition-place'),
                                receiver_zip: $(this).data('receiver-zip'),
                                cfdi_usage: $(this).data('cfdi-usage'),
                                fiscal_regime: $(this).data('fiscal-regime'),
                                items: []
                            };
                        }
                    }
                });
            }

            function populateInvoiceItems(items) {
                const itemsBody = $('#invoiceItemsBody');
                itemsBody.empty();

                let totalSubtotal = 0;
                let totalTax = 0;
                let totalGrand = 0;

                if (!items || items.length === 0) {
                    itemsBody.html('<tr><td colspan="12" class="text-center text-muted">No hay items en esta factura</td></tr>');
                    return;
                }

                $.each(items, function(index, item) {
                    const quantity = item.quantity || 1;
                    const unitPrice = item.unit_price || 0;
                    const discountRate = item.discount_rate || 0;
                    const taxRate = item.tax_rate || 0.16;

                    const unitSubtotal = quantity * unitPrice;
                    const discountAmount = unitSubtotal * discountRate;
                    const subtotalAfterDiscount = unitSubtotal - discountAmount;
                    const taxAmount = subtotalAfterDiscount * taxRate;
                    const total = subtotalAfterDiscount + taxAmount;

                    totalSubtotal += subtotalAfterDiscount;
                    totalTax += taxAmount;
                    totalGrand += total;

                    const row = $('<tr>').attr('data-item-id', item.id).html(`
                        <td>
                            <input type="number" class="form-control form-control-sm editable-field quantity-field"
                                name="items[${item.id}][quantity]" value="${quantity}" step="1" min="0"
                                data-original-value="${quantity}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm bg-body-secondary"
                                name="items[${item.id}][product_code]" value="${item.product_code || '-'}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm bg-body-secondary"
                                name="items[${item.id}][description]" value="${item.description || ''}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm bg-body-secondary"
                                name="items[${item.id}][unit_code]" value="${item.unit_code || ''}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm unit-price-field"
                                name="items[${item.id}][unit_price]" 
                                value="${unitPrice.toFixed(2)}" step="0.01" min="0"
                                data-original-value="${unitPrice}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm calculated-field unit-subtotal-field"
                                name="items[${item.id}][unit_subtotal]" value="${unitSubtotal.toFixed(2)}" step="0.01" min="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm editable-field discount-percent-field"
                                name="items[${item.id}][discount_percent]" 
                                value="${(discountRate * 100).toFixed(2)}" step="0.01" min="0" max="100"
                                data-original-value="${discountRate * 100}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm calculated-field discount-amount-field"
                                name="items[${item.id}][discount_amount]" value="${discountAmount.toFixed(2)}" step="0.01" min="0" readonly>
                        </td>
                        <td>
                            <select class="form-control form-control-sm tax-rate-field"
                                name="items[${item.id}][tax_rate]" data-original-value="${taxRate}">
                                <option value="0.00" ${taxRate === 0.00 ? 'selected' : ''}>0%</option>
                                <option value="0.16" ${taxRate === 0.16 ? 'selected' : ''}>16%</option>
                                <option value="0.08" ${taxRate === 0.08 ? 'selected' : ''}>8%</option>
                                <option value="0.10" ${taxRate === 0.10 ? 'selected' : ''}>10%</option>
                                <option value="0.25" ${taxRate === 0.25 ? 'selected' : ''}>25%</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm calculated-field subtotal-field"
                                name="items[${item.id}][subtotal]" value="${subtotalAfterDiscount.toFixed(2)}" step="0.01" min="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm calculated-field tax-total-field"
                                name="items[${item.id}][tax_total]" value="${taxAmount.toFixed(2)}" step="0.01" min="0" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm calculated-field total-field"
                                name="items[${item.id}][total]" value="${total.toFixed(2)}" step="0.01" min="0" readonly>
                        </td>
                    `);
                    itemsBody.append(row);
                });

                // Actualizar totales
                $('#total_subtotal').val(totalSubtotal.toFixed(2));
                $('#total_tax').val(totalTax.toFixed(2));
                $('#total_grand').val(totalGrand.toFixed(2));

                // Agregar event listeners para cálculos en tiempo real
                addCalculationEventListeners();
            }

            function addCalculationEventListeners() {
                // Recalcular cuando cambien cantidad, precio unitario, descuento o impuesto
                $(document).on('change keyup', '.quantity-field, .unit-price-field, .discount-percent-field, .tax-rate-field', function() {
                    calculateRowTotals($(this).closest('tr'));
                    calculateGrandTotals();
                });
            }

            function calculateRowTotals(row) {
                const quantity = parseFloat(row.find('.quantity-field').val()) || 0;
                const unitPrice = parseFloat(row.find('.unit-price-field').val()) || 0;
                const discountPercent = parseFloat(row.find('.discount-percent-field').val()) || 0;
                const taxRate = parseFloat(row.find('.tax-rate-field').val()) || 0;

                const unitSubtotal = quantity * unitPrice;
                const discountAmount = unitSubtotal * (discountPercent / 100);
                const subtotalAfterDiscount = unitSubtotal - discountAmount;
                const taxAmount = subtotalAfterDiscount * taxRate;
                const total = subtotalAfterDiscount + taxAmount;

                row.find('.unit-subtotal-field').val(unitSubtotal.toFixed(2));
                row.find('.discount-amount-field').val(discountAmount.toFixed(2));
                row.find('.subtotal-field').val(subtotalAfterDiscount.toFixed(2));
                row.find('.tax-total-field').val(taxAmount.toFixed(2));
                row.find('.total-field').val(total.toFixed(2));
            }

            function calculateGrandTotals() {
                let totalSubtotal = 0;
                let totalTax = 0;
                let totalGrand = 0;

                $('tr[data-item-id]').each(function() {
                    totalSubtotal += parseFloat($(this).find('.subtotal-field').val()) || 0;
                    totalTax += parseFloat($(this).find('.tax-total-field').val()) || 0;
                    totalGrand += parseFloat($(this).find('.total-field').val()) || 0;
                });

                $('#total_subtotal').val(totalSubtotal.toFixed(2));
                $('#total_tax').val(totalTax.toFixed(2));
                $('#total_grand').val(totalGrand.toFixed(2));
            }

            // Seleccionar factura
            btnSelect.on('click', function() {
                console.log('Seleccionando factura:', selectedInvoice);
                if (selectedInvoice) {
                    // Actualizar campos ocultos del formulario
                    $('#selected_invoice_id').val(selectedInvoice.id);
                    $('#selected_uuid').val(selectedInvoice.uuid);

                    // Actualizar campos de visualización
                    $('#selected_invoice_display').val(
                        `${selectedInvoice.folio} - ${selectedInvoice.receiver} [${selectedInvoice.rfc}]`
                    );
                    $('#uuid_display').val(selectedInvoice.uuid);

                    // Actualizar badge del título
                    $('#selectedInvoiceBadge').text(selectedInvoice.folio);

                    // Llenar automáticamente los campos del formulario con datos de la factura
                    //$('#expedition_place').val(selectedInvoice.expedition_place || '');
                    $('#receiver_name').val(selectedInvoice.receiver);
                    $('#receiver_rfc').val(selectedInvoice.rfc);
                    $('#receiver_tax_zip_code').val(selectedInvoice.receiver_zip || '');

                    if (selectedInvoice.cfdi_usage) {
                        $('#receiver_cfdi_use').val(selectedInvoice.cfdi_usage);
                    }

                    if (selectedInvoice.fiscal_regime) {
                        $('#receiver_fiscal_regime').val(selectedInvoice.fiscal_regime);
                    }

                    // Mostrar y poblar los items de la factura
                    populateInvoiceItems(selectedInvoice.items);
                    $('#invoiceItemsSection').show();

                    // Habilitar botón de envío
                    $('#submitButton').prop('disabled', false);

                    // Mostrar/ocultar secciones
                    $('#selectedInvoiceSection').show();
                    $('#noInvoiceSelectedSection').hide();

                    // Cerrar modal
                    modal.hide();

                    // Resetear selección
                    selectedInvoice = null;
                    btnSelect.prop('disabled', true);

                    // Limpiar filtros de búsqueda
                    $('#invoice_folio, #social_reason, #rfc, #issued_date').val('');
                }
            });
        });
    </script>
@endsection