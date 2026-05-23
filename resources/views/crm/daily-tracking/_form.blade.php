@php
    $model = $dailyTracking ?? null;
    $services = $services ?? collect();

    $normalizeEnumValue = static function ($value) {
        return $value instanceof \BackedEnum ? $value->value : $value;
    };

    $quotedCurrent = $normalizeEnumValue(data_get($model, 'quoted'));
    $closedCurrent = $normalizeEnumValue(data_get($model, 'closed'));
    $invoiceCurrent = $normalizeEnumValue(data_get($model, 'invoice'));

    $quotedValue = old('quoted', $quotedCurrent ?? 'pending');
    $closedValue = old('closed', $closedCurrent ?? 'pending');
    $invoiceValue = old('invoice', $invoiceCurrent ?? 'no');
    $hasCoverage = (bool) old('has_not_coverage', data_get($model, 'has_not_coverage', false));
@endphp

<div id="main-form" x-data="{
    quoted: '{{ $quotedValue }}',
    closed: '{{ $closedValue }}',
    invoice: '{{ $invoiceValue }}',
    hasCoverage: {{ $hasCoverage ? 'true' : 'false' }},
    conversion: '{{ old('conversion', '') }}',
    hasLinkedCustomer: {{ old('customer_id', data_get($model, 'customer_id')) ? 'true' : 'false' }}
}">
    <div class="row g-3">
        <div class="col-12">
            <h6 class="mb-1 text-primary"><i class="bi bi-toggles2 me-1"></i> Indicadores de contacto</h6>
            <small class="text-muted">Banderas rapidas de respuesta y cobertura del cliente.</small>
        </div>

        <div class="col-md-3">
            <input type="hidden" name="did_not_respond" value="0">

            <div class="form-check form-switch mt-2">

                <input class="form-check-input" type="checkbox" id="did_not_respond" name="did_not_respond"
                    value="1" @checked(old('did_not_respond', data_get($model, 'did_not_respond')))>

                <label class="form-check-label" for="did_not_respond">
                    No Respondió
                </label>

            </div>
        </div>

        <div class="col-md-3">
            <input type="hidden" name="has_not_coverage" value="0">

            <div class="form-check form-switch mt-2">

                <input class="form-check-input" type="checkbox" id="has_not_coverage" name="has_not_coverage"
                    value="1" @checked(old('has_not_coverage', data_get($model, 'has_not_coverage')))>

                <label class="form-check-label" for="has_not_coverage">
                    Sin cobertura
                </label>

            </div>
        </div>

        <div class="col-md-3">
            <input type="hidden" name="is_recurrent" value="0">

            <div class="form-check form-switch mt-2">

                <input class="form-check-input" type="checkbox" id="is_recurrent" name="is_recurrent" value="1"
                    @checked(old('is_recurrent', data_get($model, 'is_recurrent')))>

                <label class="form-check-label" for="is_recurrent">
                    Es recurrente
                </label>

            </div>
        </div>

        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-person-vcard me-1"></i> Datos del cliente</h6>
            <small class="text-muted">Informacion general y datos de contacto.</small>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold mb-1">Servicio *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-gear-fill"></i></span>
                <select name="service_id" class="form-select form-select-sm @error('service_id') is-invalid @enderror"
                    required>
                    <option value="">Seleccionar servicio</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', data_get($model, 'service_id')) == $service->id)>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('service_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <input type="hidden" name="customer_id" id="customer_id"
                value="{{ old('customer_id', data_get($model, 'customer_id')) }}">
            <label class="form-label fw-semibold mb-1">Nombre del cliente *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                <input type="text" name="customer_name" id="customer_name"
                    class="form-control form-control-sm @error('customer_name') is-invalid @enderror"
                    value="{{ old('customer_name', data_get($model, 'customer_name')) }}" oninput="suggestCustomer()"
                    autocomplete="off" required>
            </div>
            <ul id="customer-suggestions" class="list-group shadow-sm"
                style="display:none; position:absolute; z-index:1000; width:100%; max-height:200px; overflow-y:auto;">
            </ul>
            @error('customer_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Telefono</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                <input type="text" name="phone"
                    class="form-control form-control-sm @error('phone') is-invalid @enderror"
                    value="{{ old('phone', data_get($model, 'phone')) }}">
            </div>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Tipo de cliente *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                <select name="customer_type"
                    class="form-select form-select-sm @error('customer_type') is-invalid @enderror" required>
                    @foreach ($customerTypeOptions as $option)
                        <option value="{{ $option->value }}" @selected(old('customer_type', data_get($model, 'customer_type.value') ?? data_get($model, 'customer_type')) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('customer_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Categoria de cliente</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>

                <select name="customer_category"
                    class="form-select form-select-sm @error('customer_category') is-invalid @enderror" required>
                    @foreach ($customerCategories as $category)
                        <option value="{{ $category->id }}" @selected(old('customer_category', data_get($model, 'customer_category')) === $category->id)>
                            {{ $category->category }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('customer_category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Metodo de contacto *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
                <select name="contact_method"
                    class="form-select form-select-sm @error('contact_method') is-invalid @enderror" required>
                    @foreach ($contactMethodOptions as $option)
                        <option value="{{ $option->value }}" @selected(old('contact_method', data_get($model, 'contact_method.value') ?? data_get($model, 'contact_method')) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('contact_method')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Estado</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-map-fill"></i></span>
                @php
                    $selectedState = old('state', data_get($model, 'state'));
                @endphp
                <select id="state" name="state" onchange="load_city()"
                    class="form-select form-select-sm @error('state') is-invalid @enderror">
                    <option value="">Seleccionar estado</option>
                    @foreach ($states as $stateItem)
                        @php
                            $stateKey = is_array($stateItem)
                                ? $stateItem['key'] ?? ($stateItem['name'] ?? '')
                                : (string) $stateItem;
                            $stateName = is_array($stateItem) ? $stateItem['name'] ?? $stateKey : (string) $stateItem;
                        @endphp
                        <option value="{{ $stateKey }}" @selected((string) $selectedState === (string) $stateKey || (string) $selectedState === (string) $stateName)>
                            {{ $stateName }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Ciudad</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                <select id="city" name="city"
                    data-selected-city="{{ old('city', data_get($model, 'city')) }}"
                    class="form-select form-select-sm @error('city') is-invalid @enderror">
                    <option value="">Seleccionar ciudad</option>
                </select>
            </div>
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold mb-1">Direccion</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-pin-map-fill"></i></span>
                <textarea name="address" rows="2" class="form-control form-control-sm @error('address') is-invalid @enderror">{{ old('address', data_get($model, 'address')) }}</textarea>
            </div>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-bar-chart-steps me-1"></i>Seguimiento comercial</h6>
            <small class="text-muted">Estado del proceso, cotizacion, cierre y facturacion.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Estatus *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-flag-fill"></i></span>
                <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror"
                    required>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option->value }}" @selected(old('status', data_get($model, 'status.value') ?? data_get($model, 'status')) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 field-spotlight" x-show="closed === 'yes'" x-transition.opacity.duration.250ms>
            <label class="form-label fw-semibold mb-1">Metodo de pago</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-credit-card-fill"></i></span>
                <select name="payment_method"
                    class="form-select form-select-sm @error('payment_method') is-invalid @enderror">
                    <option value="">No definido</option>
                    @foreach ($paymentMethodOptions as $option)
                        <option value="{{ $option->value }}" @selected(old('payment_method', data_get($model, 'payment_method.value') ?? data_get($model, 'payment_method')) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('payment_method')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-file-earmark-text me-1"></i>Cotización</h6>
            <small class="text-muted">Estado del proceso, cotizacion, cierre y facturacion.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Cotizado *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-cash-stack"></i></span>
                <select name="quoted" x-model="quoted"
                    class="form-select form-select-sm @error('quoted') is-invalid @enderror" required>
                    @foreach ($quotedOptions as $option)
                        <option value="{{ $option->value }}" @selected(old('quoted', data_get($model, 'quoted.value') ?? data_get($model, 'quoted')) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('quoted')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Fecha de envio de cotizacion</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-send-fill"></i></span>
                <input type="date" name="quote_sent_date"
                    class="form-control form-control-sm @error('quote_sent_date') is-invalid @enderror"
                    value="{{ old('quote_sent_date', optional(data_get($model, 'quote_sent_date'))->format('Y-m-d')) }}">
            </div>
            @error('quote_sent_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4" x-show="quoted === 'yes'">
            <label class="form-label fw-semibold mb-1">Monto cotizado</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                <input type="number" step="0.01" min="0" name="quoted_amount"
                    class="form-control form-control-sm @error('quoted_amount') is-invalid @enderror"
                    value="{{ old('quoted_amount', data_get($model, 'quoted_amount')) }}">
            </div>
            @error('quoted_amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Cerrado *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
                <select name="closed" x-model="closed"
                    class="form-select form-select-sm @error('closed') is-invalid @enderror" required>
                    @foreach ($closedOptions as $option)
                        <option value="{{ $option->value }}" @selected(old('closed', data_get($model, 'closed.value') ?? data_get($model, 'closed')) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('closed')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 field-spotlight" x-show="closed === 'yes'" x-transition.opacity.duration.250ms>
            <label class="form-label fw-semibold mb-1">Fecha de cierre</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-calendar-check-fill"></i></span>
                <input type="date" name="close_date"
                    class="form-control form-control-sm @error('close_date') is-invalid @enderror"
                    value="{{ old('close_date', optional(data_get($model, 'close_date'))->format('Y-m-d')) }}">
            </div>
            @error('close_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Fecha de pago</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-calendar2-week-fill"></i></span>
                <input type="date" name="payment_date"
                    class="form-control form-control-sm @error('payment_date') is-invalid @enderror"
                    value="{{ old('payment_date', optional(data_get($model, 'payment_date'))->format('Y-m-d')) }}">
            </div>
            @error('payment_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-file-earmark-arrow-up me-1"></i> Factura</h6>
            <small class="text-muted">Estado del proceso, cotizacion, cierre y facturacion.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Requiere factura *</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"></i></span>
                <select name="invoice" x-model="invoice"
                    class="form-select form-select-sm @error('invoice') is-invalid @enderror" required>
                    @foreach ($invoiceOptions as $option)
                        <option value="{{ $option->value }}" @selected($invoiceValue === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('invoice')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 field-spotlight" x-show="closed === 'yes'" x-transition.opacity.duration.250ms>
            <label class="form-label fw-semibold mb-1">Monto facturado</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-receipt"></i></span>
                <input type="number" step="0.01" min="0" name="billed_amount"
                    class="form-control form-control-sm @error('billed_amount') is-invalid @enderror"
                    value="{{ old('billed_amount', data_get($model, 'billed_amount')) }}">
            </div>
            @error('billed_amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-calendar-event me-1"></i> Servicio programado</h6>
            <small class="text-muted">Define la fecha y la hora programada del servicio.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Fecha de servicio</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-calendar-event-fill"></i></span>
                <input type="date" name="service_date"
                    class="form-control form-control-sm @error('service_date') is-invalid @enderror"
                    value="{{ old('service_date', optional(data_get($model, 'service_date'))->format('Y-m-d')) }}">
            </div>
            @error('service_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Hora del servicio</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
                <input type="time" name="service_time"
                    class="form-control form-control-sm @error('service_time') is-invalid @enderror"
                    value="{{ old('service_time', substr((string) data_get($model, 'service_time', ''), 0, 5)) }}">
            </div>
            @error('service_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-calendar3-range me-1"></i> Fechas de seguimiento</h6>
            <small class="text-muted">Control de cotizacion, cierre, pago y proximo seguimiento.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold mb-1">Fecha de seguimiento</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-calendar-range-fill"></i></span>
                <input type="date" name="follow_up_date"
                    class="form-control form-control-sm @error('follow_up_date') is-invalid @enderror"
                    value="{{ old('follow_up_date', optional(data_get($model, 'follow_up_date'))->format('Y-m-d')) }}">
            </div>
            @error('follow_up_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-journal-text me-1"></i> Notas</h6>
            <small class="text-muted">Comentarios adicionales del seguimiento.</small>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold mb-1">Plaga enfocada</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-bug-fill"></i></span>
                <input type="text" name="focused_pest"
                    class="form-control form-control-sm @error('focused_pest') is-invalid @enderror"
                    value="{{ old('focused_pest', data_get($model, 'focused_pest')) }}"
                    placeholder="Ej: Cucaracha alemana, roedor, mosca">
            </div>
            @error('focused_pest')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold mb-1">Notas</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                <textarea name="notes" rows="3" class="form-control form-control-sm @error('notes') is-invalid @enderror">{{ old('notes', data_get($model, 'notes')) }}</textarea>
            </div>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        {{-- Funciones de conversión --}}
        <div class="col-12 border-top pt-3 mt-1" id="conversion-section">

            <h6 class="mb-1 text-primary">
                <i class="bi bi-person-gear me-1"></i>
                Funciones de conversión
            </h6>

            <small class="text-muted">
                Acciones para convertir el contacto en un cliente o lead.
            </small>

            {{-- ÚNICA fuente de verdad --}}
            <input type="hidden" name="conversion" id="conversion" value="{{ old('conversion', '') }}">

            <div class="row g-2 mt-2">

                {{-- Convertir en Cliente --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">

                        <div class="input-group-text bg-success-subtle">
                            <input class="form-check-input mt-0 conversion-checkbox" type="checkbox"
                                id="convert_to_client" data-value="client">
                        </div>

                        <label class="form-control bg-primary-subtle-light" for="convert_to_client"
                            style="cursor:pointer;">

                            Convertir en <b>Cliente</b>

                        </label>
                    </div>
                </div>

                {{-- Convertir en Lead --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">

                        <div class="input-group-text bg-info-subtle">
                            <input class="form-check-input mt-0 conversion-checkbox" type="checkbox"
                                id="convert_to_lead" data-value="lead">
                        </div>

                        <label class="form-control bg-warning-subtle-light" for="convert_to_lead"
                            style="cursor:pointer;">

                            Convertir en <b>Cliente Potencial (lead)</b>

                        </label>
                    </div>
                </div>

                {{-- Cliente sugerido --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">

                        <div class="input-group-text bg-warning-subtle">
                            <input class="form-check-input mt-0 conversion-checkbox" type="checkbox"
                                id="convert_to_suggested" data-value="suggested" disabled>
                        </div>

                        <label class="form-control bg-warning-subtle-light" for="convert_to_suggested"
                            style="cursor:pointer;">

                            Cliente relacionado <b>por sugerencia</b>

                        </label>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-12 border-top pt-3 mt-1">
            <h6 class="mb-1 text-primary"><i class="bi bi-tools me-1"></i> Programar actividades</h6>
            <small class="text-muted">Actividades a programar para el seguimiento u order de servicio</small>
        </div>

        <!-- Generar orden de servicio CON selección de técnico -->
        <div class="col-md-6" x-data="{ showTechnician: {{ old('generate_order', false) ? 'true' : 'false' }} }">
            <div class="input-group input-group-sm">
                <!-- Checkbox de activación -->
                <div class="input-group-text bg-success-subtle">
                    <input type="hidden" name="generate_order" value="0">
                    <input class="form-check-input mt-0" type="checkbox" value="1" id="generate_order"
                        name="generate_order" @checked(old('generate_order', false))
                        x-on:change="showTechnician = $el.checked">
                </div>

                <!-- Etiqueta principal -->
                <label class="input-group-text bg-success-subtle-light fw-semibold" for="generate_order"
                    style="width: 200px;">
                    Generar orden para:
                </label>

                <!-- Dropdown de Técnicos (Solo visible si el checkbox está marcado) -->
                <select name="technician_id" class="form-select @error('technician_id') is-invalid @enderror"
                    x-show="showTechnician" x-transition :required="showTechnician">
                    <option value="">Seleccionar técnico...</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}" @selected(old('technician_id') == $technician->id)>
                            {{ $technician->user->name ?? '-' }}
                        </option>
                    @endforeach
                </select>

                <!-- Mensaje cuando está desactivado -->
                <template x-if="!showTechnician">
                    <input type="text" class="form-control" placeholder="Marcar para asignar técnico" readonly>
                </template>
            </div>
            @error('technician_id')
                <small class="text-danger mt-1 d-block">{{ $message }}</small>
            @enderror
        </div>

        <!-- Registrar seguimiento -->
        <div class="col-md-6">
            <div class="input-group input-group-sm">
                <div class="input-group-text bg-info-subtle">
                    <input type="hidden" name="register_follow_up" value="0">
                    <input class="form-check-input mt-0" type="checkbox" value="1" id="register_follow_up"
                        name="register_follow_up" @checked(old('register_follow_up', false))>
                </div>
                <label class="form-control bg-info-subtle-light" for="register_follow_up">
                    Registrar seguimiento (Sin orden)
                </label>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var states = @json($states);
    var cities = @json($cities);


    $(document).ready(function() {
        load_city();
    });

    function load_city() {
        var state = $("#state").val();
        var $selector_city = $("#city");
        var selectedCity = $selector_city.attr('data-selected-city') || '';

        $selector_city.empty();
        $selector_city.append($('<option>', {
            value: '',
            text: 'Seleccionar ciudad'
        }));

        if (state) {
            var found_cities = cities[state] || [];

            if (Array.isArray(found_cities)) {
                $selector_city.append(found_cities.map(function(c) {
                    var value = (typeof c === 'object' && c !== null) ? (c.name || '') : c;
                    return $('<option>', {
                        value: value,
                        text: value,
                        selected: value === selectedCity
                    });
                }));
            }
        }
    }

    function convertToUppercase(id) {
        $("#" + id).val($("#" + id).val().toUpperCase());
    }

    function setConversion(value = '') {

        // Desmarcar todos
        $(".conversion-checkbox").prop("checked", false);

        // Si existe valor, marcar el correspondiente
        if (value !== '') {

            $(".conversion-checkbox[data-value='" + value + "']")
                .prop("checked", true);

        }

        // Guardar valor real
        $("#conversion").val(value);
    }

    // Cambio manual
    $(document).on("change", ".conversion-checkbox", function() {

        let value = $(this).data("value");

        if ($(this).is(":checked")) {

            setConversion(value);

        } else {

            setConversion('');

        }

    });

    // Restaurar valor old()
    $(document).ready(function() {

        let current = $("#conversion").val();

        if (current === 'suggested') {

            $("#convert_to_suggested")
                .prop("disabled", false);

        }

        setConversion(current);

    });

    let suggestTimer = null;

    function suggestCustomer() {

        var input = $("input[name='customer_name']");
        var query = input.val();
        var $list = $("#customer-suggestions");

        clearTimeout(suggestTimer);

        $list.hide().empty();

        if (query.length < 3) return;

        suggestTimer = setTimeout(function() {

            $.ajax({

                url: "{{ route('crm.daily-tracking.suggest-customers') }}",
                method: "GET",

                data: {
                    query: query
                },

                success: function(response) {

                    $list.empty();

                    if (!response.length) {

                        $list.hide();
                        return;

                    }

                    $.each(response, function(i, customer) {

                        var label = customer.name || customer;

                        var $item = $('<li>')
                            .addClass('list-group-item list-group-item-action py-1 px-2')
                            .css('cursor', 'pointer')
                            .text(label)

                            .on('mousedown', function(e) {

                                e.preventDefault();

                                // Cliente
                                input.val(customer.name);

                                $("#customer_id").val(customer.id);

                                // Telefono
                                if (customer.phone) {
                                    $("input[name='phone']")
                                        .val(customer.phone);
                                }

                                // Direccion
                                if (customer.address) {
                                    $("textarea[name='address']")
                                        .val(customer.address);
                                }

                                // Estado
                                if (customer.state) {
                                    $("#state").val(customer.state);
                                    // Cargar ciudades del estado seleccionado
                                    load_city();
                                }

                                // Ciudad
                                if (customer.city) {
                                    $("#city").val(customer.city);
                                }
                                // Conversion sugerida
                                $("#convert_to_suggested")
                                    .prop("disabled", false);
                                setConversion('suggested');
                                // Ocultar sugerencias
                                $list.hide().empty();
                            });
                        $list.append($item);
                    });

                    $list.show();

                },

                error: function() {

                    console.error("Error al obtener sugerencias de clientes.");

                }

            });

        }, 300);

    }

    $("input[name='customer_name']").on('input', function() {

        $("#customer_id").val('');

        $("#convert_to_suggested")
            .prop("disabled", true)
            .prop("checked", false);

        setConversion('');

    });

    $(document).on('click', function(e) {

        if (!$(e.target).closest('#customer_name, #customer-suggestions').length) {

            $("#customer-suggestions")
                .hide()
                .empty();

        }

    });
</script>
