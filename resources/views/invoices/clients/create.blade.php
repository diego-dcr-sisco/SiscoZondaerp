@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('invoices.customers') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR CONTRIBUYENTE
            </span>
        </div>

        <form class="p-3" method="POST" action="{{ route('invoices.customer.store') }}" enctype="multipart/form-data">
            @csrf

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



            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5>⚠️ Información Requerida</h5>
                <p>Para continuar con el proceso, primero debe seleccionar:</p>
                <ul>
                    <li><strong>Tipo de contribuyente</strong></li>
                    <li><strong>Identificación fiscal</strong></li>
                </ul>
                <p class="mb-0">Una vez completados estos campos, podrá acceder al resto del formulario.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="border rounded shadow p-3 mb-3">
                <div class="row">
                    <h5 class="fw-bold mb-3">Seleccion de Contribuyente</h5>
                    <div class="col-md-6 col-12 mb-3">
                        <label for="taxpayer" class="form-label fw-bold is-required">Tipo de Contribuyente</label>
                        <select name="taxpayer" id="taxpayer" class="form-select" onchange="handleTaxpayerData()" required>
                            <option value="" selected>Seleccione un régimen fiscal</option>
                            @forelse ($taxpayer_types as $taxpayer_type)
                                <option value="{{ $taxpayer_type->value }}">{{ $taxpayer_type->name() }}</option>
                            @empty
                                <option value="">Sin régimen fiscal</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-6 col-12 mb-3">
                        <label class="form-label fw-bold is-required">Tipo de registro</label>
                        <select name="type" id="type" class="form-select" onchange="handleFiscalType(this.value)"
                            required>
                            <option value="" selected>Seleccione un tipo de registro</option>
                            <option value="client">{{ 'Cliente' }}</option>
                            <option value="worker">{{ 'Trabajador' }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" id="taxpayer-data">
                <div class="col-md-6 col-12 mb-3" id="customer-fiscal-data">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Datos Fiscales</h5>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="customer_name" class="form-label fw-bold is-required">Nombre comercial (Empresa)</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control"
                                    required autocomplete="off">
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="customer_rfc" class="form-label fw-bold is-required">RFC</label>
                                <input type="text" name="customer_rfc" id="customer_rfc" class="form-control" required
                                    autocomplete="off" placeholder="XAXX010101000">
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="customer_social_reason" class="form-label fw-bold is-required">Razon Social</label>
                                <input type="text" name="customer_social_reason" id="customer_social_reason" class="form-control"
                                    autocomplete="off">
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="customer_tax_system" class="form-label fw-bold is-required">Régimen Fiscal</label>
                                <select name="customer_tax_system" id="customer_tax_system" class="form-select">
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
                                <label for="customer_cfdi_usage_id" class="form-label fw-bold is-required">Uso de CFDI </label>
                                <select name="customer_cfdi_usage" id="customer_cfdi_usage" class="form-select">
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
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3" id="workers-fiscal-data">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Datos Fiscales para Trabajadores</h5>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_name" class="form-label fw-bold is-required">Nombre del Trabajador</label>
                                <input type="text" name="worker_name" id="worker_name" class="form-control"
                                    autocomplete="off">
                            </div>
                            
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_rfc" class="form-label fw-bold is-required">RFC</label>
                                <input type="text" name="worker_rfc" id="worker_rfc" class="form-control"
                                    autocomplete="off" placeholder="XAXX010101000">
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_curp" class="form-label fw-bold is-required">CURP (Clave Única de Registro
                                    de Población)</label>
                                <input type="text" name="worker_curp" id="worker_curp" class="form-control"
                                    placeholder="Ingresa tu CURP (18 caracteres) Ej: GOMA560315MDFRRR09"
                                    autocomplete="off">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_nss" class="form-label fw-bold is-required">NSS (No. de Seguro
                                    social)</label>
                                <input type="text" name="worker_nss" id="worker_nss" class="form-control"
                                    placeholder="Ingresa tu NSS (11 dígitos) Ej: 12345678901" autocomplete="off">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_tax_system" class="form-label fw-bold is-required">Régimen Fiscal</label>
                                <select name="worker_tax_system" id="worker_tax_system" class="form-select">
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
                                <label for="worker_cfdi_usage_id" class="form-label fw-bold is-required">Uso de CFDI </label>
                                <select name="worker_cfdi_usage" id="worker_cfdi_usage" class="form-select">
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
                                <label for="worker_salary_daily" class="form-label fw-bold is-required">Salario diario</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="worker_salary_daily" id="worker_salary_daily" class="form-control"
                                        placeholder="$0.00" step="0.01" min="0" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_position_risk" class="form-label fw-bold is-required">Riesgo del puesto</label>
                                <select name="worker_position_risk" id="worker_position_risk" class="form-select">
                                    <option value="" disabled>Seleccione el riespo del puesto</option>
                                    @forelse ($position_risks as $pr)
                                        <option value="{{ $pr->value }}">{{ $pr->name() }}</option>
                                    @empty
                                        <option value="">Sin régimen fiscal</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_department" class="form-label fw-bold is-required">Departamento</label>
                                <input type="text" name="worker_department" id="worker_department" class="form-control"
                                    placeholder="Ej: Recursos Humanos, Ventas, Tecnología" required autocomplete="off">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="worker_position" class="form-label fw-bold is-required">Puesto</label>
                                <input type="text" name="worker_position" id="worker_position" class="form-control"
                                    placeholder="Ej: Gerente, Analista, Desarrollador Senior" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
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
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Condiciones Comerciales</h5>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="payment_method" class="form-label fw-bold is-required">Método de Pago</label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="" disabled>Seleccione método</option>
                                    @forelse ($paymentMethods as $method)
                                        <option value="{{ $method->value }}">{{ $method->value }} -
                                            {{ $method->description() }}
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
                                    @forelse ($paymentForms as $form)
                                        <option value="{{ $form->value }}">{{ $form->description() }}
                                        </option>
                                    @empty
                                        <option value="">Sin forma</option>
                                    @endforelse
                                </select>
                            </div>

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
                </div>
            </div>
            <button type="submit" id="taxpayer-submit-btn" class="btn btn-primary" disabled>
                Guardar
            </button>
        </form>
    </div>

    <script>
        (() => {
            initializeForm();
        })();

        function initializeForm() {
            $('#workers-fiscal-data').hide();
            $('#taxpayer-data').hide();

            // Inicializar estados de required
            updateRequiredFields();
        }

        function handleFiscalType(value) {
            // Ocultar/mostrar secciones
            if (value == 'client') {
                $('#customer-fiscal-data').show();
                $('#workers-fiscal-data').hide();
            } else {
                $('#customer-fiscal-data').hide();
                $('#workers-fiscal-data').show();
            }

            // Actualizar campos required
            updateRequiredFields();
            handleTaxpayerData();
        }

        function handleTaxpayerData() {
            var checked = $('#taxpayer').val() != '' && $('#type').val() != '';

            if (checked) {
                $('#taxpayer-data').show();
                $('#taxpayer-data .form-control, #taxpayer-data .form-select')
                    .prop('disabled', false);
                $('#taxpayer-submit-btn').prop('disabled', false);
            } else {
                $('#taxpayer-data').hide();
                $('#taxpayer-data .form-control, #taxpayer-data .form-select')
                    .prop('disabled', true);
                $('#taxpayer-submit-btn').prop('disabled', true);
            }

            updateRequiredFields();
        }

        function updateRequiredFields() {
            // Remover required de campos ocultos/deshabilitados
            $('.form-control, .form-select').each(function() {
                const $field = $(this);
                const shouldBeRequired = $field.is(':visible') && !$field.is(':disabled');

                if ($field.data('originally-required') === undefined) {
                    // Guardar el estado original
                    $field.data('originally-required', $field.prop('required'));
                }

                if (shouldBeRequired) {
                    // Restaurar required si originalmente lo tenía
                    $field.prop('required', $field.data('originally-required'));
                } else {
                    $field.prop('required', false);
                }
            });
        }

        // Ejecutar antes de enviar el formulario
        $('form').on('submit', function() {
            updateRequiredFields();
            return true;
        });
    </script>
@endsection
