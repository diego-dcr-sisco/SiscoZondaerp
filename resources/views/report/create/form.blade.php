@php
    $question_ids = [];
    $i = 0;

    function getOptions($id, $answers)
    {
        foreach ($answers as $answer) {
            if ($answer['id'] == $id) {
                return $answer['options'];
            }
        }
        return [];
    }

@endphp

<style>
    .modal-blur {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.3);
    }

    #fullscreen-spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.2);
    }

    .spinner-overlay {
        background-color: rgba(0, 0, 0, 0.7);
        padding: 30px;
        border-radius: 10px;
        text-align: center;
    }
</style>

<div id="fullscreen-spinner" class="d-none">
    <div class="spinner-overlay">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="text-light mt-2">Procesando...</div>
    </div>
</div>

<form id="report_form" class="m-3" method="POST" action="{{ route('report.store', ['orderId' => $order->id]) }}"
    target="_blank" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="summary-services" name="summary_services" value="">
    <div class="row mb-4">
        <div class="col-6">
            <div class="card shadow">
                <div class="card-header">
                    Orden de servicio
                </div>
                <div class="card-body">
                    @can('write_order')
                        <a class="btn btn-link p-0" href="{{ route('order.edit', ['id' => $order->id]) }}">
                            {{ __('buttons.edit') }} orden
                        </a>
                    @endcan
                    <input type="hidden" class="form-control form-control-sm" id="order-id"
                        value="{{ $order->id }}">

                    <div class="row">
                        <label for="programmed-date"
                            class="col-sm-4 col-form-label">{{ __('order.data.programmed_date') }}:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="date" class="form-control form-control-sm" id="programmed-date"
                                value="{{ $order->programmed_date }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="completed-date"
                            class="col-sm-4 col-form-label">{{ __('order.data.completed_date') }}:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="date" class="form-control form-control-sm" id="completed-date"
                                value="{{ $order->completed_date }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="start-time" class="col-sm-4 col-form-label">Hora de inicio:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="time" class="form-control form-control-sm" id="start-time"
                                value="{{ $order->start_time }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="end-time" class="col-sm-4 col-form-label">Hora de fin:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="time" class="form-control form-control-sm" id="end-time"
                                value="{{ $order->end_time }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="order-status" class="col-sm-4 col-form-label">Estado:</label>
                        <div class="col-sm-4 col-lg-8">
                            <select class="form-select form-select-sm" id="order-status">
                                @foreach ($order_status as $status)
                                    <option value="{{ $status->id }}"
                                        {{ $order->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label for="closed-by" class="col-sm-4 col-form-label">Cerrado por (Técnico):</label>
                        <div class="col-sm-4 col-lg-8">
                            <select class="form-select form-select-sm" id="closed-by">
                                <option value="" {{ $order->closed_by == null ? 'selected' : '' }}>Sin técnico
                                </option>
                                @foreach ($user_technicians as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $order->closed_by == $user->id ? 'selected' : '' }}>{{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label for="signed-by" class="col-sm-4 col-form-label">Firmado por:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="signed-by"
                                value="{{ $order->signature_name }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="signed-by" class="col-sm-4 col-form-label">Firma:</label>
                        @php
                            $signature =
                                strpos($order->customer_signature, 'data:image') === 0
                                    ? $order->customer_signature
                                    : 'data:image/png;base64,' . $order->customer_signature;
                        @endphp
                        <div class="col-sm-4 col-lg-4">
                            <img id="signature-preview" class="border" style="width: 125px;" src="{{ $signature }}"
                                alt="img_firma">
                            <input type="hidden" id="signature-base64" value="{{ $signature }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2" onclick="updateOrder()">
                        Guardar
                    </button>
                    <button type="button" class="btn btn-warning btn-sm mt-2" data-order="{{ $order }}"
                        onclick="openModal(this)">
                        Cambiar firma
                    </button>
                    <button type="button" class="btn btn-danger btn-sm mt-2" onclick="deleteSignature()">
                        Eliminar firma
                    </button>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card shadow">
                <div class="card-header">
                    Cliente
                </div>
                <div class="card-body">
                    @can('write_customer')
                        <a href="{{ route('customer.edit', ['id' => $order->customer->id, 'type' => 1, 'section' => 1]) }}"
                            class="btn btn-link p-0">
                            {{ __('buttons.edit') }} cliente
                        </a>
                    @endcan
                    <input type="hidden" class="form-control form-control-sm" id="customer-id"
                        value="{{ $order->customer_id }}">

                    <div class="row">
                        <label for="customer-name" class="col-sm-4 col-form-label">Nombre:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-name"
                                value="{{ $order->customer->name }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="customer-address" class="col-sm-4 col-form-label">Dirección:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-address"
                                value="{{ $order->customer->address }}">
                        </div>
                    </div>

                    <div class="row">
                        <label for="customer-email" class="col-sm-4 col-form-label">Correo:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-email"
                                value="{{ $order->customer->email }}">
                        </div>
                    </div>

                    <div class="row">
                        <label for="customer-rfc" class="col-sm-4 col-form-label">RFC:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-rfc"
                                value="{{ $order->customer->rfc }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="customer-time" class="col-sm-4 col-form-label">Tipo de cliente:</label>
                        <div class="col-sm-4 col-lg-8">
                            <select class="form-select form-select-sm" id="customer-type" disabled>
                                @foreach ($service_types as $status)
                                    <option value="{{ $status->id }}"
                                        {{ $status->id == $order->customer->service_type_id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-2" onclick="updateCustomer()">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion shadow mb-3" id="accordionReview">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseServices" aria-expanded="true" aria-controls="collapseServices">
                    Servicios (Tratamientos)
                </button>
            </h2>
            <div id="collapseServices" class="accordion-collapse collapse show" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.services')
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseDevices" aria-expanded="false" aria-controls="collapseDevices">
                    Dispositivos
                </button>
            </h2>
            <div id="collapseDevices" class="accordion-collapse collapse" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.devices')
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseProducts" aria-expanded="false" aria-controls="collapseProducts">
                    Productos
                </button>
            </h2>
            <div id="collapseProducts" class="accordion-collapse collapse" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.products')
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsePests" aria-expanded="true" aria-controls="collapsePests">
                    Plagas atacadas (Aplicación química)
                </button>
            </h2>
            <div id="collapsePests" class="accordion-collapse collapse" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.pests')
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
                    Notas del cliente
                </button>
            </h2>
            <div id="collapseNotes" class="accordion-collapse collapse" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.notes')
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseEvidence" aria-expanded="false" aria-controls="collapseEvidence">
                    Evidencia fotográfica
                </button>
            </h2>
            <div id="collapseEvidence" class="accordion-collapse collapse" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.evidence')
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseRecoms" aria-expanded="false" aria-controls="collapseRecoms">
                    Recomendaciones
                </button>
            </h2>
            <div id="collapseRecoms" class="accordion-collapse collapse" data-bs-parent="#accordionReview">
                <div class="accordion-body">
                    @include('report.create.recommendations')
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary my-3" id="generate-report-btn">
        {{ __('buttons.generate') }}
    </button>
    </div>
</form>

@if (count($autoreview_data) > 0)
    @include('report.create.modals.autoreview')
@endif

@include('report.create.modals.signature')
@include('report.create.modals.product')
@include('report.create.modals.review')
@include('report.create.modals.add-pests')
@include('report.create.modals.add-products')
@include('report.create.modals.new-device')

<script src="{{ asset('js/report/functions.min.js') }}"></script>

<script>
    const services = @json($order->services);
    const lots = @json($lots);
    var summaryData = [];

    /*$(document).ready(function() {
        $('.smnote').summernote({
            height: 250,
            lang: 'es-ES',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['table', 'link']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['font', ['fontsize']],
            ],
            fontSize: ['8', '10', '12', '14', '16'],
            lineHeights: ['0.25', '0.5', '1', '1.5', '2'],

            callbacks: {
                onPaste: function(e) {
                    var thisNote = $(this);
                    var updatePaste = function() {
                        // Get the current HTML code FROM the Summernote editor
                        var original = thisNote.summernote('code');
                        var cleaned = cleanPaste(original);
                        // Set the cleaned code BACK to the editor
                        thisNote.summernote('code', cleaned);
                    };
                    // Wait for Summernote to process the paste
                    setTimeout(updatePaste, 10);
                },
            }

        });
    });
*/

    $(document).ready(function() {
        // Función para limpiar contenido pegado (si la necesitas)
        function cleanPaste(html) {
            // Tu lógica de limpieza aquí
            return html.replace(/ style="[^"]*"/gi, '')
                .replace(/ class="[^"]*"/gi, '');
        }

        // Configuración base del Summernote
        let summernoteConfig = {
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
            callbacks: {
                onPaste: function(e) {
                    var thisNote = $(this);
                    var updatePaste = function() {
                        var original = thisNote.summernote('code');
                        var cleaned = cleanPaste(original);
                        thisNote.summernote('code', cleaned);
                    };
                    setTimeout(updatePaste, 10);
                }
            },

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
            }
        };

        // Función para inicializar el editor
        function initializeSummernote() {
            // Si ya existe, destruirlo primero
            if ($('.smnote').length && $('.smnote').data('summernote')) {
                $('.smnote').summernote('destroy');
            }

            // Inicializar con la configuración actual
            $('.smnote').summernote(summernoteConfig);
        }

        // Función para actualizar opciones específicas
        function updateSummernoteOptions(newOptions) {
            // Guardar el contenido actual si existe
            let currentContent = '';
            if ($('.smnote').length && $('.smnote').data('summernote')) {
                currentContent = $('.smnote').summernote('code');
            }

            // Actualizar la configuración
            summernoteConfig = {
                ...summernoteConfig,
                ...newOptions,
                // Mantener siempre los callbacks
                callbacks: {
                    ...summernoteConfig.callbacks,
                    ...(newOptions.callbacks || {})
                }
            };

            // Reinicializar
            initializeSummernote();

            // Restaurar contenido si había
            if (currentContent) {
                $('.smnote').summernote('code', currentContent);
            }
        }

        // Inicializar por primera vez
        initializeSummernote();

        // Ejemplos de cómo actualizar dinámicamente:

        // 1. Para agregar selector de nombres de fuente
        function enableFontNames() {
            updateSummernoteOptions({
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['fontsize', 'fontname']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['table', 'link']],
                ],
                fontNames: ['Arial', 'Courier New', 'Helvetica', 'Times New Roman']
            });
        }

        // 2. Para cambiar los tamaños de fuente
        function updateFontSizes(newSizes) {
            updateSummernoteOptions({
                fontSize: newSizes
            });
        }

        // 3. Para cambiar la altura
        function updateHeight(newHeight) {
            updateSummernoteOptions({
                height: newHeight
            });
        }

        // 4. Para cambiar las opciones de interlineado
        function updateLineHeights(newLineHeights) {
            updateSummernoteOptions({
                lineHeights: newLineHeights
            });
        }

        // 5. Para agregar más funcionalidades a la toolbar
        function addFullToolbar() {
            updateSummernoteOptions({
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['fontsize', 'fontname']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['table', 'link']],
                ],
                fontNames: ['Arial', 'Courier New', 'Helvetica', 'Times New Roman', 'Georgia',
                    'Verdana'
                ],
                colors: [
                    ['#000000', '#424242', '#636363', '#9C9C94', '#CEC6CE', '#EFEFEF', '#F7F7F7',
                        '#FFFFFF'
                    ],
                    ['#FF0000', '#FF9C00', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#9C00FF',
                        '#FF00FF'
                    ]
                ]
            });
        }

        // 6. Para volver a la configuración básica
        function resetToBasic() {
            summernoteConfig = {
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
                callbacks: {
                    onPaste: function(e) {
                        var thisNote = $(this);
                        var updatePaste = function() {
                            var original = thisNote.summernote('code');
                            var cleaned = cleanPaste(original);
                            thisNote.summernote('code', cleaned);
                        };
                        setTimeout(updatePaste, 10);
                    }
                }
            };
            initializeSummernote();
        }

        // Función para cambiar el idioma
        function changeLanguage(langCode) {
            updateSummernoteOptions({
                lang: langCode
            });
        }

        // Función para agregar callback adicional
        function addOnChangeCallback() {
            const newCallbacks = {
                ...summernoteConfig.callbacks,
                onChange: function(contents) {
                    console.log('Contenido cambiado:', contents.length, 'caracteres');
                    // Aquí puedes agregar lógica adicional
                }
            };

            updateSummernoteOptions({
                callbacks: newCallbacks
            });
        }

        // Hacer las funciones disponibles globalmente si es necesario
        window.summernoteFunctions = {
            enableFontNames,
            updateFontSizes,
            updateHeight,
            updateLineHeights,
            addFullToolbar,
            resetToBasic,
            changeLanguage,
            addOnChangeCallback,
            updateSummernoteOptions
        };

        // Ejemplos de uso (puedes llamarlas desde botones o eventos):
        /*
        <button onclick="summernoteFunctions.enableFontNames()">Agregar fuentes</button>
        <button onclick="summernoteFunctions.updateFontSizes(['10', '12', '14', '18', '24'])">Tamaños grandes</button>
        <button onclick="summernoteFunctions.updateHeight(350)">Altura 350px</button>
        <button onclick="summernoteFunctions.addFullToolbar()">Toolbar completa</button>
        <button onclick="summernoteFunctions.resetToBasic()">Reset básico</button>
        */
    });

    function cleanPaste(html) {
        // Elimina etiquetas no deseadas
        html = html.replace(/<(script|style|iframe)[^>]*>.*?<\/\1>/gmi, '');

        // Elimina atributos de estilo
        html = html.replace(/(<[^>]+) style=".*?"/gi, '$1');

        // Elimina clases
        html = html.replace(/(<[^>]+) class=".*?"/gi, '$1');

        // Elimina otros atributos no deseados
        html = html.replace(/(<[^>]+) [a-z\-]+=".*?"/gi, '$1');

        // Convierte divs y spans a párrafos cuando sea apropiado
        html = html.replace(/<(\/)?(div|span)>/g, '<$1p>');

        return html;
    }

    $(document).ready(function() {
        $('#generate-report-btn').on('click', function(e) {
            e.preventDefault();

            // Asegurar que el botón tenga foco
            $(this).focus();

            if (setSummary()) {
                // Pequeño delay para asegurar procesamiento
                setTimeout(() => {
                    $('#report_form').submit();
                }, 100);
            }
        });
    });

    function setSummary() {
        try {
            services.forEach(service => {
                console.log($(`#service${service.id}-text`).val());
                summaryData[service.id] = {
                    recs: $(`#summary-recs${service.id}`).summernote('code'),
                };
            });

            $('#summary-services').val(JSON.stringify(summaryData));
            console.log('Summary data guardado correctamente');
            return true;

        } catch (error) {
            console.error('Error en setSummary:', error);
            alert('Error al preparar los datos del reporte');
            return false;
        }
    }

    function cleanAddProductForm() {
        $select_lot = $('#add-product-lot');
        $('#add-product').val('')
        $('#add-product-quantity').val(0)
        $('#add-product-metric').text('-');
        $select_lot.empty();
        $select_lot.append($('<option>', {
            value: "",
            text: `Selecciona un lote`
        }));
        $('.handleP').prop('disabled', true);
    }
</script>
