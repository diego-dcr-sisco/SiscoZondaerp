@php
    preg_match('/\(([^)]+)\)/', $product->metric->value ?? 'uds', $matches);
    $shortMetric = $matches[1] ?? ($product->metric->value ?? 'uds');
@endphp

<div class="modal fade" id="inputModal" tabindex="-1" aria-labelledby="inputModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-3">
            <form action="{{ route('product.input', ['id' => $product->id]) }}" method="POST" id="form-product-input">
                @csrf

                <input type="hidden" name="application_method_id" id="application_method_id" value="">

                <div class="modal-header border-bottom bg-light px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="inputModalLabel">⚙️ Configurar Insumos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 text-start">
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-muted mb-2">Producto en configuración</label>
                            <input type="text" class="form-control bg-light text-dark fw-bold border-0"
                                value="{{ $product->name }}" readonly>
                        </div>
                    </div>

                    <div class="card border border-primary border-opacity-25 bg-light bg-opacity-25 mb-4 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold text-primary small">📥 Configurar Método, Plaga y Dosis</span>
                                <label class="form-label small fw-bold mb-1" style="color: red;">
                                    Si no hay un método, agregar desde la barra lateral *
                                </label>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <select
                                        class="form-select form-select-sm border-secondary-subtle fw-bold text-primary"
                                        id="application_method_select">
                                        <option value="" selected disabled>Seleccione un método de aplicación...
                                        </option>
                                        @foreach ($product->applicationMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="pest_category_id"
                                        class="form-label small text-muted fw-bold mb-1">Categoría de Plaga *</label>
                                    <select class="form-select form-select-sm border-secondary-subtle"
                                        id="pest_category_id">
                                        <option value="" selected disabled>Selecciona una categoría...</option>
                                        @foreach ($pest_categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->category }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small text-muted fw-bold mb-1">Marca las Plagas Específicas
                                        *</label>
                                    <div id="pest_specific_container"
                                        class="form-control form-control-sm bg-white overflow-auto p-2 border-secondary-subtle"
                                        style="max-height: 120px; min-height: 38px;">
                                        <span class="text-muted small px-1">Selecciona primero una categoría...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-8">
                                    <label for="amount" class="form-label small text-muted fw-bold mb-1">Cantidad /
                                        Dosis *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="amount" step="any"
                                            class="form-control border-secondary-subtle fw-bold" placeholder="0.0000"
                                            autocomplete="off">
                                        <span
                                            class="input-group-text bg-white text-muted border-secondary-subtle fw-bold">
                                            {{ $shortMetric }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm py-2"
                                        id="btn-save-temporary-pest" disabled>
                                        <i class="bi bi-plus-lg me-1"></i> Guardar en Lista
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border rounded shadow-sm bg-white">
                        <table class="table table-sm table-striped table-hover mb-0" id="table-temporary-pests">
                            <thead class="table-light small border-bottom">
                                <tr>
                                    <th style="width: 45%;" class="ps-3 py-2">Plaga (Categoría)</th>
                                    <th style="width: 25%;" class="py-2">Método de Aplicación</th>
                                    <th style="width: 15%;" class="py-2">Cantidad</th>
                                    <th style="width: 15%;" class="text-center py-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="table-body-input-modal" class="small"></tbody>
                        </table>
                    </div>

                    <input type="hidden" name="selected_categories" id="selected_categories_json">
                </div>

                <div
                    class="modal-footer border-top bg-light px-4 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm"
                            id="btn-submit-modal">Actualizar</button>
                        <button type="button" class="btn btn-danger btn-sm px-4 fw-bold"
                            data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.metric = @json($shortMetric ?? '');
        window.pests = [];

        const amountInput = document.getElementById('amount');
        const pestCategorySelect = document.getElementById('pest_category_id');
        const pestSpecificContainer = document.getElementById('pest_specific_container');
        const methodSelect = document.getElementById('application_method_select');
        const hiddenMethodInput = document.getElementById('application_method_id');
        const tableBody = document.getElementById('table-body-input-modal');
        const selectedCategoriesField = document.getElementById('selected_categories_json');
        const btnAddPest = document.getElementById('btn-save-temporary-pest');
        const mainForm = document.getElementById('form-product-input');

        let editingIndex = null;
        let targetPestIdsToCheck = null;

        function traducirUnidad(rawMetric) {
            if (!rawMetric) return 'uds';
            const mapa = {
                'units': 'uds', 'unit': 'uds', 'uds': 'uds',
                'wt': 'g', 'weight': 'g', 'grams': 'g', 'gramos': 'g', 'g': 'g',
                'vol': 'ml', 'volume': 'ml', 'mililiters': 'ml', 'mililitros': 'ml', 'ml': 'ml',
                'l': 'l', 'kg': 'kg'
            };
            const clave = rawMetric.toString().toLowerCase().trim();
            return mapa[clave] || rawMetric;
        }

        const validateFormInputs = function () {
            if (hiddenMethodInput && hiddenMethodInput.value !== '') {
                methodSelect.value = hiddenMethodInput.value;
                methodSelect.disabled = true;
            }

            const isMethodSelected = methodSelect && methodSelect.value !== '';
            const isCategorySelected = pestCategorySelect && pestCategorySelect.value !== '';
            const checkedBoxes = pestSpecificContainer
                ? pestSpecificContainer.querySelectorAll('.pest-checkbox:checked')
                : [];
            const isPestSelected = checkedBoxes.length > 0;
            const isAmountFilled = amountInput && amountInput.value.trim() !== '' && parseFloat(amountInput.value) > 0;
            const canSave = isMethodSelected && isCategorySelected && isPestSelected && isAmountFilled;

            if (btnAddPest) btnAddPest.disabled = !canSave;
        };

        if (methodSelect) {
            methodSelect.addEventListener('change', function () {
                if (hiddenMethodInput) hiddenMethodInput.value = this.value;
                validateFormInputs();
            });
        }
        if (pestCategorySelect) pestCategorySelect.addEventListener('change', validateFormInputs);
        if (amountInput) amountInput.addEventListener('input', validateFormInputs);

        const createPests = function () {
            if (!tableBody) return;

            if (window.pests.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3 bg-light">No hay plagas agregadas a este insumo</td></tr>`;
                if (selectedCategoriesField) selectedCategoriesField.value = JSON.stringify([]);
                return;
            }

            tableBody.innerHTML = '';
            const currentMethodText = methodSelect && methodSelect.selectedIndex >= 0
                ? methodSelect.options[methodSelect.selectedIndex].text
                : 'Asignado';

            window.pests.forEach(function (pest, index) {
                const row = document.createElement('tr');
                row.className = 'align-middle border-bottom';
                row.innerHTML = `
                    <td class="ps-3 py-2 text-dark"><strong>${pest.category}</strong></td>
                    <td class="text-muted py-2">${currentMethodText}</td>
                    <td class="py-2">
                        <span class="fw-bold text-primary">${parseFloat(pest.amount)}</span>
                        <small class="text-muted ms-1">${traducirUnidad(window.metric)}</small>
                    </td>
                    <td class="text-center py-2">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-warning btn-edit-pest" data-index="${index}">✏️</button>
                            <button type="button" class="btn btn-outline-danger btn-delete-pest" data-index="${index}">🗑️</button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            if (selectedCategoriesField) selectedCategoriesField.value = JSON.stringify(window.pests);
        };

        const resetForm = function () {
            if (pestCategorySelect) pestCategorySelect.value = '';
            if (pestSpecificContainer) {
                pestSpecificContainer.innerHTML = '<span class="text-muted small px-1">Selecciona primero una categoría...</span>';
            }
            if (amountInput) amountInput.value = '';
            editingIndex = null;
            targetPestIdsToCheck = null;
            validateFormInputs();
        };

        if (pestCategorySelect) {
            pestCategorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                if (!pestSpecificContainer) return;

                pestSpecificContainer.innerHTML = '<span class="text-muted small px-1">Cargando plagas...</span>';

                if (!categoryId) {
                    pestSpecificContainer.innerHTML = '<span class="text-muted small px-1">Selecciona primero una categoría...</span>';
                    validateFormInputs();
                    return;
                }

                fetch(`/products/pests-by-category/${categoryId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Error en el servidor');
                        return response.json();
                    })
                    .then(pests => {
                        pestSpecificContainer.innerHTML = '';

                        if (pests.length === 0) {
                            pestSpecificContainer.innerHTML = '<span class="text-muted small px-1">No hay subplagas registradas</span>';
                            validateFormInputs();
                            return;
                        }

                        let currentPestIds = [];
                        if (editingIndex !== null && window.pests[editingIndex]) {
                            currentPestIds = window.pests[editingIndex].pest_ids || [];
                        } else if (targetPestIdsToCheck) {
                            currentPestIds = targetPestIdsToCheck;
                        }

                        pests.forEach(pest => {
                            const isChecked = currentPestIds.map(String).includes(String(pest.id));
                            const div = document.createElement('div');
                            div.className = 'form-check small mb-2 d-flex align-items-center';
                            div.innerHTML = `
                                <input class="form-check-input pest-checkbox border-secondary shadow-sm mt-0 me-2"
                                    type="checkbox" value="${pest.id}" id="chk_pest_${pest.id}"
                                    data-name="${pest.name}" data-category-id="${pest.pest_category_id}"
                                    ${isChecked ? 'checked' : ''}
                                    style="cursor:pointer;border-width:1.5px;min-width:16px;min-height:16px;">
                                <label class="form-check-label" for="chk_pest_${pest.id}" style="cursor:pointer;user-select:none;">
                                    ${pest.name}
                                </label>
                            `;
                            pestSpecificContainer.appendChild(div);
                        });

                        pestSpecificContainer.querySelectorAll('.pest-checkbox').forEach(cb => {
                            cb.addEventListener('change', validateFormInputs);
                        });

                        targetPestIdsToCheck = null;
                        validateFormInputs();
                    })
                    .catch(error => {
                        console.error('Error cargando plagas:', error);
                        pestSpecificContainer.innerHTML = '<span class="text-danger small px-1">Error al cargar datos</span>';
                        validateFormInputs();
                    });
            });
        }

        window.savePest = function () {
            if (!pestSpecificContainer || !amountInput || !pestCategorySelect) return;

            const checkedBoxes = pestSpecificContainer.querySelectorAll('.pest-checkbox:checked');
            let amountValue = amountInput.value.trim().replace(',', '.');

            if (checkedBoxes.length === 0 || !amountValue || isNaN(parseFloat(amountValue))) return;

            const parsedAmount = parseFloat(amountValue);
            const categoryId = pestCategorySelect.value;
            const categoryText = pestCategorySelect.options[pestCategorySelect.selectedIndex].text;
            const selectedPestIds = [];
            const selectedPestNames = [];

            checkedBoxes.forEach(cb => {
                selectedPestIds.push(parseInt(cb.value, 10));
                selectedPestNames.push(cb.dataset.name);
            });

            const pestData = {
                id: parseInt(categoryId, 10),
                category: `${categoryText} (${selectedPestNames.join(', ')})`,
                amount: parsedAmount,
                pest_ids: selectedPestIds,
            };

            if (editingIndex !== null && window.pests[editingIndex]) {
                window.pests[editingIndex] = pestData;
                editingIndex = null;
            } else {
                window.pests.push(pestData);
            }

            createPests();
            resetForm();
        };

        window.populateInputModal = function (methodId, methodName, pestsList) {
            if (methodSelect && hiddenMethodInput) {
                const exactId = String(methodId);
                hiddenMethodInput.value = exactId;
                methodSelect.value = exactId;
                methodSelect.disabled = true;
            }

            window.pests = pestsList.map(p => ({
                id: Number(p.id) || 0,
                category: p.category,
                amount: parseFloat(p.amount) || 0,
                pest_ids: Array.isArray(p.pest_ids) ? p.pest_ids.map(Number).filter(Boolean) : [],
            }));

            createPests();
            resetForm();
        };

        window.clearInputModal = function () {
            if (methodSelect && hiddenMethodInput) {
                hiddenMethodInput.value = '';
                methodSelect.selectedIndex = 0;
                methodSelect.disabled = false;
            }
            window.pests = [];
            createPests();
            resetForm();
        };

        if (btnAddPest) {
            btnAddPest.addEventListener('click', function (e) {
                e.preventDefault();
                window.savePest();
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
                        targetPestIdsToCheck = pest.pest_ids || [];
                        if (pestCategorySelect) {
                            pestCategorySelect.value = pest.id;
                            pestCategorySelect.dispatchEvent(new Event('change'));
                        }
                        if (amountInput) amountInput.value = pest.amount;
                    }
                    return;
                }

                if (deleteButton) {
                    event.preventDefault();
                    const index = parseInt(deleteButton.getAttribute('data-index'), 10);
                    window.pests.splice(index, 1);
                    createPests();
                    resetForm();
                }
            });
        }

        if (mainForm) {
            mainForm.addEventListener('submit', function (e) {
                const checkedMethod = hiddenMethodInput ? hiddenMethodInput.value : '';
                if (!checkedMethod || checkedMethod === '') {
                    e.preventDefault();
                    alert('Por favor, seleccione un método de aplicación válido.');
                }
            });
        }

        createPests();
        validateFormInputs();
    });
</script>