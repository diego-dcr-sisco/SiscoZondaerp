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

    function cleanHtmlSimple(?string $html, array $config = []): string
    {
        // Si es null o vacío, retornar string vacío
        if (empty($html)) {
            return '';
        }

        // Configuración por defecto
        $defaultConfig = [
            'keepHtml' => true,
            'keepOnlyTags' =>
                '<p><br><ul><ol><li><a><b><strong><table><thead><tbody><tfoot><tr><th><td><col><colgroup><caption><div>',
            'badTags' => ['style', 'script', 'applet', 'embed', 'noframes', 'noscript'],
            'badAttributes' => ['style', 'start', 'dir', 'class'],
            'newline' => '<br>',
            'keepClasses' => false,
        ];

        $config = array_merge($defaultConfig, $config);

        // Si no se debe mantener HTML
        if (!$config['keepHtml']) {
            return nl2br(htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // 1. Primero eliminar las etiquetas peligrosas con su contenido
        foreach ($config['badTags'] as $tag) {
            $pattern = '/<' . $tag . '\b[^>]*>.*?<\/' . $tag . '>/is';
            $html = preg_replace($pattern, '', $html);
        }

        // 2. Aplicar strip_tags para permitir solo ciertas etiquetas
        $html = strip_tags($html, $config['keepOnlyTags']);

        // 3. Eliminar atributos de las etiquetas restantes
        if (!empty($config['badAttributes'])) {
            $html = removeAttributes($html, $config['badAttributes'], $config['keepClasses']);
        }

        // 4. Normalizar espacios y saltos de línea
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/(\r\n|\r|\n)+/', $config['newline'], $html);

        return trim($html);
    }

    function removeAttributes(string $html, array $badAttributes, bool $keepClasses = false): string
    {
        // Si keepClasses es true, remover 'class' de los atributos a eliminar
        if ($keepClasses) {
            $badAttributes = array_diff($badAttributes, ['class']);
        }

        // Patrón para encontrar atributos en etiquetas
        foreach ($badAttributes as $attr) {
            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*"[^"]*"/i';
            $html = preg_replace($pattern, '', $html);

            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*\'[^\']*\'/i';
            $html = preg_replace($pattern, '', $html);

            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*[^\s>]+/i';
            $html = preg_replace($pattern, '', $html);
        }

        return $html;
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

    .note-editor .note-editable,
    .note-editor .note-editable p {
        font-size: 11pt !important;
        font-family: inherit;
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

    function normalizeHtmlForPdfFront(html) {
        if (!html) return '';

        // 1. Eliminar caracteres invisibles (BOM, NBSP, zero-width)
        html = html.replace(/[\u0000-\u001F\u007F\u00A0\u200B-\u200F\uFEFF]/g, ' ');

        // 2. Eliminar &nbsp;
        html = html.replace(/&nbsp;/gi, ' ');

        // 3. Quitar estilos inline y clases
        html = html.replace(/\s*style="[^"]*"/gi, '');
        html = html.replace(/\s*class="[^"]*"/gi, '');

        // 4. Eliminar spans completamente
        html = html.replace(/<\/?span[^>]*>/gi, '');

        // 5. Eliminar scripts y estilos (seguridad)
        html = html.replace(/<(script|style)[^>]*>.*?<\/\1>/gis, '');

        // 6. Eliminar párrafos vacíos
        html = html.replace(/<p>\s*(<br\s*\/?>)?\s*<\/p>/gi, '');

        // 7. Normalizar múltiples <br> a párrafos
        html = html.replace(/(<br\s*\/?>\s*){2,}/gi, '</p><p>');

        // 8. Compactar espacios múltiples
        html = html.replace(/\s{2,}/g, ' ');

        // 9. Asegurar envoltura en <p>
        html = html.trim();
        if (!/^<p>/i.test(html)) {
            html = `<p>${html}</p>`;
        }

        return html.trim();
    }


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
        function forceFontSize(note, size = 11) {
            const editable = note.next('.note-editor')
                .find('.note-editable');

            editable.css('font-size', size + 'pt');
            note.summernote('fontSize', size);
        }

        function normalizeFontSize(html) {
            html = html.replace(/font-size\s*:\s*[^;"]+;?/gi, '');
            html = html.replace(/<span[^>]*>(.*?)<\/span>/gi, '$1');
            return html;
        }

        function normalizeHtmlFromSummernote(html) {
            if (!html) return '';

            // 1. Decodificar entidades rotas (&nbsp;, &lt, etc.)
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            html = textarea.value;

            // 2. Eliminar caracteres invisibles (Word, BOM)
            html = html.replace(/[\u0000-\u001F\u007F\u00A0\u200B-\u200F\uFEFF]/g, ' ');

            // 3. Eliminar estilos y clases
            html = html.replace(/\s*style="[^"]*"/gi, '');
            html = html.replace(/\s*class="[^"]*"/gi, '');

            // 4. Eliminar spans SIN romper texto
            html = html.replace(/<\/?span[^>]*>/gi, '');

            // 5. Quitar espacios antes de signos
            html = html.replace(/\s+([,.!?;:])/g, '$1');

            // 6. Asegurar espacio después de </b> </strong> </em>
            html = html.replace(/<\/(b|strong|em|i)>(\S)/gi, '</$1> $2');

            // 7. Compactar espacios múltiples
            html = html.replace(/\s{2,}/g, ' ');

            return html.trim();
        }

        function hasWordGarbage(html) {
            const patterns = [
                /&nbsp;/i,
                /<span[^>]*>/i,
                /mso-/i,
                /font-family/i,
                /font-size/i,
                /[\u200B-\u200F\uFEFF]/, // caracteres invisibles
                /[“”‘’]/, // comillas Word
                /&[a-zA-Z]{1,6}(?!;)/ // entidades rotas (&ea &lt etc)
            ];

            return patterns.some(regex => regex.test(html));
        }


        let summernoteConfig = {
            height: 250,
            lang: 'es-ES',
            fontSize: ['8', '10', '11', '12', '14', '16'],
            lineHeights: ['0.5', '1', '1.5', '2'],
            fontSizeUnit: 'pt',
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['fontsize', 'fontname']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['table', 'link']],
            ],

            callbacks: {
                onInit: function() {
                    forceFontSize($(this), 11);
                },

                onPaste: function() {
                    const note = $(this);

                    setTimeout(() => {
                        let content = note.summernote('code');

                        if (hasWordGarbage(content)) {
                            alert(
                                '⚠️ El texto pegado contiene formato de Word.\n\n' +
                                'Puede causar:\n' +
                                '• Espacios extra\n' +
                                '• Caracteres raros\n' +
                                '• Problemas en el PDF\n\n' +
                                'Recomendación:\n' +
                                'Pegue como texto plano (Ctrl + Shift + V).'
                            );
                        }

                        content = normalizeHtmlFromSummernote(content);
                        note.summernote('code', content);
                    }, 0);
                },


                /*onChange: function(contents) {
                    const note = $(this);

                    if (contents === '<p><br></p>' || contents === '') {
                        setTimeout(() => forceFontSize(note, 11), 0);
                    }
                },*/

                /*onKeydown: function() {
                    const note = $(this);
                    setTimeout(() => forceFontSize(note, 11), 0);
                }*/
            }
        };

        function initializeSummernote() {
            if ($('.smnote').data('summernote')) {
                $('.smnote').summernote('destroy');
            }
            $('.smnote').summernote(summernoteConfig);
        }

        initializeSummernote();
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
