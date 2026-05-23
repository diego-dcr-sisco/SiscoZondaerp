<style>
    #quoteModal .modal-content {
        max-height: 92vh;
    }

    #quoteModal .modal-body {
        max-height: calc(92vh - 140px);
        overflow-y: auto;
        overflow-x: hidden;
    }

    #quoteModal .section-card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #fff;
    }

    #quoteModal .section-title {                                                                                                                                                                                                                                                                                                                                                                                                            
        font-size: 1rem;
        font-weight: 700;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
    }

    #quoteModal .item-card {
        border: 1px dashed #ced4da;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background-color: #fcfdff;
    }

    #quoteModal .item-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    #quoteModal .totals-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem;
    }

    #quoteModal .manual-mode-note {
        background-color: #eef6ff;
        border: 1px solid #cfe2ff;
        border-radius: 0.5rem;
        color: #244a7c;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
    }
</style>

<div class="modal fade" id="quoteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="quoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <form action="{{ route('customer.quote.store') }}" method="POST" id="quote-form">
                @csrf
                <input type="hidden" name="generate_manual_pdf" value="1">

                <div class="modal-header">
                    <h5 class="modal-title" id="quoteModalLabel">Cotización manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="manual-mode-note">
                        Captura toda la información manualmente. La cotización, sus datos del cliente, sus conceptos y cada PDF generado quedarán almacenados.
                    </div>

                    <div class="section-card">
                        <div class="section-title">Datos de la cotización</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="service" class="form-label">Servicio</label>
                                <select class="form-select" id="service" name="service_id">
                                    <option value="">Sin servicio</option>
                                    @foreach ($service_options as $serviceOption)
                                        <option value="{{ data_get($serviceOption, 'id') }}">{{ data_get($serviceOption, 'name', '-') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Título</label>
                                <input type="text" class="form-control" name="title" value="Cotizacion de Servicios" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">No. cotización</label>
                                <input type="text" class="form-control" name="quote_no" value="COT-{{ now()->format('Ymd-His') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha emisión</label>
                                <input type="text" class="form-control" name="issued_date" value="{{ now()->format('d-m-Y') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label is-required">Fecha de inicio</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label is-required">Fecha estimada de fin</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label is-required">Válido hasta</label>
                                <input type="date" class="form-control" name="valid_until" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Moneda</label>
                                <input type="text" class="form-control" name="currency" value="MXN" maxlength="6">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">IVA %</label>
                                <input type="number" class="form-control" id="quote-tax-percent" name="tax_percent" value="16" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label is-required">Prioridad</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    @foreach ($quote_priority as $priority)
                                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label is-required">Estado</label>
                                <select class="form-select" id="status" name="status" required>
                                    @foreach ($quote_status as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Valor de la cotización</label>
                                <input type="number" class="form-control" name="value" value="0.00" step="0.01" readonly>
                                <small class="text-muted">Se calcula automáticamente con base en los conceptos y el IVA.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Comentarios</label>
                                <textarea class="form-control" name="comments" rows="2" placeholder="Comentarios internos"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <div class="section-card h-100 mb-0">
                                <div class="section-title">Empresa</div>
                                <div class="row g-3">
                                    <div class="col-12"><input type="text" name="company_name" class="form-control" value="SISCOPLAGAS" placeholder="Nombre empresa" required></div>
                                    <div class="col-md-6"><input type="text" name="company_rfc" class="form-control" placeholder="RFC"></div>
                                    <div class="col-md-6"><input type="text" name="company_phone" class="form-control" placeholder="Telefono"></div>
                                    <div class="col-12"><input type="email" name="company_email" class="form-control" placeholder="Correo"></div>
                                    <div class="col-12"><input type="text" name="company_address" class="form-control" placeholder="Direccion"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="section-card h-100 mb-0">
                                <div class="section-title">Cliente</div>
                                <div class="row g-3">
                                    <div class="col-12"><input type="text" name="customer_name" class="form-control" value="{{ $customer['name'] }}" placeholder="Nombre cliente" required></div>
                                    <div class="col-12"><input type="text" name="customer_company" class="form-control" placeholder="Razon social"></div>
                                    <div class="col-md-6"><input type="text" name="customer_attn" class="form-control" placeholder="Atencion a"></div>
                                    <div class="col-md-6"><input type="text" name="customer_rfc" class="form-control" placeholder="RFC"></div>
                                    <div class="col-md-6"><input type="text" name="customer_phone" class="form-control" placeholder="Telefono"></div>
                                    <div class="col-md-6"><input type="email" name="customer_email" class="form-control" placeholder="Correo"></div>
                                    <div class="col-12"><input type="text" name="customer_address" class="form-control" placeholder="Direccion"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="section-title mb-0">Conceptos de servicio <span class="badge text-bg-secondary" id="quote-services-count">1</span></div>
                            <button type="button" class="btn btn-success btn-sm" id="add-quote-service-item">
                                <i class="bi bi-plus-circle"></i> Agregar concepto
                            </button>
                        </div>
                        <div id="quote-services-container"></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <div class="section-card h-100 mb-0">
                                <div class="section-title">Condiciones comerciales</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Términos de pago</label>
                                        <textarea name="payment_terms" class="form-control" rows="3">50% anticipo y 50% contra entrega.</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Tiempo de entrega</label>
                                        <textarea name="delivery_time" class="form-control" rows="2">Segun cronograma aprobado por el cliente.</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Condiciones adicionales</label>
                                        <textarea name="conditions" class="form-control" rows="4">Precios sujetos a cambio sin previo aviso.</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="section-card h-100 mb-0">
                                <div class="section-title">Resumen</div>
                                <div class="totals-box mb-3">
                                    <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><strong id="quote-subtotal-preview">$0.00</strong></div>
                                    <div class="d-flex justify-content-between mb-1"><span>IVA</span><strong id="quote-tax-preview">$0.00</strong></div>
                                    <div class="d-flex justify-content-between"><span>Total</span><strong id="quote-total-preview">$0.00</strong></div>
                                </div>
                                <label class="form-label">Notas para el cliente</label>
                                <textarea name="notes" class="form-control" rows="8" placeholder="Notas"></textarea>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="model-id" name="model_id" value="{{ $customer['id'] }}" />
                    <input type="hidden" id="model-type" name="model_type" value="{{ $customer['type'] }}" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="add-quote-btn" class="btn btn-primary">Guardar cotización</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quote-form');
        const servicesContainer = document.getElementById('quote-services-container');
        const addServiceBtn = document.getElementById('add-quote-service-item');
        const servicesCount = document.getElementById('quote-services-count');
        const taxPercentInput = document.getElementById('quote-tax-percent');
        const subtotalPreview = document.getElementById('quote-subtotal-preview');
        const taxPreview = document.getElementById('quote-tax-preview');
        const totalPreview = document.getElementById('quote-total-preview');
        const quoteValueInput = form.querySelector('[name="value"]');
        const serviceSelect = form.querySelector('[name="service_id"]');

        function toNumber(value) {
            const normalized = String(value || '').replace(/\$/g, '').replace(/,/g, '.').trim();
            const number = parseFloat(normalized);
            return isNaN(number) ? 0 : number;
        }

        function formatMoney(amount) {
            return '$' + amount.toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        }

        function updateCountersAndTotals() {
            const serviceItems = servicesContainer.querySelectorAll('.service-item');
            servicesCount.textContent = serviceItems.length;

            let subtotal = 0;
            serviceItems.forEach(function(item, index) {
                const title = item.querySelector('.service-item-title');
                if (title) {
                    title.textContent = 'Concepto ' + (index + 1);
                }

                const qtyInput = item.querySelector('[name="services_qty[]"]');
                const priceInput = item.querySelector('[name="services_unit_price[]"]');
                const lineTotalEl = item.querySelector('.line-total');

                const qty = toNumber(qtyInput ? qtyInput.value : 0);
                const unitPrice = toNumber(priceInput ? priceInput.value : 0);
                const lineTotal = qty * unitPrice;

                subtotal += lineTotal;
                if (lineTotalEl) {
                    lineTotalEl.textContent = formatMoney(lineTotal);
                }
            });

            const taxPercent = toNumber(taxPercentInput.value);
            const taxAmount = subtotal * (taxPercent / 100);
            const total = subtotal + taxAmount;

            subtotalPreview.textContent = formatMoney(subtotal);
            taxPreview.textContent = formatMoney(taxAmount);
            totalPreview.textContent = formatMoney(total);
            quoteValueInput.value = total.toFixed(2);
        }

        function createServiceItem(service) {
            const item = service || {};
            const wrapper = document.createElement('div');
            wrapper.className = 'service-item item-card';
            wrapper.innerHTML = '<div class="item-card-header">'
                + '<strong class="service-item-title">Concepto</strong>'
                + '<button type="button" class="btn btn-outline-danger btn-sm remove-service" title="Eliminar"><i class="bi bi-trash"></i></button>'
                + '</div>'
                + '<div class="row g-2">'
                + '<div class="col-md-5"><input type="text" name="services_name[]" class="form-control" placeholder="Nombre del servicio" value="' + (item.name || '') + '"></div>'
                + '<div class="col-md-2"><input type="number" name="services_qty[]" class="form-control service-calc" placeholder="Cant." min="0" step="0.01" value="' + (item.qty || 1) + '"></div>'
                + '<div class="col-md-2"><input type="text" name="services_unit[]" class="form-control" placeholder="Unidad" value="' + (item.unit || 'servicio') + '"></div>'
                + '<div class="col-md-3"><input type="number" name="services_unit_price[]" class="form-control service-calc" placeholder="Precio unitario" min="0" step="0.01" value="' + (item.unit_price || 0) + '"></div>'
                + '<div class="col-12"><textarea name="services_description[]" class="form-control" rows="2" placeholder="Descripcion">' + (item.description || '') + '</textarea></div>'
                + '<div class="col-12 text-end small text-muted">Importe: <strong class="line-total">$0.00</strong></div>'
                + '</div>';

            servicesContainer.appendChild(wrapper);
            updateCountersAndTotals();
        }

        function syncServiceNameFromSelect() {
            const firstItemNameInput = servicesContainer.querySelector('[name="services_name[]"]');
            if (!firstItemNameInput) {
                return;
            }

            if (firstItemNameInput.value.trim() !== '') {
                return;
            }

            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                firstItemNameInput.value = selectedOption.text.trim();
            }
        }

        createServiceItem({});

        addServiceBtn.addEventListener('click', function() {
            createServiceItem({});
        });

        servicesContainer.addEventListener('click', function(event) {
            if (event.target.closest('.remove-service')) {
                const items = servicesContainer.querySelectorAll('.service-item');
                if (items.length > 1) {
                    event.target.closest('.service-item').remove();
                    updateCountersAndTotals();
                }
            }
        });

        servicesContainer.addEventListener('input', function(event) {
            if (event.target.classList.contains('service-calc')) {
                updateCountersAndTotals();
            }
        });

        taxPercentInput.addEventListener('input', updateCountersAndTotals);
        serviceSelect.addEventListener('change', syncServiceNameFromSelect);

        updateCountersAndTotals();
    });
</script>
