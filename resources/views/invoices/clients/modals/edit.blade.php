<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('invoices.customer.update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_createClientModalLabel">
                        Editar Contribuyente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Buscador de Cliente -->
                    {{-- <div class="mb-2">
                        <label for="generalCustomerSearch" class="form-label fw-bold">Buscar Cliente Existente</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="edit_generalCustomerSearch" class="form-control" placeholder="Escriba el nombre del cliente...">
                        </div>
                        <input type="hidden" name="customer_id" id="edit_customer_id" required>
                        <div id="edit_generalCustomerResults" class="list-group" style="max-height: 200px; overflow-y: auto;"></div>
                    </div> --}}
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <label for="edit_comercial_name" class="form-label fw-bold is-required">Nombre Comercial </label>
                            <input type="text" name="comercial_name" id="edit_comercial_name" class="form-control"
                                required autocomplete="off">
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label for="social_reason" class="form-label fw-bold is-required">Nombre Fiscal </label>
                            <input type="text" name="social_reason" id="edit_social_reason" class="form-control" required
                                autocomplete="off">
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label for="rfc" class="form-label fw-bold is-required">RFC</label>
                            <input type="text" name="rfc" id="edit_rfc" class="form-control" required
                                autocomplete="off" placeholder="XAXX010101000">
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="tax_system" class="form-label fw-bold is-required">Régimen Fiscal</label>
                            <select name="tax_system" id="edit_tax_system" class="form-select" required>
                                <option value="" disabled>Seleccione un régimen fiscal</option>
                                @forelse ($taxRegimes as $regime)
                                    <option value="{{ $regime['Value'] }}">{{ $regime['Value'] }} - {{ $regime['Name'] }}</option>                             
                                @empty
                                    <option value="">Sin régimen fiscal</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="cfdi_usage_id" class="form-label fw-bold is-required">Uso de CFDI </label>
                            <select name="cfdi_usage" id="edit_cfdi_usage" class="form-select" required>
                                <option value="" disabled>Seleccione un uso de CFDI</option>
                                @forelse ($cfdiUsages as $cfdiUse)
                                    <option value="{{ $cfdiUse['Value'] }}">{{ $cfdiUse['Value'] }} - {{ $cfdiUse['Name'] }}</option>
                                @empty
                                    <option value="">Sin uso</option>
                                @endforelse
                            </select>
                        </div>
                        <hr />
                        <div class="col-md-6 col-12 mb-3">
                            <label for="email" class="form-label fw-bold is-required">Email </label>
                            <input type="email" name="email" id="edit_email" class="form-control" required
                                autocomplete="off">
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label for="phone" class="form-label fw-bold is-required">Teléfono</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" autocomplete="off">
                        </div>
                        <div class="col-md-2 col-12 mb-3">
                            <label for="zip_code" class="form-label fw-bold is-required">Código Postal </label>
                            <input type="text" name="zip_code" id="edit_zip_code" class="form-control" minlength="5"
                                maxlength="5" required>
                        </div>


                        <div class="col-md-6 col-12 mb-3">
                            <label for="address" class="form-label fw-bold is-required">Dirección</label>
                            <input type="text" name="address" id="edit_address" class="form-control" required>
                        </div>
                        <div class="col-md-3 col-12 mb-3">
                            <label for="state" class="form-label fw-bold is-required">Estado </label>
                            <input type="text" name="state" id="edit_state" class="form-control" required>
                        </div>
                        <div class="col-md-3 col-12 mb-3">
                            <label for="city" class="form-label fw-bold is-required">Ciudad </label>
                            <input type="text" name="city" id="edit_city" class="form-control" required>
                        </div>

                        <hr />

                        <div class="col-md-4 col-12 mb-3">
                            <label for="credit_limit" class="form-label fw-bold">Límite de Crédito (MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="credit_limit" id="edit_credit_limit" class="form-control"
                                    step="0.01" min="0" autocomplete="off" placeholder="0.00" 
                                    aria-label="Límite de Crédito en pesos mexicanos">
                                <span class="input-group-text">MXN</span>
                            </div>
                            <div class="form-text">Ingrese el monto en pesos mexicanos (MXN).</div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label for="payment_method" class="form-label fw-bold is-required">Método de Pago</label>
                            <select name="payment_method" id="edit_payment_method" class="form-select" required>
                                <option value="" disabled>Seleccione método</option>
                                @forelse ($paymentMethods as $index => $method)
                                    <option value="{{ $index }}">{{ $index }} - {{ $method }}
                                    </option>
                                @empty
                                    <option value="">Sin método</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-md-4 col-12 mb-3">
                            <label for="payment_form" class="form-label fw-bold is-required">Forma de Pago</label>
                            <select name="payment_form" id="edit_payment_form" class="form-select" required>
                                <option value="" disabled>Seleccione la forma</option>
                                @forelse ($paymentForms as $index => $form)
                                    <option value="{{ $index }}">{{ $index }} - {{ $form }}
                                    </option>
                                @empty
                                    <option value="">Sin forma</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-md-4 col-12 mb-3">
                            <label for="credit_days" class="form-label fw-bold">Días de Crédito </label>
                            <select name="credit_days" id="edit_credit_days" class="form-select" required>
                                <option value="" disabled>Seleccione días</option>
                                <option value="0">(0 dias) Contado</option>
                                <option value="30">30 días</option>
                                <option value="60">60 días</option>
                                <option value="90">90 días</option>
                            </select>
                        </div>
                        <!-- Status -->
                        <div class="col-md-4 col-12 mb-3">
                            <label for="status" class="form-label fw-bold">Estado</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="facturable">Facturable</option>
                                <option value="moroso">Moroso</option>
                                <option value="no_facturable">No Facturable</option>
                            </select>
                        </div>
                        <input type="hidden" name="invoice_customer_id" id="edit_invoice_customer_id" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editInvoiceCustomer(element) {
            const icData = element.getAttribute('data-ic');
            const parsed = JSON.parse(icData);

            console.log(parsed);
            
            $('#edit_comercial_name').val(parsed.comercial_name);
            $('#edit_social_reason').val(parsed.social_reason);
            $('#edit_rfc').val(parsed.rfc);
            $('#edit_tax_system').val(parsed.tax_system);
            $('#edit_cfdi_usage').val(parsed.cfdi_usage);
            $('#edit_email').val(parsed.email);
            $('#edit_phone').val(parsed.phone);
            $('#edit_zip_code').val(parsed.zip_code);
            $('#edit_address').val(parsed.address);
            $('#edit_state').val(parsed.state);
            $('#edit_city').val(parsed.city);

            $('#edit_credit_limit').val(parsed.credit_limit);
            $('#edit_payment_method').val(parsed.payment_method);
            $('#edit_payment_form').val(parsed.payment_form);

            $('#edit_credit_days').val(parsed.credit_days);
            $('#edit_status').val(parsed.status);

            $('#edit_invoice_customer_id').val(parsed.id);

            $('#editClientModal').modal('show');
        }
</script>
