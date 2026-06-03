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

    .custom-interval-container {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 1rem;
        background: #fff;
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
                            <input type="text" class="form-control form-control-sm" id="serviceModal-prefix"
                                value="SRV-001" disabled>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Servicio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-service"
                                value="Mantenimiento Preventivo" disabled>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-type"
                                value="Preventivo" disabled>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Línea de negocio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-bsline"
                                value="Mantenimiento" disabled>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Costo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" class="form-control form-control-sm" id="serviceModal-cost"
                                value="$150.00" disabled>
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

                <h6 class="mb-3 mt-4 border-bottom fw-bold pb-2">
                    <i class="bi bi-calendar2-week me-1"></i> Intervalo personalizado
                </h6>

                <div class="custom-interval-container">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch"
                            id="custom-interval-enabled">
                        <label class="form-check-label fw-semibold" for="custom-interval-enabled">
                            Repetir este servicio con intervalo personalizado
                        </label>
                    </div>

                    <div id="custom-interval-fields" class="row g-3 d-none">
                        <div class="col-md-4">
                            <label for="custom-interval-start-date" class="form-label">Fecha inicial</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" id="custom-interval-start-date">
                            </div>
                            <div class="form-text">Por defecto toma la fecha programada.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="custom-interval-days" class="form-label">Repetir cada</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="custom-interval-days"
                                    min="1" max="365" step="1" placeholder="Ej. 15">
                                <span class="input-group-text">días</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vista previa</label>
                            <div id="custom-interval-preview" class="small text-muted border rounded p-2 bg-light">
                                Configura fecha y días para ver las próximas fechas.
                            </div>
                        </div>
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

    function getDefaultIntervalStartDate() {
        return $('#programmed-date').val() || $('#programmed_date').val() || $('#startdate').val() || '';
    }

    function formatPreviewDate(date) {
        return date.toLocaleDateString('es-MX', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function updateCustomIntervalPreview() {
        const enabled = $('#custom-interval-enabled').is(':checked');
        const startDateValue = $('#custom-interval-start-date').val();
        const days = parseInt($('#custom-interval-days').val(), 10);
        const preview = $('#custom-interval-preview');

        $('#custom-interval-fields').toggleClass('d-none', !enabled);

        if (!enabled) {
            preview.text('Activa el intervalo personalizado para generar fechas.');
            return;
        }

        if (!startDateValue || !days || days < 1) {
            preview.text('Configura fecha y días para ver las próximas fechas.');
            return;
        }

        const dates = [];
        const currentDate = new Date(startDateValue + 'T00:00:00');

        for (let i = 0; i < 6; i++) {
            dates.push(formatPreviewDate(currentDate));
            currentDate.setDate(currentDate.getDate() + days);
        }

        preview.html(dates.map(date => `<span class="badge text-bg-light border me-1 mb-1">${date}</span>`).join(''));
    }

    function resetCustomInterval(config = {}) {
        const enabled = Boolean(config.custom_interval_enabled);
        const startDate = config.custom_interval_start_date || getDefaultIntervalStartDate();

        $('#custom-interval-enabled').prop('checked', enabled);
        $('#custom-interval-start-date').val(startDate || '');
        $('#custom-interval-days').val(config.custom_interval_days || '');
        updateCustomIntervalPreview();
    }

    // Inicializar Summernote
    $(document).ready(function() {
        $('#service-description-editor').summernote({   
            height: 250,
            lang: 'es-ES',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['fontsize', 'fontname']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['table', 'link']],
            ],
            fontSize: ['8', '10', '12', '14', '16'],
            lineHeights: ['0.25', '0.5', '1', '1.5', '2'],

            cleaner: {
                action: 'both', // 'both' | 'button' | 'paste'
                newline: '<br>', // Formato para saltos de línea
                notStyle: 'position:absolute;top:0;left:0;right:0', // Estilo de notificación
                keepHtml: true, // Activa el modo de "lista blanca" (whitelist)
                keepOnlyTags: ['<p>', '<br>', '<ul>', '<ol>', '<li>', '<a>', '<b>',
                    '<strong>'
                ], // Etiquetas permitidas
                keepClasses: false, // Remueve todas las clases CSS
                badTags: ['style', 'script', 'applet', 'embed', 'noframes',
                    'noscript'
                ], // Etiquetas prohibidas (se eliminan con su contenido)
                badAttributes: ['style', 'start', 'dir',
                    'class'
                ] // Atributos prohibidos (se eliminan de las etiquetas restantes)
            },

            callbacks: {
                onPaste: function(e) {
                    var thisNote = $(this);
                    var updatePaste = function() {
                        // Get the current HTML code FROM the Summernote editor
                        var original = thisNote.summernote('code');
                        var cleaned = original;
                        // Set the cleaned code BACK to the editor
                        thisNote.summernote('code', cleaned);
                    };
                    // Wait for Summernote to process the paste
                    setTimeout(updatePaste, 10);
                },

                onChange: function(contents) {
                    serviceDescription = contents;
                }
            }
        });

        // Manejar clic en el botón de guardar
        $("#save-description").on("click", function() {
            saveServiceDescription();
        });

        $('#custom-interval-enabled, #custom-interval-start-date, #custom-interval-days').on('change input',
            function() {
                updateCustomIntervalPreview();
            });

        // Manejar la apertura del modal
        $('#configureServiceModal').on('show.bs.modal', function(event) {
            let service_id = $('#service-id').val();
            let config = services_configuration.find(sc => sc.service_id == service_id);
            serviceDescription = config?.description || '';
            $("#service-description-editor").summernote('code', serviceDescription);
            resetCustomInterval(config || {});
        });
    });

    // Función para guardar la descripción
    function saveServiceDescription() {
        const service_id = $('#service-id').val();
        const customIntervalEnabled = $('#custom-interval-enabled').is(':checked');
        const customIntervalDays = parseInt($('#custom-interval-days').val(), 10);
        const customIntervalStartDate = $('#custom-interval-start-date').val() || getDefaultIntervalStartDate() || null;

        if (customIntervalEnabled && (!customIntervalStartDate || !customIntervalDays || customIntervalDays < 1)) {
            alert('Configura una fecha inicial y un intervalo válido en días.');
            return;
        }

        let config = services_configuration.find(sc => sc.service_id == service_id);
        if (config) {
            config.description = serviceDescription;
            config.custom_interval_enabled = customIntervalEnabled;
            config.custom_interval_start_date = customIntervalEnabled ? customIntervalStartDate : null;
            config.custom_interval_days = customIntervalEnabled ? customIntervalDays : null;
        } else {
            services_configuration.push({
                service_id: service_id,
                setting_id: null,
                contract_id: null,
                description: serviceDescription,
                custom_interval_enabled: customIntervalEnabled,
                custom_interval_start_date: customIntervalEnabled ? customIntervalStartDate : null,
                custom_interval_days: customIntervalEnabled ? customIntervalDays : null
            });
        }
        alert('Configuración guardada correctamente.');
        $('#configureServiceModal').modal('hide');

        console.log(services_configuration);
    }
</script>
