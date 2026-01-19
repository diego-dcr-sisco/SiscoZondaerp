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
        align-self: auto;
    }

    .day-pill.active {
        background-color: #0d6efd;
        color: white;
    }

    /*.configurations-container {
                max-height: 300px;
                overflow-y: auto;
            }*/
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
                            <input type="text" class="form-control" id="serviceModal-prefix" value="SRV-001"
                                disabled>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Servicio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text" class="form-control" id="serviceModal-service"
                                value="Mantenimiento Preventivo" disabled>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                            <input type="text" class="form-control" id="serviceModal-type" value="Preventivo"
                                disabled>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Línea de negocio</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                            <input type="text" class="form-control" id="serviceModal-bsline" value="Mantenimiento"
                                disabled>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Costo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="text" class="form-control" id="serviceModal-cost" value="$150.00" disabled>
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
        configDates = {};
        configDescriptions = {};

        const service_id = $('#service-id').val();
        //setServiceVisualData(service_id);

        // Reiniciar configurations solo para este servicio
        configurations = contract_configurations.filter(c => c.service_id == service_id);

        console.log('Service ID:', service_id);
        console.log('Contract Configurations:', contract_configurations);
        console.log('Filtered Configurations:', configurations);

        // Inicializar configCounter con el máximo ID existente para evitar colisiones
        configCounter = configurations.length > 0 ?
            Math.max(...configurations.map(c => c.config_id)) :
            0;

        if (configurations.length != 0) {
            $("#empty-config-state").hide();

            configurations.forEach(config => {
                // Establecer el configCounter al ID específico de esta configuración ANTES de llamar a addConfiguration
                configCounter = config.config_id - 1;
                addConfiguration();

                // Cargar fechas desde las órdenes existentes MANTENIENDO IDs
                const datesFromOrders = config.dates || (config.orders ? config.orders.map(order =>
                    order
                    .programmed_date) : []);

                //  Asignar directamente al config_id específico
                configDates[config.config_id] = datesFromOrders;

                // Mover can_renew fuera del forEach si es necesario
                // (depende de la lógica de negocio)
                if (can_renew) {
                    const service = {
                        frequency: config.frequency_id,
                        interval: config.interval_id,
                        days: config.days.filter(d => d !== ''),
                        index: config.config_id
                    };

                    const startDate = $("#startdate").val();
                    const endDate = $("#enddate").val();

                    // Verificar que las fechas existan
                    if (startDate && endDate) {
                        const dates = createDates(service, startDate, endDate, config.config_id);
                        configDates[config.config_id] = dates.map(d => new Date(d).toISOString());
                    }
                }

                // Mover configDescriptions fuera del forEach
                if (config.description) {
                    configDescriptions[config.config_id] = config.description;
                }

                // Cargar valores en los formularios
                $(`#service-frequency-${config.config_id}`).val(config.frequency_id).trigger('change');
                if (config.frequency_id == 3 || config.frequency_id == 5) {
                    $(`#service-interval-${config.config_id}`).val(config.interval_id).trigger('change');
                }
                $(`#service-days-${config.config_id}`).val(config.days);
                
                // Cargar fecha para quincenal o día específico
                if (config.frequency_id == 1 || config.frequency_id == 5) {
                    if (config.frequency_id == 5 && config.interval_id == 7) {
                        // Para quincenal, cargar la fecha directamente
                        if (config.days && config.days[0]) {
                            // Si viene con prefijo QUINCENAL_, extraer la fecha
                            const dateValue = config.days[0].startsWith('QUINCENAL_') ? 
                                config.days[0].replace('QUINCENAL_', '') : 
                                config.days[0];
                            $(`#service-date-${config.config_id}`).val(dateValue);
                        } else if (config.quincenal_start_date) {
                            $(`#service-date-${config.config_id}`).val(config.quincenal_start_date);
                        }
                    } else if (config.frequency_id == 1) {
                        $(`#service-date-${config.config_id}`).val(config.days);
                    }
                }

                // Guardar referencia a config.orders en configurations local
                const localConfig = configurations.find(c => c.config_id === config.config_id);
                if (localConfig && config.orders) {
                    localConfig.orders = config.orders;
                }

                // Cargar fechas y órdenes después de que el DOM esté listo
                setTimeout(() => {
                    if (configDates[config.config_id] && configDates[config.config_id].length >
                        0) {
                        updateDatesList(config.config_id);
                    }

                    // Cargar órdenes MANTENIENDO IDs existentes
                    console.log(`Config ${config.config_id} orders:`, config.orders);
                    if (config.orders && config.orders.length > 0) {
                        console.log(
                            `Actualizando tabla de órdenes para config ${config.config_id}`);
                        updateOrdersTable(config.config_id, config.orders);

                        // Expandir el acordeón DESPUÉS de actualizar la tabla
                        $(`#ordersCollapse${config.config_id}`).addClass('show');
                        $(`#orders-accordion-btn${config.config_id}`).attr('aria-expanded',
                            'true');
                    } else {
                        console.log(`Config ${config.config_id} no tiene órdenes o está vacío`);
                    }
                }, 300); // Aumentar el timeout para asegurar que el DOM esté listo

                // Cargar descripción después de inicializar Summernote
                setTimeout(() => {
                    if (configDescriptions[config.config_id]) {
                        $(`#config-summernote${config.config_id}`).summernote('code',
                            configDescriptions[config.config_id]);
                    }
                }, 400);
            });

            // Actualizar configCounter al máximo ID al final
            configCounter = Math.max(...configurations.map(c => c.config_id));

            // Mover configDescriptions fuera del forEach
            configDescriptions = configurations.reduce((acc, curr) => {
                acc[curr.config_id] = curr.description || null;
                return acc;
            }, {});

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

    /*function setServiceVisualData(service_id) {
        var contain_ss = contain_selected_services.find(contain_service => contain_service.id == service_id);
        $('#serviceModal-prefix').val(prefixes[contain_ss.prefix]);
        $('#serviceModal-service').val(contain_ss.name);
        $('#serviceModal-type').val(contain_ss.type);
        $('#serviceModal-bsline').val(contain_ss.line);
        $('#serviceModal-cost').val(contain_ss.cost);
    }*/

    function addConfiguration() {
        configCounter++;
        const configId = configCounter;

        // Inicializar array de fechas y órdenes para esta configuración
        configDates[configId] = [];

        // Ocultar estado vacío si existe
        const emptyState = $("#empty-config-state");
        if (emptyState.length) emptyState.hide();

        const configHTML = `
            <div class="configuration-item mb-3 p-3 bg-light rounded" data-config-id="${configId}" >
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
                
                <!-- Botón para agregar fecha manualmente -->
                <div class="mb-3">
                    <button class="btn btn-sm btn-outline-primary" onclick="addManualDate(${configId})">
                        <i class="bi bi-plus-circle me-1"></i> Agregar fecha manualmente
                    </button>
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

                <!-- Collapse para órdenes -->
                <div class="accordion my-3" id="accordionOrders">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                        <button class="accordion-button" id="orders-accordion-btn${configId}" type="button" data-bs-toggle="collapse" aria-expanded="false" onclick="handleOrdersAccordion(this, ${configId})">
                            <i class="bi bi-list-check me-1"></i> Ver órdenes generadas (<span id="orders-count-${configId}">0</span>)
                        </button>
                        </h2>
                        <div id="ordersCollapse${configId}" class="accordion-collapse collapse show" data-bs-parent="#accordionOrders">
                            <div class="accordion-body">
                                <h6 class="mb-2 fw-bold">Órdenes de servicio</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Fecha Programada</th>
                                                <th>Estado</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="orders-table-${configId}">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No hay órdenes generadas</td>
                                            </tr>
                                        </tbody>
                                    </table>
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

        // Configurar execution_frequency_ideventos con jQuery
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
            const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
            const interval_id = parseInt($(`#service-interval-${configId}`).val());
            
            if (frequency_id === 1) {
                // Para frecuencia diaria
                $(`#service-days-${configId}`).val($(this).val());
            } else if (frequency_id === 5 && interval_id === 7) {
                // Para quincenal - guardar solo la fecha sin prefijo
                $(`#service-days-${configId}`).val($(this).val());
            }
        });

        $(`#datesCollapse${configId}`).removeClass('show');
        $(`#ordersCollapse${configId}`).removeClass('show');

        // Inicializar manualmente los componentes Collapse para el nuevo elemento
        const datesCollapseElement = document.getElementById(`datesCollapse${configId}`);
        const ordersCollapseElement = document.getElementById(`ordersCollapse${configId}`);

        if (datesCollapseElement) {
            new bootstrap.Collapse(datesCollapseElement, {
                toggle: false
            });
        }
        if (ordersCollapseElement) {
            new bootstrap.Collapse(ordersCollapseElement, {
                toggle: false
            });
        }
    }

    // Función para agregar fecha manualmente
    function addManualDate(configId) {
        const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
        const interval_id = parseInt($(`#service-interval-${configId}`).val());
        
        // Si es quincenal, mostrar mensaje especial
        if (frequency_id === 5 && interval_id === 7) {
            if (confirm('Esta configuración es quincenal. ¿Desea agregar una fecha adicional además de las fechas generadas automáticamente?')) {
                const today = new Date().toISOString().split('T')[0];
                const newDate = prompt('Ingrese la fecha adicional (YYYY-MM-DD):', today);
                
                if (newDate) {
                    handleManualDateInput(configId, newDate);
                }
            }
            return;
        }
        
        // Código normal para otras frecuencias
        const today = new Date().toISOString().split('T')[0];
        const newDate = prompt('Ingrese la fecha (YYYY-MM-DD):', today);
        
        if (newDate) {
            handleManualDateInput(configId, newDate);
        }
    }
    
    function handleManualDateInput(configId, newDate) {
        const dateObj = new Date(newDate + 'T00:00:00');
        if (!isNaN(dateObj.getTime())) {
            // Inicializar configDates[configId] si no existe
            if (!configDates[configId]) {
                configDates[configId] = [];
            }

            // Verificar si la fecha ya existe
            const dateExists = configDates[configId].some(existingDate => {
                const existingDateStr = new Date(existingDate).toISOString().split('T')[0];
                return existingDateStr === newDate;
            });

            if (!dateExists) {
                // Agregar la fecha en formato ISO
                const isoDate = dateObj.toISOString();
                configDates[configId].push(isoDate);

                // Buscar configuración existente
                let config = configurations.find(c => c.config_id === configId);
                if (!config) {
                    config = {
                        config_id: configId,
                        service_id: parseInt($('#service-id').val()),
                        orders: []
                    };
                    configurations.push(config);
                }

                if (!config.orders) {
                    config.orders = [];
                }

                // Crear nueva orden con ID temporal único
                const newOrder = {
                    id: `temp_manual_${configId}_${Date.now()}`,
                    folio: null,
                    programmed_date: isoDate,
                    status_id: 1,
                    status_name: 'Pendiente',
                    url: null
                };

                config.orders.push(newOrder);

                // Actualizar la interfaz
                updateDatesList(configId);
                updateOrdersTable(configId, config.orders);

                // Expandir los acordeones para mostrar los cambios
                $(`#ordersCollapse${configId}`).addClass('show');
                $(`#orders-accordion-btn${configId}`).attr('aria-expanded', 'true');

                showSuccessMessage('Fecha y orden agregadas correctamente');
            } else {
                showErrorMessage('La fecha ya existe en la lista');
            }
        } else {
            showErrorMessage('Fecha inválida. Use el formato YYYY-MM-DD');
        }
    }

    function initializeSummernote(configId) {
        $(`#config-summernote${configId}`).summernote({
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

    function handleOrdersAccordion(buttonElement, configId) {
        const collapseElement = document.getElementById(`ordersCollapse${configId}`);
        if (collapseElement) {
            const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
            const exProp = buttonElement.getAttribute('aria-expanded')
            if (bsCollapse) {
                bsCollapse.toggle();
                buttonElement.setAttribute('aria-expanded', exProp == 'true' ? 'false' : 'true');
            }
        }
    }

    // Función para actualizar la tabla de órdenes
    function updateOrdersTable(configId, orders) {
        const ordersTable = $(`#orders-table-${configId}`);
        const ordersCount = $(`#orders-count-${configId}`);

        if (!orders || orders.length === 0) {
            ordersTable.html('<tr><td colspan="4" class="text-center text-muted">No hay órdenes generadas</td></tr>');
            ordersCount.text('0');
            return;
        }

        ordersCount.text(orders.length);

        const ordersHTML = orders.map(order => `
                <tr>
                    <td>${order.folio || 'Sin folio'}</td>
                    <td>${formatDate(order.programmed_date)}</td>
                    <td>
                        <span class="fw-bold ${getStatusBadgeClass(order.status_id)}">
                            ${order.status_name}
                        </span>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-info" href="${order.url || '#'}" target="_blank" ${!order.url ? 'disabled' : ''} >
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        ${order.status_id == 1 ? 
                            `<button class="btn btn-sm btn-secondary" onclick="editOrder('${order.id}', '${order.programmed_date}', ${configId})"
                                data-bs-toggle="tooltip"    
                                data-bs-title="This top tooltip is themed via CSS variables.">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteOrder('${order.id}', ${configId})">
                                <i class="bi bi-trash-fill"></i> 
                            </button>` 
                            : 
                            `<span class="text-muted fw-bold">No editable</span>`
                        }
                    </td>
                </tr>
            `).join('');

        ordersTable.html(ordersHTML);
    }

    // Función para editar orden
    function editOrder(orderId, currentDate, configId) {
        const currentDateObj = new Date(currentDate);
        const formattedDate = currentDateObj.toISOString().split('T')[0];
        const newDate = prompt('Editar fecha programada:', formattedDate);

        if (newDate) {
            const newDateObj = new Date(newDate + 'T00:00:00');
            if (!isNaN(newDateObj.getTime())) {
                // Buscar y actualizar la orden en las configuraciones
                const config = configurations.find(c => c.config_id === configId);
                if (config && config.orders) {
                    const order = config.orders.find(o => o.id == orderId);
                    if (order && order.status_id == 1) {
                        order.programmed_date = newDateObj.toISOString();

                        // Actualizar también la fecha correspondiente en configDates
                        const dateIndex = configDates[configId].findIndex(date =>
                            new Date(date).toISOString().split('T')[0] === formattedDate
                        );
                        if (dateIndex !== -1) {
                            configDates[configId][dateIndex] = newDateObj.toISOString();
                        }

                        updateOrdersTable(configId, config.orders);
                        updateDatesList(configId);
                        showSuccessMessage('Orden actualizada correctamente');
                    } else {
                        showErrorMessage('No se puede editar una orden que no está pendiente');
                    }
                }
            } else {
                showErrorMessage('Fecha inválida');
            }
        }
    }

    // Función para eliminar orden
    function deleteOrder(orderId, configId) {
        if (confirm('¿Está seguro de que desea eliminar esta orden?')) {
            // Buscar y eliminar la orden en las configuraciones
            const config = configurations.find(c => c.config_id === configId);
            if (config && config.orders) {
                const orderIndex = config.orders.findIndex(o => o.id == orderId);
                if (orderIndex !== -1) {
                    const order = config.orders[orderIndex];

                    if (order.status_id == 1) {
                        // Eliminar también la fecha correspondiente en configDates
                        const dateIndex = configDates[configId].findIndex(date =>
                            new Date(date).toISOString() === order.programmed_date
                        );
                        if (dateIndex !== -1) {
                            configDates[configId].splice(dateIndex, 1);
                        }

                        config.orders.splice(orderIndex, 1);
                        updateOrdersTable(configId, config.orders);
                        updateDatesList(configId);
                        showSuccessMessage('Orden eliminada correctamente');
                    } else {
                        showErrorMessage('No se puede eliminar una orden que no está pendiente');
                    }
                }
            }
        }
    }

    function getStatusBadgeClass(statusId) {
        const statusClasses = {
            1: 'text-warning', // Pendiente
            2: 'text-info', // En proceso
            3: 'text-primary', // Completado
            5: 'text-success', // Aceptarlo
            6: 'text-danger' // Cancelado
        };
        return statusClasses[statusId] || 'bg-secondary';
    }

    function showSuccessMessage(message) {
        alert(`✅ ${message}`);
    }

    function showErrorMessage(message) {
        alert(`❌ ${message}`);
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
                    <div class="date-item d-flex justify-content-between align-items-center p-2 border-bottom">
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
        const dateObj = new Date(date);
        if (isNaN(dateObj.getTime())) {
            return 'Fecha inválida';
        }

        // Forzar zona horaria UTC en la conversión
        return dateObj.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            timeZone: 'UTC' // ¡Clave aquí!
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
        const $element = $(`[data-config-id="${configId}"]`);
        const service_id = $('#service-id').val();

        const c_config = contract_configurations.find(
            (c) => c.config_id == configId && c.service_id == service_id
        );

        if (c_config) {
            $has_orders = configurations.find(c => c.config_id == configId && c.orders && c.orders.length > 0 && c
                .orders.some(o => o.status_id != 1));

            if ($has_orders) {
                alert(
                    'No se puede eliminar esta configuración porque tiene órdenes en estado diferente a "Pendiente".'
                );
                return;
            } else {
                if (!confirm('Esta configuración tiene órdenes generadas. ¿Está seguro de que desea eliminarla?')) {
                    return;
                }
            }
        }

        // Animación de desvanecimiento
        $element.fadeOut(300, function() {
            $(this).remove();
            // No decrementar configCounter para evitar IDs duplicados
            delete configDates[configId];

            // Eliminar solo de contract_configurations (mantener otros servicios)
            contract_configurations = contract_configurations.filter(
                (c) => !(c.config_id == configId && c.service_id == service_id)
            );

            // Eliminar de configurations local
            configurations = configurations.filter(
                (c) => !(c.config_id == configId && c.service_id == service_id)
            );

            // Actualizar el campo hidden
            $('#contract-configurations').val(JSON.stringify(contract_configurations));

            // Actualizar contadores
            updateServiceCounters(service_id);

            // Mostrar estado vacío si no hay configuraciones
            if ($("#configurations-list").children().length === 0) {
                $("#empty-config-state").show();
            }
        });
    }

    function handleFrequencyChange(configId) {
        const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
        const intervalField = $(`#interval-field-${configId}`);
        const daysField = $(`#days-field-${configId}`);
        const daysLabel = $(`#days-label-${configId}`);
        const daysInfo = $(`#days-info-${configId}`);
        const daysInput = $(`#service-days-${configId}`);

        // Mostrar todo por defecto
        daysInput.closest('.input-group').show();
        daysField.find('.form-text').show();
        daysInput.prop('disabled', false);

        // Ocultar todos los selectores primero
        $(`#week-days-selector-${configId}`).addClass('d-none');
        $(`#month-days-selector-${configId}`).addClass('d-none');
        $(`#single-date-selector-${configId}`).addClass('d-none');

        // Mostrar/ocultar campo de intervalo según la frecuencia
        if (frequency_id === 3 || frequency_id === 5) { // Mensual o Por periodo
            intervalField.removeClass('d-none');
        } else {
            intervalField.addClass('d-none');
        }

        // Configurar según la frecuencia
        switch (frequency_id) {
            case 1: // Día - Input de fecha
                // Ocultar el input de días normal
                daysInput.closest('.input-group').hide();
                daysField.find('.form-text').hide();
                
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
                    '<i class="bi bi-info-circle me-1"></i> Todo el año (seleccionado automáticamente)'
                );
                daysInput.val('1-365');
                daysInput.prop('disabled', true);
                break;

            case 5: // Por periodo
                handleIntervalChange(configId);
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
        const daysField = $(`#days-field-${configId}`);
        const start_date = $("#startdate").val();

        // Solo aplica para frecuencia mensual (id: 3) o por periodo (id: 5)
        if (frequency_id !== 3 && frequency_id !== 5) return;

        // Resetear campo de días
        daysInput.val('');
        daysInput.prop('disabled', false);

        // Mostrar el campo de días por defecto
        daysInput.closest('.input-group').show();
        daysField.find('.form-text').show();

        // Ocultar todos los selectores primero
        $(`#week-days-selector-${configId}`).addClass('d-none');
        $(`#month-days-selector-${configId}`).addClass('d-none');
        $(`#single-date-selector-${configId}`).addClass('d-none');

        if (frequency_id === 3) {
            // Frecuencia mensual
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
        } else if (frequency_id === 5) {
            console.log('Interval changed for Por periodo');
            // Frecuencia por periodo
            if (interval_id === 7) { // Quincenal (ID 7 en el array)
                // Ocultar el input de días normal
                daysInput.closest('.input-group').hide();
                daysField.find('.form-text').hide();

                daysLabel.text('Fecha de inicio quincenal');
                daysInfo.html(
                    '<i class="bi bi-info-circle me-1"></i> Seleccione la fecha de inicio para el servicio quincenal. Se generarán órdenes cada 15 días a partir de esta fecha.'
                );

                // Mostrar el selector de fecha individual
                $(`#single-date-selector-${configId}`).removeClass('d-none');

                // Configurar el input de fecha con la fecha de inicio del contrato
                if (start_date) {
                    $(`#service-date-${configId}`).val(start_date);
                    // También ponerla en el campo de días (oculto)
                    daysInput.val(start_date);
                }

            } else {
                // Para otros intervalos de periodo, mostrar campo normal
                daysInput.closest('.input-group').show();
                daysField.find('.form-text').show();

                // Otros intervalos de periodo (semanas)
                daysLabel.text('Días de la semana');
                daysInfo.html(
                    '<i class="bi bi-info-circle me-1"></i> Seleccione los días de la semana (L, M, I, J, V, S, D)'
                );
                $(`#week-days-selector-${configId}`).removeClass('d-none');
            }
        }
    }

    function validateDaysInput(configId) {
        const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
        const interval_id = parseInt($(`#service-interval-${configId}`).val());
        const daysInput = $(`#service-days-${configId}`);
        let value = daysInput.val().toUpperCase();

        // Validar según el tipo de entrada esperada
        if (frequency_id === 2 || (frequency_id === 3 && interval_id !== 1) || (frequency_id === 5 && interval_id !== 7)) {
            // Solo permitir letras L,M,I,J,V,S,D y comas
            value = value.replace(/[^LMIJVSD,]/g, '');

            // Validar formato (solo letras válidas separadas por comas)
            const days = value.split(',');
            for (let day of days) {
                if (day && !['L', 'M', 'I', 'J', 'V', 'S', 'D'].includes(day)) {
                    value = value.replace(day, '');
                }
            }
        } else if ((frequency_id === 3 && interval_id === 1) || (frequency_id === 5 && interval_id === 1)) {
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
        
        // Para quincenal, obtener la fecha del selector de fecha individual
        let daysValue;
        if (frequency_id === 5 && interval_id === 7) {
            // Obtener del selector de fecha individual
            daysValue = $(`#service-date-${configId}`).val();
            if (!daysValue) {
                alert('Por favor seleccione una fecha de inicio para el servicio quincenal');
                return;
            }
            // Guardar solo la fecha sin prefijo
            $(`#service-days-${configId}`).val(daysValue);
        } else if (frequency_id === 1) {
            // Para frecuencia diaria, también obtener del selector individual
            daysValue = $(`#service-date-${configId}`).val();
            $(`#service-days-${configId}`).val(daysValue);
        } else {
            // Para otras frecuencias, obtener del input normal
            daysValue = $(`#service-days-${configId}`).val();
        }
        
        const days = daysValue;

        // Validar campos obligatorios
        if (frequency_id === 0) {
            alert('Por favor seleccione una frecuencia para esta configuración');
            return;
        }

        // Validación especial para quincenal
        if (frequency_id === 5 && interval_id === 7) {
            if (!daysValue || daysValue.trim() === '') {
                alert('Por favor seleccione una fecha de inicio para el servicio quincenal');
                return;
            }
        } else if (frequency_id !== 4 && days.trim() === '') {
            // Para quincenal, validar que haya fecha en el selector individual
            if (frequency_id === 5 && interval_id === 7) {
                const dateValue = $(`#service-date-${configId}`).val();
                if (!dateValue || dateValue.trim() === '') {
                    alert('Por favor seleccione una fecha de inicio para el servicio quincenal');
                    return;
                }
                // Actualizar el valor de days
                days = dateValue;
                $(`#service-days-${configId}`).val(days);
            } else {
                alert('Por favor complete los días para esta configuración');
                return;
            }
        }

        // Crear objeto de servicio
        const service = {
            frequency: frequency_id,
            interval: interval_id,
            days: days.split(',').filter(d => d !== ''),
            index: configId
        };

        // Obtener fechas de inicio y fin del contrato
        const contractStartDate = $("#startdate").val();
        const contractEndDate = $("#enddate").val();

        if (contractStartDate == "" || contractEndDate == "") {
            alert("Incluye la fecha de inicio y/o finalización del contrato");
            return;
        }

        // Para frecuencia quincenal, usar la fecha específica seleccionada
        let startDate = contractStartDate;
        if (frequency_id === 5 && interval_id === 7) {
            const quincenalStartDate = $(`#service-date-${configId}`).val();
            if (quincenalStartDate) {
                startDate = quincenalStartDate;
            } else {
                alert('No se ha seleccionado fecha de inicio para el servicio quincenal');
                return;
            }
        }

        // Llamar a la función createDates
        const dates = createDates(service, startDate, contractEndDate, configId);

        // Guardar fechas para esta configuración
        configDates[configId] = dates;

        // Actualizar la lista de fechas
        updateDatesList(configId);

        // Buscar la configuración existente para obtener órdenes actuales
        let config = configurations.find(c => c.config_id === configId);
        const existingOrders = config ? (config.orders || []) : [];

        // Generar órdenes MANTENIENDO IDs existentes
        const generatedOrders = generateOrdersFromDates(dates, configId, existingOrders);

        if (!config) {
            // Crear nueva configuración si no existe
            config = {
                config_id: configId,
                service_id: $('#service-id').val(),
                frequency_id: frequency_id,
                interval_id: interval_id,
                days: [days],
                orders: generatedOrders,
                quincenal_start_date: (frequency_id === 5 && interval_id === 7) ? startDate : null
            };
            configurations.push(config);
        } else {
            // Actualizar configuración existente
            config.frequency_id = frequency_id;
            config.interval_id = interval_id;
            config.days = [days];
            config.orders = generatedOrders;
            if (frequency_id === 5 && interval_id === 7) {
                config.quincenal_start_date = startDate;
            }
        }

        updateOrdersTable(configId, config.orders);

        // Mostrar resultado
        const newOrdersCount = generatedOrders.filter(order => order.id.startsWith('temp_')).length;
        const existingOrdersCount = generatedOrders.length - newOrdersCount;

        console.log(configurations)
        alert(
            `Configuración ${configId} guardada. ${existingOrdersCount} órdenes existentes, ${newOrdersCount} órdenes nuevas.`
        );
    }

    function generateOrdersFromDates(dates, configId, existingOrders = []) {
        return dates.map((date, index) => {
            // Buscar si ya existe una orden para esta fecha
            const existingOrder = existingOrders.find(order =>
                new Date(order.programmed_date).toISOString() === new Date(date).toISOString()
            );


            // Si existe, mantener el ID original, sino crear temporal
            return existingOrder ? {
                id: existingOrder.id, // ← MANTENER ID EXISTENTE
                folio: existingOrder.folio,
                programmed_date: date,
                status_id: existingOrder.status_id,
                status_name: existingOrder.status_name,
                url: existingOrder.url
            } : {
                id: `temp_${configId}_${index}`, // ← SOLO TEMP PARA NUEVAS
                folio: null,
                programmed_date: date,
                status_id: 1,
                status_name: 'Pendiente',
                url: null
            };
        });
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
                configDates[configId][dateIndex] = newDateObj.toISOString();

                // Actualizar también la orden correspondiente si existe
                const config = configurations.find(c => c.config_id === configId);
                if (config && config.orders && config.orders[dateIndex]) {
                    config.orders[dateIndex].programmed_date = newDateObj.toISOString();
                    updateOrdersTable(configId, config.orders);
                } else if (config && !config.orders) {
                    // Si no existe la orden, crear una nueva
                    config.orders = [{
                        id: `temp_edit_${configId}_${Date.now()}`,
                        folio: null,
                        programmed_date: newDateObj.toISOString(),
                        status_id: 1,
                        status_name: 'Pendiente',
                        url: null
                    }];
                    updateOrdersTable(configId, config.orders);
                }

                updateDatesList(configId);
                showSuccessMessage('Fecha actualizada correctamente');
            } else {
                showErrorMessage('La fecha ingresada no es válida.');
            }
        }
    }

    // Función para eliminar una fecha
    function deleteDate(configId, dateIndex) {
        if (confirm('¿Está seguro de que desea eliminar esta fecha?')) {
            // Eliminar también la orden correspondiente si existe
            const config = configurations.find(c => c.config_id === configId);
            if (config && config.orders && config.orders[dateIndex]) {
                config.orders.splice(dateIndex, 1);
                updateOrdersTable(configId, config.orders);
            }

            configDates[configId].splice(dateIndex, 1);
            updateDatesList(configId);
            showSuccessMessage('Fecha eliminada correctamente');
        }
    }


    function clearAllDates(configId) {
        const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
        const interval_id = parseInt($(`#service-interval-${configId}`).val());

        // Obtener información para el mensaje
        const config = configurations.find(c => c.config_id === configId);
        const datesCount = configDates[configId] ? configDates[configId].length : 0;
        const ordersCount = config && config.orders ? config.orders.length : 0;

        // Contar órdenes por estado para el mensaje informativo
        let ordersByStatus = {};
        if (config && config.orders) {
            ordersByStatus = {
                pending: config.orders.filter(order => order.status_id === 1).length,
                inProgress: config.orders.filter(order => order.status_id === 2).length,
                completed: config.orders.filter(order => order.status_id === 3).length,
                cancelled: config.orders.filter(order => order.status_id === 4).length
            };
        }

        // Si es quincenal, preguntar si también quiere resetear la fecha de inicio
        let additionalMessage = '';
        if (frequency_id === 5 && interval_id === 7) {
            additionalMessage = '\n\n⚠️  Esta configuración es quincenal. Al eliminar todas las fechas también se reseteará la fecha de inicio.';
        }

        // Crear mensaje detallado
        let message = `¿Está seguro de que desea eliminar TODAS las fechas y órdenes de esta configuración?\n\n`;
        message += `📅 Fechas a eliminar: ${datesCount}\n`;
        message += `📋 Órdenes a eliminar: ${ordersCount}\n`;

        if (ordersCount > 0) {
            message += `\nDesglose de órdenes:\n`;
            if (ordersByStatus.pending > 0) message += `• Pendientes: ${ordersByStatus.pending}\n`;
            if (ordersByStatus.inProgress > 0) message += `• En progreso: ${ordersByStatus.inProgress}\n`;
            if (ordersByStatus.completed > 0) message += `• Completadas: ${ordersByStatus.completed}\n`;
            if (ordersByStatus.cancelled > 0) message += `• Canceladas: ${ordersByStatus.cancelled}\n`;
        }

        message +=
            `\n⚠️  ADVERTENCIA: Esta acción no se puede deshacer y eliminará todas las órdenes independientemente de su estado actual.`;
        message += additionalMessage;

        if (confirm(message)) {
            // Limpiar el array de fechas
            configDates[configId] = [];

            // Si es quincenal, también resetear la fecha en el selector
            if (frequency_id === 5 && interval_id === 7) {
                const start_date = $("#startdate").val();
                $(`#service-date-${configId}`).val(start_date);
                $(`#service-days-${configId}`).val(start_date);
            }

            // Limpiar TODAS las órdenes independientemente de su estado
            if (config && config.orders) {
                config.orders = [];
                updateOrdersTable(configId, config.orders);
            }

            updateDatesList(configId);

            // Mensaje de éxito detallado
            let successMessage = `✅ Se eliminaron correctamente:\n`;
            successMessage += `• ${datesCount} fecha(s)\n`;
            successMessage += `• ${ordersCount} orden(es) de servicio`;

            if (ordersByStatus.completed > 0 || ordersByStatus.inProgress > 0) {
                successMessage += `\n\n⚠️  Se eliminaron órdenes en estado "Completado" y "En progreso".`;
            }

            alert(successMessage);
        }
    }

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
                if (service.interval > 0) {
                    if (service.interval === 7) { // Quincenal
                        // Para quincenal, obtener la fecha específica del selector
                        const quincenalStartDate = $(`#service-date-${configId}`).val() || startDate;
                        new_dates = generateQuincenalDatesFromStart(quincenalStartDate, endDate);
                    } else {
                        new_dates = generateDatesByInterval(
                            startDate,
                            endDate,
                            service.days,
                            service.interval - 1
                        );
                    }
                } else {
                    alert(
                        "El intervalo seleccionado para el servicio " +
                        service.index +
                        " es incorrecto"
                    );
                }
                break;
            default:
                alert("La frecuencia no se encuentra en la lista de opciones");
                break;
        }

        return new_dates;
    }

    function generateQuincenalDatesFromStart(startDate, endDate) {
        const dates = [];
        
        // Convertir fechas a objetos Date
        const start = new Date(startDate + 'T00:00:00');
        const end = new Date(endDate + 'T00:00:00');
        
        // Validar que la fecha de inicio no sea mayor a la de fin
        if (start > end) {
            alert('La fecha de inicio no puede ser mayor a la fecha de fin');
            return [];
        }
        
        // Empezar desde la fecha específica seleccionada
        let currentDate = new Date(start);
        
        // Agregar la fecha de inicio
        dates.push(new Date(currentDate));
        
        // Generar fechas cada 15 días hasta llegar a la fecha de fin
        while (currentDate <= end) {
            // Sumar 15 días
            currentDate.setDate(currentDate.getDate() + 15);
            
            // Verificar que no nos pasemos de la fecha de fin
            if (currentDate <= end) {
                dates.push(new Date(currentDate));
            }
        }
        
        // Mostrar información de depuración
        console.log(`Quincenal: ${dates.length} fechas generadas desde ${startDate} hasta ${endDate}`);
        
        return dates;
    }

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

    function saveAllConfigurations() {
        const configElements = $('.configuration-item');
        const service_id = $('#service-id').val();

        // Array temporal para las nuevas configuraciones de ESTE servicio
        const newConfigurationsForThisService = [];

        configElements.each(function() {
            const configId = $(this).data('config-id');
            const frequency_id = parseInt($(`#service-frequency-${configId}`).val());
            const frequency = frequencies.find(f => f.id === frequency_id);
            const interval_id = parseInt($(`#service-interval-${configId}`).val());
            const interval = interval_id > 0 ? intervals[interval_id - 1] : null;
            const days = $(`#service-days-${configId}`).val();

            // Verificar si tiene fechas manuales O configuración de frecuencia
            const hasManualDates = configDates[configId] && configDates[configId].length > 0;
            const hasFrequencyConfig = frequency_id !== 0 && days.trim() !== '';

            if (hasManualDates || hasFrequencyConfig) {
                // Crear nueva configuración para este servicio
                const c_config = contract_configurations.find(c => c.config_id == configId && c.service_id ==
                    service_id) ?? null;
                const newConfig = {
                    config_id: configId,
                    setting_id: c_config ? c_config.setting_id : null,
                    service_id: parseInt(service_id),
                    frequency: frequency ? frequency.name : 'Manual',
                    frequency_id: (frequency_id && frequency_id > 0) ? frequency_id :
                    1, // Frecuencia por defecto: 1 (Día)
                    interval: interval,
                    interval_id: (interval_id && interval_id > 0) ? interval_id :
                    1, // Intervalo por defecto: 1
                    days: days ? [days] : [],
                    dates: configDates[configId] || [],
                    orders: generateOrdersFromDates(configDates[configId] || [], configId, c_config ?
                        c_config.orders : []),
                    description: configDescriptions[configId] || null,
                    quincenal_start_date: (frequency_id === 5 && interval_id === 7) ? $(`#service-date-${configId}`).val() : null
                };

                newConfigurationsForThisService.push(newConfig);
            }
        });

        // PRESERVAR configuraciones de otros servicios y actualizar solo las de este servicio
        const otherServicesConfigs = contract_configurations.filter(c => c.service_id != service_id);
        contract_configurations = [...otherServicesConfigs, ...newConfigurationsForThisService];

        // Actualizar contadores para este servicio específico
        updateServiceCounters(service_id);

        // Actualizar el campo hidden con TODAS las configuraciones
        $('#contract-configurations').val(JSON.stringify(contract_configurations));

        alert(
            `Se guardaron ${newConfigurationsForThisService.length} configuración(es) para el servicio correctamente. Total en contrato: ${contract_configurations.length}`
        );

        // Cerrar el modal después de guardar
        $('#configureServiceModal').modal('hide');
    }

    // Función auxiliar para actualizar contadores
    function updateServiceCounters(service_id) {
        const c_configs = contract_configurations.filter(c => c.service_id == service_id);

        if (c_configs.length > 0) {
            $(`#service${service_id}-count-configs`).text(c_configs.length);
            const totalDates = c_configs.reduce((total, item) => total + (item.dates ? item.dates.length : 0), 0);
            const totalOrders = c_configs.reduce((total, item) => total + (item.orders ? item.orders.length : 0), 0);
            $(`#service${service_id}-count-dates`).text(totalDates);
            $(`#service${service_id}-count-orders`).text(totalOrders);
        } else {
            $(`#service${service_id}-count-configs`).text('0');
            $(`#service${service_id}-count-dates`).text('0');
            $(`#service${service_id}-count-orders`).text('0');
        }
    }
</script>