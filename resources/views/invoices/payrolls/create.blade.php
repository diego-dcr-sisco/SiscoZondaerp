@extends('layouts.app')
@section('content')
    <div class="row m-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('payrolls.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR NÓMINA
            </span>
        </div>

        <form class="form p-3" action="{{ route('payrolls.store') }}" method="POST" id="payrollForm">
            @csrf
            <input type="hidden" id="payroll_data" name="payroll_data">

            <div class="row">
                <!-- Sección de Datos Generales -->
                <div class="col-lg-6 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Datos del Empleador</h5>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label is-required">Folio</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">N -</span>
                                    <input type="text" class="form-control bg-light" id="folio" name="folio"
                                        value="{{ old('folio') ?? $folio }}" readonly>
                                </div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label is-required">Registro patronal</label>
                                <input class="form-control bg-light " type="text" id="employer_registration"
                                    name="employer_registration" value="{{ $sat_config['employer_registration'] ?? '' }}"
                                    readonly>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <label class="form-label is-required">Lugar de Expedición</label>
                                <input type="text" class="form-control bg-light" id="expedition_place"
                                    name="expedition_place" value="{{ $sat_config['zip_code'] ?? '' }}" readonly>
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label class="form-label is-required">Fecha de Pago</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label class="form-label is-required">Tipo de Nómina</label>
                                <select class="form-select" id="payroll_type" name="payroll_type" required>
                                    @foreach ($payroll_types as $type)
                                        <option value="{{ $type->value }}">{{ $type->value }} - {{ $type->name() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">Metodo de pago</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    @foreach ($payment_methods as $payment_method)
                                        <option value="{{ $payment_method->value }}">
                                            {{ $payment_method->value }} - {{ $payment_method->description() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <label class="form-label is-required">Uso de CFDI</label>
                                <select class="form-select" id="cfdi_use" name="cfdi_use" required>
                                    @foreach ($cfdi_usage as $usage)
                                        <option value="{{ $usage->value }}">{{ $usage->value }} - {{ $usage->name() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección del Empleado -->
                <div class="col-lg-6 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Datos del Empleado</h5>

                            <!-- Selector de Empleado -->
                            <div class="col-lg-12 mb-3">
                                <label class="form-label is-required">Seleccionar Empleado</label>
                                <select class="form-select" id="employee_selector" name="employee_id">
                                    <option value="">-- Seleccione un empleado --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user['id'] }}" data-rfc="{{ $user['rfc'] ?? '' }}"
                                            data-curp="{{ $user['curp'] ?? '' }}"
                                            data-social-security="{{ $user['nss'] ?? '' }}"
                                            data-daily-salary="{{ $user['salary'] ?? '' }}"
                                            data-name="{{ $user['name'] }}" data-regime="{{ $user['tax_regime'] ?? '' }}"
                                            data-department="{{ $user['department'] ?? '' }}"
                                            data-position="{{ $user['position'] ?? '' }}"
                                            data-employee-number="{{ $user['employer_registration'] ?? '' }}"
                                            data-contract-type="{{ $user['contract_type'] ?? '01' }}"
                                            data-frequency-payment="{{ $user['frequency_payment'] ?? '04' }}"
                                            data-start-date="{{ $user['start_date_labor_relations'] ?? '' }}"
                                            data-model="{{ $user['model'] ?? '' }}"
                                            data-zip-code="{{ $user['zip_code'] ?? '' }}">  
                                            {{ $user['name'] }} - {{ $user['rfc'] ?? 'Sin RFC' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">Nombre Completo</label>
                                <input type="text" class="form-control" id="employee_name" name="employee_name" required>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">RFC</label>
                                <input type="text" class="form-control" id="employee_rfc" name="employee_rfc"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">CURP</label>
                                <input type="text" class="form-control" id="employee_curp" name="employee_curp"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">NSS</label>
                                <input type="text" class="form-control" id="employee_nss" name="employee_nss"
                                    required>
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label class="form-label is-required">Código Postal</label>
                                <input type="string" class="form-control" id="employee_zip_code"
                                    name="employee_zip_code" maxlength="5" required>
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Fecha de Ingreso</label>
                                <input type="date" class="form-control" id="start_date_labor_relations"
                                    name="start_date_labor_relations">
                            </div>

                            <!-- Campos adicionales que se pueden autocompletar -->
                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">Número de Empleado</label>
                                <input type="text" class="form-control" id="employee_number"
                                    name="employee_number" required>
                            </div>  
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <h5 class="fw-bold mb-3">Datos del pago</h5>
                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">Tipo de Contrato</label>
                                <select class="form-select" id="contract_type" name="contract_type" required>
                                    @foreach ($contract_types as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label class="form-label is-required">Régimen</label>
                                <select class="form-select" id="tax_regime" name="tax_regime" required>
                                    @foreach ($tax_regimes as $tax_regime)
                                        <option value="{{ $tax_regime->value }}">{{ $tax_regime->value }} -
                                            {{ $tax_regime->name() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Departamento</label>
                                <input type="text" class="form-control" id="department" name="department">
                            </div>

                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Puesto</label>
                                <input type="text" class="form-control" id="position" name="position">
                            </div>

                            <div class="col-lg-2 mb-3">
                                <label class="form-label is-required">Salario Diario</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">$</span>
                                    <input type="number" step="0.01" class="form-control" id="employee_daily_salary"
                                        name="employee_daily_salary" required>
                                </div>
                            </div>

                            <div class="col-lg-2 mb-3">
                                <label class="form-label is-required">Mes de operación</label>
                                <select class="form-select" id="month" name="month">
                                    @foreach ($months as $m)
                                        <option value="{{ $m->value }}" {{ now()->month == $m->value ? 'selected' : ''}}>{{ $m->name() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 mb-3">
                                <label class="form-label is-required">Frecuencia de Pago</label>
                                <select class="form-select" id="frequency_payment" name="frequency_payment" required>
                                    @foreach ($periodicities as $per)
                                        <option value="{{ $per->value }}">{{ $per->description() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 mb-3">
                                <label class="form-label is-required">Días Pagados</label>
                                <input type="number" class="form-control" id="days_paid" name="days_paid"
                                    min="0" required>
                            </div>

                            <div class="col-lg-2 mb-3">
                                <label class="form-label is-required">Rango Fecha de Pago</label>
                                <input type="text" class="form-control date_range" id="date_range" name="date_range"
                                    placeholder="Seleccionar rango de fechas" required>
                            </div>

                            <div class="col-lg-2 mb-3">
                                <label class="form-label is-required">Riesgo del puesto</label>
                                <select class="form-select" id="position_risk" name="position_risk" required>
                                    @foreach ($position_risks as $risk)
                                        <option value="{{ $risk->value }}">{{ $risk->name() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secciones modificadas para usar tables -->
            <div class="row">
                <!-- Sección de Percepciones -->
                <div class="col-lg-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Percepciones</h5>
                                <button type="button" class="btn btn-success btn-sm" id="btnAddPerception">
                                    <i class="fas fa-plus me-1"></i> Agregar Percepción
                                </button>
                            </div>
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="perceptionsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="20%">Tipo</th>
                                                <th width="15%">Código</th>
                                                <th width="25%">Descripción</th>
                                                <th width="12%">Gravado</th>
                                                <th width="12%">Exento</th>
                                                <th width="12%">Total</th>
                                                <th width="6%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="perceptionsContainer">
                                            <!-- Las percepciones se agregarán aquí dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Deducciones -->
                <div class="col-lg-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Deducciones</h5>
                                <button type="button" class="btn btn-success btn-sm" id="btnAddDeduction">
                                    <i class="fas fa-plus me-1"></i> Agregar Deducción
                                </button>
                            </div>
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="deductionsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="25%">Tipo</th>
                                                <th width="15%">Código</th>
                                                <th width="35%">Descripción</th>
                                                <th width="15%">Monto</th>
                                                <th width="10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="deductionsContainer">
                                            <!-- Las deducciones se agregarán aquí dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Otros Pagos -->
                <div class="col-lg-12 mb-3">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">Otros Pagos</h5>
                                <button type="button" class="btn btn-success btn-sm" id="btnAddOtherPayment">
                                    <i class="fas fa-plus me-1"></i> Agregar Otro Pago
                                </button>
                            </div>
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="otherPaymentsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="15%">Tipo</th>
                                                <th width="10%">Clave</th>
                                                <th width="25%">Concepto</th>
                                                <th width="15%">Monto</th>
                                                <th width="15%">Subsidio</th>
                                                <th width="10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="otherPaymentsContainer">
                                            <!-- Los otros pagos se agregarán aquí dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="border rounded shadow p-3 mb-3 bg-light">
                        <div class="row">
                            <div class="col-lg-3 text-center">
                                <h6>Total Percepciones</h6>
                                <h4 class="text-success" id="totalPerceptions">$0.00</h4>
                            </div>
                            <div class="col-lg-3 text-center">
                                <h6>Total Deducciones</h6>
                                <h4 class="text-danger" id="totalDeductions">$0.00</h4>
                            </div>
                            <div class="col-lg-3 text-center">
                                <h6>Total Otros Pagos</h6>
                                <h4 class="text-info" id="totalOtherPayments">$0.00</h4>
                            </div>
                            <div class="col-lg-3 text-center">
                                <h6>Total Neto</h6>
                                <h4 class="text-primary" id="totalNet">$0.00</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitButton">
                <i class="fas fa-file-invoice me-2"></i> Guardar Registro
            </button>
        </form>
    </div>

    <script>
        // Pasar variables PHP a JavaScript
        const perceptionsData = @json($perceptions ?? []);
        const deductionsData = @json($deductions ?? []);
        const otherPaymentTypes = {
            '001': 'Reintegro de ISR pagado en exceso (siempre es un ingreso para el trabajador)',
            '002': 'Subsidio para el empleo (efectivamente entregado al trabajador)',
            '003': 'Viáticos (entregados al trabajador)',
            '004': 'Aplicación de saldo a favor por compensación anual',
            '005': 'Reintegro de ISR retenido en exceso del ejercicio fiscal anterior (siempre es un ingreso para el trabajador)'
        };

        // Arrays para almacenar los datos
        let perceptions = [];
        let deductions = [];
        let otherPayments = [];

        $(document).ready(function() {
            // Inicializar daterangepicker
            $('input[name="date_range"]').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                },
                opens: 'left',
                autoUpdateInput: false
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            // Evento para autocompletar datos del empleado
            $('#employee_selector').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                console.log(selectedOption);

                if (selectedOption.val()) {
                    // Autocompletar campos básicos
                    $('#employee_name').val(selectedOption.data('name'));
                    $('#employee_rfc').val(selectedOption.data('rfc'));
                    $('#employee_curp').val(selectedOption.data('curp'));
                    $('#employee_nss').val(selectedOption.data('social-security'));
                    $('#employee_daily_salary').val(selectedOption.data('daily-salary'));
                    $('#employee_zip_code').val(selectedOption.data('zip-code'));

                    // Autocompletar datos laborales
                    $('#tax_regime').val(selectedOption.data('regime')).trigger('change');
                    $('#contract_type').val(selectedOption.data('contract-type')).trigger('change');
                    $('#frequency_payment').val(selectedOption.data('frequency-payment')).trigger('change');

                    // Autocompletar campos adicionales
                    $('#employer_registration').val(selectedOption.data('employee-number'));
                    $('#department').val(selectedOption.data('department'));
                    $('#position').val(selectedOption.data('position'));
                    $('#start_date_labor_relations').val(selectedOption.data('start-date'));

                    $

                    // Mostrar mensaje de éxito
                    showNotification('Datos del empleado cargados correctamente', 'success');
                } else {
                    // Limpiar campos si no se selecciona empleado
                    clearEmployeeFields();
                }
            });

            function clearEmployeeFields() {
                $('#employee_name').val('');
                $('#employee_rfc').val('');
                $('#employee_curp').val('');
                $('#employee_nss').val('');
                $('#employee_daily_salary').val('');
                $('#employer_registration').val('');
                $('#department').val('');
                $('#position').val('');
                $('#start_date_labor_relations').val('');
            }

            function showNotification(message, type) {
                if (typeof toastr !== 'undefined') {
                    toastr[type](message);
                } else {
                    alert(message);
                }
            }

            // Configurar eventos de los botones
            $('#btnAddPerception').on('click', function() {
                addPerception();
            });

            $('#btnAddDeduction').on('click', function() {
                addDeduction();
            });

            $('#btnAddOtherPayment').on('click', function() {
                addOtherPayment();
            });

            // Actualizar totales cuando cambien los montos
            $(document).on('change keyup', '.perception-amount, .deduction-amount, .other-payment-amount',
                updateTotals);

            // Generar JSON al enviar el formulario
            $('#payrollForm').on('submit', function(e) {
                e.preventDefault();

                // Actualizar todos los arrays antes de construir el JSON
                perceptions.forEach((_, index) => {
                    updatePerceptionInArray(perceptions[index].id);
                });
                deductions.forEach((_, index) => {
                    updateDeductionInArray(deductions[index].id);
                });
                otherPayments.forEach((_, index) => {
                    updateOtherPaymentInArray(otherPayments[index].id);
                });

                const payrollJson = buildPayrollJson();
                $('#payroll_data').val(JSON.stringify(payrollJson));

                console.log('Payroll JSON:', payrollJson);
                this.submit();
            });

            // Inicializar con una percepción y deducción por defecto
            addPerception();
            addDeduction();
        });

        // FUNCIONES PARA AGREGAR ELEMENTOS

        function addPerception() {
            const perceptionId = 'perception_' + Date.now();

            // Generar opciones de percepciones
            let perceptionOptions = '<option value="">Seleccionar tipo</option>';
            const items = Object.entries(perceptionsData || {}).map(([key, desc]) => ({
                value: key,
                name: desc
            }));

            items.forEach(item => {
                const value = item.value || item.id || item;
                const label = item.name || item.label || item.description || value;
                perceptionOptions += `<option value="${value}">${value} - ${label}</option>`;
            });

            const perceptionHtml = `
                <tr class="perception-item" data-perception-id="${perceptionId}">
                    <td>
                        <select class="form-select form-select-sm perception-type" required>
                            ${perceptionOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm perception-code" 
                            placeholder="Ej: 046" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm perception-description" 
                            placeholder="Ej: ASIMILIADOS A SALARIOS" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm perception-taxed-amount" 
                            value="0" step="0.01" min="0" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm perception-exempt-amount" 
                            value="0" step="0.01" min="0" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm perception-total" 
                            value="0" step="0.01" min="0" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-perception-btn" 
                                data-perception-id="${perceptionId}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#perceptionsContainer').append(perceptionHtml);

            // Agregar objeto vacío al array
            perceptions.push({
                id: perceptionId,
                type: '',
                code: '',
                description: '',
                taxed_amount: 0,
                exempt_amount: 0,
                total: 0
            });

            addPerceptionEventListeners(perceptionId);
        }

        function addDeduction() {
            const deductionId = 'deduction_' + Date.now();

            // Generar opciones de deducciones
            let deductionOptions = '<option value="">Seleccionar tipo</option>';
            const items = Object.entries(deductionsData || {}).map(([key, desc]) => ({
                value: key,
                name: desc
            }));

            items.forEach(item => {
                const value = item.value || item.id || item;
                const label = item.name || item.label || item.description || value;
                deductionOptions += `<option value="${value}">${value} - ${label}</option>`;
            });

            const deductionHtml = `
                <tr class="deduction-item" data-deduction-id="${deductionId}">
                    <td>
                        <select class="form-select form-select-sm deduction-type" required>
                            ${deductionOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm deduction-code" 
                            placeholder="Ej: 002" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm deduction-description" 
                            placeholder="Ej: ISR" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm deduction-amount" 
                            value="0" step="0.01" min="0" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-deduction-btn" 
                                data-deduction-id="${deductionId}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#deductionsContainer').append(deductionHtml);

            // Agregar objeto vacío al array
            deductions.push({
                id: deductionId,
                type: '',
                code: '',
                description: '',
                amount: 0
            });

            addDeductionEventListeners(deductionId);
        }

        function addOtherPayment() {
            const otherPaymentId = 'other_payment_' + Date.now();

            // Generar opciones de otros pagos
            let otherPaymentOptions = '<option value="">Seleccionar tipo</option>';
            for (const [key, value] of Object.entries(otherPaymentTypes)) {
                otherPaymentOptions += `<option value="${key}">${key} - ${value}</option>`;
            }

            const otherPaymentHtml = `
                <tr class="other-payment-item" data-other-payment-id="${otherPaymentId}">
                    <td>
                        <select class="form-control form-control-sm other-payment-type" required>
                            ${otherPaymentOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm other-payment-key" 
                            placeholder="Ej: 001" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm other-payment-concept" 
                            placeholder="Ej: Reintegro ISR" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm other-payment-amount" 
                            value="0" step="0.01" min="0" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm other-payment-subsidy" 
                            value="0" step="0.01" min="0">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-other-payment-btn" 
                                data-other-payment-id="${otherPaymentId}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#otherPaymentsContainer').append(otherPaymentHtml);

            // Agregar objeto vacío al array
            otherPayments.push({
                id: otherPaymentId,
                type: '',
                key: '',
                concept: '',
                amount: 0,
                subsidy: 0
            });

            addOtherPaymentEventListeners(otherPaymentId);
        }

        // FUNCIONES AUXILIARES

        function addPerceptionEventListeners(perceptionId) {
            const selector = `[data-perception-id="${perceptionId}"]`;

            // Eliminar percepción
            $(document).on('click', `${selector} .remove-perception-btn`, function() {
                $(this).closest('.perception-item').remove();
                removeFromArray(perceptions, perceptionId);
                updateTotals();
            });

            // Actualizar array cuando cambien los valores
            $(document).on('change keyup',
                `${selector} .perception-type, ${selector} .perception-code, ${selector} .perception-description, ${selector} .perception-taxed-amount, ${selector} .perception-exempt-amount`,
                function() {
                    updatePerceptionInArray(perceptionId);
                    updateTotals();
                });

            // Auto-completar código y descripción
            $(document).on('change', `${selector} .perception-type`, function() {
                const selectedValue = $(this).val();
                const item = $(this).closest('.perception-item');

                if (selectedValue) {
                    item.find('.perception-code').val(selectedValue);

                    const perceptionsArray = Object.entries(perceptionsData || {}).map(([key, description]) => ({
                        value: key,
                        name: description,
                        label: description,
                        description: description
                    }));

                    const perception = perceptionsArray.find(p => (p.value || p.id || p) == selectedValue);
                    if (perception) {
                        const description = perception.name || perception.label || perception.description ||
                            selectedValue;
                        item.find('.perception-description').val(description);
                    }
                }
            });

            // Calcular total automáticamente
            $(document).on('change keyup', `${selector} .perception-taxed-amount, ${selector} .perception-exempt-amount`,
                function() {
                    const item = $(this).closest('.perception-item');
                    const taxedAmount = parseFloat(item.find('.perception-taxed-amount').val()) || 0;
                    const exemptAmount = parseFloat(item.find('.perception-exempt-amount').val()) || 0;
                    const total = taxedAmount + exemptAmount;

                    item.find('.perception-total').val(total.toFixed(2));
                    updatePerceptionInArray(perceptionId);
                    updateTotals();
                });
        }

        function addDeductionEventListeners(deductionId) {
            const selector = `[data-deduction-id="${deductionId}"]`;

            // Eliminar deducción
            $(document).on('click', `${selector} .remove-deduction-btn`, function() {
                $(this).closest('.deduction-item').remove();
                removeFromArray(deductions, deductionId);
                updateTotals();
            });

            // Actualizar array cuando cambien los valores
            $(document).on('change keyup',
                `${selector} .deduction-type, ${selector} .deduction-code, ${selector} .deduction-description, ${selector} .deduction-amount`,
                function() {
                    updateDeductionInArray(deductionId);
                    updateTotals();
                });

            // Auto-completar código y descripción
            $(document).on('change', `${selector} .deduction-type`, function() {
                const selectedValue = $(this).val();
                const item = $(this).closest('.deduction-item');

                if (selectedValue) {
                    item.find('.deduction-code').val(selectedValue);

                    const deductionsArray = Object.entries(deductionsData || {}).map(([key, description]) => ({
                        value: key,
                        name: description,
                        label: description,
                        description: description
                    }));

                    const deduction = deductionsArray.find(p => (p.value || p.id || p) == selectedValue);

                    if (deduction) {
                        const description = deduction.name || deduction.label || deduction.description ||
                            selectedValue;
                        item.find('.deduction-description').val(description);
                    }
                }
            });
        }

        function addOtherPaymentEventListeners(otherPaymentId) {
            const selector = `[data-other-payment-id="${otherPaymentId}"]`;

            // Eliminar otro pago
            $(document).on('click', `${selector} .remove-other-payment-btn`, function() {
                $(this).closest('.other-payment-item').remove();
                removeFromArray(otherPayments, otherPaymentId);
                updateTotals();
            });

            // Actualizar array cuando cambien los valores
            $(document).on('change keyup',
                `${selector} .other-payment-type, ${selector} .other-payment-key, ${selector} .other-payment-concept, ${selector} .other-payment-amount, ${selector} .other-payment-subsidy`,
                function() {
                    updateOtherPaymentInArray(otherPaymentId);
                    updateTotals();
                });

            // Auto-completar clave y concepto
            $(document).on('change', `${selector} .other-payment-type`, function() {
                const selectedValue = $(this).val();
                const item = $(this).closest('.other-payment-item');

                if (selectedValue) {
                    item.find('.other-payment-key').val(selectedValue);
                    item.find('.other-payment-concept').val(otherPaymentTypes[selectedValue] || selectedValue);
                }
            });
        }

        // FUNCIONES PARA MANEJAR LOS ARRAYS

        function updatePerceptionInArray(perceptionId) {
            const index = perceptions.findIndex(p => p.id === perceptionId);
            if (index !== -1) {
                const item = $(`[data-perception-id="${perceptionId}"]`);
                perceptions[index] = {
                    id: perceptionId,
                    type: item.find('.perception-type').val(),
                    code: item.find('.perception-code').val(),
                    description: item.find('.perception-description').val(),
                    taxed_amount: parseFloat(item.find('.perception-taxed-amount').val()) || 0,
                    exempt_amount: parseFloat(item.find('.perception-exempt-amount').val()) || 0,
                    total: parseFloat(item.find('.perception-total').val()) || 0
                };
            }
        }

        function updateDeductionInArray(deductionId) {
            const index = deductions.findIndex(d => d.id === deductionId);
            if (index !== -1) {
                const item = $(`[data-deduction-id="${deductionId}"]`);
                deductions[index] = {
                    id: deductionId,
                    type: item.find('.deduction-type').val(),
                    code: item.find('.deduction-code').val(),
                    description: item.find('.deduction-description').val(),
                    amount: parseFloat(item.find('.deduction-amount').val()) || 0
                };
            }
        }

        function updateOtherPaymentInArray(otherPaymentId) {
            const index = otherPayments.findIndex(op => op.id === otherPaymentId);
            if (index !== -1) {
                const item = $(`[data-other-payment-id="${otherPaymentId}"]`);
                otherPayments[index] = {
                    id: otherPaymentId,
                    type: item.find('.other-payment-type').val(),
                    key: item.find('.other-payment-key').val(),
                    concept: item.find('.other-payment-concept').val(),
                    amount: parseFloat(item.find('.other-payment-amount').val()) || 0,
                    subsidy: parseFloat(item.find('.other-payment-subsidy').val()) || 0
                };
            }
        }

        function removeFromArray(array, id) {
            const index = array.findIndex(item => item.id === id);
            if (index !== -1) {
                array.splice(index, 1);
            }
        }

        function updateTotals() {
            let totalPerceptions = 0;
            let totalDeductions = 0;
            let totalOtherPayments = 0;

            // Sumar percepciones (usando el campo total)
            $('.perception-total').each(function() {
                totalPerceptions += parseFloat($(this).val()) || 0;
            });

            // Sumar deducciones
            $('.deduction-amount').each(function() {
                totalDeductions += parseFloat($(this).val()) || 0;
            });

            // Sumar otros pagos
            $('.other-payment-amount').each(function() {
                totalOtherPayments += parseFloat($(this).val()) || 0;
            });

            // Calcular total neto
            const totalNet = totalPerceptions - totalDeductions + totalOtherPayments;

            // Actualizar la interfaz
            $('#totalPerceptions').text('$' + totalPerceptions.toFixed(2));
            $('#totalDeductions').text('$' + totalDeductions.toFixed(2));
            $('#totalOtherPayments').text('$' + totalOtherPayments.toFixed(2));
            $('#totalNet').text('$' + totalNet.toFixed(2));
        }

        function buildPayrollJson() {
            return {
                "perceptions": perceptions,
                "deductions": deductions,
                "other_payments": otherPayments,
                "totals": {
                    "perceptions": parseFloat($('#totalPerceptions').text().replace('$', '')) || 0,
                    "deductions": parseFloat($('#totalDeductions').text().replace('$', '')) || 0,
                    "other_payments": parseFloat($('#totalOtherPayments').text().replace('$', '')) || 0,
                    "net": parseFloat($('#totalNet').text().replace('$', '')) || 0
                }
            };
        }
    </script>
@endsection