<div class="modal fade" id="autoreviewModal" tabindex="-1" role="dialog" aria-labelledby="autoreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h1 class="modal-title fs-5" id="autoreviewModalLabel">Configurar autorevisión para dispositivos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="autoreviewForm" action="{{ route('report.autoreview', ['orderId' => $order->id]) }}"
                    method="POST">
                    @foreach ($autoreview_data as $autoreview)
                        <div class="card mb-3">
                            <div class="card-header fw-bold">
                                Dispositivo: <span class="text-primary">
                                    {{ $autoreview['control_point_name'] }}</span>
                            </div>
                            <div class="card-body p-3">

                                <!-- Preguntas -->
                                <div class="border rounded p-2 bg-light mb-3">
                                    <div class="mb-1">
                                        <span class="fw-bold fs-5">Preguntas</span>
                                    </div>
                                    @foreach ($autoreview['questions'] as $question)
                                        <div class="mb-3">
                                            <label for="question-{{ $question['id'] }}" class="form-label is-required">
                                                {{ $question['question'] }}
                                            </label>
                                            <select class="form-select form-select-sm question-select"
                                                id="question-{{ $autoreview['control_point_id'] }}-{{ $question['id'] }}"
                                                data-question-id="{{ $question['id'] }}"
                                                onchange="updateQuestionAnswer({{ $autoreview['control_point_id'] }}, {{ $question['id'] }}, this.value)">
                                                <option value="">Sin respuesta</option>
                                                @if (isset($question['answers']))
                                                    @foreach ($question['answers'] as $answer)
                                                        <option value="{{ $answer }}">{{ $answer }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Productos -->
                                <div class="border rounded p-2 bg-light mb-3">
                                    <div class="mb-1 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-5">Productos</span>
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="addNewProductField({{ $autoreview['control_point_id'] }})">
                                            <i class="bi bi-plus-square-fill"></i> Añadir producto
                                        </button>
                                    </div>

                                    <div id="products-container-{{ $autoreview['control_point_id'] }}">
                                        <!-- Producto inicial -->
                                        <div class="product-row mb-3 border-bottom pb-3"
                                            id="product-row-{{ $autoreview['control_point_id'] }}-0">
                                            <div class="row">
                                                <div class="col-lg-6 col-12 mb-2">
                                                    <label class="form-label">Producto aplicado</label>
                                                    <select class="form-select form-select-sm product-select"
                                                        onchange="handleAutoreviewProduct({{ $autoreview['control_point_id'] }}, this.value, 0)">
                                                        <option value="" selected>Selecciona un producto</option>
                                                        @foreach ($autoreview['products'] as $product)
                                                            <option value="{{ $product['id'] }}">
                                                                {{ $product['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-2">
                                                    <label class="form-label">Cantidad</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number"
                                                            class="form-control form-control-sm product-amount"
                                                            value="1" min="0"
                                                            oninput="updateAutoreviewProduct({{ $autoreview['control_point_id'] }}, 0)"
                                                            id="product-amount-{{ $autoreview['control_point_id'] }}-0"
                                                            disabled />
                                                        <span class="input-group-text product-metric"
                                                            id="product-metric-{{ $autoreview['control_point_id'] }}-0">Unidades</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-12 mb-2">
                                                    <label class="form-label">Método aplicación</label>
                                                    <select class="form-select form-select-sm product-method"
                                                        id="product-method-{{ $autoreview['control_point_id'] }}-0"
                                                        onchange="updateAutoreviewProduct({{ $autoreview['control_point_id'] }}, 0)"
                                                        disabled>
                                                        <option value="" selected>Selecciona método</option>
                                                        @foreach ($application_methods as $method)
                                                            <option value="{{ $method['id'] }}">{{ $method['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-6 col-12 mb-2">
                                                    <label class="form-label">Lote</label>
                                                    <select class="form-select form-select-sm product-lot"
                                                        id="product-lot-{{ $autoreview['control_point_id'] }}-0"
                                                        onchange="updateAutoreviewProduct({{ $autoreview['control_point_id'] }}, 0)"
                                                        disabled>
                                                        <option value="" selected>Selecciona un lote</option>
                                                    </select>
                                                </div>
                                                <div class="col-4 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="removeProductField({{ $autoreview['control_point_id'] }}, 0)"
                                                        disabled>
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Plagas -->
                                <div class="mb-3 border rounded p-2 bg-light">
                                    <div class="mb-1 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-5">Plagas e incidencias</span>
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="addNewPestField({{ $autoreview['control_point_id'] }})">
                                            <i class="bi bi-plus-square-fill"></i> Añadir plaga
                                        </button>
                                    </div>

                                    <div id="pests-container-{{ $autoreview['control_point_id'] }}">
                                        <!-- Plaga inicial -->
                                        <div class="pest-row mb-3 border-bottom pb-3"
                                            id="pest-row-{{ $autoreview['control_point_id'] }}-0">
                                            <div class="row">
                                                <div class="col-lg-8 col-12">
                                                    <label class="form-label">Plaga/Incidencias</label>
                                                    <select class="form-select form-select-sm pest-select"
                                                        onchange="updateAutoreviewPest({{ $autoreview['control_point_id'] }}, 0)">
                                                        <option value="" selected>Selecciona una plaga</option>
                                                        @if (isset($autoreview['pests']))
                                                            @foreach ($autoreview['pests'] as $pest)
                                                                <option value="{{ $pest['id'] }}">
                                                                    {{ $pest['name'] }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 col-12">
                                                    <label class="form-label">Cantidad</label>
                                                    <input class="form-control form-control-sm pest-count"
                                                        placeholder="0"
                                                        oninput="updateAutoreviewPest({{ $autoreview['control_point_id'] }}, 0)" />
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="removePestField({{ $autoreview['control_point_id'] }}, 0)"
                                                        disabled>
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 border rounded p-2 bg-light">
                                    <div class="mb-1">
                                        <span class="fw-bold fs-5">Observaciones</span>
                                    </div>
                                    <textarea class="form-control" rows="3" id="observations-{{ $autoreview['control_point_id'] }}"
                                        placeholder="Escribe aquí cualquier observación adicional sobre este punto de control..."
                                        oninput="updateObservations({{ $autoreview['control_point_id'] }}, this.value)"></textarea>
                                </div>

                                <!-- Opciones de limpieza -->
                                <div class="border p-2 rounded">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            id="clearQuestions-{{ $autoreview['control_point_id'] }}">
                                        <label class="form-check-label"
                                            for="clearQuestions-{{ $autoreview['control_point_id'] }}">Limpiar
                                            preguntas</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            id="clearProducts-{{ $autoreview['control_point_id'] }}">
                                        <label class="form-check-label"
                                            for="clearProducts-{{ $autoreview['control_point_id'] }}">Limpiar
                                            productos</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            id="clearPests-{{ $autoreview['control_point_id'] }}">
                                        <label class="form-check-label"
                                            for="clearPests-{{ $autoreview['control_point_id'] }}">Limpiar
                                            plagas</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            id="clearObservs-{{ $autoreview['control_point_id'] }}">
                                        <label class="form-check-label"
                                            for="clearObservs-{{ $autoreview['control_point_id'] }}">Limpiar
                                            observaciones</label>
                                    </div>
                                    {{--<div class="row mt-2">
                                        <div class="col-lg-12 col-12">
                                            <label class="form-label">Observaciones del dispositivo</label>
                                            <textarea class="form-control form-control-sm" rows="3"
                                                oninput="setAutoreviewObservations({{ $autoreview['control_point_id'] }}, 0, this.value)"
                                                placeholder="Escriba las observaciones"></textarea>
                                        </div>
                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <input type="hidden" id="autoreview-data" name="autoreview_data" />
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="submitAutoreview()">
                    Guardar Autorevisión
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales para manejar los datos
    var autoreview_data = @json($autoreview_data ?? []);
    var productsData = {}; // Almacenará los productos por control_point_id
    var observationsData = {}; // Almacenará las observaciones por control_point_id
    var appMethods = @json($application_methods);

    // Inicializar productsData
    autoreview_data.forEach(function(controlPoint) {
        productsData[controlPoint.control_point_id] = controlPoint.products || [];
        observationsData[controlPoint.control_point_id] = ''; // Inicializar observaciones vacías
    });

    // Función para añadir un nuevo campo de producto
    function addNewProductField(controlPointId) {
        const container = document.getElementById(`products-container-${controlPointId}`);
        const newIndex = container.querySelectorAll('.product-row').length;

        const newProductRow = document.createElement('div');
        newProductRow.className = 'product-row mb-3 border-bottom pb-3';
        newProductRow.id = `product-row-${controlPointId}-${newIndex}`;

        newProductRow.innerHTML = `
            <div class="row">
                <div class="col-lg-6 col-12 mb-2">
                    <label class="form-label">Producto aplicado</label>
                    <select class="form-select form-select-sm product-select"
                        onchange="handleAutoreviewProduct(${controlPointId}, this.value, ${newIndex})">
                        <option value="" selected>Selecciona un producto</option>
                        ${generateProductOptions(controlPointId)}
                    </select>
                </div>
                <div class="col-lg-6 col-12 mb-2">
                    <label class="form-label">Cantidad</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm product-amount"
                            value="1" min="0"
                            oninput="updateAutoreviewProduct(${controlPointId}, ${newIndex})"
                            id="product-amount-${controlPointId}-${newIndex}" disabled />
                        <span class="input-group-text product-metric"
                            id="product-metric-${controlPointId}-${newIndex}">Unidades</span>
                    </div>
                </div>
                <div class="col-lg-6 col-12 mb-2">
                    <label class="form-label">Método aplicación</label>
                    <select class="form-select form-select-sm product-method"
                        id="product-method-${controlPointId}-${newIndex}"
                        onchange="updateAutoreviewProduct(${controlPointId}, ${newIndex})" disabled>
                        <option value="" selected>Selecciona método</option>
                        ${generateMethodOptions()}
                    </select>
                </div>

                <div class="col-lg-6 col-12 mb-2">
                    <label class="form-label">Lote</label>
                    <select class="form-select form-select-sm product-lot"
                        id="product-lot-${controlPointId}-${newIndex}"
                        onchange="updateAutoreviewProduct(${controlPointId}, ${newIndex})" disabled>
                        <option value="" selected>Selecciona un lote</option>
                    </select>
                </div>
                <div class="col-4 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" 
                        onclick="removeProductField(${controlPointId}, ${newIndex})">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(newProductRow);

        // Habilitar botón de eliminar del primer producto si hay más de uno
        if (newIndex > 0) {
            const firstDeleteBtn = document.querySelector(`#product-row-${controlPointId}-0 .btn-danger`);
            if (firstDeleteBtn) firstDeleteBtn.disabled = false;
        }
    }

    // Generar opciones de métodos de aplicación
    function generateMethodOptions() {
        let options = '';
        if (appMethods && Array.isArray(appMethods)) {
            appMethods.forEach(method => {
                options += `<option value="${method.id}">${method.name}</option>`;
            });
        }
        return options;
    }

    // Generar opciones de productos dinámicamente para un controlPointId específico
    function generateProductOptions(controlPointId) {
        let options = '';
        if (productsData[controlPointId]) {
            productsData[controlPointId].forEach(product => {
                options += `<option value="${product.id}">${product.name}</option>`;
            });
        }
        return options;
    }

    // Manejar selección de producto
    function handleAutoreviewProduct(controlPointId, productId, rowIndex) {
        const row = document.getElementById(`product-row-${controlPointId}-${rowIndex}`);
        if (!row) return;

        const amountInput = row.querySelector('.product-amount');
        const methodSelect = row.querySelector('.product-method');
        const lotSelect = row.querySelector('.product-lot');

        if (productId) {
            amountInput.disabled = false;
            methodSelect.disabled = false;

            // Buscar el producto seleccionado
            const selectedProduct = productsData[controlPointId].find(p => p.id == productId);

            // Actualizar lotes disponibles
            if (selectedProduct && selectedProduct.lots && selectedProduct.lots.length > 0) {
                let lotOptions = '<option value="" selected>Selecciona un lote</option>';
                selectedProduct.lots.forEach(lot => {
                    lotOptions += `<option value="${lot.id}">${lot.registration_number}</option>`;
                });
                lotSelect.innerHTML = lotOptions;
                lotSelect.disabled = false;
            } else {
                lotSelect.innerHTML = '<option value="" selected>No hay lotes disponibles</option>';
                lotSelect.disabled = true;
            }
        } else {
            amountInput.disabled = true;
            methodSelect.disabled = true;
            lotSelect.innerHTML = '<option value="" selected>Selecciona un lote</option>';
            lotSelect.disabled = true;
        }

        updateAutoreviewProduct(controlPointId, rowIndex);
    }

    // Actualizar información de producto
    function updateAutoreviewProduct(controlPointId, rowIndex) {
        const row = document.getElementById(`product-row-${controlPointId}-${rowIndex}`);
        if (!row) return;

        const productId = row.querySelector('.product-select').value;
        const amount = row.querySelector('.product-amount').value;
        const methodId = row.querySelector('.product-method').value;
        const lotId = row.querySelector('.product-lot').value;

        console.log(
            `Producto actualizado - Punto: ${controlPointId}, Fila: ${rowIndex}, Producto: ${productId}, Cantidad: ${amount}, Método: ${methodId}, Lote: ${lotId}`
        );
    }

    // Función para eliminar un campo de producto
    function removeProductField(controlPointId, index) {
        const row = document.getElementById(`product-row-${controlPointId}-${index}`);
        if (row) row.remove();

        // Si solo queda un producto, deshabilitar su botón de eliminar
        const productRows = document.querySelectorAll(`#products-container-${controlPointId} .product-row`);
        if (productRows.length === 1) {
            const deleteBtn = productRows[0].querySelector('.btn-danger');
            if (deleteBtn) deleteBtn.disabled = true;
        }
    }

    function updateObservations(controlPointId, value) {
        observationsData[controlPointId] = value;
        console.log(`Observaciones actualizadas - Punto: ${controlPointId}, Valor: ${value}`);
    }

    // Función para añadir un nuevo campo de plaga
    function addNewPestField(controlPointId) {
        const container = document.getElementById(`pests-container-${controlPointId}`);
        const newIndex = container.querySelectorAll('.pest-row').length;

        const newPestRow = document.createElement('div');
        newPestRow.className = 'pest-row mb-3 border-bottom pb-3';
        newPestRow.id = `pest-row-${controlPointId}-${newIndex}`;

        // Obtener plagas para este controlPointId
        const controlPointData = autoreview_data.find(cp => cp.control_point_id == controlPointId);
        const pestsOptions = controlPointData && controlPointData.pests ?
            controlPointData.pests.map(pest => `<option value="${pest.id}">${pest.name}</option>`).join('') :
            '';

        newPestRow.innerHTML = `
            <div class="row">
                <div class="col-lg-8 col-12">
                    <label class="form-label">Plaga/Incidencias</label>
                    <select class="form-select form-select-sm pest-select"
                        onchange="updateAutoreviewPest(${controlPointId}, ${newIndex})">
                        <option value="" selected>Selecciona una plaga</option>
                        ${pestsOptions}
                    </select>
                </div>
                <div class="col-lg-4 col-12">
                    <label class="form-label">Cantidad</label>
                    <input class="form-control form-control-sm pest-count" placeholder="0"
                        oninput="updateAutoreviewPest(${controlPointId}, ${newIndex})"
                    />
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" 
                        onclick="removePestField(${controlPointId}, ${newIndex})">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(newPestRow);

        // Habilitar botón de eliminar de la primera plaga si hay más de una
        if (newIndex > 0) {
            const firstDeleteBtn = document.querySelector(`#pest-row-${controlPointId}-0 .btn-danger`);
            if (firstDeleteBtn) firstDeleteBtn.disabled = false;
        }
    }

    // Función para eliminar un campo de plaga
    function removePestField(controlPointId, index) {
        const row = document.getElementById(`pest-row-${controlPointId}-${index}`);
        if (row) row.remove();

        // Si solo queda una plaga, deshabilitar su botón de eliminar
        const pestRows = document.querySelectorAll(`#pests-container-${controlPointId} .pest-row`);
        if (pestRows.length === 1) {
            const deleteBtn = pestRows[0].querySelector('.btn-danger');
            if (deleteBtn) deleteBtn.disabled = true;
        }
    }

    // Función para limpiar secciones según los checkboxes
    function clearSections(controlPointId) {
        const clearQuestions = document.getElementById(`clearQuestions-${controlPointId}`).checked;
        const clearProducts = document.getElementById(`clearProducts-${controlPointId}`).checked;
        const clearPests = document.getElementById(`clearPests-${controlPointId}`).checked;

        if (clearQuestions) {
            resetQuestionsToDefault(controlPointId);
        }

        if (clearProducts) {
            // Limpiar productos (dejar solo el primero)
            const productsContainer = document.getElementById(`products-container-${controlPointId}`);
            while (productsContainer.children.length > 1) {
                productsContainer.removeChild(productsContainer.lastChild);
            }

            // Resetear el primer producto
            const firstProduct = productsContainer.firstElementChild;
            if (firstProduct) {
                firstProduct.querySelector('.product-select').value = '';
                firstProduct.querySelector('.product-amount').value = '1';
                firstProduct.querySelector('.product-method').value = '';
                firstProduct.querySelector('.product-lot').innerHTML =
                    '<option value="" selected>Selecciona un lote</option>';
                firstProduct.querySelector('.btn-danger').disabled = true;
            }
        }

        if (clearPests) {
            // Limpiar plagas (dejar solo la primera)
            const pestsContainer = document.getElementById(`pests-container-${controlPointId}`);
            while (pestsContainer.children.length > 1) {
                pestsContainer.removeChild(pestsContainer.lastChild);
            }

            // Resetear la primera plaga
            const firstPest = pestsContainer.firstElementChild;
            if (firstPest) {
                firstPest.querySelector('.pest-select').value = '';
                firstPest.querySelector('.pest-count').value = '';
                firstPest.querySelector('.btn-danger').disabled = true;
            }
        }
    }

    // Configurar eventos de los checkboxes de limpieza para todos los puntos de control
    function setupCleanupCheckboxes() {
        autoreview_data.forEach(controlPoint => {
            const controlPointId = controlPoint.control_point_id;

            document.getElementById(`clearQuestions-${controlPointId}`).addEventListener('change', function() {
                clearSections(controlPointId);
            });

            document.getElementById(`clearProducts-${controlPointId}`).addEventListener('change', function() {
                clearSections(controlPointId);
            });

            document.getElementById(`clearPests-${controlPointId}`).addEventListener('change', function() {
                clearSections(controlPointId);
            });
        });
    }

    // Función para guardar respuestas de preguntas
    function updateQuestionAnswer(controlPointId, questionId, answer) {
        if (!window.autoreviewAnswers) window.autoreviewAnswers = {};
        if (!window.autoreviewAnswers[controlPointId]) window.autoreviewAnswers[controlPointId] = {};

        window.autoreviewAnswers[controlPointId][questionId] = answer;

        console.log(`Respuesta actualizada - Punto: ${controlPointId}, Pregunta: ${questionId}, Valor: ${answer}`);
    }

    // Función para actualizar información de plaga
    function updateAutoreviewPest(controlPointId, rowIndex) {
        const row = document.getElementById(`pest-row-${controlPointId}-${rowIndex}`);
        if (!row) return;

        const pestId = row.querySelector('.pest-select').value;
        const pestCount = row.querySelector('.pest-count').value;

        console.log(
            `Plaga actualizada - Punto: ${controlPointId}, Fila: ${rowIndex}, Plaga: ${pestId}, Cantidad: ${pestCount}`
        );
    }

    // Función para enviar todas las respuestas
    function submitAutoreview() {
        const data = {
            control_points: []
        };

        // Recopilar datos de todos los puntos de control
        autoreview_data.forEach(controlPoint => {
            const controlPointId = controlPoint.control_point_id;

            const answers = window.autoreviewAnswers ? window.autoreviewAnswers[controlPointId] : null;
            const products = getProductsData(controlPointId);
            const pests = getPestsData(controlPointId);
            const observations = observationsData[controlPointId] || '';

            const autoreview = autoreview_data.find(auto => auto.control_point_id == controlPointId);

            data.control_points.push({
                control_point_id: controlPointId,
                answers: answers,
                products: products,
                pests: pests,
                observations: observations,
                devices: autoreview.devices ?? [],
                clear: {
                    questions: $(`#clearQuestions-${controlPointId}`).is(':checked'),
                    products: $(`#clearProducts-${controlPointId}`).is(':checked'),
                    pests: $(`#clearPests-${controlPointId}`).is(':checked'),
                    observations: $(`#clearObservs-${controlPointId}`).is(':checked'),
                }
            });
        });

        console.log("Datos a enviar:", JSON.stringify(data, null, 2));
        return;

        const new_formdata = new FormData();
        new_formdata.append('autoreview_data', JSON.stringify(data));

        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        alert("✅ Autorevisión enviada correctamente\n\n" +
            `• Puntos de control: ${data.control_points.length}\n` +
            `• Hora: ${new Date().toLocaleTimeString()}`);

        console.log("Datos enviados", JSON.stringify(data, null, 2));
        showSpinner();

        $.ajax({
            url: "{{ route('report.autoreview', ['orderId' => $order['id']]) }}",
            type: 'POST',
            data: new_formdata,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                console.error('Error al enviar la autorevisión:', xhr);
            },
            complete: function() {
                hideSpinner();
            }
        });
    }

    // Funciones auxiliares para obtener datos de productos y plagas
    function getProductsData(controlPointId) {
        const products = [];
        const productRows = document.querySelectorAll(`#products-container-${controlPointId} .product-row`);

        productRows.forEach(row => {
            const productId = row.querySelector('.product-select').value;
            const amount = row.querySelector('.product-amount').value;
            const methodId = row.querySelector('.product-method').value;
            const lotId = row.querySelector('.product-lot').value;

            if (productId) {
                products.push({
                    product_id: productId,
                    amount: amount,
                    application_method_id: methodId,
                    lot_id: lotId
                });
            }
        });

        return products;
    }

    function getPestsData(controlPointId) {
        const pests = [];
        const pestRows = document.querySelectorAll(`#pests-container-${controlPointId} .pest-row`);

        pestRows.forEach(row => {
            const pestId = row.querySelector('.pest-select').value;
            const count = row.querySelector('.pest-count').value;

            if (pestId) {
                pests.push({
                    pest_id: pestId,
                    count: count,
                });
            }
        });

        return pests;
    }

    // Función para inicializar respuestas con valores por defecto para todos los puntos de control
    function initializeDefaultAnswers() {
        try {
            // Verificar que los datos estén disponibles
            if (!autoreview_data || !Array.isArray(autoreview_data)) {
                console.error("Datos de autorevisión no disponibles o formato incorrecto");
                return;
            }

            // Inicializar objeto global de respuestas
            window.autoreviewAnswers = {};

            // Procesar cada punto de control
            autoreview_data.forEach(controlPoint => {
                const controlPointId = controlPoint.control_point_id;

                // Inicializar objeto para este punto de control
                window.autoreviewAnswers[controlPointId] = {};

                // Procesar cada pregunta en este punto de control
                if (Array.isArray(controlPoint.questions)) {
                    controlPoint.questions.forEach(question => {
                        if (!question.id) {
                            console.warn("Pregunta sin ID en punto de control", controlPointId,
                                question);
                            return;
                        }

                        // Determinar valor por defecto con prioridades:
                        // 1. answer_default explícito (si no es null/undefined)
                        // 2. Primer elemento de answers (para selects)
                        // 3. Cadena vacía como último recurso
                        let defaultValue = '';

                        if (question.answer_default !== null && question.answer_default !== undefined) {
                            defaultValue = question.answer_default;
                        } else if (question.answers?.length > 0) {
                            defaultValue = question.answers[0];
                        }

                        // Almacenar respuesta
                        window.autoreviewAnswers[controlPointId][question.id] = defaultValue;

                        // Actualizar elemento en el DOM (si existe)
                        const elementId = `question-${controlPointId}-${question.id}`;
                        const formElement = document.getElementById(elementId);

                        if (formElement) {
                            if (formElement.tagName === 'SELECT') {
                                formElement.value = defaultValue;
                            } else if (formElement.tagName === 'INPUT') {
                                formElement.value = defaultValue;
                            }
                        }
                    });
                }
            });

            console.log("Respuestas inicializadas para todos los puntos de control:", window.autoreviewAnswers);

        } catch (error) {
            console.error("Error crítico al inicializar respuestas:", error);
        }
    }

    // Función para resetear preguntas a valores por defecto
    function resetQuestionsToDefault(controlPointId) {
        if (!autoreview_data || !window.autoreviewAnswers[controlPointId]) return;

        const controlPoint = autoreview_data.find(cp => cp.control_point_id == controlPointId);
        if (!controlPoint) return;

        controlPoint.questions.forEach(question => {
            const elementId = `question-${controlPointId}-${question.id}`;
            const formElement = document.getElementById(elementId);
            const defaultValue = window.autoreviewAnswers[controlPointId][question.id];

            if (formElement) {
                formElement.value = '';
                // Disparar evento de cambio si es necesario
                const event = new Event('change');
                formElement.dispatchEvent(event);
            }
        });

        //showToast('Las preguntas se han restablecido a sus valores por defecto');
    }

    // Mostrar notificación toast
    function showToast(message) {
        console.log("Feedback:", message);
        alert(message);
    }

    // Llamar cuando el modal se muestra
    document.addEventListener('DOMContentLoaded', function() {
        initializeDefaultAnswers();
        setupCleanupCheckboxes();
    });

    // También inicializar cuando se muestra el modal (por si se carga dinámicamente)
    document.addEventListener('shown.bs.modal', function(event) {
        if (event.target.id === 'autoreviewModal') {
            initializeDefaultAnswers();
            setupCleanupCheckboxes();
        }
    });


    function showSpinner() {
        $("#fullscreen-spinner").removeClass("d-none");
    }

    function hideSpinner() {
        $("#fullscreen-spinner").addClass("d-none");
    }
</script>
