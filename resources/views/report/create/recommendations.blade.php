<!-- Modal para seleccionar recomendaciones -->
<div class="modal fade" id="recommendationsModal" tabindex="-1" aria-labelledby="recommendationsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recommendationsModalLabel">Seleccionar Recomendaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchRecommendations"
                        placeholder="Buscar recomendaciones...">
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th width="50px" class="text-center">Seleccionar</th>
                                <th>Recomendación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recommendations as $index => $description)
                                <tr class="recommendation-item">
                                    <td>
                                        <input class="form-check-input recommendation-checkbox" type="checkbox"
                                            value="{{ $index }}" id="rec{{ $index }}">
                                    </td>
                                    <td>
                                        <label class="form-check-label w-100" for="rec{{ $index }}"
                                            style="cursor: pointer;">
                                            {{ $description }}
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="clearSelectedRecommendations">
                    <i class="bi bi-x-circle"></i> Limpiar Selección
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="addSelectedRecommendations">
                    <i class="bi bi-plus-lg"></i> Agregar Seleccionadas
                </button>
            </div>
        </div>
    </div>
</div>

<div class="accordion" id="recommendationsAccordion">
    @foreach ($order->services as $service)
        <div class="accordion-item mb-2">
            <h2 class="accordion-header" id="recommendations-heading-{{ $service->id }}">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                    data-bs-toggle="collapse" data-bs-target="#recommendations-collapse-{{ $service->id }}"
                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    aria-controls="recommendations-collapse-{{ $service->id }}">
                    Recomendaciones de {{ $service->name }}
                </button>
            </h2>
            <div id="recommendations-collapse-{{ $service->id }}"
                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                aria-labelledby="recommendations-heading-{{ $service->id }}"
                data-bs-parent="#recommendationsAccordion">
                <div class="accordion-body">
                    <div class="mb-3">
                        <div id="summary-recs{{ $service->id }}" class="smnote smnote-recommendation"
                            data-autosave-type="recommendation" data-service-id="{{ $service->id }}" style="height: 300px">
                            @if ($order->reportRecommendations->where('service_id', $service->id)->first())
                                {!! $order->reportRecommendations->where('service_id', $service->id)->first()->recommendation_text !!}
                            @else
                                @if ($service->prefix == 2)
                                    <p><strong>ANTES DE LA APLICACIÓN QUÍMICA</strong></p>
                                    <ol>
                                        <li>Identificar la plaga a controlar.</li>
                                        <li>No debe encontrarse personal en el área.</li>
                                        <li>No debe de haber materia prima expuesta.</li>
                                        <li>Asegurar que la aplicación no afecte el proceso, producción o a terceros.</li>
                                    </ol>
                                    <p><br></p>
                                    <p><strong>DURANTE DE LA APLICACIÓN QUÍMICA</strong></p>
                                    <ol>
                                        <li>En el área solo debe de encontrarse el técnico aplicador</li>
                                    </ol>
                                    <p><br></p>
                                    <p><strong>DESPUÉS DE LA APLICACIÓN QUÍMICA</strong></p>
                                    <ol>
                                        <li>Respetar el tiempo de reentrada conforme a la etiqueta del producto a utilizar.</li>
                                        <li>Realizar recolección de plaga o limpieza necesaria al tipo de área.</li>
                                    </ol>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="section-action-bar">
                        <span id="autosave-status-recommendation-{{ $service->id }}" class="autosave-status">Sin cambios</span>
                        <div class="section-action-buttons">
                            <button type="button" class="btn btn-success btn-sm add-recommendation-btn"
                                data-service-id="{{ $service->id }}">
                                <i class="bi bi-plus-lg"></i> Agregar
                            </button>

                            <button type="button" class="btn btn-secondary btn-sm clear-recommendations-btn"
                                data-service-id="{{ $service->id }}">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                            </button>

                            <button type="button" class="btn btn-primary btn-sm report-save-btn update-recommendations-btn"
                                data-service-id="{{ $service->id }}">
                                <i class="bi bi-save"></i> Guardar recomendaciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<input type="hidden" id="recommendations" name="recommendations" />

