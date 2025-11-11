<div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="inputModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" class="form" method="POST"
            action="{{ route('product.input', ['id' => $product->id]) }}">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="inputModalLabel">Insumo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="product" class="form-label">Producto </label>
                    <input type="text" class="form-control" id="product"
                        name="product-id" value="{{ $product->name }}" disabled>
                </div>
                <div class="mb-3">
                    <label for="appMethod" class="form-label is-required">Método de aplicación </label>
                    <select class="form-select" id="application-method"
                        name="application_method_id" required>
                        @foreach ($product->applicationMethods as $appMethod)
                            <option value="{{ $appMethod->id }}">{{ $appMethod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-baseline border-bottom mb-3">
                        <label class="form-label m-0 is-required">Plagas </label>
                        <button type="button" class="btn btn-success btn-sm mb-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false"
                            aria-controls="collapseExample">{{ __('buttons.add') }}</button>
                    </div>

                    <div class="collapse my-2" id="collapseExample">
                        <div class="card card-body">
                            <div class="mb-3 row">
                                <label class="col-auto col-form-label is-required">Plaga
                                    (Categoría): </label>
                                <div class="col-7">
                                    <select class="form-select"
                                        id="pest_category_id">
                                        @foreach ($pest_categories as $pest_category)
                                            <option value="{{ $pest_category->id }}">{{ $pest_category->category }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-auto col-form-label is-required">Cantidad:
                                </label>
                                <div class="col-auto">
                                    <input class="form-control" type="number"
                                        id="amount" name="amount" placeholder="{{ $product->metric->value }}"
                                        min="0" />
                                    <div class="form-text">{{ $product->metric->value }} x litro(L) de agua</div>
                                </div>
                            </div>

                            <div>
                                <button type="button" class="btn btn-primary btn-sm"
                                    onclick="setPest()">{{ __('buttons.store') }}</button>
                            </div>
                        </div>
                    </div>


                    <table class="table table-bordered table-striped text-center align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Plagas</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">{{ __('buttons.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="table-body-input-modal">
                        </tbody>
                    </table>
                </div>
                <input type="hidden" id="input" name="input_id" value="">
                <input type="hidden" id="selected-categories" name="selected_categories" value="">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __('buttons.update') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    var pest_categories = @json($pest_categories);
    var metric = @json($product->metric->value);
    var pests = [];

    $('#inputModal').on('submit', function(event) {
        if (pests.length == 0) {
            alert('Por favor, selecciona al menos una Categoría de plaga.');
            event.preventDefault();
        }

        $('#selected-categories').val(JSON.stringify(pests));
    });


    function setInput(element, appmethod_id) {
        const data = JSON.parse(element.getAttribute("data-input"));
        pests = [];
        data.forEach(pest => {
            pests.push({
                'index': pests.length,
                'category_id': pest.id,
                'amount': pest.amount
            });
        });
        $('#application-method').val(appmethod_id);
        createPests();
    }

    function resetForm() {
        $('#inputModal').find(
                'input[type="text"]:not(:disabled), input[type="number"], input[type="email"], input[type="date"], input[type="file"], select, textarea'
            )
            .val('');

        $('#inputModal').find('select').each(function() {
            $(this).prop('selectedIndex', 0); // Establece la primera opción como seleccionada
        });

        pests = [];
    }

    function setPest() {
        var category_id = parseInt($('#pest_category_id').val());
        var amount = parseInt($('#amount').val());
        var found_pest = pests.find(item => item.category_id == category_id);
        if (!found_pest && amount > 0) {
            pests.push({
                'index': pests.length,
                'category_id': category_id,
                'amount': amount
            });
        }

        createPests();
    }

    function createPests() {
        var html = ``;
        pests.forEach(pest => {
            html += `
                <tr>
                    <th scope="row">${pest.index + 1}</th>
                    <td>${pest_categories.find(item => item.id == pest.category_id).category ?? 'S/A'}</td>
                    <td>${pest.amount} ${metric} x lt</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deletePest(${pest.index})"><i class="bi bi-trash-fill"></i></button>    
                    </td>
                </tr>
            `;
        })

        $('#table-body-input-modal').html(html);
    }

    function deletePest(index) {
        pests = pests
            .filter(item => item.index != index)
            .map((item, i) => ({
                ...item,
                index: i
            }));
        createPests();
    }
</script>
