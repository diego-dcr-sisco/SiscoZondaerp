<!-- Modal para seleccionar recomendaciones -->
<div class="modal fade" id="recommendationsModal" tabindex="-1" aria-labelledby="recommendationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recommendationsModalLabel">Seleccionar Recomendaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchRecommendations" placeholder="Buscar recomendaciones...">
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
                                <td class="text-center">
                                    <input class="form-check-input recommendation-checkbox" type="checkbox" 
                                           value="{{ $index }}" id="rec{{ $index }}">
                                </td>
                                <td>
                                    <label class="form-check-label w-100" for="rec{{ $index }}" style="cursor: pointer;">
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

<!-- Contenido original modificado -->
@foreach ($order->services as $service)
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 fw-bold">Servicio - {{ $service->name }}</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm mb-2 add-recommendation-btn" data-service-id="{{ $service->id }}">
                <i class="bi bi-plus-lg"></i> Agregar Recomendaciones
            </button>

            <button type="button" class="btn btn-secondary btn-sm mb-2 clear-recommendations-btn" data-service-id="{{ $service->id }}">
                <i class="bi bi-arrow-clockwise"></i> Limpiar Recomendaciones
            </button>
        </div>

        <div class="mb-3">
            <div id="summary-recs{{ $service->id }}" class="smnote">
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
            </div>
        </div>
    </div>
</div>
@endforeach

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
    padding: 10px;
}
</style>

<script>
$(document).ready(function() {
    let currentServiceId = null;
    const recommendations = @json($recommendations);

    // Inicializar Summernote en cada textarea
    $('.smnote').each(function() {
        $(this).summernote({
            height: 300,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });

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

    // Agregar recomendaciones seleccionadas al Summernote
    $('#addSelectedRecommendations').click(function() {
        const selectedIndexes = [];
        $('.recommendation-checkbox:checked').each(function() {
            selectedIndexes.push(parseInt($(this).val()));
        });

        if (selectedIndexes.length > 0 && currentServiceId) {
            addRecommendationsToSummernote(currentServiceId, selectedIndexes);
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
        if (confirm('¿Estás seguro de que quieres limpiar todas las recomendaciones adicionales de este servicio?')) {
            clearServiceRecommendations(serviceId);
        }
    });

    // Función para agregar recomendaciones al Summernote
    function addRecommendationsToSummernote(serviceId, indexes) {
        const summernoteElement = $(`#summary-recs${serviceId}`);
        let currentContent = summernoteElement.summernote('code');
        
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
                    summernoteElement.summernote('code', tempDiv.html());
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
                summernoteElement.summernote('code', currentContent + additionalSection);
            }
            
            updateHiddenField();
        }
    }

    // Función para limpiar recomendaciones del Summernote - CORREGIDA
    function clearServiceRecommendations(serviceId) {
        const summernoteElement = $(`#summary-recs${serviceId}`);
        let currentContent = summernoteElement.summernote('code');
        
        // Crear un elemento temporal para manipular el HTML
        const tempDiv = $('<div>').html(currentContent);
        
        // Remover la sección de recomendaciones adicionales
        tempDiv.find('#custom-recommendations').closest('p').prev('p').remove(); // Remover <p><br></p> anterior
        tempDiv.find('#custom-recommendations').closest('p').remove(); // Remover el párrafo del título
        tempDiv.find('#custom-recommendations').remove(); // Remover la lista
        
        // Actualizar el contenido de Summernote
        summernoteElement.summernote('code', tempDiv.html());
        updateHiddenField();
        
        console.log('Recomendaciones limpiadas para el servicio:', serviceId);
    }

    // Función alternativa más robusta para limpiar recomendaciones
    function clearServiceRecommendations(serviceId) {
        const summernoteElement = $(`#summary-recs${serviceId}`);
        let currentContent = summernoteElement.summernote('code');
        
        // Usar una expresión regular más precisa para remover la sección completa
        const cleanedContent = currentContent.replace(/(<p><br><\/p>\s*<p><strong>RECOMENDACIONES ADICIONALES<\/strong><\/p>\s*<ol id="custom-recommendations">[\s\S]*?<\/ol>)/g, '');
        
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
            
            summernoteElement.summernote('code', tempDiv.html());
        } else {
            summernoteElement.summernote('code', cleanedContent);
        }
        
        updateHiddenField();
    }

    // Función para actualizar el campo hidden
    function updateHiddenField() {
        const allRecommendations = [];
        
        $('.card').each(function() {
            const serviceId = $(this).find('.add-recommendation-btn').data('service-id');
            if (serviceId) {
                const summernoteElement = $(`#summary-recs${serviceId}`);
                const content = summernoteElement.summernote('code');
                
                // Extraer las recomendaciones adicionales del contenido
                const tempDiv = $('<div>').html(content);
                const customRecommendations = tempDiv.find('#custom-recommendations li').map(function() {
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

    // Actualizar el campo hidden cuando cambie el contenido de Summernote
    $('.smnote').on('summernote.change', function() {
        updateHiddenField();
    });
});
</script>