<div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('invoices.customer.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createClientModalLabel">
                        Nuevo Contribuyente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Buscador de Cliente -->
                    {{-- <div class="mb-2">
                        <label for="generalCustomerSearch" class="form-label fw-bold">Buscar Cliente Existente</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="generalCustomerSearch" class="form-control" placeholder="Escriba el nombre del cliente...">
                        </div>
                        <input type="hidden" name="customer_id" id="customer_id" required>
                        <div id="generalCustomerResults" class="list-group" style="max-height: 200px; overflow-y: auto;"></div>
                    </div> --}}
                    <div class="row">
                        <h5 class="fw-bold mb-3">Seleccion de Contribuyente</h5>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="tax_system" class="form-label fw-bold is-required">Tipo de Contribuyente</label>
                            <select name="tax_system" id="tax_system" class="form-select" required>
                                <option value="" selected>Seleccione un régimen fiscal</option>
                                @forelse ($taxpayer_types as $taxpayer_type)
                                    <option value="{{ $taxpayer_type->value }}">{{ $taxpayer_type->name() }}</option>
                                @empty
                                    <option value="">Sin régimen fiscal</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="form-label fw-bold">Tipo de registro</label>
                            <select name="type" id="type" class="form-select" onchange="handleFiscalType(this.value)">
                                <option value="" selected>Seleccione un tipo de registro</option>
                                <option value="customer">{{ 'Cliente' }}</option>
                                <option value="worker">{{ 'Trabajador' }}</option>
                            </select>
                        </div>
                        <hr />
                    </div>
                    <div class="row" id="customer-fiscal-data">
                        <h5 class="fw-bold mb-3">Datos Fiscales</h5>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="comercial_name" class="form-label fw-bold is-required">Nombre
                                persona/empresa</label>
                            <input type="text" name="comercial_name" id="comercial_name" class="form-control"
                                required autocomplete="off">
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="rfc" class="form-label fw-bold is-required">RFC</label>
                            <input type="text" name="rfc" id="rfc" class="form-control" required
                                autocomplete="off" placeholder="XAXX010101000">
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="social_reason" class="form-label fw-bold is-required">Razon Social</label>
                            <input type="text" name="social_reason" id="social_reason" class="form-control" required
                                autocomplete="off">
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="tax_system" class="form-label fw-bold is-required">Régimen Fiscal</label>
                            <select name="tax_system" id="tax_system" class="form-select" required>
                                <option value="" disabled>Seleccione un régimen fiscal</option>
                                @forelse ($taxRegimes as $regime)
                                    <option value="{{ $regime->value }}">{{ $regime->value }} - {{ $regime->name() }}
                                    </option>
                                @empty
                                    <option value="">Sin régimen fiscal</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="cfdi_usage_id" class="form-label fw-bold is-required">Uso de CFDI </label>
                            <select name="cfdi_usage" id="cfdi_usage" class="form-select" required>
                                <option value="" disabled>Seleccione un uso de CFDI</option>
                                @forelse ($cfdiUsages as $cfdiUse)
                                    <option value="{{ $cfdiUse->value }}">{{ $cfdiUse->value }} -
                                        {{ $cfdiUse->name() }}
                                    </option>
                                @empty
                                    <option value="">Sin uso</option>
                                @endforelse
                            </select>
                        </div>
                        <hr />
                    </div>

                    <div class="row" id="workers-fiscal-data">
                        <h5 class="fw-bold mb-3">Datos Fiscales para Trabajadores</h5>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="comercial_name" class="form-label fw-bold is-required">Nombre
                                persona/empresa</label>
                            <input type="text" name="comercial_name" id="comercial_name" class="form-control"
                                required autocomplete="off">
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="rfc" class="form-label fw-bold is-required">RFC</label>
                            <input type="text" name="rfc" id="rfc" class="form-control" required
                                autocomplete="off" placeholder="XAXX010101000">
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="curp" class="form-label fw-bold is-required">CURP (Clave Única de Registro
                                de Población)</label>
                            <input type="text" name="curp" id="curp" class="form-control"
                                placeholder="Ingresa tu CURP (18 caracteres) Ej: GOMA560315MDFRRR09" required
                                autocomplete="off">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="nss" class="form-label fw-bold is-required">NSS (No. de Seguro
                                social)</label>
                            <input type="text" name="nss" id="nss" class="form-control"
                                placeholder="Ingresa tu NSS (11 dígitos) Ej: 12345678901" required autocomplete="off">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="tax_system" class="form-label fw-bold is-required">Régimen Fiscal</label>
                            <select name="tax_system" id="tax_system" class="form-select" required>
                                <option value="" disabled>Seleccione un régimen fiscal</option>
                                @forelse ($taxRegimes as $regime)
                                    <option value="{{ $regime->value }}">{{ $regime->value }} -
                                        {{ $regime->name() }}
                                    </option>
                                @empty
                                    <option value="">Sin régimen fiscal</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="cfdi_usage_id" class="form-label fw-bold is-required">Uso de CFDI </label>
                            <select name="cfdi_usage" id="cfdi_usage" class="form-select" required>
                                <option value="" disabled>Seleccione un uso de CFDI</option>
                                @forelse ($cfdiUsages as $cfdiUse)
                                    <option value="{{ $cfdiUse->value }}">{{ $cfdiUse->value }} -
                                        {{ $cfdiUse->name() }}
                                    </option>
                                @empty
                                    <option value="">Sin uso</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="nss" class="form-label fw-bold is-required">Salario diario</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="salary_daily" id="salary_daily" class="form-control"
                                    placeholder="$0.00" step="0.01" min="0" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="tax_system" class="form-label fw-bold is-required">Riesgo del puesto</label>
                            <select name="tax_system" id="tax_system" class="form-select" required>
                                <option value="" disabled>Seleccione el riespo del puesto</option>
                                @forelse ($position_risks as $pr)
                                    <option value="{{ $pr->value }}">{{ $pr->name() }}</option>
                                @empty
                                    <option value="">Sin régimen fiscal</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="department" class="form-label fw-bold is-required">Departamento</label>
                            <input type="text" name="department" id="department" class="form-control"
                                placeholder="Ej: Recursos Humanos, Ventas, Tecnología" required autocomplete="off">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="position" class="form-label fw-bold is-required">Puesto</label>
                            <input type="text" name="position" id="position" class="form-control"
                                placeholder="Ej: Gerente, Analista, Desarrollador Senior" required autocomplete="off">
                        </div>
                        <hr />
                    </div>


                    <div class="row">
                        <h5 class="fw-bold mb-3">Información de Contacto y Ubicación</h5>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="email" class="form-label fw-bold is-required">Email </label>
                            <input type="email" name="email" id="email" class="form-control" required
                                autocomplete="off">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="phone" class="form-label fw-bold is-required">Teléfono</label>
                            <input type="text" name="phone" id="phone" class="form-control"
                                autocomplete="off">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="zip_code" class="form-label fw-bold is-required">Código Postal </label>
                            <input type="text" name="zip_code" id="zip_code" class="form-control"
                                minlength="5" maxlength="5" required>
                        </div>


                        <div class="col-md-6 col-12 mb-3">
                            <label for="address" class="form-label fw-bold is-required">Dirección</label>
                            <input type="text" name="address" id="address" class="form-control" required>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="state" class="form-label fw-bold is-required">Estado </label>
                            <input type="text" name="state" id="state" class="form-control" required>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="city" class="form-label fw-bold is-required">Ciudad </label>
                            <input type="text" name="city" id="city" class="form-control" required>
                        </div>
                        <hr />
                    </div>

                    <div class="row">
                        <h5 class="fw-bold mb-3">Condiciones Comerciales</h5>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="credit_limit" class="form-label fw-bold">Límite de Crédito (MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="credit_limit" id="credit_limit" class="form-control"
                                    step="0.01" min="0" autocomplete="off" placeholder="0.00"
                                    aria-label="Límite de Crédito en pesos mexicanos">
                                <span class="input-group-text">MXN</span>
                            </div>
                            <div class="form-text">Ingrese el monto en pesos mexicanos (MXN).</div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="payment_method" class="form-label fw-bold is-required">Método de Pago</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="" disabled>Seleccione método</option>
                                @forelse ($paymentMethods as $index => $method)
                                    <option value="{{ $index }}">{{ $index }} - {{ $method }}
                                    </option>
                                @empty
                                    <option value="">Sin método</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="payment_form" class="form-label fw-bold is-required">Forma de Pago</label>
                            <select name="payment_form" id="payment_form" class="form-select" required>
                                <option value="" disabled>Seleccione la forma</option>
                                @forelse ($paymentForms as $index => $form)
                                    <option value="{{ $index }}">{{ $index }} - {{ $form }}
                                    </option>
                                @empty
                                    <option value="">Sin forma</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-md-6 col-12 mb-3">
                            <label for="credit_days" class="form-label fw-bold">Días de Crédito </label>
                            <select name="credit_days" id="credit_days" class="form-select" required>
                                <option value="" disabled>Seleccione días</option>
                                <option value="0">(0 dias) Contado</option>
                                <option value="30">30 días</option>
                                <option value="60">60 días</option>
                                <option value="90">90 días</option>
                            </select>
                        </div>
                        <!-- Status -->
                        <div class="col-md-6 col-12 mb-3">
                            <label for="status" class="form-label fw-bold">Estado</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="facturable">Facturable</option>
                                <option value="moroso">Moroso</option>
                                <option value="no_facturable">No Facturable</option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        $('#workers-fiscal-data').hide();
    })();

    function handleFiscalType(value) {
        if (value == 'customer') {
            $('#customer-fiscal-data').show();
            $('#workers-fiscal-data').hide();
        } else {
            $('#customer-fiscal-data').hide();
            $('#workers-fiscal-data').show();
        }
    }
</script>
