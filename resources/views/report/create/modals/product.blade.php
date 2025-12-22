<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form class="modal-content" id="product-form" action="{{ route('report.set.product', ['orderId' => $order->id]) }}"
            method="POST">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="productModalLabel">Editar producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="op-id" name="op_id" value="" />
                <div class="mb-3">
                    <label for="service-id" class="form-label is-required">Servicio relacionado</label>
                    <select class="form-select" id="service" name="service_id" required>
                        <option value="" selected>Sin servicio</option>
                        @foreach ($order->services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product-id" class="form-label is-required">Producto</label>
                    <select class="form-select" id="product" name="product_id" onchange="completeProduct()" required>
                        <option value="" selected>Sin producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product-id" class="form-label is-required">Método de aplicación</label>
                    <select class="form-select" id="application-method" name="application_method_id"
                        onchange="completeProduct()" required>
                        @foreach ($application_methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product-amount" class="form-label is-required">Cantidad usada</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="amount" name="amount" placeholder="0.00"
                            min="0" step="0.01" required>
                        <select class="form-select" id="metric" name="metric_id">
                            <option value="" selected>Sin métrica o unidades</option>
                            @foreach ($metrics as $metric)
                                <option value="{{ $metric->id }}">{{ $metric->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="product-dosage" class="form-label">Dosificación (Por litro)</label>
                    <input type="text" class="form-control" id="dosage" name="dosage" placeholder="10ml x Litro">
                </div>
                <div class="mb-3">
                    <label for="product-unit" class="form-label">Lote: </label>
                    <select class="form-select" id="lot" name="lot_id">
                        @foreach ($lots as $lot)
                            <option value="{{ $lot->id }}">[ {{ $lot->registration_number }} ]
                                {{ $lot->product->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary"
                    onclick="cleanDisabled()">{{ __('buttons.store') }}</button>
            </div>
        </form>
    </div>
</div>


<script>
    var products_found = [];

    function setProduct(element) {
        const productData = element.getAttribute("data-product");
        let data;

        try {
            data = JSON.parse(productData);
        } catch {
            data = productData;
        }

        console.log(data);

        $('#op-id').val(data.id);
        $('#service').val(data.service_id);
        $('#application-method').val(data.application_method_id ?? 1);
        $('#product').val(data.product_id);
        $('#metric').val(data.metric_id);
        $('#lot').val(data.lot_id);
        $('#amount').val(data.amount)
        $('#dosage').val(data.dosage)

        $('#service').prop('disabled', true);
        //$('#application-method').prop('disabled', data.application_method_id ? true : false);
        $('#product').prop('disabled', true);
    }

    function cleanDisabled() {
        $('#service').prop('disabled', false);
        $('#application-method').prop('disabled', false);
        $('#product').prop('disabled', false);
    }

    function completeProduct() {
        var product_id = $('#product').val();

        if (product_id /*&& appmethod_id*/ ) {
            var found_product = allProducts.find(item => item.id == product_id);
            var found_lots = lots.find(item => item.product_id == product_id);

            console.log(found_product);
            console.log(found_lots);

            $('#metric').val(found_product.metric_id)
            $('#dosage').val(found_product.dosage);
            $('#lot').val(found_lots.id);
        }
    }

    function cleanForm() {
        $('#product-form').find('input[type="text"], input[type="email"], input[type="number"]').val('');
        $('#op-id').val(null);
        $('#product-form').find('select').val('');
        $('#product-form').find('input[type="checkbox"], input[type="radio"]').prop('checked', false);

        $('#service').prop('disabled', false);
        $('#application-method').prop('disabled', false);
        $('#product').prop('disabled', false);
    }
</script>
