<style>
    .reduced-image {
        transform: scale(0.5);
    }
</style>

<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="reviewModalLabel">Revisión de Dispositivo</h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Card para información básica -->
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>Código:</strong> <span class="fw-bold text-primary" id="modal-code"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Plano:</strong> <span id="modal-floorplan"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <h6><strong>Punto de Control:</strong> <span id="modal-control-point"></span></h6>
                        </div>
                        <div class="col-md-6 mb-2">
                            Supervisión de áreas de oportunidad. <h6><strong>Área de Aplicación:</strong> <span
                                    id="modal-application-area"></span></h6>
                        </div>
                    </div>
                </div>

                <!-- Card para preguntas -->
                <div class="border rounded p-3 bg-light mb-3">
                    <h5 class="fw-bold mb-3">Preguntas</h5>
                    <div id="modal-questions-container">
                        <p class="text-muted">No hay preguntas para este dispositivo</p>
                    </div>
                </div>

                <!-- Card para plagas y productos -->
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Card para plagas -->
                            <div class="border rounded p-3 bg-white mb-3 h-100">
                                <h5 class="fw-bold mb-3">Plagas</h5>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="new-pest-select">
                                        <option value="" selected disabled>Seleccione una plaga</option>
                                        @foreach ($pests as $pest)
                                            <option value="{{ $pest->id }}">{{ $pest->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" id="pest-quantity" class="form-control" placeholder="Cantidad"
                                        style="max-width: 100px;" min="1" value="1">
                                    <button class="btn btn-success" type="button" id="add-pest-btn">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <div id="modal-pests-container">
                                    <p class="text-muted">No hay plagas asignadas</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card para productos (solo la parte modificada) -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-white mb-3 h-100">
                                <h5 class="fw-bold mb-3">Productos</h5>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="new-product-select">
                                        <option value="" selected disabled>Seleccione un producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product['id'] }}"
                                                data-methods="{{ json_encode($product['application_methods']) }}"
                                                data-lots="{{ json_encode($product['lots']) }}">
                                                {{ $product['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" id="product-quantity" class="form-control"
                                        placeholder="Cantidad" style="max-width: 100px;" min="1" value="1">
                                    <button class="btn btn-success" type="button" id="add-product-btn">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <div id="modal-products-container">
                                    <p class="text-muted">No hay productos asignados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card para observaciones -->
                <div class="border rounded p-3 bg-light mb-3">
                    <h5 class="fw-bold mb-3">Observaciones</h5>
                    <textarea class="form-control" id="modal-observations" rows="3"
                        placeholder="Agregue observaciones sobre este punto de control..."></textarea>
                </div>

                <div class="border rounded p-3 bg-light">
                    <h5 class="fw-bold mb-3">Imagen del dispostivo</h5>
                    <div class="border rounded p-3 bg-white">
                        <img id="device-img" src="" class="rounded mx-auto d-block" alt="device-img"
                            style="width: 50%">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="save-review-btn">Guardar revisión</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let currentDeviceId = null;
    let currentServiceId = null;
    const allProducts = @json($products);
    const allPests = @json($pests);
    const applicationMethods = @json($application_methods);

    // Recopilar datos de productos
    const products = [];

    // Recopilar datos de plagas
    const pests = [];

    // Funciones de ayuda
    function findProductById(id) {
        return allProducts.find(product => product.id == id);
    }

    function findPestById(id) {
        return allPests.find(pest => pest.id == id);
    }


    // Función principal para abrir el modal
    function openReviewModal(buttonElement, serviceId) {
        console.log('Apertura del modal')
        const deviceData = JSON.parse(buttonElement.getAttribute('data-device'));

        // CRÍTICO: Limpiar arrays globales solo si es un dispositivo diferente
        if (currentDeviceId !== deviceData.id) {
            pests.length = 0;
            products.length = 0;
        }

        currentDeviceId = deviceData.id;
        currentServiceId = serviceId;

        document.getElementById('reviewModalLabel').textContent =
            `Revisión de Dispositivo | ${deviceData.code || 'N/A'} |`;

        // Llenar información básica
        document.getElementById('modal-code').textContent = deviceData.code || 'N/A';
        document.getElementById('modal-floorplan').textContent = deviceData.floorplan?.name || 'N/A';
        document.getElementById('modal-control-point').textContent = deviceData.control_point?.name || 'N/A';
        document.getElementById('modal-application-area').textContent = deviceData.application_area?.name || 'N/A';

        // Llenar preguntas
        const questionsContainer = document.getElementById('modal-questions-container');
        questionsContainer.innerHTML = '';

        if (deviceData.questions?.length > 0) {
            deviceData.questions.forEach(question => {
                console.log('Pregunta procesada: ', question);
                const questionDiv = document.createElement('div');
                questionDiv.className = 'mb-3';
                questionDiv.innerHTML = `
                    <label class="form-label">${question.question}</label>
                    <select class="form-select form-select-sm question-answer" data-question-id="${question.id}">
                        <option value="" ${!question.answer ? 'selected' : ''}>Sin Responder</option>
                        ${question.answers.map(answer => 
                            `<option value="${answer}" ${answer == question.answer ? 'selected' : ''}>${answer}</option>`
                        ).join('')}
                    </select>
                `;
                questionsContainer.appendChild(questionDiv);
            });
        } else {
            questionsContainer.innerHTML = '<p class="text-muted">No hay preguntas para este dispositivo</p>';
        }

        // Llenar plagas
        document.getElementById('modal-pests-container').innerHTML = deviceData.pests?.length > 0 ?
            '' :
            '<p class="text-muted">No hay plagas asignadas</p>';

        var mockPests = pests.length > 0 ? pests : deviceData.pests;

        console.log('Plagas recopiladas: ', pests);
        console.log('Plagas en dispositivos: ', deviceData.pests);

        mockPests?.forEach(pest => {
            const key = pest.key ?? null;
            addPestToContainer(pest.id, pest.name, pest.quantity, pest.key);
        });

        // Llenar productos
        document.getElementById('modal-products-container').innerHTML = deviceData.products?.length > 0 ?
            '' :
            '<p class="text-muted">No hay productos asignados</p>';

        var mockProducts = products.length > 0 ? products : deviceData.products;
        mockProducts?.forEach(product => {
            // Buscar el producto en allProducts para obtener los lotes disponibles
            const productInAllProducts = allProducts.find(p => p.id == product.id);
            let availableLots = [];

            // Si el producto en deviceData no tiene lotes pero existe en allProducts, usar esos lotes
            availableLots = productInAllProducts.lots || [];

            // Manejar valores null/undefined para métodos y lotes
            const methodId = product.application_method_id || '';
            const lotId = product.lot_id || '';
            const metric = productInAllProducts.metric ?? null;
            const key = product.key ?? null;

            addProductToContainer(
                product.id,
                product.name,
                product.quantity,
                methodId,
                lotId,
                applicationMethods,
                availableLots,
                metric,
                key
            );
        });

        // Llenar observaciones
        document.getElementById('modal-observations').value = deviceData.states.observations || '';
        $('#device-img').attr('src', deviceData.states.device_image);

        // Mostrar modal
        new bootstrap.Modal(document.getElementById('reviewModal')).show();
    }


    function addPestToContainer(pestId, pestName, quantity, key) {
        const container = document.getElementById('modal-pests-container');
        console.log("Cantidad: ", quantity);

        if (container.innerHTML.includes('No hay plagas asignadas')) {
            container.innerHTML = '';
        }

        // Generar key si no existe
        const pestKey = key != null ? key : generateTimeKey();

        // Prevenir duplicados por key (permite misma plaga con diferentes keys)
        if (document.querySelector(`.remove-pest[data-pest-key="${pestKey}"]`)) {
            console.log('return via KEY')
            return;
        }

        const pestDiv = document.createElement('div');
        pestDiv.className = 'border rounded p-2 mb-2 bg-white';
        pestDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold">${pestName}</span>
                <button class="btn btn-sm btn-danger remove-pest" data-pest-id="${pestId}" data-pest-key="${pestKey}">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label small text-muted">Cantidad</label>
                    <input type="number" class="form-control form-control-sm pest-quantity" 
                           data-pest-id="${pestId}" value="${quantity || 1}" min="1">
                </div>
            </div>
        `;
        container.appendChild(pestDiv);
    }

    function addProductToContainer(productId, productName, quantity, selectedMethodId = null, selectedLotId = null,
        methods = [], lots = [], metric = null, key = null) {
        const container = document.getElementById('modal-products-container');

        if (container.innerHTML.includes('No hay productos asignados')) {
            container.innerHTML = '';
        }

        // Generar key si no existe
        const productKey = key != null ? key : generateTimeKey();

        // Prevenir duplicados por key
        if (document.querySelector(`.remove-product[data-product-key="${productKey}"]`)) {
            return;
        }

        // Opciones de métodos con "Sin método"
        const methodOptions = [
            `<option value="">Sin método</option>`,
            ...methods.map(method =>
                `<option value="${method.id}" ${method.id == selectedMethodId ? 'selected' : ''}>
                ${method.name}
            </option>`
            )
        ].join('');

        // Opciones de lotes con "Sin lote"
        const lotOptions = [
            `<option value="">Sin lote</option>`,
            ...lots.map(lot =>
                `<option value="${lot.id}" ${lot.id == selectedLotId ? 'selected' : ''}>
                ${lot.registration_number}
            </option>`
            )
        ].join('');

        const productDiv = document.createElement('div');
        productDiv.className = 'border rounded p-2 mb-2 bg-white';
        productDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">${productName}</span>
                <button class="btn btn-sm btn-danger remove-product" data-product-id="${productId}" data-product-key="${productKey}">
                    <i class="bi bi-trash-fill"></i>
                </button>
        </div>
        <div class="row g-2">
            <div class="col-12">
                <label class="form-label small text-muted">Cantidad</label>
                <div class="input-group">
                    <input type="number" class="form-control form-control-sm product-quantity" 
                       data-product-id="${productId}" value="${quantity || 1}" min="1">
                    <span class="input-group-text">${metric ?? '-'}</span>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label small text-muted">Método</label>
                <select class="form-select form-select-sm product-method" data-product-id="${productId}" required>
                    ${methodOptions}
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small text-muted">Lote</label>
                <select class="form-select form-select-sm product-lot" data-product-id="${productId}" required>
                    ${lotOptions}
                </select>
            </div>
        </div>
    `;
        container.appendChild(productDiv);
    }

    // Event Listeners
    document.getElementById('add-pest-btn').addEventListener('click', () => {
        const pestId = document.getElementById('new-pest-select').value;
        const quantity = document.getElementById('pest-quantity').value || 1;

        if (!pestId) {
            alert('Por favor seleccione una plaga');
            return;
        }

        const pest = findPestById(pestId);
        if (pest) {
            addPestToContainer(pest.id, pest.name, quantity, null);
            document.getElementById('new-pest-select').value = '';
            document.getElementById('pest-quantity').value = 1;

            // Feedback visual
            const btn = document.getElementById('add-pest-btn');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i>';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-primary');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 800);
        }
    });

    document.getElementById('add-product-btn').addEventListener('click', () => {
        const productSelect = document.getElementById('new-product-select');
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const productId = productSelect.value;
        const quantity = document.getElementById('product-quantity').value || 1;

        if (!productId) {
            alert('Por favor seleccione un producto');
            return;
        }

        // Obtener métodos y lotes desde los data attributes
        const methods = applicationMethods; // Usamos la constante global
        const lots = JSON.parse(selectedOption.getAttribute('data-lots') || '[]');

        const productInAllProducts = allProducts.find(p => p.id == productId);
        const metric = productInAllProducts.metric ?? null;

        addProductToContainer(
            productId,
            selectedOption.text,
            quantity,
            null, // methodId seleccionado inicial
            null, // lotId seleccionado inicial
            methods,
            lots,
            metric,
            null
        );

        // Resetear el formulario
        productSelect.value = '';
        document.getElementById('product-quantity').value = 1;

        // Feedback visual
        const btn = document.getElementById('add-product-btn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-primary');
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 800);
    });

    document.getElementById('modal-pests-container').addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-pest');
        if (removeBtn) {
            e.preventDefault();

            const pestName = removeBtn.closest('.border.rounded').querySelector('.fw-bold').textContent;

            // Confirmación antes de eliminar
            if (!confirm(`¿Está seguro de eliminar la plaga "${pestName}"?`)) {
                return;
            }

            const pestKey = removeBtn.dataset.pestKey;

            // Eliminar del array global
            const index = pests.findIndex(p => p.key == pestKey);
            if (index != -1) {
                pests.splice(index, 1);
            }

            // Eliminar del DOM
            const pestItem = removeBtn.closest('.border.rounded');
            pestItem.remove();

            // Mostrar mensaje si no hay plagas
            if (document.getElementById('modal-pests-container').children.length == 0) {
                document.getElementById('modal-pests-container').innerHTML =
                    '<p class="text-muted">No hay plagas asignadas</p>';
            }
        }
    });

    // Event Delegation para eliminar productos
    document.getElementById('modal-products-container').addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.remove-product');

        if (removeBtn) {
            e.preventDefault();

            const productName = removeBtn.closest('.border.rounded').querySelector('.fw-bold').textContent;

            // Confirmación antes de eliminar
            if (!confirm(`¿Está seguro de eliminar el producto "${productName}"?`)) {
                return;
            }

            const productItem = removeBtn.closest('.border.rounded');
            productItem.remove();

            const productKey = removeBtn.dataset.productKey;

            const index = products.findIndex(p => p.key == productKey);
            if (index != -1) {
                products.splice(index, 1);
            }
            // Mostrar mensaje si no hay productos
            if (document.getElementById('modal-products-container').children.length == 0) {
                document.getElementById('modal-products-container').innerHTML =
                    '<p class="text-muted">No hay productos asignados</p>';
            }
        }
    });

    document.getElementById('save-review-btn').addEventListener('click', () => {
        if (!currentDeviceId || !currentServiceId) return;

        // Recopilar plagas
        document.querySelectorAll('#modal-pests-container > .border.rounded').forEach(item => {
            const pestId = item.querySelector('.remove-pest').dataset.pestId;
            const pestKey = item.querySelector('.remove-pest').dataset.pestKey;
            const pestName = item.querySelector('.fw-bold').textContent;
            const quantity = item.querySelector('.pest-quantity').value;

            // Buscar por key para manejar correctamente múltiples plagas del mismo tipo
            const pest_index = pests.findIndex(p => p.key == pestKey);

            if (pest_index != -1) {
                pests[pest_index].quantity = parseInt(quantity) || 1;
            } else {
                pests.push({
                    key: pestKey,
                    id: pestId,
                    name: pestName,
                    quantity: parseInt(quantity) || 1
                });
            }
        });

        // Recopilar productos
        document.querySelectorAll('#modal-products-container > .border.rounded').forEach(item => {
            const productId = item.querySelector('.remove-product').dataset.productId;
            const productKey = item.querySelector('.remove-product').dataset.productKey;
            const productName = item.querySelector('.fw-bold').textContent;
            const quantity = item.querySelector('.product-quantity').value;
            const methodId = item.querySelector('.product-method').value || null;
            const lotId = item.querySelector('.product-lot').value || null;

            // Buscar el producto en allProducts para referencia
            const productInAllProducts = allProducts.find(p => p.id == productId);

            // Encontrar el nombre del método y lote para mostrar
            const method = methodId ? applicationMethods.find(m => m.id == methodId) : null;
            const lot = lotId ? (productInAllProducts?.lots?.find(l => l.id == lotId) || null) : null;

            // Buscar por key para manejar correctamente múltiples instancias
            const product_index = products.findIndex(p => p.key == productKey);

            if (product_index != -1) {
                // Actualizar producto existente
                products[product_index].quantity = parseInt(quantity) || 1;
                products[product_index].application_method_id = methodId || null;
                products[product_index].method_name = method?.name || 'Sin método';
                products[product_index].lot_id = lotId || null;
                products[product_index].lot_number = lot?.registration_number || 'Sin lote';
            } else {
                products.push({
                    key: productKey,
                    id: productId,
                    name: productName,
                    quantity: parseInt(quantity) || 1,
                    application_method_id: methodId || null,
                    method_name: method?.name || 'Sin método',
                    lot_id: lotId || null,
                    lot_number: lot?.registration_number || 'Sin lote',
                    metric: productInAllProducts.metric,
                    // Mantener referencia a los lotes disponibles para futuras ediciones
                    available_lots: productInAllProducts?.lots || []
                });
            }
        });

        const updatedData = {
            device_id: currentDeviceId,
            service_id: currentServiceId,
            questions: Array.from(document.querySelectorAll('.question-answer')).map(select => ({
                id: select.dataset.questionId,
                answer: select.value
            })),
            pests: pests,
            products: products,
            observations: document.getElementById('modal-observations').value
        };

        // Convertir los productos y plagas al formato correcto para copy_devices
        const formattedData = {
            device_id: currentDeviceId,
            service_id: currentServiceId,
            questions: updatedData.questions,
            pests: pests.map(pest => ({
                key: pest.key,
                id: pest.id.toString(), // Asegurar que sea string para coincidir con el formato
                name: pest.name,
                device_id: currentDeviceId.toString(), // Asegurar formato string
                quantity: pest.quantity.toString() // Convertir a string
            })),
            products: products.map(product => ({
                key: product.key,
                id: product.id.toString(), // Asegurar string
                order_id: "{{ $order->id }}", // Obtener del contexto actual
                device_id: currentDeviceId.toString(), // Asegurar string
                application_method_id: product.application_method_id ? product
                    .application_method_id.toString() : null,
                lot_id: product.lot_id ? product.lot_id.toString() : null,
                name: product.name,
                quantity: product.quantity.toString(), // Convertir a string
                metric: product.metric
            })),
            states: {
                order_id: {{ $order->id }},
                device_id: currentDeviceId,
                is_scanned: "1", // Asumir que se ha escaneado/visitado
                is_checked: "1", // Marcar como revisado
                observations: updatedData.observations || null,
                device_image: null // Puedes añadir lógica para imágenes si es necesario
            }
        };

        console.log('Datos a guardar:', JSON.stringify(formattedData, null, 2));

        new_formdata = new FormData();
        new_formdata.append('review', JSON.stringify(formattedData));

        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        showSpinner();

        $.ajax({
            url: "{{ route('report.set.incident', ['orderId' => $order->id]) }}",
            type: 'POST',
            data: new_formdata,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            contentType: false,
            processData: false,
            success: function(response) {
                var pest_row_query = $(`#table-row-pest${currentDeviceId}-list`);
                var product_row_query = $(`#table-row-product${currentDeviceId}-list`);
                const tbody = $('#products-table-body');

                pest_row_query.empty();
                product_row_query.empty();
                tbody.empty();

                // Actualizar la UI
                $.each(pests, function(index, p) {
                    pest_row_query.append(`
                    <li class="product-item">
                        <span class="fw-bold">${p.name}</span>
                        (<span>${p.quantity}</span>)
                    </li>
                `);
                });

                $.each(products, function(index, p) {
                    product_row_query.append(`
                    <li class="product-item">
                        <span class="fw-bold">${p.name}</span>
                        (<span>${p.quantity} ${extractParenthesesContent(p.metric)}</span>)
                    </li>
                `);
                });

                $.each(response.order_products, function(i, op) {
                    const row = `
                <tr>
                    <th scope="row">${i + 1}</th>
                    <td>${op.product?.name || '-'}</td>
                    <td>${op.service?.name || '-'}</td>
                    <td>${op.application_method?.name || '-'}</td>
                    <td class="fw-bold">
                        ${(op.amount || '0')}<br>
                        <small
                            class="text-muted">${op.metric?.value || ''}</small>
                    </td>
                    <td>${op.dosage || '-'}</td>
                    <td>${op.lot?.registration_number || op.possible_lot || '-'}</td>
                    <td class="action-buttons text-center">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#productModal" data-product='${JSON.stringify(op.data)}'
                            onclick="setProduct(this)">
                            <i class="bi bi-pencil-square"></i> 
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" 
                            onclick="deleteProduct(${op.id})">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </td>
                </tr>
            `;
                    tbody.append(row);
                });

                // Actualizar copy_devices con los nuevos datos
                updateDeviceDataInCopyDevices(formattedData);

                // Marcar el dispositivo como revisado en la tabla principal
                markDeviceAsReviewed(currentDeviceId);

                $('#reviewModal').modal('hide');
            },
            error: function(xhr) {
                console.error('Error al guardar:', xhr.responseText);
                alert('Error al guardar los cambios. Por favor, intente nuevamente.');
            },
            complete: function() {
                hideSpinner();
            }
        });
    });

    // Función para actualizar copy_devices con el nuevo formato
    function updateDeviceDataInCopyDevices(updatedData) {
        // Buscar el dispositivo en copy_devices
        const deviceIndex = copy_devices.findIndex(dev => dev.id == updatedData.device_id);

        if (deviceIndex !== -1) {
            // Actualizar preguntas
            updatedData.questions.forEach(updatedQuestion => {
                const questionIndex = copy_devices[deviceIndex].questions.findIndex(q => q.id == updatedQuestion
                    .id);
                if (questionIndex !== -1) {
                    copy_devices[deviceIndex].questions[questionIndex].answer = updatedQuestion.answer;
                }
            });

            // Actualizar plagas - convertir al formato correcto
            copy_devices[deviceIndex].pests = updatedData.pests.map(pest => ({
                key: pest.key,
                id: pest.id,
                name: pest.name,
                device_id: pest.device_id,
                quantity: pest.quantity
            }));

            // Actualizar productos - convertir al formato correcto
            copy_devices[deviceIndex].products = updatedData.products.map(product => ({
                key: product.key,
                id: product.id,
                order_id: product.order_id,
                device_id: product.device_id,
                application_method_id: product.application_method_id,
                lot_id: product.lot_id,
                name: product.name,
                quantity: product.quantity,
                metric: product.metric
            }));

            // Actualizar estados
            copy_devices[deviceIndex].states = {
                order_id: updatedData.states.order_id,
                device_id: updatedData.states.device_id,
                is_scanned: updatedData.states.is_scanned,
                is_checked: updatedData.states.is_checked,
                observations: updatedData.states.observations,
                device_image: updatedData.states.device_image
            };

            console.log('Dispositivo actualizado en copy_devices:', copy_devices[deviceIndex]);

            // Opcional: Actualizar la interfaz principal si es necesario
            updateDeviceInUI(updatedData.device_id);
        } else {
            console.warn(`Dispositivo con ID ${updatedData.device_id} no encontrado en copy_devices`);
        }
    }

    // Función para marcar dispositivo como revisado en la tabla principal
    function markDeviceAsReviewed(deviceId) {
        const deviceRow = $(`tr[data-device-id="${deviceId}"]`);
        if (deviceRow.length) {
            deviceRow.find('.device-status').html('<span class="badge bg-success">Revisado</span>');
            deviceRow.addClass('table-success');
        }
    }

    // Función para actualizar la UI del dispositivo principal si es necesario
    function updateDeviceInUI(deviceId) {
        const deviceIndex = copy_devices.findIndex(dev => dev.id == deviceId);
        if (deviceIndex !== -1) {
            const device = copy_devices[deviceIndex];

            // Aquí puedes actualizar cualquier elemento de la UI que muestre información del dispositivo
            // Por ejemplo, actualizar contadores, resúmenes, etc.
            console.log(`UI actualizada para dispositivo ${deviceId}`);
        }
    }

    function showSpinner() {
        $("#fullscreen-spinner").removeClass("d-none");
    }

    function hideSpinner() {
        $("#fullscreen-spinner").addClass("d-none");
    }

    function extractParenthesesContent(cadena) {
        // Buscar el contenido entre paréntesis
        const regex = /\((.*?)\)/;
        const coincidencias = cadena.match(regex);
        return coincidencias && coincidencias[1] ? coincidencias[1] : '';
    }

    function generateTimeKey() {
        return Date.now().toString();
    }
</script>
