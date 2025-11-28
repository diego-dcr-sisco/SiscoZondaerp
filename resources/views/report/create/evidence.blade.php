<div class="bg-light d-flex justify-content-between align-items-center mb-3">
    <div>
        <button type="button" class="btn btn-success btn-sm" id="add-evidence-btn">
            <i class="bi bi-plus-lg"></i> Agregar Evidencia
        </button>
        <button type="button" class="btn btn-secondary btn-sm" id="clear-all-evidence">
            <i class="bi bi-trash-fill"></i> Limpiar Todo
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="save-all-evidences">
            <i class="bi bi-save"></i> Guardar Cambios
        </button>
    </div>
    <span class="badge bg-primary" id="evidence-counter">0 evidencias</span>
</div>

<!-- Mensaje cuando no hay evidencias -->
<div id="no-evidence-message" class="text-center py-4 border rounded">
    <i class="bi bi-camera display-4 text-muted"></i>
    <p class="text-muted mt-2">No hay evidencias fotográficas agregadas</p>
</div>

<!-- Tabla para las evidencias -->
<div id="evidence-container" class="table-responsive" style="display: none;">
    <table class="table table-hover table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th width="80">Miniatura</th>
                <th>Archivo</th>
                <th>Descripción</th>
                <th width="120">Área</th>
                <th width="120">Servicio</th>
                <th width="150">Fecha</th>
                <th width="100"></th>
            </tr>
        </thead>
        <tbody id="evidence-tbody">
            <!-- Las evidencias se agregarán aquí dinámicamente -->
        </tbody>
    </table>
</div>

