        <style>
            .configuration-item {
                border-left: 3px solid #0d6efd;
                transition: all 0.3s ease;
            }

            .configuration-item:hover {
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            }

            .day-pill {
                cursor: pointer;
                transition: all 0.2s;
                a
            }

            .day-pill.active {
                background-color: #0d6efd;
                color: white;
            }

            /*.configurations-container {
                max-height: 300px;
                overflow-y: auto;
            }*/
            --- IGNORE --- .modal-content {
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

            .dates-list {
                max-height: 250px;
                overflow-y: auto;
            }

            .date-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem;
                border-bottom: 1px solid #dee2e6;
            }

            .date-item:last-child {
                border-bottom: none;
            }

            .date-actions {
                display: flex;
                gap: 0.5rem;
            }

            .empty-dates {
                text-align: center;
                padding: 1rem;
                color: #6c757d;
                font-style: italic;
            }
        </style>

        <!-- Modal -->
        <div class="modal fade" id="configureServiceModal" tabindex="-1" aria-labelledby="configureServiceModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-light">
                        <h5 class="modal-title" id="configureServiceModalLabel">
                            Configurar Servicio
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
                                    <input type="text" class="form-control" value="SRV-001" disabled>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Servicio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <input type="text" class="form-control" value="Mantenimiento Preventivo"
                                        disabled>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control" value="Preventivo" disabled>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Línea de negocio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                    <input type="text" class="form-control" value="Mantenimiento" disabled>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Costo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="text" class="form-control" value="$150.00" disabled>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3 border-bottom fw-bold pb-2">
                            <i class="bi bi-list-check me-1"></i> Configuraciones del Servicio
                        </h6>

                        <div class="configurations-container mb-4 p-2 border rounded">
                            <div id="configurations-list">
                                <!-- Las configuraciones se agregarán aquí dinámicamente -->
                            </div>
                            <div id="empty-config-state" class="text-center py-4 text-muted">
                                <i class="bi bi-inboxes display-4 d-block mb-2"></i>
                                <p class="mb-1">No hay configuraciones agregadas</p>
                                <small>Agregue una nueva configuración para comenzar</small>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="button" class="btn btn-outline-primary" id="add-configuration">
                                <i class="bi bi-plus-circle me-1"></i> Agregar Nueva Configuración
                            </button>
                        </div>

                        <input type="hidden" id="service-id" value="" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" id="save-configurations">
                            Guardar Configuraciones
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <script>
            $('#configureServiceModal').on('show.bs.modal', function(event) {
                configCounter = 0;
                configDates = {};
                configDescriptions = {};

                const service_id = $('#service-id').val();
                configurations = contract_configurations.filter(c => c.service_id == service_id);

                if (configurations.length != 0) {

                    $("#empty-config-state").hide();

                    configurations.forEach(config => {
                        addConfiguration();

                        configDates = configurations.reduce((acc, curr) => {
                            acc[curr.config_id] = curr.dates || [];
                            return acc;
                        }, {});

                        if (can_renew) {
                            const service = {
                                frequency: config.frequency_id,
                                interval: config.interval_id,
                                days: config.days.filter(d => d !== ''),
                                index: config.config_id
                            };

                            const startDate = $("#startdate").val();
                            const endDate = $("#enddate").val();
                            const dates = createDates(service, startDate, endDate, config.config_id);

                            configDates[config.config_id] = dates.map(d => new Date(d).toISOString());
                        }

                        configDescriptions = configurations.reduce((acc, curr) => {
                            acc[curr.config_id] = curr.description || null;
                            return acc;
                        }, {});

                        $(`#service-frequency-${config.config_id}`).val(config.frequency_id).trigger('change');
                        if (config.frequency_id == 3) {
                            $(`#service-interval-${config.config_id}`).val(config.interval_id).trigger(
                                'change');
                        }
                        $(`#service-days-${config.config_id}`).val(config.days);
                        if (config.frequency_id == 1) {
                            $(`#service-date-${config.config_id}`).val(config.days);
                        }


                        // Cargar fechas
                        if (configDates[config.config_id] && configDates[config.config_id].length > 0) {
                            $(`#datesCollapse${config.config_id}`).addClass('show');
                            $(`#accordion-btn${config.config_id}`).attr('aria-expanded', 'true');
                            //$(`#dates-list-${config.config_id}`).html(generateDatesList(config.config_id));
                            updateDatesList(config.config_id);
                        }
                        // Cargar descripción
                        if (configDescriptions[config.config_id]) {
                            $(`#config-summernote${config.config_id}`).summernote('code', configDescriptions[
                                config.config_id]);
                        }

                        configCounter = config.config_id;
                    });
                } else {
                    configurations = [];
                    $("#empty-config-state").show();
                }
            });

            // Manejar clic en el botón de agregar configuración
            $("#add-configuration").on("click", function() {
                addConfiguration();
            });

            // Manejar clic en el botón de guardar
            $("#save-configurations").on("click", function() {
                saveAllConfigurations();
            });

            // Configurar event listeners para los day-pills de días de la semana
            $(document).on("click", ".day-pill", function(e) {
                if ($(this).closest('[id^="week-days-selector"]').length) {
                    $(this).toggleClass("active");
                    if ($(this).hasClass("active")) {
                        $(this).removeClass("bg-secondary").addClass("bg-primary");
                    } else {
                        $(this).removeClass("bg-primary").addClass("bg-secondary");
                    }

                    // Actualizar el campo de días
                    const configId = $(this).closest('.configuration-item').data("config-id");
                    updateDaysInputFromPills(configId, 'week-days');
                }
            });

            function addConfiguration() {
                configCounter++;
                const configId = configCounter;

                // Inicializar array de fechas para esta configuración
                configDates[configId] = [];

                // Ocultar estado vacío si existe
                const emptyState = $("#empty-config-state");
                if (emptyState.length) emptyState.hide();

                const configHTML = `
                        <div class="configuration-item mb-3 p-3 bg-light rounded" data-config-id="${configId}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-primary mb-0">Configuración ${configId}</h6>
                                <button type="button" class="btn-close" aria-label="Eliminar" onclick="removeConfiguration(${configId})"></button>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Frecuencia</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-arrow-repeat"></i></span>
                                        <select class="form-select service-frequency" id="service-frequency-${configId}">
                                            <option value="0">Seleccione</option>
                                            ${generateFrequencyOptions()}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8 mb-3 d-none" id="interval-field-${configId}">
                                    <label class="form-label">Intervalo</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                        <select class="form-select service-interval" id="service-interval-${configId}">
                                            <option value="0">Seleccione</option>
                                            ${generateIntervalOptions()}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8 mb-3" id="days-field-${configId}">
                                    <label class="form-label" id="days-label-${configId}">Días</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-week"></i></span>
                                        <input type="text" class="form-control service-days" id="service-days-${configId}">
                                    </div>
                                    <div class="form-text" id="days-info-${configId}">
                                        <i class="bi bi-info-circle me-1"></i> Ingrese los días separados por comas (ej: 1,15,28)
                                    </div>
                                    
                                    <!-- Selector de días de la semana -->
                                    <div class="mt-2 d-none" id="week-days-selector-${configId}">
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="day-pill badge bg-secondary" data-day="L">Lunes</span>
                                            <span class="day-pill badge bg-secondary" data-day="M">Martes</span>
                                            <span class="day-pill badge bg-secondary" data-day="I">Miércoles</span>
                                            <span class="day-pill badge bg-secondary" data-day="J">Jueves</span>
                                            <span class="day-pill badge bg-secondary" data-day="V">Viernes</span>
                                            <span class="day-pill badge bg-secondary" data-day="S">Sábado</span>
                                            <span class="day-pill badge bg-secondary" data-day="D">Domingo</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Selector de días del mes -->
                                    <div class="mt-2 d-none" id="month-days-selector-${configId}">
                                        <div class="d-flex flex-wrap gap-1">
                                            ${generateMonthDays(configId)}
                                        </div>
                                    </div>

                                    <!-- Selector de fecha única -->
                                    <div class="mt-2 d-none" id="single-date-selector-${configId}">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" class="form-control service-date" id="service-date-${configId}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="config-actions">
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="clearAllDates(${configId})">
                                    <i class="bi bi-trash-fill me-1"></i> Eliminar todas las fechas
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="saveConfiguration(${configId})">
                                    <i class="bi bi-check-circle-fill me-1"></i> Guardar configuración
                                </button>
                            </div>
                            
                            <!-- Collapse para fechas -->
                            <div class="accordion my-3" id="accordionDates">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                    <button class="accordion-button" id="accordion-btn${configId}" type="button" data-bs-toggle="collapse" aria-expanded="false" onclick="handleAccordion(this, ${configId})">
                                        <i class="bi bi-calendar3 me-1"></i> Ver fechas generadas (${configDates[configId].length})
                                    </button>
                                    </h2>
                                    <div id="datesCollapse${configId}" class="accordion-collapse collapse show" data-bs-parent="#accordionDates">
                                        <div class="accordion-body">
                                            <h6 class="mb-2 fw-bold">Fechas generadas</h6>
                                        <div class="dates-list" id="dates-list-${configId}">
                                            ${generateDatesList(configId)}
                                        </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Editor de texto enriquecido para descripción -->
                            <div class="mb-3">
                                <label class="form-label">Descripción del servicio</label>
                                <div id="config-summernote${configId}" class="summernote"></div>
                                <div class="form-text">
                                    Describe los detalles específicos de esta configuración del servicio.
                                </div>
                            </div>
                        </div>
                    `;

                $("#configurations-list").append(configHTML);

                initializeSummernote(configId);

                // Configurar eventos con jQuery
                $(`#service-frequency-${configId}`).on("change", function() {
                    handleFrequencyChange(configId);
                });

                $(`#service-interval-${configId}`).on("change", function() {
                    handleIntervalChange(configId);
                });

                $(`#service-days-${configId}`).on("input", function() {
                    validateDaysInput(configId);
                });

                $(`#service-date-${configId}`).on("change", function() {
                    $(`#service-days-${configId}`).val($(this).val());
                });

                $(`#datesCollapse${configId}`).removeClass('show');

                // Inicializar manualmente el componente Collapse para el nuevo elemento
                const collapseElement = document.getElementById(`datesCollapse${configId}`);
                if (collapseElement) {
                    new bootstrap.Collapse(collapseElement, {
                        toggle: false
                    });
                }
            }

            function initializeSummernote(configId) {
                $(`#config-summernote${configId}`).summernote({
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
                        onPaste: function(e) {
                            var thisNote = $(this);
                            var updatePaste = function(someNote) {
                                var original = someNote.code();
                                var cleaned = cleanPaste(original);
                                someNote.code('').code(cleaned);
                            };

                            // Espera a que Summernote procese el pegado
                            setTimeout(function() {
                                updatePaste(thisNote.summernote('code'));
                            }, 10);
                        },

                        onChange: function(contents) {
                            configDescriptions[configId] = contents;
                        }
                    }

                });
            }

            function handleAccordion(buttonElement, configId) {
                const collapseElement = document.getElementById(`datesCollapse${configId}`);
                if (collapseElement) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
                    const exProp = buttonElement.getAttribute('aria-expanded')
                    if (bsCollapse) {
                        bsCollapse.toggle();
                        buttonElement.setAttribute('aria-expanded', exProp == 'true' ? 'false' : 'true');
                    }
                }
            }

            function generateFrequencyOptions() {
                return frequencies.map(f => `<option value="${f.id}">${f.name}</option>`).join('');
            }

            function generateIntervalOptions() {
                return intervals.map((interval, index) => `<option value="${index + 1}">${interval}</option>`).join('');
            }

            function generateMonthDays(configId) {
                let html = '';
                for (let i = 1; i <= 31; i++) {
                    html +=
                        `<span class="day-pill badge bg-secondary" data-day="${i}" onclick="toggleDayPill(this, ${configId})">${i}</span>`;
                }
                return html;
            }

            function generateDatesList(configId) {
                if (!configDates[configId] || configDates[configId].length === 0) {
                    return '<div class="empty-dates">No hay fechas generadas</div>';
                }

                return configDates[configId].map((date, index) => `
                    <div class="date-item">
                        <span id="date${index}-config${configId}">${formatDate(date)}</span>
                        <div class="date-actions">
                            <button class="btn btn-sm btn-secondary" onclick="editDate(${configId}, ${index})">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDate(${configId}, ${index})">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            function formatDate(date) {
                // Asegurarse de que date es un objeto Date válido
                const dateObj = new Date(date);
                if (isNaN(dateObj.getTime())) {
                    return 'Fecha inválida';
                }

                return dateObj.toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function toggleDayPill(pill, configId) {
                $(pill).toggleClass('active');
                if ($(pill).hasClass('active')) {
                    $(pill).removeClass('bg-secondary').addClass('bg-primary');
                } else {
                    $(pill).removeClass('bg-primary').addClass('bg-secondary');
                }

                // Actualizar el campo de días
                updateDaysInputFromPills(configId, 'month-days');
            }

            function removeConfiguration(configId) {
                configCounter--;
                $(`[data-config-id="${configId}"]`).remove();
                delete configDates[configId];

                // Mostrar estado vacío si no hay configuraciones
                if ($("#configurations-list").children().length === 0) {
                    $("#empty-config-state").show();
                }
            }

            function handleFrequencyChange(configId) {
                const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
                const intervalField = $(`#interval-field-${configId}`);
                const daysField = $(`#days-field-${configId}`);
                const daysLabel = $(`#days-label-${configId}`);
                const daysInfo = $(`#days-info-${configId}`);
                const daysInput = $(`#service-days-${configId}`);

                // Resetear campo de días
                daysInput.val('');
                daysInput.prop('disabled', false);

                // Ocultar todos los selectores primero
                $(`#week-days-selector-${configId}`).addClass('d-none');
                $(`#month-days-selector-${configId}`).addClass('d-none');
                $(`#single-date-selector-${configId}`).addClass('d-none');

                // Mostrar/ocultar campo de intervalo según la frecuencia
                if (frequency_id === 3) { // Mensual
                    intervalField.removeClass('d-none');
                } else {
                    intervalField.addClass('d-none');
                }

                // Configurar según la frecuencia
                switch (frequency_id) {
                    case 1: // Día - Input de fecha
                        daysLabel.text('Fecha');
                        daysInfo.html('<i class="bi bi-info-circle me-1"></i> Seleccione una fecha específica');
                        $(`#single-date-selector-${configId}`).removeClass('d-none');
                        break;

                    case 2: // Semanal - Días de la semana
                        daysLabel.text('Días de la semana');
                        daysInfo.html(
                            '<i class="bi bi-info-circle me-1"></i> Seleccione los días de la semana (L, M, I, J, V, S, D)'
                        );
                        $(`#week-days-selector-${configId}`).removeClass('d-none');
                        break;

                    case 3: // Mensual
                        handleIntervalChange(configId);
                        break;

                    case 4: // Anual - Todo el año
                        daysLabel.text('Días');
                        daysInfo.html(
                            '<i class="bi bi-info-circle me-1"></i> Todo el año (seleccionado automáticamente)');
                        daysInput.val('1-365');
                        daysInput.prop('disabled', true);
                        break;

                    case 5: // Por periodo - Similar a mensual pero con diferentes opciones
                        daysLabel.text('Días');
                        daysInfo.html('<i class="bi bi-info-circle me-1"></i> Ingrese los días del periodo');
                        break;

                    default: // Valor por defecto
                        daysLabel.text('Días');
                        daysInfo.html('<i class="bi bi-info-circle me-1"></i> Ingrese los días separados por comas');
                }
            }

            function handleIntervalChange(configId) {
                const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
                const interval_id = parseInt($(`#service-interval-${configId}`).val());
                const daysLabel = $(`#days-label-${configId}`);
                const daysInfo = $(`#days-info-${configId}`);
                const daysInput = $(`#service-days-${configId}`);

                // Solo aplica para frecuencia mensual (id: 3)
                if (frequency_id !== 3) return;

                // Resetear campo de días
                daysInput.val('');
                daysInput.prop('disabled', false);

                // Ocultar todos los selectores primero
                $(`#week-days-selector-${configId}`).addClass('d-none');
                $(`#month-days-selector-${configId}`).addClass('d-none');

                if (interval_id === 1) {
                    // Intervalo "Por día": Días del mes (1-31)
                    daysLabel.text('Días del mes');
                    daysInfo.html('<i class="bi bi-info-circle me-1"></i> Seleccione los días del mes (1-31)');
                    $(`#month-days-selector-${configId}`).removeClass('d-none');
                } else {
                    // Otros intervalos: Días de la semana
                    daysLabel.text('Días de la semana');
                    daysInfo.html(
                        '<i class="bi bi-info-circle me-1"></i> Seleccione los días de la semana (L, M, I, J, V, S, D)'
                    );
                    $(`#week-days-selector-${configId}`).removeClass('d-none');
                }
            }

            function validateDaysInput(configId) {
                const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
                const interval_id = parseInt($(`#service-interval-${configId}`).val());
                const daysInput = $(`#service-days-${configId}`);
                let value = daysInput.val().toUpperCase();

                // Validar según el tipo de entrada esperada
                if (frequency_id === 2 || (frequency_id === 3 && interval_id !== 1)) {
                    // Solo permitir letras L,M,I,J,V,S,D y comas
                    value = value.replace(/[^LMIJVSD,]/g, '');

                    // Validar formato (solo letras válidas separadas por comas)
                    const days = value.split(',');
                    for (let day of days) {
                        if (day && !['L', 'M', 'I', 'J', 'V', 'S', 'D'].includes(day)) {
                            value = value.replace(day, '');
                        }
                    }
                } else if (frequency_id === 3 && interval_id === 1) {
                    // Solo permitir números del 1-31 y comas
                    value = value.replace(/[^0-9,]/g, '');

                    // Validar que los números estén entre 1-31
                    const days = value.split(',');
                    for (let day of days) {
                        const num = parseInt(day);
                        if (day && (isNaN(num) || num < 1 || num > 31)) {
                            value = value.replace(day, '');
                        }
                    }
                }

                daysInput.val(value);
            }

            function updateDaysInputFromPills(configId, selectorType) {
                const activePills = $(`#${selectorType}-selector-${configId} .day-pill.active`);
                const days = activePills.map(function() {
                    return $(this).data('day');
                }).get().join(',');
                $(`#service-days-${configId}`).val(days);
            }

            // Función para guardar una configuración individual
            function saveConfiguration(configId) {
                const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
                const frequency = frequencies.find(f => f.id === frequency_id);
                const interval_id = parseInt($(`#service-interval-${configId}`).val());
                const interval = interval_id > 0 ? intervals[interval_id - 1] : '';
                const days = $(`#service-days-${configId}`).val();

                // Validar campos obligatorios
                if (frequency_id === 0) {
                    alert('Por favor seleccione una frecuencia para esta configuración');
                    return;
                }

                if (frequency_id !== 4 && days.trim() === '') {
                    alert('Por favor complete los días para esta configuración');
                    return;
                }

                // Crear objeto de servicio
                const service = {
                    frequency: frequency_id,
                    interval: interval_id,
                    days: days.split(',').filter(d => d !== ''),
                    index: configId
                };



                // Obtener fechas de inicio y fin (en un caso real, estos vendrían de inputs del usuario)
                const startDate = $("#startdate").val();
                const endDate = $("#enddate").val();

                if (startDate == "" || endDate == "") {
                    alert("Incluye la fecha de inicio y/o finalización del contrato");
                    return;
                }


                // Llamar a la función createDates
                const dates = createDates(service, startDate, endDate, configId);

                // Guardar fechas para esta configuración
                configDates[configId] = dates;

                // Actualizar la lista de fechas
                updateDatesList(configId);

                // Mostrar resultado
                alert(`Configuración ${configId} guardada. Se generaron ${dates.length} fechas.`);
            }

            // Función para actualizar la lista de fechas
            function updateDatesList(configId) {
                const datesListElement = $(`#dates-list-${configId}`);
                if (datesListElement.length) {
                    datesListElement.html(generateDatesList(configId));
                }

                // Actualizar el texto del botón del collapse
                const collapseButton = $(`#accordion-btn${configId}`);
                if (collapseButton.length) {
                    collapseButton.html(
                        `<i class="bi bi-calendar3 me-1"></i> Ver fechas generadas (${configDates[configId].length})`
                    );
                }
            }

            // Función para editar una fecha
            function editDate(configId, dateIndex) {
                const currentDate = new Date(configDates[configId][dateIndex]);
                // Formatear la fecha para el input type="date" (YYYY-MM-DD)
                const formattedDate = currentDate.toISOString().split('T')[0];
                const newDate = prompt('Editar fecha:', formattedDate);

                if (newDate) {
                    // Convertir la nueva fecha a objeto Date y almacenarla
                    const newDateObj = new Date(newDate + "T00:00:00");
                    if (!isNaN(newDateObj.getTime())) {
                        configDates[configId][dateIndex] = newDateObj;
                        $(`#date${dateIndex}-config${configId}`).text(formatDate(newDateObj));
                    } else {
                        alert('La fecha ingresada no es válida.');
                    }
                }
            }

            // Función para eliminar una fecha
            function deleteDate(configId, dateIndex) {
                if (confirm('¿Está seguro de que desea eliminar esta fecha?')) {
                    configDates[configId].splice(dateIndex, 1);
                    updateDatesList(configId);
                }
            }

            function clearAllDates(configId) {
                if (confirm('¿Está seguro de que desea eliminar TODAS las fechas de esta configuración?')) {
                    // Limpiar el array de fechas
                    configDates[configId] = [];
                    updateDatesList(configId);
                    alert('Todas las fechas han sido eliminadas');
                }
            }

            // Función createDates proporcionada
            function createDates(service, startDate, endDate, configId) {
                var new_dates = [];
                switch (service.frequency) {
                    case 1:
                        var new_date = $(`#service-date-${configId}`).val() ? new Date($(`#service-date-${configId}`)
                            .val() + "T00:00:00") : null;
                        new_date ? new_dates.push(new_date) : new_dates = [];
                        break;
                    case 2:
                        new_dates = generateDatesByLetter(startDate, endDate, service.days);
                        break;
                    case 3:
                        if (service.interval > 0) {
                            new_dates =
                                service.interval == 1 ?
                                generateDatesByNumber(
                                    startDate,
                                    endDate,
                                    service.days.map(Number)
                                ) :
                                generateDatesByInterval(
                                    startDate,
                                    endDate,
                                    service.days,
                                    service.interval - 1
                                );
                        } else {
                            alert(
                                "El intervalo seleccionado para el servicio " +
                                service.index +
                                " es incorrecto"
                            );
                        }
                        break;
                    case 4:
                        new_dates = getAllDatesBetween(startDate, endDate);
                        break;
                    case 5:
                        new_dates = generateDatesByPeriod(startDate, endDate, service.days);
                        break;
                    default:
                        alert("La frecuencia no se encuentra en la lista de opciones");
                        break;
                }

                return new_dates;
            }

            // Funciones auxiliares (implementaciones básicas)
            function getAllDatesBetween(startDate, endDate) {
                const dates = [];
                const currentDate = new Date(startDate);

                while (currentDate <= endDate) {
                    dates.push(new Date(currentDate));
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                return dates;
            }

            function generateDatesByLetter(startDate, endDate, days) {
                const dates = [];
                const dayMap = {
                    'L': 1,
                    'M': 2,
                    'I': 3,
                    'J': 4,
                    'V': 5,
                    'S': 6,
                    'D': 0
                };
                const currentDate = new Date(startDate);

                while (currentDate <= endDate) {
                    const dayOfWeek = currentDate.getDay();
                    const dayLetter = Object.keys(dayMap).find(key => dayMap[key] === dayOfWeek);

                    if (days.includes(dayLetter)) {
                        dates.push(new Date(currentDate));
                    }

                    currentDate.setDate(currentDate.getDate() + 1);
                }

                return dates;
            }

            function generateDatesByNumber(startDate, endDate, days) {
                const dates = [];
                const currentDate = new Date(startDate);

                while (currentDate <= endDate) {
                    const dayOfMonth = currentDate.getDate();

                    if (days.includes(dayOfMonth)) {
                        dates.push(new Date(currentDate));
                    }

                    currentDate.setDate(currentDate.getDate() + 1);
                }

                return dates;
            }

            function generateDatesByInterval(startDate, endDate, days, interval) {
                // Implementación básica para ejemplo
                const dates = [];
                const currentDate = new Date(startDate);

                while (currentDate <= endDate) {
                    dates.push(new Date(currentDate));
                    // Saltar según el intervalo
                    currentDate.setDate(currentDate.getDate() + (interval * 7));
                }

                return dates;
            }

            function generateDatesByPeriod(startDate, endDate, days) {
                // Implementación básica para ejemplo
                return [new Date(startDate)];
            }

            function saveAllConfigurations() {
                const configElements = $('.configuration-item');
                const service_id = $('#service-id').val();

                contract_configurations = contract_configurations.filter(c => c.service_id != service_id);

                configElements.each(function() {
                    const configId = $(this).data('config-id');
                    const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
                    const frequency = frequencies.find(f => f.id === frequency_id);
                    const interval_id = parseInt($(`#service-interval-${configId}`).val());
                    const interval = interval_id > 0 ? intervals[interval_id - 1] : '';
                    const days = $(`#service-days-${configId}`).val();

                    if (frequency_id !== 0 && days.trim() !== '') {
                        const existingConfig = configurations.find(c => c.config_id == configId && c.service_id ==
                            service_id);
                        if (existingConfig) {
                            // Si ya existe, actualizar
                            existingConfig.frequency = frequency.name;
                            existingConfig.frequency_id = frequency_id;
                            existingConfig.interval = interval;
                            existingConfig.interval_id = interval_id;
                            existingConfig.days = [days];
                            existingConfig.dates = configDates[configId] || [];
                            existingConfig.description = configDescriptions[configId] || null;
                        } else {
                            configurations.push({
                                config_id: configId,
                                setting_id: null, // Aquí se podría asignar un ID si es necesario
                                service_id: $('#service-id')
                                    .val(), // Aquí se podría asignar un ID si es necesario
                                frequency: frequency.name,
                                frequency_id: frequency_id,
                                interval: interval,
                                interval_id: interval_id,
                                days: [days],
                                dates: configDates[configId] || [],
                                description: configDescriptions[configId] || null
                            });
                        }
                    }
                });

                contract_configurations = contract_configurations.concat(configurations);

                // Aquí se enviarían todas las configuraciones al servidor
                //console.log('Configuraciones a guardar:', configurations);
                alert(`Se guardaron ${configurations.length} configuración(es) correctamente`);

                $('#contract-configurations').val(JSON.stringify(contract_configurations));
                // Cerrar el modal después de guardar
                $('#configureServiceModal').modal('hide');
            }
        </script>
