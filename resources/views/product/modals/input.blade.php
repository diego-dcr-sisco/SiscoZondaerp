@php
    // 1. Jalamos el método asignado directamente del producto y su ID correspondiente
    $productMethod = $product->applicationMethod->name ?? ($product->applicationMethods->first()->name ?? 'No asignado');
    $productMethodId = $product->applicationMethod->id ?? ($product->applicationMethods->first()->id ?? '');

    // 2. Limpiamos la unidad de medida para extraer solo lo que está entre paréntesis
    $fullMetric = $product->metric->value ?? 'uds';
    preg_match('/\(([^)]+)\)/', $fullMetric, $matches);
    $shortMetric = $matches[1] ?? $fullMetric;
@endphp

<div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="inputModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('product.input', ['id' => $product->id]) }}" method="POST" id="form-product-input">
                @csrf

                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label for="product_name_display"
                            class="form-label fw-bold small text-muted mb-1">Producto</label>
                        <input type="text" class="form-control bg-light text-dark fw-bold" id="product_name_display"
                            value="{{ $product->name }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Método de aplicación</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $productMethod }}"
                            readonly>

                        <input type="hidden" name="application_method_id" id="application_method_id"
                            value="{{ $productMethodId }}">
                    </div>

                    <div class="card border mb-3 shadow-sm">
                        <div class="card-body bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small">Plagas <span class="text-danger">*</span></span>
                                <button type="button" class="btn btn-success btn-sm px-3" id="btn-add-pest-row"
                                    style="display:none;">Agregar</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <label for="pest_category_id" class="form-label small text-muted mb-1">Plaga
                                        (Categoría): <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="pest_category_id">
                                        <option value="" selected disabled>Seleccione una plaga</option>
                                        @foreach ($pest_categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-5">
                                    <label for="amount" class="form-label small text-muted mb-1">Cantidad *</label>
                                    <input type="number" id="amount" name="amount" step="any"
                                        class="form-control form-control-sm" placeholder="0.00" autocomplete="off">

                                    <div id="unit-helper-text" class="form-text text-muted small mt-1"
                                        style="font-size: 0.75rem;">
                                        Unidad de medida:
                                        <strong>{{ $product->metric->value ?? 'Unidades (uds)' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-primary btn-sm px-4"
                                    id="btn-save-temporary-pest">Guardar</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-sm table-striped table-hover mb-0" id="table-temporary-pests">
                            <thead class="table-light small">
                                <tr>
                                    <th style="width: 8%;" class="ps-2"># Plagas</th>
                                    <th>Método de Aplicación</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody id="table-body-input-modal" class="small"></tbody>
                        </table>
                    </div>

                    <input type="hidden" name="selected_categories" id="selected_categories_json">
                </div>

                <div class="modal-footer border-top bg-light g-2">
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btn-submit-modal">Actualizar</button>
                    <button type="button" class="btn btn-danger btn-sm px-4" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.pest_categories = @json($pest_categories);
        window.metric = @json($shortMetric);
        window.productApplicationMethod = @json($productMethod);

        const amountInput = document.getElementById('amount');
        const unitHelperText = document.getElementById('unit-helper-text');
        const pestSelect = document.getElementById('pest_category_id');
        const methodSelect = document.getElementById('application_method_id'); // Ahora apunta de forma segura al input hidden
        const tableBody = document.getElementById('table-body-input-modal');
        const selectedCategoriesField = document.getElementById('selected_categories_json');
        const btnAddPest = document.getElementById('btn-save-temporary-pest');
        const formProductInput = document.getElementById('form-product-input');
        let editingIndex = null;

        const updateQuantityStep = function () {
            if (!amountInput) return;
            amountInput.setAttribute('step', 'any');
            amountInput.placeholder = '0.00';
            if (unitHelperText) {
                unitHelperText.innerHTML = `Unidad de medida: <strong>${window.metric}</strong>`;
            }
        };

        const setInput = function (categoryId, categoryLabel, amountValue) {
            if (pestSelect) pestSelect.value = categoryId || '';
            if (amountInput) amountInput.value = amountValue || '';
        };

        const resetForm = function () {
            if (pestSelect) pestSelect.value = '';
            if (amountInput) amountInput.value = '';
            editingIndex = null;
            updateQuantityStep();
        };

        const createPests = function () {
            if (!tableBody || !selectedCategoriesField) return;
            tableBody.innerHTML = '';

            window.pests.forEach(function (pest, index) {
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td class="ps-2">
                        <span class="text-muted small">#${index + 1}</span>
                        <strong class="ms-2">${pest.category}</strong>
                    </td>
                    <td>${window.productApplicationMethod}</td>
                    <td>
                        <span class="fw-bold">${pest.amount}</span>
                        <span class="badge bg-light text-dark border ms-1">${window.metric}</span>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            selectedCategoriesField.value = JSON.stringify(window.pests);
        };

        const deletePest = function (index) {
            if (index < 0 || index >= window.pests.length) return;
            window.pests.splice(index, 1);
            createPests();
        };

        const savePest = function () {
            if (!pestSelect || !amountInput) return;

            const categoryId = pestSelect.value;
            const categoryLabel = pestSelect.options[pestSelect.selectedIndex] ? pestSelect.options[pestSelect.selectedIndex].textContent : '';
            let amountValue = amountInput.value.trim().replace(',', '.');

            if (!categoryId || !amountValue || isNaN(parseFloat(amountValue))) return;

            const pestData = {
                id: categoryId,
                category: categoryLabel,
                amount: parseFloat(amountValue)
            };

            if (editingIndex !== null && window.pests[editingIndex]) {
                window.pests[editingIndex] = pestData;
            } else {
                window.pests.push(pestData);
            }

            createPests();
            resetForm();
        };

        // Funciones puente globales actualizadas para trabajar con el input hidden sin provocar errores de nulos
        window.clearInputModal = function () {
            if (methodSelect) {
                methodSelect.value = @json($productMethodId); // Restablece al ID por defecto del producto
            }
            window.pests = [];
            resetForm();
            createPests();
        };

        window.populateInputModal = function (methodId, pestsArray) {
            if (methodSelect) {
                methodSelect.value = methodId; // Asigna el ID del método guardado previamente
            }
            window.pests = Array.isArray(pestsArray) ? JSON.parse(JSON.stringify(pestsArray)) : [];
            resetForm();
            createPests();
        };

        if (btnAddPest) {
            btnAddPest.addEventListener('click', function (event) {
                event.preventDefault();
                savePest();
            });
        }

        if (tableBody) {
            tableBody.addEventListener('click', function (event) {
                const editButton = event.target.closest('.btn-edit-pest');
                const deleteButton = event.target.closest('.btn-delete-pest');

                if (editButton) {
                    event.preventDefault();
                    const index = parseInt(editButton.getAttribute('data-index'), 10);
                    const pest = window.pests[index];
                    if (pest) {
                        editingIndex = index;
                        setInput(pest.id, pest.category, pest.amount);
                    }
                    return;
                }

                if (deleteButton) {
                    event.preventDefault();
                    const index = parseInt(deleteButton.getAttribute('data-index'), 10);
                    deletePest(index);
                }
            });
        }

        resetForm();
        createPests();
    });
</script>