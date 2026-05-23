@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('invoices.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                MANEJO DE FACTURA
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ $url_action }}" enctype="multipart/form-data">
            @csrf
            <!-- Si se recibe un order_id, se incluye en el formulario -->
            <input type="hidden" name="order_id"
                value="{{ $order_id ?? ($invoice->order_id ?? ($preloadedData['order_id'] ?? null)) }}">

            {{-- DATOS DEL EMISOR Y RECEPTOR --}}
            <div class="row">
                <!-- Datos del Emisor -->
                <div class="col-md-6 mb-3">
                    <div class="border border-secondary-subtle shadow-sm rounded p-3 bg-light mb-4">
                        <div class="d-flex flex-column align-items-start mx-3">
                            <img src="{{ asset('images/logo.png') }}" class="me-3 mb-3" style="width: 240px;">
                            <div>
                                <h5 class="mb-1">{{ config('services.sat.business_name') }}</h5>
                                <small class="text-muted">RFC: {{ config('services.sat.rfc') }}</small>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item">Régimen Fiscal:
                                <span class="fw-bold">{{ config('services.sat.tax_regime') }} -
                                    {{ config('services.sat.tax_regime_name') }}</span>
                            </li>
                            <li class="list-group-item">Teléfono: <strong>{{ config('services.company.phone') }}</strong>
                            </li>
                            <li class="list-group-item">Licencia Sanitaria:
                                <span class="fw-bold">{{ config('services.company.sanitary_license') }}</span>
                                <span class="fw-bold">{{ config('services.company.sanitary_license_2') }}</span>
                            </li>
                        </ul>
                    </div>

                    {{-- PERIODO DE LA FACTURA --}}
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item list-group-item list-group-item-primary fw-bold">Uso del CFDI
                        </li>
                        <li class="list-group-item">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-3 col-12">
                                    <label for="inputPassword6" class="col-form-label">Selecciona el uso del CFDI:</label>
                                </div>
                                <div class="col-lg-9 col-12">
                                    <div class="input-group">
                                        <select class="form-select" id="cfdi_usage" name="cfdi_usage" required">
                                            @foreach ($cfdiUsages as $cfdiUsage)
                                                <option value="{{ $cfdiUsage['Value'] }}">{{ $cfdiUsage['Value'] }} -
                                                    {{ $cfdiUsage['Name'] }}</option>
                                            @endforeach
                                        </select>
                                        {{-- <button class="btn btn-secondary" type="button"
                                        id="button-addon2">Fijar uso</button> --}}
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>

                    {{-- PERIODO DE LA FACTURA --}}
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item list-group-item list-group-item-primary fw-bold">Periodo de facturación
                        </li>
                        <li class="list-group-item">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="inputPassword6" class="col-form-label">Fecha de inicio:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="date" class="form-control" id="issued_date" name="issued_date"
                                        value="{{ old(
                                            'issued_date',
                                            isset($invoice)
                                                ? $invoice->issued_date->format('Y-m-d')
                                                : (isset($preloadedData)
                                                    ? $preloadedData['issued_date']->format('Y-m-d') ?? ''
                                                    : ''),
                                        ) }}"
                                        required>
                                </div>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="inputPassword6" class="col-form-label">Fecha de fin:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="date" class="form-control" id="due_date" name="due_date"
                                        value="{{ old(
                                            'due_date',
                                            isset($invoice)
                                                ? $invoice->due_date->format('Y-m-d')
                                                : (isset($preloadedData)
                                                    ? $preloadedData['due_date']->format('Y-m-d') ?? ''
                                                    : ''),
                                        ) }}"
                                        required>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- CLIENTE -->
                <div class="col-md-6 mb-3">
                    {{-- SELECCIONAR CLIENTE --}}
                    <ul class="list-group list-group-flush shadow-sm">
                        <li class="list-group-item list-group-item-success fw-bold">
                            Contribuyente
                        </li>
                        <li class="list-group-item">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="inputPassword6" class="col-form-label">Selecciona un cliente: </label>
                                </div>
                                <div class="col-auto">
                                    <select class="form-select" id="invoice_customer_id" name="invoice_customer_id"
                                        onchange="setCustomerCFDI(this.value)" required>
                                        <option value="">Selecciona un cliente</option>
                                        @foreach ($invoiceCustomers as $customer)
                                            <option value="{{ $customer->id }}"
                                                data-comercial-name="{{ $customer->comercial_name }}"
                                                data-social-reason="{{ $customer->social_reason }}"
                                                data-rfc="{{ $customer->rfc }}" data-phone="{{ $customer->phone }}"
                                                data-email="{{ $customer->email }}"
                                                data-tax-system="{{ $customer->tax_system }}"
                                                data-cfdi-usage="{{ $customer->cfdi_usage }}"
                                                data-zip-code="{{ $customer->zip_code }}"
                                                data-state="{{ $customer->state }}" data-city="{{ $customer->city }}"
                                                data-address="{{ $customer->address }}"
                                                data-credit-limit="{{ $customer->credit_limit }}"
                                                data-credit-days="{{ $customer->credit_days }}"
                                                data-payment-method="{{ $customer->payment_method }}"
                                                data-payment-form="{{ $customer->payment_form }}"
                                                data-status="{{ $customer->status }}"
                                                {{ isset($preloadedData) && $preloadedData['invoice_customer_id'] == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->social_reason }} - {{ $customer->rfc }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </li>

                        {{-- DATOS DEL CLIENTE SELECCIONADO --}}
                        <li class="list-group-item" id="customer-details"
                            style="{{ isset($invoice) ? '' : 'display: none;' }}">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-3 text-primary border-bottom pb-2">
                                    <i class="bi bi-info-circle me-2"></i> Información del Cliente
                                </h6>
                                <div class="row g-3">
                                    {{-- Los datos se cargarán automáticamente via JavaScript --}}
                                    <div class="col-md-6">
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">Razón Social:</small>
                                            <div id="customer-social-reason" class="fw-semibold text-dark">{{ $invoice->customer->social_reason ?? '-' }}</div>
                                        </div>
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">RFC:</small>
                                            <div id="customer-rfc" class="fw-semibold text-dark">{{ $invoice->customer->rfc ?? '-'}}</div>
                                        </div>
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">Teléfono:</small>
                                            <div id="customer-phone" class="fw-semibold text-dark">{{ $invoice->customer->phone ?? '-'}}</div>
                                        </div>
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">Email:</small>
                                            <div id="customer-email" class="fw-semibold text-dark">{{ $invoice->customer->email ?? '-'}}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">Régimen Fiscal:</small>
                                            <div id="customer-tax-system" class="fw-semibold text-dark">{{ $invoice->customer->tax_system ?? '-'}}</div>
                                        </div>
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">Uso de CFDI:</small>
                                            <div id="customer-cfdi-usage" class="fw-semibold text-dark">{{ $invoice->customer->cfdi_usage ?? '-'}}</div>
                                        </div>
                                        <div class="mb-2 p-2 bg-white rounded border">
                                            <small class="text-muted d-block">Dirección:</small>
                                            <div id="customer-full-address" class="fw-semibold text-dark">{{ $invoice->customer->address ?? '-'}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- TABLA DE CONCEPTOS (DINÁMICA) --}}
            <div class="table-responsive mb-3">
                <table class="table table-sm table-striped caption-top" id="concepts-table">
                    <caption class="bg-secondary-subtle px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Conceptos</span>
                            <button type="button" class="btn btn-sm btn-success" id="add-concept-btn">
                                <i class="fas fa-plus"></i> Agregar Concepto
                            </button>
                        </div>
                    </caption>
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 8%;">Cant.</th>
                            <th style="width: 30%;">Concepto</th>
                            <th class="text-center" style="width: 12%;">P. Unit.</th>
                            <th class="text-center" style="width: 8%;">Desc. %</th>
                            <th class="text-center" style="width: 8%;">Impuesto</th>
                            <th class="text-center" style="width: 10%;">Subtotal</th>
                            <th class="text-center" style="width: 10%;">Impuestos</th>
                            <th class="text-center" style="width: 10%;">Total</th>
                            <th class="text-center" style="width: 4%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @if (isset($invoice) && $invoice->items->count() > 0) --}}
                        @if(isset($preloadedData) && !empty($preloadedData['services']))
                            {{-- Cargar conceptos desde preloadedData --}}
                            @foreach ($preloadedData['services'] as $index => $service)
                                @php
                                    // Calcular subtotal, impuestos y total para cada servicio
                                    $quantity = $service['quantity'] ?? 1;
                                    $amount = $service['amount'] ?? 0;
                                    $discountRate = $service['discount_rate'] ?? 0;
                                    $taxRate = $service['tax_total'] ?? 0.16;

                                    $grossAmount = $quantity * $amount;
                                    $discountAmount = $grossAmount * ($discountRate / 100);
                                    $subtotal = $service['subtotal'];
                                    $taxesAmount = $service['tax_total'];
                                    $total = $service['total'];
                                @endphp

                                <tr>
                                    <td>
                                        <input type="number" name="services[{{ $index }}][quantity]"
                                            value="{{ $quantity }}" min="1"
                                            class="form-control text-center quantity-input"
                                            data-index="{{ $index }}">
                                    </td>
                                    <td>
                                        <select name="services[{{ $index }}][concept_id]"
                                            class="form-select concept-select" data-index="{{ $index }}" required>
                                            <option value="">Selecciona un concepto</option>
                                            @foreach ($invoiceConcepts as $concept)
                                                <option value="{{ $concept->id }}"
                                                    {{ ($service['concept_id'] ?? '') == $concept->id ? 'selected' : '' }}
                                                    data-sat-key="{{ $concept->product_key }}"
                                                    data-description="{{ $concept->description }}"
                                                    data-unit-code="{{ $concept->unit_code }}"
                                                    data-amount="{{ $concept->amount }}"
                                                    data-tax-rate="{{ $concept->tax_rate ?? 0.16 }}">
                                                    {{ $concept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="services[{{ $index }}][product_key]"
                                            class="sat-key-input" data-index="{{ $index }}"
                                            value="{{ $service['product_key'] ?? '' }}">
                                        <input type="hidden" name="services[{{ $index }}][description]"
                                            class="description-input" data-index="{{ $index }}"
                                            value="{{ $service['description'] ?? '' }}">
                                        <input type="hidden" name="services[{{ $index }}][unit_code]"
                                            class="unit-code-input" data-index="{{ $index }}"
                                            value="{{ $service['unit_code'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01"
                                            name="services[{{ $index }}][amount]"
                                            value="{{ number_format($amount, 2, '.', '') }}"
                                            class="form-control text-center amount-input"
                                            data-index="{{ $index }}" min="0.01">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01"
                                            name="services[{{ $index }}][discount_rate]"
                                            value="{{ $discountRate ?? 0 }}"
                                            class="form-control text-center discount-rate-input"
                                            data-index="{{ $index }}" min="0" max="100">
                                    </td>
                                    <td>
                                        <select name="services[{{ $index }}][tax_total]"
                                            class="form-select tax-rate-select" data-index="{{ $index }}">
                                            <option value="0.16" {{ ($taxRate ?? 0.16) == 0.16 ? 'selected' : '' }}>16%
                                            </option>
                                            <option value="0.08" {{ ($taxRate ?? 0.16) == 0.08 ? 'selected' : '' }}>8%
                                            </option>
                                            <option value="0.00" {{ ($taxRate ?? 0.16) == 0.0 ? 'selected' : '' }}>0%
                                            </option>
                                        </select>
                                    </td>
                                    <td class="text-center fw-semibold">
                                        $<span class="subtotal-cell" id="subtotal-{{ $index }}">
                                            {{ number_format($subtotal, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        $<span class="taxes-amount-cell" id="taxes-amount-{{ $index }}">
                                            {{ number_format($taxesAmount, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold">
                                        $<span class="total-cell" id="total-{{ $index }}">
                                            {{ number_format($total, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-concept-btn"
                                            title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            {{-- Fila por defecto para crear nueva factura --}}
                            <tr>
                                <td>
                                    <input type="number" name="services[0][quantity]" value="1" min="1"
                                        class="form-control text-center quantity-input" data-index="0">
                                </td>
                                <td>
                                    <select name="services[0][concept_id]" class="form-select concept-select"
                                        data-index="0" required>
                                        <option value="">Selecciona un concepto</option>
                                        @foreach ($invoiceConcepts as $concept)
                                            <option value="{{ $concept->id }}"
                                                data-sat-key="{{ $concept->product_key }}"
                                                data-description="{{ $concept->description }}"
                                                data-unit-code="{{ $concept->unit_code }}"
                                                data-amount="{{ $concept->amount }}"
                                                data-tax-rate="{{ $concept->tax_rate ?? 0.16 }}">
                                                {{ $concept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="services[0][product_key]" class="sat-key-input"
                                        data-index="0">
                                    <input type="hidden" name="services[0][description]" class="description-input"
                                        data-index="0">
                                    <input type="hidden" name="services[0][unit_code]" class="unit-code-input"
                                        data-index="0">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="services[0][amount]" value="0.00"
                                        class="form-control text-center amount-input" data-index="0" min="0.01">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="services[0][discount_rate]"
                                        value="0.00" class="form-control text-center discount-rate-input"
                                        data-index="0" min="0" max="100">
                                </td>
                                <td>
                                    <select name="services[0][tax_total]" class="form-select tax-rate-select"
                                        data-index="0">
                                        <option value="0.16">16%</option>
                                        <option value="0.08">8%</option>
                                        <option value="0.00">0%</option>
                                    </select>
                                </td>
                                <td class="text-center fw-semibold">
                                    $<span class="subtotal-cell" id="subtotal-0">0.00</span>
                                </td>
                                <td>
                                    $<span class="taxes-amount-cell" id="taxes-amount-0">0.00</span>
                                </td>
                                <td class="text-center fw-semibold">
                                    $<span class="total-cell" id="total-0">0.00</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-concept-btn"
                                        title="Eliminar" disabled>
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    </tbody>

                    {{-- TABLA DE TOTALES --}}
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-semibold border-0">Subtotal:</td>
                            <td class="text-center fw-semibold text-info-emphasis border-0" id="subtotal-total">
                                @if (isset($preloadedData) && isset($preloadedData['total']) && isset($preloadedData['tax']))
                                    ${{ number_format($preloadedData['total'] - $preloadedData['tax'], 2) }}
                                @else
                                    $0.00
                                @endif
                            </td>
                            <td class="text-end fw-semibold border-0">IVA:</td>
                            <td class="text-center fw-semibold text-danger border-0" id="tax-total">
                                @if (isset($preloadedData) && isset($preloadedData['tax']))
                                    ${{ number_format($preloadedData['tax'], 2) }}
                                @else
                                    $0.00
                                @endif
                            </td>
                            <td class="text-end fw-semibold border-0">Total:</td>
                            <td class="text-center fw-semibold text-success border-0" id="total-general">
                                @if (isset($preloadedData) && isset($preloadedData['total']))
                                    ${{ number_format($preloadedData['total'], 2) }}
                                @else
                                    $0.00
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- INFORMACION DE PAGO --}}
            <div class="table-responsive shadow-sm mb-4">
                <table class="table table-sm table-striped caption-top">
                    <caption class="bg-secondary-subtle px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Informacion de pago</span>
                        </div>
                    </caption>
                    <thead class="table-light">
                        <tr>
                            <th>Método de pago</th>
                            <th>Forma de pago</th>
                            <th>Moneda</th>
                            <th>Tipo de cambio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="w-25">
                                <select id="payment_method" name="payment_method" class="form-select">
                                    <option value="PUE"
                                        {{ old(
                                            'payment_method',
                                            isset($invoice)
                                                ? $invoice->payment_method
                                                : (isset($preloadedData)
                                                    ? $preloadedData['payment_method'] ?? 'PUE'
                                                    : 'PUE'),
                                        ) == 'PUE'
                                            ? 'selected'
                                            : '' }}>
                                        Pago en una sola exhibición (PUE)
                                    </option>
                                    <option value="PPD"
                                        {{ old(
                                            'payment_method',
                                            isset($invoice) ? $invoice->payment_method : (isset($preloadedData) ? $preloadedData['payment_method'] ?? '' : ''),
                                        ) == 'PPD'
                                            ? 'selected'
                                            : '' }}>
                                        Pago en parcialidades o diferido (PPD)
                                    </option>
                                </select>
                            </td>
                            <td class="w-25">
                                <select id="payment_form" name="payment_form" class="form-select">
                                    @foreach ($paymentForms as $code => $label)
                                        <option value="{{ $code }}"
                                            {{ old(
                                                'payment_form',
                                                isset($invoice) ? $invoice->payment_form : (isset($preloadedData) ? $preloadedData['payment_form'] ?? '01' : '01'),
                                            ) == $code
                                                ? 'selected'
                                                : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="w-25">
                                <select id="currency" name="currency" class="form-select">
                                    <option value="MXN"
                                        {{ old(
                                            'currency',
                                            isset($invoice) ? $invoice->currency : (isset($preloadedData) ? $preloadedData['currency'] ?? 'MXN' : 'MXN'),
                                        ) == 'MXN'
                                            ? 'selected'
                                            : '' }}>
                                        Peso Mexicano (MXN)
                                    </option>
                                    <option value="USD"
                                        {{ old(
                                            'currency',
                                            isset($invoice) ? $invoice->currency : (isset($preloadedData) ? $preloadedData['currency'] ?? '' : ''),
                                        ) == 'USD'
                                            ? 'selected'
                                            : '' }}>
                                        Dólar Estadounidense (USD)
                                    </option>
                                    <option value="EUR"
                                        {{ old(
                                            'currency',
                                            isset($invoice) ? $invoice->currency : (isset($preloadedData) ? $preloadedData['currency'] ?? '' : ''),
                                        ) == 'EUR'
                                            ? 'selected'
                                            : '' }}>
                                        Euro (EUR)
                                    </option>
                                </select>
                            </td>
                            <td class="w-25">
                                <input id="exchange_rate" type="number" step="0.0001" name="exchange_rate"
                                    value="{{ old('exchange_rate', '1.0') }}" class="form-control">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <input type="hidden" name="tax" id="tax"
                value="{{ isset($invoice) ? $invoice->tax : (isset($preloadedData) ? $preloadedData['tax'] ?? '' : '') }}">
            <input type="hidden" name="total" id="total"
                value="{{ isset($invoice) ? $invoice->total : (isset($preloadedData) ? $preloadedData['total'] ?? '' : '') }}">
            <input type="hidden" name="invoice_id" id="invoice_id"
                value="{{ isset($invoice) ? $invoice->id : (isset($preloadedData) ? $preloadedData['invoice_id'] ?? '' : '') }}">

            {{-- BOTÓN DE GUARDAR --}}
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i>
                {{ isset($invoice) ? 'Actualizar Factura' : 'Confirmar Datos' }}
            </button>
        </form>
    </div>

    <script>
        let conceptIndex = {{ isset($invoice) && $invoice->items->count() > 0 ? $invoice->items->count() : 1 }};
        let invoice_customers = @json($invoiceCustomers);

        // Datos de conceptos para autocompletar campos
        const conceptsData = {!! json_encode(
            $invoiceConcepts->mapWithKeys(
                fn($c) => [
                    $c->id => [
                        'product_key' => $c->product_key,
                        'description' => $c->description,
                        'unit_code' => $c->unit_code,
                        'amount' => $c->amount,
                        'tax_rate' => $c->tax_rate ?? 0.16, // Cambiado de tax_total a tax_rate
                    ],
                ],
            ),
        ) !!};

        // Inicializar
        $(document).ready(function() {
            updateRemoveButtons();
            updateTotals();

            // Calcular totales iniciales para todas las filas
            /*$('#concepts-table tbody tr').each(function() {
                calculateRowTotals($(this));
            });*/


            // Si estamos editando y hay un cliente seleccionado, mostrar detalles
            @if (isset($invoice) && $invoice->customer)
                $('#customer-details').show();
            @else
                $('#customer-details').hide();
            @endif

            // Forzar actualización después de un breve delay para asegurar que todo esté cargado
            setTimeout(function() {
                updateTotals();
            }, 500);
        });

        // Manejar selección de cliente
        $(document).on('change', '#invoice_customer_id', function() {
            let customerId = $(this).val();
            let selectedOption = $(this).find('option:selected');

            if (customerId && selectedOption.length) {
                // Obtener datos del cliente del option seleccionado
                let customerData = {
                    comercial_name: selectedOption.data('comercial-name') || '-',
                    social_reason: selectedOption.data('social-reason') || '-',
                    rfc: selectedOption.data('rfc') || '-',
                    phone: selectedOption.data('phone') || '-',
                    email: selectedOption.data('email') || '-',
                    tax_system: selectedOption.data('tax-system') || '-',
                    cfdi_usage: selectedOption.data('cfdi-usage') || '-',
                    zip_code: selectedOption.data('zip-code') || '',
                    state: selectedOption.data('state') || '',
                    city: selectedOption.data('city') || '',
                    address: selectedOption.data('address') || '',
                    credit_limit: selectedOption.data('credit-limit') || 0,
                    credit_days: selectedOption.data('credit-days') || 0,
                    payment_method: selectedOption.data('payment-method') || '',
                    payment_form: selectedOption.data('payment-form') || '',
                    status: selectedOption.data('status') || ''
                };

                // Actualizar los campos de información
                $('#customer-social-reason').text(customerData.social_reason);
                $('#customer-comercial-name').text(customerData.comercial_name);
                $('#customer-rfc').text(customerData.rfc);
                $('#customer-phone').text(customerData.phone);
                $('#customer-email').text(customerData.email);
                $('#customer-tax-system').text(customerData.tax_system);
                $('#customer-cfdi-usage').text(customerData.cfdi_usage);

                // Actualizar campos de pago
                if (customerData.payment_method) {
                    $('#payment_method').val(customerData.payment_method);
                }
                if (customerData.payment_form) {
                    $('#payment_form').val(customerData.payment_form);
                }

                // Construir dirección completa
                let fullAddress = '';
                if (customerData.address) {
                    fullAddress = customerData.address;
                    if (customerData.city) fullAddress += ', ' + customerData.city;
                    if (customerData.state) fullAddress += ', ' + customerData.state;
                    if (customerData.zip_code) fullAddress += ', CP: ' + customerData.zip_code;
                }
                $('#customer-full-address').text(fullAddress || '-');

                // Información de crédito
                let creditInfo = '-';
                if (customerData.credit_limit > 0) {
                    creditInfo =
                        `$${parseFloat(customerData.credit_limit).toFixed(2)} (${customerData.credit_days} días)`;
                }
                $('#customer-credit-info').text(creditInfo);

                // Badge de estado
                let statusBadge = $('#customer-status-badge');
                statusBadge.removeClass('bg-success bg-warning bg-danger bg-secondary');

                switch (customerData.status) {
                    case 'facturable':
                        statusBadge.addClass('text-success').text('Facturable');
                        break;
                    case 'moroso':
                        statusBadge.addClass('text-warning').text('Moroso');
                        break;
                    case 'no_facturable':
                        statusBadge.addClass('text-danger').text('No Facturable');
                        break;
                    default:
                        statusBadge.addClass('text-secondary').text('-');
                }

                // Actualizar campos de pago si están disponibles
                if (customerData.payment_method) {
                    $('#payment_method').val(customerData.payment_method);
                }
                if (customerData.payment_form) {
                    $('#payment_form').val(customerData.payment_form);
                }

                // Mostrar la sección de detalles
                $('#customer-details').slideDown();

            } else {
                // Ocultar la sección si no hay cliente seleccionado
                $('#customer-details').slideUp();
            }
        });

        // Genera una nueva fila de concepto
        function getConceptRowHtml(index) {
            let options = `<option value="">Selecciona un concepto</option>`;
            @foreach ($invoiceConcepts as $concept)
                options += `<option value="{{ $concept->id }}"
        data-sat-key="{{ $concept->product_key }}"
        data-description="{{ $concept->description }}"
        data-unit-code="{{ $concept->unit_code }}"
        data-amount="{{ $concept->amount }}"
        data-tax-rate="{{ $concept->tax_rate ?? 0.16 }}">
        {{ $concept->name }}
    </option>`;
            @endforeach

            return `
    <tr>
        <td>
            <input type="number" name="services[${index}][quantity]" value="1" min="1"
                class="form-control text-center quantity-input" data-index="${index}">
        </td>
        <td>
            <select name="services[${index}][concept_id]" class="form-select concept-select" data-index="${index}" required>
                ${options}
            </select>
            <input type="hidden" name="services[${index}][product_key]" class="sat-key-input" data-index="${index}">
            <input type="hidden" name="services[${index}][description]" class="description-input" data-index="${index}">
            <input type="hidden" name="services[${index}][unit_code]" class="unit-code-input" data-index="${index}">
        </td>
        <td>
            <input type="number" step="0.01" name="services[${index}][amount]" value="0.00"
                class="form-control text-center amount-input" data-index="${index}" min="0.01">
        </td>
        <td>
            <input type="number" step="0.01" name="services[${index}][discount_rate]" value="0.00"
                class="form-control text-center discount-rate-input" data-index="${index}" min="0" max="100">
        </td>
        <td>
            <select name="services[${index}][tax_rate]" class="form-select tax-rate-select" data-index="${index}">
                <option value="0.16">16%</option>
                <option value="0.08">8%</option>
                <option value="0.00">0%</option>
            </select>
        </td>
        <td class="text-center fw-semibold">
            $<span class="subtotal-cell" id="subtotal-${index}">0.00</span>
        </td>
        <td>
            $<span class="taxes-amount-cell" id="taxes-amount-${index}">0.00</span>
        </td>
        <td class="text-center fw-semibold">
            $<span class="total-cell" id="total-${index}">0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-concept-btn" title="Eliminar">
                <i class="bi bi-trash-fill"></i>
            </button>
        </td>
    </tr>
    `;
        }
        // Agregar concepto
        $(document).on('click', '#add-concept-btn', function() {
            $('#concepts-table tbody').append(getConceptRowHtml(conceptIndex));
            conceptIndex++;
            updateRemoveButtons();
        });

        // Eliminar concepto
        $(document).on('click', '.remove-concept-btn', function() {
            if ($('#concepts-table tbody tr').length > 1) {
                $(this).closest('tr').remove();
                updateRemoveButtons();
                updateTotals();
            }
        });

        // Habilitar/deshabilitar botón eliminar según cantidad de filas
        function updateRemoveButtons() {
            let rows = $('#concepts-table tbody tr');
            rows.find('.remove-concept-btn').prop('disabled', rows.length === 1);
        }

        // Autocompletar campos al seleccionar concepto
        // En el evento change del concepto
        $(document).on('change', '.concept-select', function() {
            let index = $(this).data('index');
            let conceptId = $(this).val();
            let row = $(this).closest('tr');

            if (conceptId && conceptsData[conceptId]) {
                row.find('.sat-key-input').val(conceptsData[conceptId]['product_key']);
                row.find('.description-input').val(conceptsData[conceptId]['description']);
                row.find('.unit-code-input').val(conceptsData[conceptId]['unit_code']);
                row.find('.amount-input').val(conceptsData[conceptId]['amount']);
                row.find('.tax-rate-select').val(conceptsData[conceptId]['tax_rate'] || '0.16');
                row.find('.discount-rate-input').val('0.00');

                calculateRowTotals(row);
            } else {
                row.find('.sat-key-input').val('');
                row.find('.description-input').val('');
                row.find('.unit-code-input').val('');
                row.find('.amount-input').val('0.00');
                row.find('.tax-rate-select').val('0.16');
                row.find('.discount-rate-input').val('0.00');
                calculateRowTotals(row);
            }
            updateTotals();
        });

        // En el cálculo de totales por fila
        // Calcular totales por fila
        function calculateRowTotals(row) {
            let quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            let amount = parseFloat(row.find('.amount-input').val()) || 0;
            let discountRate = parseFloat(row.find('.discount-rate-input').val()) || 0;
            let taxRate = parseFloat(row.find('.tax-rate-select').val()) || 0;

            console.log('Calculando fila:', {
                quantity,
                amount,
                discountRate,
                taxRate
            });

            // Cálculos: Cantidad * Precio -> Descuento -> Subtotal -> IVA
            let grossAmount = quantity * amount;
            let discountAmount = grossAmount * (discountRate / 100);
            let subtotal = grossAmount - discountAmount;
            let taxesAmount = subtotal * taxRate;
            let total = subtotal + taxesAmount;

            console.log('Resultados:', {
                grossAmount,
                discountAmount,
                subtotal,
                taxesAmount,
                total
            });

            // Actualizar celdas de la fila
            row.find('.subtotal-cell').text(subtotal.toFixed(2));
            row.find('.taxes-amount-cell').text(taxesAmount.toFixed(2));
            row.find('.total-cell').text(total.toFixed(2));
        }

        // En el evento de recálculo
        $(document).on('input change', '.quantity-input, .amount-input, .discount-rate-input, .tax-rate-select',
            function() {
                let row = $(this).closest('tr');
                calculateRowTotals(row);
                updateTotals();
            });

        // Actualizar totales generales
        function updateTotals() {
            let totalSubtotal = 0;
            let totalTax = 0;
            let grandTotal = 0;

            $('#concepts-table tbody tr').each(function() {
                let subtotalText = $(this).find('.subtotal-cell').text();
                let taxesText = $(this).find('.taxes-amount-cell').text();
                let totalText = $(this).find('.total-cell').text();

                // Extraer solo el número (remover el $ si existe)
                let subtotal = parseFloat(subtotalText.replace('$', '')) || 0;
                let taxes = parseFloat(taxesText.replace('$', '')) || 0;
                let total = parseFloat(totalText.replace('$', '')) || 0;

                totalSubtotal += subtotal;
                totalTax += taxes;
                grandTotal += total;
            });

            // Verificar que la suma sea consistente
            if (Math.abs(grandTotal - (totalSubtotal + totalTax)) > 0.01) {
                grandTotal = totalSubtotal + totalTax;
            }

            //console.log('Subtotal:', totalSubtotal, 'IVA:', totalTax, 'Total:', grandTotal);

            // Actualizar totales en la interfaz
            $('#subtotal-total').text(`$${totalSubtotal.toFixed(2)}`);
            $('#tax-total').text(`$${totalTax.toFixed(2)}`);
            $('#total-general').text(`$${grandTotal.toFixed(2)}`);

            // Actualizar inputs ocultos para enviar al backend
            $('#tax').val(totalTax.toFixed(2));
            $('#total').val(grandTotal.toFixed(2));
        }

        function setCustomerCFDI(inv_customer_id) {
            var invoice_customer = invoice_customers.find(ic => ic.id == inv_customer_id);
            if (invoice_customer) {
                $('#cfdi_usage').val(invoice_customer.cfdi_usage);
            }
        }

       
    </script>

@endsection
