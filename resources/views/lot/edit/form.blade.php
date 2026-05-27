@php
    if (!function_exists('extractAbbreviation')) {
        function extractAbbreviation(string $input): string
        {
            if (preg_match('/\((.*?)\)/', $input, $matches)) {
                return trim($matches[1]);
            }

            return $input;
        }
    }
@endphp

<form class="m-3 needs-validation" method="POST" action="{{ route('lot.update', $lot->id) }}" novalidate>
    @csrf
    @method('PUT')

    <div class="border rounded shadow p-3 mb-3" style="background-color: #ffffff">
        <div class="fw-bold mb-2 fs-5">Información del lote</div>

        <div class="row">
            <div class="col-lg-6 col-12 mb-3">
                <label class="form-label is-required" for="product">Producto</label>
                <select name="product_id" id="product" class="form-select" required>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ $lot->product_id == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Seleccione un producto.</div>
            </div>

            <div class="col-lg-6 col-12 mb-3">
                <label class="form-label is-required" for="warehouse">Almacén destino</label>
                <select class="form-select" name="warehouse_id" id="warehouse" required>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}"
                            {{ $lot->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Seleccione un almacén.</div>
            </div>

            <div class="col-lg-4 col-12 mb-3">
                <label class="form-label is-required" for="registration-number">Número de lote</label>
                <input type="text" class="form-control" id="registration-number" name="registration_number"
                    value="{{ old('registration_number', $lot->registration_number) }}" required>
                <div class="invalid-feedback">Ingrese el número de lote.</div>
            </div>

            <div class="col-lg-4 col-12 mb-3">
                <label class="form-label is-required" for="start-date">Fecha de fabricación</label>
                <input type="date" class="form-control" name="start_date" id="start-date"
                    value="{{ old('start_date', $lot->start_date ? \Illuminate\Support\Carbon::parse($lot->start_date)->format('Y-m-d') : '') }}"
                    required>
                <div class="invalid-feedback">Ingrese la fecha de fabricación.</div>
            </div>

            <div class="col-lg-4 col-12 mb-3">
                <label class="form-label" for="expiration-date">Fecha de expiración</label>
                <input type="date" class="form-control" id="expiration-date" name="expiration_date"
                    value="{{ old('expiration_date', $lot->expiration_date ? \Illuminate\Support\Carbon::parse($lot->expiration_date)->format('Y-m-d') : '') }}">
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3 mb-3" style="background-color: #ffffff">
        <div class="fw-bold mb-2 fs-5">Stock y configuración</div>

        <div class="row">
            <div class="col-lg-6 col-12 mb-3">
                <label class="form-label is-required" for="amount">
                    Cantidad total
                    <span class="metrics-help-icon" data-bs-toggle="tooltip" data-bs-html="true"
                        title="<div class='text-start'><h6 class='mb-2'>Métricas disponibles</h6><ul class='list-unstyled small'>
                        @foreach ($metrics as $metric)
                            <li><strong>{{ extractAbbreviation($metric->value) }}</strong>: {{ str_replace('(' . extractAbbreviation($metric->value) . ')', '', $metric->value) }}</li>
                        @endforeach
                        </ul></div>">
                        <i class="bi bi-question-circle-fill text-primary"></i>
                    </span>
                </label>
                <div class="input-group">
                    <input type="number" class="form-control" name="amount" id="amount" min="0"
                        step="0.01" value="{{ old('amount', $lot->amount) }}" required>
                    <select class="input-group-text" id="metric" style="max-width: 120px;">
                        @foreach ($metrics as $metric)
                            <option value="{{ $metric->id }}"
                                {{ optional($lot->product)->metric_id == $metric->id ? 'selected' : '' }}>
                                {{ extractAbbreviation($metric->value) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Ingrese la cantidad del lote.</div>
                </div>
            </div>

            <div class="col-lg-6 col-12 mb-3">
                <label class="form-label d-block" for="is-active">Estado del lote</label>
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is-active"
                        value="1" {{ old('is_active', $lot->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is-active">
                        Lote activo para captura
                    </label>
                </div>
                <div class="form-text">
                    Si está activo, podrá seleccionarse en entradas, salidas y reportes nuevos.
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary my-3">
            {{ __('buttons.update') }}
        </button>
        <a href="{{ route('lot.index') }}" class="btn btn-secondary my-3">
            Cancelar
        </a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.needs-validation');

        if (form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                }
            });
        }

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
            new bootstrap.Tooltip(element);
        });
    });
</script>
