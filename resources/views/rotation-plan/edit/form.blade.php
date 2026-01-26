<form class="form" method="POST" action="{{ route('rotation.update', ['id' => $rotation_plan->id]) }}"
    enctype="multipart/form-data">
    @csrf

    <div class="row mb-2">
        <h5 class="fw-bold pb-1 border-bottom">Revisión</h5>
        <div class="col-12 mb-1">
            <div class="form-check">
                <input class="form-check-input border-secondary" type="checkbox" id="create-review" name="create_review"
                    value="" onchange="$(this).val(this.checked ? 1 : null);" autocomplete="off">
                <label class="form-check-label" for="create-version">
                    ¿Deseas crear una revisión?
                </label>
            </div>
        </div>
        <div class="col-6 mb-3">
            <div class="input-group">
                <select class="form-select" id="review" name="review">
                    @foreach ($reviews as $date => $review)
                        <option value="{{ $review }}"
                            {{ $rotation_plan->no_review == $review ? 'selected' : '' }}>{{ $review }} -
                            ({{ $date }})
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-success" type="button" id="btn-search-review"
                    onclick="searchReview()">{{ __('buttons.search') }}</button>
            </div>
        </div>

    </div>

    <div class="row mb-2">
        <h5 class="fw-bold pb-1 border-bottom">Asignación</h5>
        <div class="col-4 mb-3">
            <label class="form-label is-required">Nombre del cliente</label>
            <input type="text" class="form-control" id="customer"
                value="{{ $rotation_plan->contract->customer->name }}" disabled />
        </div>
        <div class="col-1 mb-3">
            <label class="form-label is-required">Revisión </label>
            <input type="number" class="form-control bg-secondary-subtle" id="no-review" name="no_review"
                value="{{ $rotation_plan->no_review }}" min="1" readonly />
        </div>
        <div class="col-2 mb-3">
            <label class="form-label is-required">Inicia en </label>
            <input type="date" class="form-control" id="startdate" name="start_date"
                value="{{ $rotation_plan->contract->startdate }}" disabled />
        </div>
        <div class="col-2 mb-3">
            <label class="form-label is-required">Termina en </label>
            <input type="date" class="form-control" id="enddate" name="end_date"
                value="{{ $rotation_plan->contract->enddate }}" disabled />
        </div>
    </div>
    <div class="row mb-2">
        <h5 class="fw-bold pb-1 border-bottom">Información general</h5>
        <div class="col-6 mb-3">
            <label class="form-label is-required">Nombre </label>
            <input type="text" class="form-control" id="name" name="name"
                placeholder="Nombre del plan de rotación" value="{{ $rotation_plan->name }}" maxlength="50" required />
        </div>
        <div class="col-2 mb-3">
            <label class="form-label is-required">Código </label>
            <input type="text" class="form-control" id="code" name="code" value="{{ $rotation_plan->code }}"
                required />
        </div>
        <div class="col-2 mb-3">
            <label class="form-label is-required">Fecha de autorización </label>
            <input type="date" class="form-control" id="authorizated-at" name="authorizated_at"
                value="{{ $rotation_plan->authorizated_at }}" required />
        </div>
    </div>

    <div class="row mb-2">
        <h5 class="fw-bold pb-1 border-bottom">Productos</h5>
        <div class="form-text text-danger m-0" id="basic-addon4">
            * Selecciona al menos 1 producto.
        </div>
        <div class="form-text text-danger m-0" id="basic-addon4">
            * En caso de que no aparezca deberas crearlo.
        </div>
        <div class="col-12 p-0 m-0 mb-1">
            <a href="{{ route('product.create') }}" id="form_service_button" class="btn btn-link" target="_blank">
                {{ __('product.title.create') }}
            </a>
        </div>

        <div class="col-12">
            <h6 class="pb-1 mb-1">Buscar producto </h6>
            <div class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" id="search"
                        placeholder="Nombre del producto/materia prima">
                    <button class="btn btn-primary" type="button" onclick="getProducts()"><i
                            class="bi bi-search"></i>
                        {{ __('buttons.search') }}</button>
                </div>
            </div>

            <div class="mb-3">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Producto</th>
                            <th scope="col">Utilización</th>
                            <th scope="col">Ingrediente Activo</th>
                            <th scope="col">Color</th>
                            <th scope="col">Meses</th>
                            <th scope="col">{{ __('buttons.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="selected-products"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <h5 class="fw-bold pb-1 border-bottom">Información adicional</h5>
        <div class="col-12 mb-3">
            <label class="form-label">Aviso importante </label>
            <input type="text" class="form-control" id="important-text" name="important_text" value="{{ $rotation_plan->important_text }}" placeholder="" />
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Notas </label>
            <textarea class="form-control" id="notes" name="notes" rows="5">{{ $rotation_plan->notes }}</textarea>
        </div>
    </div>
    <div class="row mb-2">
        <h5 class="fw-bold pb-1 border-bottom">Cambios</h5>
        <div class="mb-3">
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                data-bs-target="#changeModal"> <i class="bi bi-plus-lg"></i>
                Agregar
                cambio</button>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Revisión</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">{{ __('buttons.actions') }}</th>
                </tr>
            </thead>
            <tbody id="change-table-body">
            </tbody>
        </table>
    </div>

    <input type="hidden" id="url-search-product" value="{{ route('rotation.search.product') }}" />
    <input type="hidden" id="products" name="products" />
    <input type="hidden" id="changes" name="changes" />

    <button type="submit" class="btn btn-primary my-3 me-3" onclick="submitForm()">
        {{ __('buttons.update') }}
    </button>

    <a href="{{ route('rotation.print', ['id' => $rotation_plan->id]) }}" class="btn btn-dark">
        <i class="bi bi-file-pdf-fill"></i> {{ __('buttons.file') }}</a>
    </a>
</form>

<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Productos encontrados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="product-list"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    onclick="setProducts()">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="monthModal" tabindex="-1" aria-labelledby="monthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monthModalLabel">Meses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="months-list"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    onclick="setProducts()" data-bs-dismiss="modal" >{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeModal" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeModalLabel">Meses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label is-required">Descripción del cambio</label>
                    <textarea class="form-control" id="review-change"
                        placeholder="Detalle el motivo del cambio en el plan de rotación de productos, incluyendo los ajustes realizados, los productos involucrados y el impacto esperado en la operación."
                        rows="5"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    onclick="setChange()">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeModal" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeModalLabel">Meses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label is-required">Descripción del cambio</label>
                    <textarea class="form-control" id="review-change"
                        placeholder="Detalle el motivo del cambio en el plan de rotación de productos, incluyendo los ajustes realizados, los productos involucrados y el impacto esperado en la operación."
                        rows="5"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    onclick="setChange()">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </div>
    </div>
</div>


<script>
    const fetched_products = @json($products);

    function searchReview() {
        var formData = new FormData();

        const review = $('#review').val();
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        formData.append('review', review);
        formData.append('contractId', "{{ $rotation_plan->contract->id }}");

        $.ajax({
            url: "{{ route('rotation.search.review') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function(response, status, xhr) {
                if (xhr.status == 200) {
                    window.location.href = response.url;
                }
            },
            error: function(error) {
                console.log("Error:", error);
            },
        });
    }
</script>
