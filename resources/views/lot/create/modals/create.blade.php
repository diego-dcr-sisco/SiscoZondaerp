<!-- Modal -->
@php
    function extractAbbreviation(string $input): string
    {
        if (preg_match('/\((.*?)\)/', $input, $matches)) {
            return trim($matches[1]);
        }
        return $input;
    }
@endphp

<div class="modal fade" id="createLotModal" tabindex="-1" aria-labelledby="createLotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form class="modal-content border-0 shadow-sm needs-validation" action="{{ route('lot.store') }}"
            method="POST" id="lotForm" novalidate>
            @csrf

            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title text-primary fw-bold mb-1" id="createLotModalLabel">
                        <i class="bi bi-box-seam me-2"></i>
                        Crear lote
                    </h5>
                    <div class="small text-muted">Registra el lote y su disponibilidad inicial en almacén.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-info-circle me-2"></i>
                            Información del lote
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="product" class="form-label fw-semibold">
                                    <i class="bi bi-box me-1"></i>
                                    Producto <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <label class="input-group-text" for="search-product">
                                        <i class="bi bi-search"></i>
                                    </label>
                                    <input type="text" class="form-control" placeholder="Buscar producto"
                                        id="search-product" name="search_product" aria-label="Buscar producto"
                                        oninput="searchProducts(this.value)">
                                    <select name="product_id" id="product" class="form-select"
                                        onchange="setUnit(this.value)" required>
                                        <option value="" selected disabled>Seleccione un producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un producto.
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Si no encuentras el producto,
                                        <a href="{{ route('product.create') }}" target="_blank"
                                            class="text-decoration-underline link-primary">
                                            créalo aquí
                                        </a>
                                    </div>

                                    <div id="resultsHelp" class="form-text text-muted small">
                                        <span id="resultsCount">0</span> resultados
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" for="warehouse">
                                    <i class="bi bi-building me-1"></i>
                                    Almacén de registro <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="warehouse_id" id="warehouse" required>
                                    <option value="" selected disabled>Seleccione un almacén</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Seleccione un almacén.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="registration-number" class="form-label fw-semibold">
                                    <i class="bi bi-upc-scan me-1"></i>
                                    Número de lote <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="registration_number"
                                    id="registration-number" placeholder="Ej: L-2026-001" required autocomplete="off">
                                <div class="invalid-feedback">
                                    Ingrese el número de lote.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="start_date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar2-plus me-1"></i>
                                    Fecha de fabricación
                                </label>
                                <input type="date" class="form-control" name="start_date" id="start_date">
                            </div>

                            <div class="col-md-6">
                                <label for="expiration_date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar2-x me-1"></i>
                                    Fecha de expiración
                                </label>
                                <input type="date" class="form-control" name="expiration_date" id="expiration_date">
                                <div class="form-text">
                                    También se usará como fin de uso del lote.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-sliders me-2"></i>
                            Stock y configuración
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-stretch">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="amount" class="form-label fw-semibold">
                                    <i class="bi bi-boxes me-1"></i>
                                    Cantidad total <span class="text-danger">*</span>
                                    <span class="metrics-help-icon" data-bs-toggle="tooltip" data-bs-html="true"
                                        title="<div class='text-start'><h6 class='mb-2'>Métricas disponibles</h6><ul class='list-unstyled small'>
                                        @foreach ($metrics as $metric)
                                            <li>
                                                <strong>{{ extractAbbreviation($metric->value) }}</strong>: {{ str_replace('(' . extractAbbreviation($metric->value) . ')', '', $metric->value) }}</li>
                                        @endforeach
                                        </ul></div>">
                                        <i class="bi bi-question-circle-fill text-primary"></i>
                                    </span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="amount" id="amount"
                                        min="0" step="0.01" placeholder="0.00" required>
                                    <select class="input-group-text" id="metric" style="max-width: 120px;">
                                        @foreach ($metrics as $metric)
                                            <option value="{{ $metric->id }}">{{ extractAbbreviation($metric->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Ingrese la cantidad inicial del lote.
                                    </div>
                                </div>
                                <div class="form-text">
                                    Esta cantidad registra el lote y genera su entrada inicial.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="h-100 bg-light rounded p-3 text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input form-check-input-lg" type="checkbox"
                                            role="switch" name="is_active" id="is-active" value="1" checked>
                                    </div>
                                    <label class="form-check-label fw-semibold mt-2" for="is-active">
                                        <i class="bi bi-eye-fill text-success me-1"></i>
                                        Lote activo para captura
                                    </label>
                                    <p class="small text-muted mt-1 mb-0">
                                        Si está activo, podrá seleccionarse en entradas, salidas y reportes nuevos.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Los campos con <span class="text-danger">*</span> son obligatorios.
                </small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="lotSubmitBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="lotSubmitSpinner"></span>
                        <i class="bi bi-check-lg me-1" id="lotSubmitIcon"></i>
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    #createLotModal .modal-content,
    #createLotModal .card {
        border-radius: 0.5rem;
    }

    #createLotModal .form-check-input-lg {
        width: 2.5rem;
        height: 1.25rem;
    }
</style>

<script>
    const metrics = @json($metrics);
    const products = @json($products);

    function getAbbreviation(cadena) {
        const regex = /\(([^)]+)\)/;
        const coincidencia = cadena.match(regex);
        return coincidencia ? coincidencia[1] : null;
    }

    function setUnit(product_id) {
        var product = products.find(item => item.id == product_id);
        if (product) {
            var metric = metrics.find(item => item.id == product.metric_id);
            $('#metric').val(product.metric_id);
        }
    }

    function searchProducts(query) {

        // Create FormData object
        const formData = new FormData();
        formData.append('q', query);
        formData.append('_token', '{{ csrf_token() }}'); // Add CSRF token for security

        $.ajax({
            url: "{{ route('lot.products.search') }}",
            method: 'POST', // Changed to POST since we're using FormData
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            success: function(response) {
                const $select = $('#product');
                console.log(response);
                $select.empty().append(
                    '<option value="" selected disabled>Seleccione un producto</option>'
                );

                if (response.data && response.data.length > 0) {
                    response.data.forEach(product => {
                        $select.append(
                            `<option value="${product.id}">${product.name}</option>`
                        );
                    });
                } else {
                    $select.append('<option value="" disabled>No se encontraron productos</option>');
                }

                $('#resultsCount').text(response.data.length);
            },
            error: function(xhr) {
                //console.error('Error searching products:', xhr.responseText);
                $('#product').html(`
                <option value="" selected disabled>Error en la búsqueda</option>
                <option value="" disabled>${xhr.responseJSON?.message || 'Intente nuevamente'}</option>
            `);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const lotForm = document.getElementById('lotForm');
        const submitBtn = document.getElementById('lotSubmitBtn');
        const submitSpinner = document.getElementById('lotSubmitSpinner');
        const submitIcon = document.getElementById('lotSubmitIcon');

        if (!lotForm) {
            return;
        }

        lotForm.addEventListener('submit', function(event) {
            if (!lotForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                lotForm.classList.add('was-validated');
                return;
            }

            submitBtn.disabled = true;
            submitSpinner.classList.remove('d-none');
            submitIcon.classList.add('d-none');
        });
    });
</script>
