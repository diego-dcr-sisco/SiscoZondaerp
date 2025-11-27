<style>
    .configuration-item {
        border-left: 3px solid #0d6efd;
        transition: all 0.3s ease;
    }

    .configuration-item:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    .config-actions {
        display: flex;
        justify-content: end;
        gap: 10px;
        margin-top: 15px;
    }

    .description-container {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 1rem;
    }
</style>

<!-- Modal para Configurar Servicio -->
<div class="modal fade" id="configureServiceModal" tabindex="-1" aria-labelledby="configureServiceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="configureServiceModalLabel">
                    Configurar Descripción del Servicio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="mb-3 border-bottom fw-bold pb-2">
                    <i class="bi bi-info-circle me-1"></i> Información del Servicio
                </h6>

                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Prefijo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-prefix" value="SRV-001" disabled>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Servicio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-service" value="Mantenimiento Preventivo" disabled>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-type" value="Preventivo" disabled>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Línea de negocio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-bsline" value="Mantenimiento" disabled>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Costo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-cost" value="$150.00" disabled>
                        </div>
                    </div>
                </div>

                <h6 class="mb-3 border-bottom fw-bold pb-2">
                    <i class="bi bi-card-text me-1"></i> Descripción del Servicio
                </h6>

                <div class="description-container">
                    <div id="service-description-editor" class="summernote"></div>
                    <div class="form-text mt-2">
                        Describe los detalles específicos del servicio a realizar.
                    </div>
                </div>

                <input type="hidden" id="service-id" value="1" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="save-description">
                    Guardar Descripción
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variable para almacenar la descripción
    let serviceDescription = '';

    // Inicializar Summernote
    $(document).ready(function() {
        $('#service-description-editor').summernote({
            height: 250,
            lang: 'es-ES',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['table', 'link', 'picture']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['fontsize', ['fontsize']],
            ],
            fontSize: ['8', '10', '12', '14', '16'],
            lineHeights: ['0.25', '0.5', '1', '1.5', '2'],
            callbacks: {
                onChange: function(contents) {
                    serviceDescription = contents;
                }
            }
        });

        // Manejar clic en el botón de guardar
        $("#save-description").on("click", function() {
            saveServiceDescription();
        });

        // Manejar la apertura del modal
        $('#configureServiceModal').on('show.bs.modal', function(event) {
            let service_id = $('#service-id').val();
            let config = services_configuration.find(sc => sc.service_id == service_id);
            $("#service-description-editor").summernote('code', config.description);
        });
    });

    // Función para guardar la descripción
    function saveServiceDescription() {
        const service_id = $('#service-id').val();
        let config = services_configuration.find(sc => sc.service_id == service_id);
        if (config) {
            config.description = serviceDescription;
        } else {
            services_configuration.push({
                service_id: service_id,
                setting_id: null,
                contract_id: null,
                description: serviceDescription
            });
        }
        alert('Descripción guardada correctamente.');
        $('#configureServiceModal').modal('hide');

        console.log(services_configuration); 
    }
</script>
