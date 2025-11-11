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
    <form class="modal-dialog modal-dialog-scrollable modal-lg" action="{{ route('lot.store') }}" method="POST">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLotModalLabel">Crear Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="product_id" class="form-label is-required">Producto</label>
                        <div class="input-group">
                            <label class="input-group-text" for="search-product"><i class="bi bi-search"></i></label>
                            <input type="text" class="form-control" placeholder="Buscar producto" id="search-product"
                                name="search_product" aria-label="Buscar producto" oninput="searchProducts(this.value)">
                            <select name="product_id" id="product" class="form-select" onchange="setUnit(this.value)"
                                required>
                                <option value="" selected disabled>Seleccione un producto</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1 mb-2">
                            <!-- Texto de ayuda para creación de productos -->
                            <div class="form-text">
                                <span class="text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Si no encuentras el producto,
                                    <a href="{{ route('product.create') }}" target="_blank"
                                        class="text-decoration-underline link-primary">
                                        crea uno nuevo aquí
                                    </a>
                                </span>
                            </div>

                            <!-- Contador de resultados (inicialmente oculto) -->
                            <div id="resultsHelp" class="form-text text-muted small">
                                <span id="resultsCount">0</span> resultados encontrados
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label class="form-label is-required" for="warehouse">Almacen de registro</label>
                        <select class="form-select" name="warehouse_id" id="warehouse" required>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="registration-number" class="form-label is-required">Número de Lote </label>
                        <input type="text" class="form-control" name="registration_number" id="registration-number"
                            required>
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <label for="expiration_date" class="form-label">Fecha de Expiración </label>
                        <input type="date" class="form-control" name="expiration_date" id="expiration_date">
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <div class="d-flex">
                            <label for="amount" class="form-label is-required">Cantidad total
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
                        </div>
                        <div class="input-group">
                            <input type="number" class="form-control" name="amount" id="amount" min="0"
                                step="0.01" required>
                            <select class="input-group-text" id="metric" style="max-width: 120px;">
                                @foreach ($metrics as $metric)
                                    <option value="{{ $metric->id }}">{{ extractAbbreviation($metric->value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
            </div>
        </div>
    </form>
</div>

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
                console.error('Error searching products:', xhr.responseText);
                $('#product').html(`
                <option value="" selected disabled>Error en la búsqueda</option>
                <option value="" disabled>${xhr.responseJSON?.message || 'Intente nuevamente'}</option>
            `);
            }
        });
    }
</script>
