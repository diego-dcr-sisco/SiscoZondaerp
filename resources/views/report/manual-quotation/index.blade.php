@extends('layouts.app')

@section('content')
    <style>
        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
        }

        .item-card {
            border: 1px dashed #ced4da;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: #fcfdff;
        }

        .item-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .totals-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .manual-nav-tabs {
            display: flex;
            margin-left: auto;
            min-width: 250px;
            justify-content: flex-end;
        }

        .manual-nav-list {
            display: flex;
            gap: 0.4rem;
        }

        .manual-nav-link {
            color: #182A41;
            text-decoration: none;
            background-color: transparent;
            transition: all 0.3s ease;
            padding: 6px 12px;
            display: block;
            border: 1px solid #ced4da;
            border-radius: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .manual-nav-link:hover {
            color: #182A41;
            background-color: #eef2f7;
            transform: translateX(4px);
        }

        .manual-nav-link.active {
            color: #fff;
            background-color: #182A41;
            border-color: #182A41;
        }
    </style>

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2 mb-3">
            <a href="{{ route('dashboard') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">COTIZACION MANUAL</span>
        </div>

        <div class="px-5 py-3">
            @include('messages.alert')

            <div class="alert alert-info mb-3" role="alert">
                Completa los campos para generar la cotizacion en PDF. Esta pantalla no guarda informacion en base de datos.
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error de validacion:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="manual-quotation-form" action="{{ route('report.manual-quotation.generate') }}" method="POST" target="_blank">
                @csrf

                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="section-title mb-0">Datos de cotizacion</div>
                        <button type="button" class="btn btn-success btn-sm" id="fill-demo">
                            <i class="bi bi-magic"></i> Autocompletar demo
                        </button>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Titulo</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', 'Cotizacion de Servicios') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. cotizacion</label>
                            <input type="text" name="quote_no" class="form-control" value="{{ old('quote_no', 'COT-' . now()->format('Ymd-His')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Archivo (opcional)</label>
                            <input type="text" name="filename" class="form-control" value="{{ old('filename', '') }}" placeholder="cotizacion.pdf">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha emision</label>
                            <input type="text" name="issued_date" class="form-control" value="{{ old('issued_date', now()->format('d-m-Y')) }}" placeholder="dd-mm-aaaa" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vigencia hasta</label>
                            <input type="text" name="valid_until" class="form-control" value="{{ old('valid_until', now()->addDays(15)->format('d-m-Y')) }}" placeholder="dd-mm-aaaa" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Moneda</label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', 'MXN') }}" maxlength="6">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">IVA %</label>
                            <input type="number" name="tax_percent" id="tax_percent" class="form-control" value="{{ old('tax_percent', '16') }}" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-lg-6">
                        <div class="section-card h-100 mb-0">
                            <div class="section-title">Empresa</div>
                            <div class="row g-3">
                                <div class="col-12"><input type="text" name="company_name" class="form-control" value="{{ old('company_name', 'SISCOPLAGAS') }}" placeholder="Nombre empresa" required></div>
                                <div class="col-md-6"><input type="text" name="company_rfc" class="form-control" value="{{ old('company_rfc', '') }}" placeholder="RFC"></div>
                                <div class="col-md-6"><input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', '') }}" placeholder="Telefono"></div>
                                <div class="col-12"><input type="email" name="company_email" class="form-control" value="{{ old('company_email', '') }}" placeholder="Correo"></div>
                                <div class="col-12"><input type="text" name="company_address" class="form-control" value="{{ old('company_address', '') }}" placeholder="Direccion"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="section-card h-100 mb-0">
                            <div class="section-title">Cliente</div>
                            <div class="row g-3">
                                <div class="col-12"><input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', '') }}" placeholder="Nombre cliente" required></div>
                                <div class="col-12"><input type="text" name="customer_company" class="form-control" value="{{ old('customer_company', '') }}" placeholder="Razon social"></div>
                                <div class="col-md-6"><input type="text" name="customer_attn" class="form-control" value="{{ old('customer_attn', '') }}" placeholder="Atencion a"></div>
                                <div class="col-md-6"><input type="text" name="customer_rfc" class="form-control" value="{{ old('customer_rfc', '') }}" placeholder="RFC"></div>
                                <div class="col-md-6"><input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', '') }}" placeholder="Telefono"></div>
                                <div class="col-md-6"><input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', '') }}" placeholder="Correo"></div>
                                <div class="col-12"><input type="text" name="customer_address" class="form-control" value="{{ old('customer_address', '') }}" placeholder="Direccion"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title mb-0">Conceptos de servicio <span class="badge text-bg-secondary" id="services-count">1</span></div>
                        <button type="button" class="btn btn-success btn-sm" id="add-service">
                            <i class="bi bi-plus-circle"></i> Agregar concepto
                        </button>
                    </div>
                    <div id="services-container"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-lg-6">
                        <div class="section-card h-100 mb-0">
                            <div class="section-title">Condiciones comerciales</div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Terminos de pago</label>
                                    <textarea name="payment_terms" class="form-control" rows="3" placeholder="Terminos de pago">{{ old('payment_terms', '50% anticipo y 50% contra entrega.') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Tiempo de entrega</label>
                                    <textarea name="delivery_time" class="form-control" rows="2" placeholder="Tiempo de entrega">{{ old('delivery_time', '5 dias habiles a partir de la aprobacion.') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Condiciones adicionales</label>
                                    <textarea name="conditions" class="form-control" rows="4" placeholder="Condiciones">{{ old('conditions', 'Precios sujetos a cambio sin previo aviso.') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="section-card h-100 mb-0">
                            <div class="section-title">Resumen</div>
                            <div class="totals-box mb-3">
                                <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><strong id="subtotal-preview">$0.00</strong></div>
                                <div class="d-flex justify-content-between mb-1"><span>IVA</span><strong id="tax-preview">$0.00</strong></div>
                                <div class="d-flex justify-content-between"><span>Total</span><strong id="total-preview">$0.00</strong></div>
                            </div>
                            <label class="form-label">Notas para el cliente</label>
                            <textarea name="notes" class="form-control" rows="8" placeholder="Notas">{{ old('notes', '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Generar cotizacion
                    </button>
                    <button type="button" class="btn btn-outline-dark" id="clear-form">Limpiar formulario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var samplePayload = @json($sampleJson);
            var form = document.getElementById('manual-quotation-form');
            var servicesContainer = document.getElementById('services-container');
            var addServiceBtn = document.getElementById('add-service');
            var fillDemoBtn = document.getElementById('fill-demo');
            var clearFormBtn = document.getElementById('clear-form');
            var servicesCount = document.getElementById('services-count');
            var subtotalPreview = document.getElementById('subtotal-preview');
            var taxPreview = document.getElementById('tax-preview');
            var totalPreview = document.getElementById('total-preview');
            var taxPercentInput = document.getElementById('tax_percent');

            function toNumber(value) {
                var normalized = String(value || '').replace(/\$/g, '').replace(/,/g, '.').trim();
                var number = parseFloat(normalized);
                return isNaN(number) ? 0 : number;
            }

            function formatMoney(amount) {
                return '$' + amount.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function updateCountersAndTotals() {
                var serviceItems = servicesContainer.querySelectorAll('.service-item');
                servicesCount.innerText = serviceItems.length;

                var subtotal = 0;
                serviceItems.forEach(function(item, index) {
                    var title = item.querySelector('.service-item-title');
                    if (title) {
                        title.innerText = 'Concepto ' + (index + 1);
                    }

                    var qtyInput = item.querySelector('[name="services_qty[]"]');
                    var priceInput = item.querySelector('[name="services_unit_price[]"]');
                    var lineTotalEl = item.querySelector('.line-total');

                    var qty = toNumber(qtyInput ? qtyInput.value : 0);
                    var unitPrice = toNumber(priceInput ? priceInput.value : 0);
                    var lineTotal = qty * unitPrice;

                    subtotal += lineTotal;
                    if (lineTotalEl) {
                        lineTotalEl.innerText = formatMoney(lineTotal);
                    }
                });

                var taxPercent = toNumber(taxPercentInput.value);
                var taxAmount = subtotal * (taxPercent / 100);
                var total = subtotal + taxAmount;

                subtotalPreview.innerText = formatMoney(subtotal);
                taxPreview.innerText = formatMoney(taxAmount);
                totalPreview.innerText = formatMoney(total);
            }

            function createServiceItem(service) {
                service = service || {};
                var wrapper = document.createElement('div');
                wrapper.className = 'service-item item-card bg-light';
                wrapper.innerHTML = '<div class="item-card-header">'
                    + '<strong class="service-item-title">Concepto</strong>'
                    + '<button type="button" class="btn btn-outline-danger btn-sm remove-service" title="Eliminar"><i class="bi bi-trash"></i></button>'
                    + '</div>'
                    + '<div class="row g-2">'
                    + '<div class="col-md-5"><input type="text" name="services_name[]" class="form-control" placeholder="Nombre del servicio" value="' + (service.name || '') + '"></div>'
                    + '<div class="col-md-2"><input type="number" name="services_qty[]" class="form-control service-calc" placeholder="Cant." min="0" step="0.01" value="' + (service.qty || 1) + '"></div>'
                    + '<div class="col-md-2"><input type="text" name="services_unit[]" class="form-control" placeholder="Unidad" value="' + (service.unit || 'servicio') + '"></div>'
                    + '<div class="col-md-3"><input type="number" name="services_unit_price[]" class="form-control service-calc" placeholder="Precio unitario" min="0" step="0.01" value="' + (service.unit_price || 0) + '"></div>'
                    + '<div class="col-12"><textarea name="services_description[]" class="form-control" rows="2" placeholder="Descripcion">' + (service.description || '') + '</textarea></div>'
                    + '<div class="col-12 text-end small text-muted">Importe: <strong class="line-total">$0.00</strong></div>'
                    + '</div>';

                servicesContainer.appendChild(wrapper);
                updateCountersAndTotals();
            }

            createServiceItem({});

            addServiceBtn.addEventListener('click', function() {
                createServiceItem({});
            });

            servicesContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-service')) {
                    var items = servicesContainer.querySelectorAll('.service-item');
                    if (items.length > 1) {
                        e.target.closest('.service-item').remove();
                        updateCountersAndTotals();
                    }
                }
            });

            servicesContainer.addEventListener('input', function(e) {
                if (e.target.classList.contains('service-calc')) {
                    updateCountersAndTotals();
                }
            });

            taxPercentInput.addEventListener('input', updateCountersAndTotals);

            fillDemoBtn.addEventListener('click', function() {
                var sample = JSON.parse(samplePayload || '{}');
                var company = sample.company || {};
                var customer = sample.customer || {};

                form.querySelector('[name="title"]').value = sample.title || '';
                form.querySelector('[name="quote_no"]').value = sample.quote_no || '';
                form.querySelector('[name="filename"]').value = sample.filename || '';
                form.querySelector('[name="issued_date"]').value = sample.issued_date || '';
                form.querySelector('[name="valid_until"]').value = sample.valid_until || '';
                form.querySelector('[name="currency"]').value = sample.currency || 'MXN';
                form.querySelector('[name="tax_percent"]').value = sample.tax_percent || 16;

                form.querySelector('[name="company_name"]').value = company.name || '';
                form.querySelector('[name="company_rfc"]').value = company.rfc || '';
                form.querySelector('[name="company_phone"]').value = company.phone || '';
                form.querySelector('[name="company_email"]').value = company.email || '';
                form.querySelector('[name="company_address"]').value = company.address || '';

                form.querySelector('[name="customer_name"]').value = customer.name || '';
                form.querySelector('[name="customer_company"]').value = customer.company || '';
                form.querySelector('[name="customer_attn"]').value = customer.attn || '';
                form.querySelector('[name="customer_rfc"]').value = customer.rfc || '';
                form.querySelector('[name="customer_phone"]').value = customer.phone || '';
                form.querySelector('[name="customer_email"]').value = customer.email || '';
                form.querySelector('[name="customer_address"]').value = customer.address || '';

                form.querySelector('[name="payment_terms"]').value = sample.payment_terms || '';
                form.querySelector('[name="delivery_time"]').value = sample.delivery_time || '';
                form.querySelector('[name="conditions"]').value = sample.conditions || '';
                form.querySelector('[name="notes"]').value = sample.notes || '';

                servicesContainer.innerHTML = '';
                if (Array.isArray(sample.services) && sample.services.length > 0) {
                    sample.services.forEach(function(service) {
                        createServiceItem(service);
                    });
                } else {
                    createServiceItem({});
                }

                updateCountersAndTotals();
            });

            clearFormBtn.addEventListener('click', function() {
                form.reset();
                servicesContainer.innerHTML = '';
                createServiceItem({});
                updateCountersAndTotals();
            });

            form.addEventListener('submit', function(e) {
                var customerName = (form.querySelector('[name="customer_name"]').value || '').trim();
                var companyName = (form.querySelector('[name="company_name"]').value || '').trim();

                if (customerName === '' || companyName === '') {
                    e.preventDefault();
                    alert('Completa al menos empresa emisora y cliente para generar la cotizacion.');
                }
            });

            updateCountersAndTotals();
        });
    </script>
@endsection