<!-- Modal para agregar evidencia -->
<div class="modal fade" id="evidenceModal" tabindex="-1" aria-labelledby="evidenceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="evidenceModalLabel">Agregar Evidencia Fotográfica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="evidence-image" class="form-label is-required">Imagen</label>
                    <input type="file" class="form-control" id="evidence-image"
                        accept=".png, .jpg, .jpeg, .webp, image/png, image/jpg, image/jpeg, image/webp" required>
                    <div class="form-text">Formatos permitidos: JPG, PNG, JPEG, WebP. Tamaño máximo: 5MB</div>
                </div>

                <div class="mb-3">
                    <label for="evidence-description" class="form-label is-required">Descripción</label>
                    <textarea class="form-control" id="evidence-description" rows="3"
                        placeholder="Describe la evidencia fotográfica..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="evidence-area" class="form-label is-required">Área de visualización</label>
                    <select class="form-select" id="evidence-area" required>
                        <option value="">Selecciona un área</option>
                        <option value="servicio">Servicio</option>
                        <option value="notas">Notas</option>
                        <option value="recomendaciones">Recomendaciones</option>
                        <option value="evidencias">Evidencias Fotográficas</option>
                    </select>
                    <div class="form-text">
                        <small>
                            <strong>Servicio:</strong> Se mostrará en la sección de servicios<br>
                            <strong>Notas:</strong> Se mostrará en las notas del cliente<br>
                            <strong>Recomendaciones:</strong> Se mostrará en las recomendaciones<br>
                            <strong>Evidencias Fotográficas:</strong> Se mostrará en la sección de evidencias
                            fotográficas
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evidence-service-id" class="form-label">Servicio ligado</label>
                    <select class="form-select" id="evidence-service-id">
                        <option value="">Ninguno</option>
                        @foreach ($order->services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Vista previa:</label>
                    <div id="image-preview" class="text-center border p-3 rounded" style="display: none;">
                        <img id="preview-img" src="#" alt="Vista previa" class="img-fluid rounded"
                            style="max-height: 200px;">
                        <div class="mt-2">
                            <small class="text-muted" id="file-info"></small>
                        </div>
                    </div>
                    <div id="no-preview" class="text-center border p-3 rounded">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Vista previa de la imagen aparecerá aquí</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="save-evidence">
                    Guardar Evidencia
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .evidence-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .evidence-thumbnail:hover {
        transform: scale(1.1);
    }

    .area-badge-servicio {
        background-color: #0d6efd;
    }

    .area-badge-notas {
        background-color: #6c757d;
    }

    .area-badge-recomendaciones {
        background-color: #198754;
    }

    .area-badge-evidencias {
        background-color: #6f42c1;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }
</style>

<script>
    $(document).ready(function() {
        let evidenceCounter = 0;
        let evidencesArray = @json($order->photoEvidencesToJsonArray() ?? []); // Array para almacenar todas las evidencias
        const services = @json($order->services->pluck('name', 'id'));

        console.log(evidencesArray);
        // Inicializar contador
        updateEvidenceCounter();

        // Cargar evidencias existentes al iniciar
        loadExistingEvidences();

        // Abrir modal para agregar evidencia
        $('#add-evidence-btn').click(function() {
            resetEvidenceForm();
            $('#evidenceModal').modal('show');
        });

        // Guardar todas las evidencias
        $('#save-all-evidences').click(function() {
            saveAllEvidences();
        });

        // Función para resetear el formulario
        function resetEvidenceForm() {
            $('#evidence-image').val('');
            $('#evidence-description').val('');
            $('#evidence-area').val('');
            $('#evidence-service-id').val('');
            $('#image-preview').hide();
            $('#no-preview').show();
            $('#preview-img').attr('src', '#');
            $('#file-info').text('');
        }

        // Vista previa de imagen
        $('#evidence-image').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen no debe superar los 5MB');
                    $(this).val('');
                    return;
                }

                // Validar tipo de archivo - SOLO PNG, JPEG, JPG, WEBP
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten imágenes PNG, JPEG, JPG y WebP');
                    $(this).val('');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-img').attr('src', e.target.result);
                    $('#file-info').text(`${file.name} (${formatFileSize(file.size)})`);
                    $('#image-preview').show();
                    $('#no-preview').hide();
                }
                reader.readAsDataURL(file);
            } else {
                $('#image-preview').hide();
                $('#no-preview').show();
            }
        });

        // Formatear tamaño de archivo
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Guardar evidencia
        $('#save-evidence').click(function() {
            // Validar formulario
            const imageFile = $('#evidence-image')[0].files[0];
            const description = $('#evidence-description').val().trim();
            const area = $('#evidence-area').val();
            const serviceId = $('#evidence-service-id').val();

            // Validaciones
            if (!imageFile) {
                alert('Por favor selecciona una imagen');
                $('#evidence-image').focus();
                return;
            }

            if (!description) {
                alert('Por favor ingresa una descripción');
                $('#evidence-description').focus();
                return;
            }

            if (!area) {
                alert('Por favor selecciona un área de visualización');
                $('#evidence-area').focus();
                return;
            }

            // Procesar la imagen
            const reader = new FileReader();
            reader.onload = function(e) {
                const evidenceId = 'evidence_' + evidenceCounter++;
                const serviceName = serviceId ? services[serviceId] : 'Ninguno';

                const evidenceData = {
                    index: evidenceId,
                    id: null, // Será asignado por el servidor
                    order_id: {{ $order->id }},
                    service_id: serviceId,
                    service_name: serviceName,
                    image: e.target.result, // base64
                    description: description,
                    area: area,
                    filename: imageFile.name,
                    filetype: imageFile.type,
                    timestamp: new Date().toISOString()
                };

                // Agregar al array y mostrar en la tabla
                addEvidenceToArray(evidenceData);
                addEvidenceToTable(evidenceData);
                $('#evidenceModal').modal('hide');
                resetEvidenceForm();
            };
            reader.readAsDataURL(imageFile);
        });

        // Agregar evidencia al array
        function addEvidenceToArray(evidenceData) {
            evidencesArray.push(evidenceData);
            console.log('Evidencia agregada al array:', evidenceData);
            console.log('Total de evidencias:', evidencesArray);
        }

        // Remover evidencia del array
        function removeEvidenceFromArray(evidenceIndex) {
            evidencesArray = evidencesArray.filter(evidence => evidence.index !== evidenceIndex);
            console.log('Evidencia removida del array:', evidenceIndex);
            console.log('Total de evidencias:', evidencesArray);
        }

        // Agregar fila a la tabla
        function addEvidenceToTable(evidenceData) {
            const areaNames = {
                'servicio': 'Servicio',
                'notas': 'Notas',
                'recomendaciones': 'Recomendaciones',
                'evidencias': 'Evidencias'
            };

            const areaBadgeClasses = {
                'servicio': 'area-badge-servicio',
                'notas': 'area-badge-notas',
                'recomendaciones': 'area-badge-recomendaciones',
                'evidencias': 'area-badge-evidencias'
            };

            const tableRow = `
            <tr id="evidence-row-${evidenceData.index}">
                <td>
                    <img src="${evidenceData.image}" 
                         class="evidence-thumbnail" 
                         alt="Miniatura"
                         data-bs-toggle="tooltip"
                         title="Click para ver imagen completa"
                         onclick="showFullImage('${evidenceData.index}')">
                </td>
                <td>
                    <div class="fw-semibold">${evidenceData.filename}</div>
                    <small class="text-muted">${formatFileSize(getBase64Size(evidenceData.image))}</small>
                </td>
                <td>${evidenceData.description}</td>
                <td>
                    <span class="badge ${areaBadgeClasses[evidenceData.area]}">
                        ${areaNames[evidenceData.area]}
                    </span>
                </td>
                <td>${evidenceData.service_name}</td>
                <td>
                    <small>${new Date(evidenceData.timestamp).toLocaleDateString()}</small><br>
                    <small class="text-muted">${new Date(evidenceData.timestamp).toLocaleTimeString()}</small>
                </td>
                <td>
                    <button type="button" 
                            class="btn btn-sm btn-danger" 
                            onclick="removeEvidence('${evidenceData.index}')"
                            data-bs-toggle="tooltip"
                            title="Eliminar evidencia">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
        `;

            $('#evidence-tbody').append(tableRow);

            // Inicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Mostrar tabla y ocultar mensaje de no evidencias
            $('#evidence-container').show();
            $('#no-evidence-message').hide();

            updateEvidenceCounter();
        }

        // Calcular tamaño aproximado de base64
        function getBase64Size(base64String) {
            // Eliminar el prefijo data:image/...;base64,
            const base64 = base64String.replace(/^data:image\/\w+;base64,/, '');
            // Calcular tamaño en bytes: (longitud * 3) / 4 - padding
            return Math.floor((base64.length * 3) / 4);
        }

        // Mostrar imagen completa (función global)
        window.showFullImage = function(evidenceIndex) {
            const evidence = evidencesArray.find(e => e.index === evidenceIndex);
            if (evidence) {
                // Crear modal para mostrar imagen completa
                const modalHtml = `
                <div class="modal fade" id="imageModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${evidence.filename}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${evidence.image}" class="img-fluid" alt="${evidence.description}">
                                <div class="mt-3 text-start">
                                    <p class="mb-1"><strong>Descripción:</strong> ${evidence.description}</p>
                                    <p class="mb-1"><strong>Área:</strong> ${evidence.area}</p>
                                    <p class="mb-1"><strong>Servicio:</strong> ${evidence.service_name}</p>
                                    <p class="mb-0"><strong>Archivo:</strong> ${evidence.filename}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                // Remover modal existente si hay uno
                $('#imageModal').remove();
                $('body').append(modalHtml);
                $('#imageModal').modal('show');
            }
        };

        // Remover evidencia (función global)
        window.removeEvidence = function(index) {
            if (confirm('¿Estás seguro de que quieres eliminar esta evidencia?')) {
                $(`#evidence-row-${index}`).remove();
                removeEvidenceFromArray(index);
                updateEvidenceCounter();

                // Si no hay evidencias, mostrar mensaje
                if ($('#evidence-tbody').children().length === 0) {
                    $('#evidence-container').hide();
                    $('#no-evidence-message').show();
                }
            }
        };

        // Limpiar todas las evidencias
        $('#clear-all-evidence').click(function() {
            const evidenceCount = evidencesArray.length;
            if (evidenceCount > 0) {
                if (confirm(
                        `¿Estás seguro de que quieres eliminar todas las ${evidenceCount} evidencias?`
                        )) {
                    $('#evidence-tbody').empty();
                    $('#evidence-container').hide();
                    $('#no-evidence-message').show();
                    evidencesArray = []; // Limpiar el array
                    updateEvidenceCounter();
                    console.log('Todas las evidencias eliminadas');
                }
            } else {
                alert('No hay evidencias para limpiar.');
            }
        });

        // Actualizar contador de evidencias
        function updateEvidenceCounter() {
            const count = evidencesArray.length;
            $('#evidence-counter').text(`${count} evidencia${count !== 1 ? 's' : ''}`);
        }

        // Cargar evidencias existentes
        // Cargar evidencias existentes
        function loadExistingEvidences() {
            // Si ya tenemos evidencias cargadas desde PHP, mostrarlas
            if (evidencesArray && evidencesArray.length > 0) {
                console.log('Cargando evidencias existentes desde PHP:', evidencesArray);

                // Procesar cada evidencia existente
                evidencesArray.forEach((evidence, index) => {
                    // Asignar un índice temporal si no existe
                    if (!evidence.index) {
                        evidence.index = 'existing_evidence_' + index;
                    }

                    // Asegurar que service_name esté presente
                    if (!evidence.service_name && evidence.service_id) {
                        evidence.service_name = services[evidence.service_id] || 'Ninguno';
                    }

                    // Agregar a la tabla
                    addEvidenceToTable(evidence);
                });

                // Actualizar interfaz
                $('#evidence-container').show();
                $('#no-evidence-message').hide();
                updateEvidenceCounter();

                console.log('Evidencias existentes cargadas:', evidencesArray.length);
            } else {
                console.log('No hay evidencias existentes para cargar');
                $('#evidence-container').hide();
                $('#no-evidence-message').show();
            }
        }

        // Guardar todas las evidencias
        function saveAllEvidences() {
            /*if (evidencesArray.length === 0) {
                alert('No hay evidencias para guardar.');
                return;
            }*/

            // Mostrar loading
            const saveBtn = $('#save-all-evidences');
            const originalText = saveBtn.html();
            saveBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

            $.ajax({
                url: '{{ route('report.evidence.store', $order->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    evidences: evidencesArray
                },
                success: function(response) {
                    console.log('Respuesta del servidor al guardar evidencias:', response);
                    if (response.success) {
                        alert('Evidencias guardadas correctamente');
                        // Actualizar los IDs de las evidencias con los del servidor
                        if (response.saved_evidences) {
                            response.saved_evidences.forEach(savedEvidence => {
                                const localEvidence = evidencesArray.find(e =>
                                    e.filename === savedEvidence.filename &&
                                    e.description === savedEvidence.description
                                );
                                if (localEvidence) {
                                    localEvidence.id = savedEvidence.id;
                                }
                            });
                        }
                    } else {
                        alert('Error al guardar evidencias: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error al guardar evidencias. Por favor intenta nuevamente.');
                    console.log('Error:', xhr);
                },
                complete: function() {
                    // Restaurar botón
                    saveBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        // Función para establecer evidencias (cargar datos existentes)
        window.setEvidencesData = function(data) {
            evidencesArray = data;
            // Limpiar tabla y reconstruir filas
            $('#evidence-tbody').empty();
            if (data.length > 0) {
                data.forEach(evidence => {
                    evidence.index = evidence.index || 'evidence_' + evidenceCounter++;
                    addEvidenceToTable(evidence);
                });
                $('#evidence-container').show();
                $('#no-evidence-message').hide();
            } else {
                $('#evidence-container').hide();
                $('#no-evidence-message').show();
            }
            updateEvidenceCounter();
        };

        // Función para obtener todas las evidencias (para usar en AJAX)
        window.getEvidencesData = function() {
            return evidencesArray;
        };

        // Cerrar modal con ESC
        $(document).keydown(function(e) {
            if (e.keyCode === 27 && $('#evidenceModal').is(':visible')) {
                $('#evidenceModal').modal('hide');
            }
        });

        // Resetear formulario cuando se cierra el modal
        $('#evidenceModal').on('hidden.bs.modal', function() {
            resetEvidenceForm();
        });

        // Enter en textarea no envía el formulario
        $('#evidence-description').keydown(function(e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                e.preventDefault();
            }
        });
    });
</script>