<style>
    .recommendation-item:hover {
        background-color: #f8f9fa;
    }

    .recommendation-item .form-check-label {
        cursor: pointer;
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .smnote {
        min-height: 200px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .smnote-recommendation .ql-toolbar {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }

    .smnote-recommendation .ql-container {
        min-height: 250px;
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
        font-size: 0.95rem;
    }

    .smnote-recommendation .ql-editor {
        min-height: 250px;
    }
</style>

<script>
    $(document).ready(function() {
        let currentServiceId = null;
        const recommendations = @json($recommendations);

        function getRecommendationHtml(serviceId) {
            if (typeof window.getRecommendationEditorHtml === 'function') {
                return window.getRecommendationEditorHtml(serviceId);
            }

            return '';
        }

        function setRecommendationHtml(serviceId, html) {
            if (typeof window.setRecommendationEditorHtml === 'function') {
                window.setRecommendationEditorHtml(serviceId, html || '');
            }
        }

        // Abrir modal para agregar recomendaciones
        $('.add-recommendation-btn').click(function() {
            currentServiceId = $(this).data('service-id');
            $('#recommendationsModal').modal('show');
        });

        // Buscar recomendaciones
        $('#searchRecommendations').on('input', function() {
            const searchText = $(this).val().toLowerCase();
            $('.recommendation-item').each(function() {
                const text = $(this).find('.form-check-label').text().toLowerCase();
                if (text.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Limpiar selección en el modal
        $('#clearSelectedRecommendations').click(function() {
            $('.recommendation-checkbox:checked').prop('checked', false);
        });

        // Agregar recomendaciones seleccionadas al editor
        $('#addSelectedRecommendations').click(function() {
            const selectedIndexes = [];
            $('.recommendation-checkbox:checked').each(function() {
                selectedIndexes.push(parseInt($(this).val()));
            });

            if (selectedIndexes.length > 0 && currentServiceId) {
                addRecommendationsToEditor(currentServiceId, selectedIndexes);
                $('#recommendationsModal').modal('hide');
                $('.recommendation-checkbox:checked').prop('checked', false);
                $('#searchRecommendations').val('');
            } else {
                alert('Por favor selecciona al menos una recomendación.');
            }
        });

        // Limpiar recomendaciones de un servicio específico - CORREGIDO
        $('.clear-recommendations-btn').click(function() {
            const serviceId = $(this).data('service-id');
            if (confirm(
                    '¿Estás seguro de que quieres limpiar todas las recomendaciones adicionales de este servicio?'
                )) {
                clearServiceRecommendations(serviceId);
            }
        });

        // Actualizar recomendaciones de un servicio específico
        $('.update-recommendations-btn').click(function() {
            const serviceId = $(this).data('service-id');
            updateRecommendations(serviceId, false);
        });

        // Función para agregar recomendaciones al editor
        function addRecommendationsToEditor(serviceId, indexes) {
            let currentContent = getRecommendationHtml(serviceId);

            // Crear el HTML de las nuevas recomendaciones
            let newRecommendationsHTML = '';
            indexes.forEach(index => {
                if (recommendations[index]) {
                    const recommendationText = recommendations[index].trim();

                    // Verificar si ya existe en el contenido
                    if (!currentContent.includes(recommendationText)) {
                        newRecommendationsHTML += `<li>${recommendationText}</li>`;
                    }
                }
            });

            if (newRecommendationsHTML) {
                // Verificar si ya existe la sección de recomendaciones adicionales
                if (currentContent.includes('RECOMENDACIONES ADICIONALES')) {
                    // Encontrar la posición donde insertar
                    const tempDiv = $('<div>').html(currentContent);
                    const customList = tempDiv.find('#custom-recommendations');
                    if (customList.length > 0) {
                        // Agregar al final de la lista existente
                        customList.append(newRecommendationsHTML);
                        setRecommendationHtml(serviceId, tempDiv.html());
                    }
                } else {
                    // Crear nueva sección
                    const additionalSection = `
                    <p><br></p>
                    <p><strong>RECOMENDACIONES ADICIONALES</strong></p>
                    <ol id="custom-recommendations">
                        ${newRecommendationsHTML}
                    </ol>
                `;
                    setRecommendationHtml(serviceId, currentContent + additionalSection);
                }

                updateHiddenField();
            }
        }

        function clearServiceRecommendations(serviceId) {
            let currentContent = getRecommendationHtml(serviceId);

            // Usar una expresión regular más precisa para remover la sección completa
            const cleanedContent = currentContent.replace(
                /(<p><br><\/p>\s*<p><strong>RECOMENDACIONES ADICIONALES<\/strong><\/p>\s*<ol id="custom-recommendations">[\s\S]*?<\/ol>)/g,
                '');

            // Si la expresión regular no funcionó, usar el método del DOM
            if (cleanedContent === currentContent) {
                const tempDiv = $('<div>').html(currentContent);
                const recommendationsSection = tempDiv.find('p:contains("RECOMENDACIONES ADICIONALES")');

                if (recommendationsSection.length > 0) {
                    // Remover el párrafo <br> anterior
                    recommendationsSection.prev('p').remove();
                    // Remover el título
                    recommendationsSection.remove();
                    // Remover la lista
                    recommendationsSection.next('ol').remove();
                }

                setRecommendationHtml(serviceId, tempDiv.html());
            } else {
                setRecommendationHtml(serviceId, cleanedContent);
            }

            updateHiddenField();
        }

        // Función para actualizar el campo hidden
        function updateHiddenField() {
            const allRecommendations = [];

            $('.smnote-recommendation').each(function() {
                const serviceId = $(this).data('service-id');
                if (serviceId) {
                    const content = getRecommendationHtml(serviceId);

                    // Extraer las recomendaciones adicionales del contenido
                    const tempDiv = $('<div>').html(content);
                    const customRecommendations = tempDiv.find('#custom-recommendations li').map(
                        function() {
                            return $(this).text().trim();
                        }).get();

                    if (customRecommendations.length > 0) {
                        allRecommendations.push({
                            service_id: serviceId,
                            recommendations: customRecommendations
                        });
                    }
                }
            });

            $('#recommendations').val(JSON.stringify(allRecommendations));
            console.log('Recomendaciones guardadas:', allRecommendations);
        }

        updateHiddenField();

        // Función para actualizar recomendaciones en el servidor
        function updateRecommendations(serviceId, silent = false) {
            const recommendationsHtml = getRecommendationHtml(serviceId);
            const statusEl = $(`#autosave-status-recommendation-${serviceId}`);

            const recommendations = {
                service_id: parseInt(serviceId),
                text: recommendationsHtml,
                order_id: parseInt($('#order-id').val()),
            };

            statusEl.removeClass('is-saved is-error').addClass('is-saving').text('Guardando...');

            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (!csrfToken) {
                alert('Error: No se encontró el token CSRF. Por favor, recarga la página.');
                return;
            }

            const formData = new FormData();
            formData.append('recommendations', JSON.stringify(recommendations));
            formData.append('_token', csrfToken);

            // Mostrar spinner si existe la función
            if (typeof showSpinner === 'function') {
                showSpinner();
            }

            $.ajax({
                type: 'POST',
                url: '/report/recommendations/update',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        statusEl.removeClass('is-saving is-error').addClass('is-saved').text('Guardado');
                        if (!silent) {
                            alert('Recomendaciones actualizadas correctamente!');
                        }
                    } else {
                        statusEl.removeClass('is-saving is-saved').addClass('is-error').text('Error al guardar');
                        if (!silent) {
                            alert('Error al actualizar las recomendaciones: ' + response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'Error al actualizar las recomendaciones';
                    statusEl.removeClass('is-saving is-saved').addClass('is-error').text('Error al guardar');

                    if (xhr.status === 403) {
                        errorMsg =
                            'Error 403: Acceso denegado. Tu sesión puede haber expirado. Por favor, recarga la página.';
                    } else if (xhr.status === 419) {
                        errorMsg = 'Error 419: Token CSRF expirado. Por favor, recarga la página.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else {
                        errorMsg += ': ' + error;
                    }

                    if (!silent) {
                        alert(errorMsg);
                    }
                },
                complete: function() {
                    // Ocultar spinner si existe la función
                    if (typeof hideSpinner === 'function') {
                        hideSpinner();
                    }
                }
            });
        }
    });
</script>
