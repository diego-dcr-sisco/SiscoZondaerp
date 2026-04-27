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
                '<p><br><ul><ol><li><a><b><strong><table><thead><tbody><tfoot><tr><th><td><col><colgroup><caption><div><img>',
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

    .smnote .ql-editor,
    .smnote .ql-editor p {
        font-size: 11pt !important;
        font-family: inherit;
    }

    .smnote .ql-container {
        min-height: 250px;
    }

    .smnote .ql-editor {
        min-height: 250px;
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
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    Orden de servicio
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-order" aria-expanded="true" aria-controls="collapse-order">
                        <i class="bi bi-chevron-up"></i>
                    </button>
                </div>
                <div id="collapse-order" class="collapse show">
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
                            <input type="hidden" id="signature-changed" value="0">
                            <input type="hidden" id="signature-original" value="{{ $signature }}">
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
        </div>
        <div class="col-6">
            <div class="card shadow">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    Cliente
                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-customer" aria-expanded="true" aria-controls="collapse-customer">
                        <i class="bi bi-chevron-up"></i>
                    </button>
                </div>
                <div id="collapse-customer" class="collapse show">
                    <div class="card-body">
                    @can('write_customer')
                        <a href="{{ isset($order->customer) ? route('customer.edit', ['id' => $order->customer->id ?? 0, 'type' => 1, 'section' => 1]) : '#' }}"
                            class="btn btn-link p-0">
                            {{ __('buttons.edit') }} cliente
                        </a>
                    @endcan
                    <input type="hidden" class="form-control form-control-sm" id="customer-id"
                        value="{{ $order->customer_id ?? '-' }}">

                    <div class="row">
                        <label for="customer-name" class="col-sm-4 col-form-label">Nombre:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-name"
                                value="{{ isset($order->customer) ? ($order->customer->name ?? '-') : '-' }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="customer-address" class="col-sm-4 col-form-label">Dirección:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-address"
                                value="{{ isset($order->customer) ? ($order->customer->address ?? '-') : '-' }}">
                        </div>
                    </div>

                    <div class="row">
                        <label for="customer-email" class="col-sm-4 col-form-label">Correo:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-email"
                                value="{{ isset($order->customer) ? ($order->customer->email ?? '-') : '-' }}">
                        </div>
                    </div>

                    <div class="row">
                        <label for="customer-rfc" class="col-sm-4 col-form-label">RFC:</label>
                        <div class="col-sm-4 col-lg-8">
                            <input type="text" class="form-control form-control-sm" id="customer-rfc"
                                value="{{ isset($order->customer) ? ($order->customer->rfc ?? '-') : '-' }}">
                        </div>
                    </div>
                    <div class="row">
                        <label for="customer-time" class="col-sm-4 col-form-label">Tipo de cliente:</label>
                        <div class="col-sm-4 col-lg-8">
                            <select class="form-select form-select-sm" id="customer-type" disabled>
                                @foreach ($service_types as $status)
                                    <option value="{{ $status->id }}"
                                        {{ isset($order->customer) && $status->id == ($order->customer->service_type_id ?? 0) ? 'selected' : '' }}>
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
    </div>

    <div class="mb-3">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">Servicios
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-services" aria-expanded="true" aria-controls="collapse-services">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-services" class="collapse show">
                        <div class="card-body">
                            @include('report.create.services')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">Dispositivos
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-devices" aria-expanded="true" aria-controls="collapse-devices">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-devices" class="collapse show">
                        <div class="card-body">
                            @include('report.create.devices')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">Productos
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-products" aria-expanded="true" aria-controls="collapse-products">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-products" class="collapse show">
                        <div class="card-body">
                            @include('report.create.products')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">Plagas
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-pests" aria-expanded="true" aria-controls="collapse-pests">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-pests" class="collapse show">
                        <div class="card-body">
                            @include('report.create.pests')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">Notas
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-notes" aria-expanded="true" aria-controls="collapse-notes">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-notes" class="collapse show">
                        <div class="card-body">
                            @include('report.create.notes')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">Recomendaciones
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-recommendations" aria-expanded="true" aria-controls="collapse-recommendations">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-recommendations" class="collapse show">
                        <div class="card-body">
                            @include('report.create.recommendations')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">Evidencias fotográficas
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-evidence" aria-expanded="true" aria-controls="collapse-evidence">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>
                    <div id="collapse-evidence" class="collapse show">
                        <div class="card-body">
                            @include('report.create.evidence')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-generate-bar">
        <button type="submit" class="btn btn-dark report-save-btn" id="generate-report-btn">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('buttons.generate') }}
        </button>
    </div>
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


    window.reportQuillEditors = window.reportQuillEditors || {};

    function getEditorById(editorId) {
        return window.reportQuillEditors[editorId] || null;
    }

    function getEditorHtmlById(editorId) {
        const quill = getEditorById(editorId);

        if (!quill) {
            return '';
        }

        const html = quill.root.innerHTML;
        return html === '<p><br></p>' ? '' : html;
    }

    function setEditorHtmlById(editorId, html) {
        const quill = getEditorById(editorId);

        if (!quill) {
            return;
        }

        quill.clipboard.dangerouslyPasteHTML(html || '');
    }

    function getServiceEditorHtml(serviceId) {
        return getEditorHtmlById(`service${serviceId}-text`);
    }

    function getNotesEditorHtml() {
        return getEditorHtmlById('order-notes');
    }

    function getRecommendationEditorHtml(serviceId) {
        return getEditorHtmlById(`summary-recs${serviceId}`);
    }

    function setRecommendationEditorHtml(serviceId, html) {
        setEditorHtmlById(`summary-recs${serviceId}`, html);
    }

    window.getRecommendationEditorHtml = getRecommendationEditorHtml;
    window.setRecommendationEditorHtml = setRecommendationEditorHtml;

    $(document).ready(function() {
        const quillToolbar = [
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link', 'image'],
            ['clean']
        ];

        $('.smnote').each(function() {
            if (!this.id || window.reportQuillEditors[this.id]) {
                return;
            }

            const quill = new Quill(this, {
                theme: 'snow',
                modules: {
                    toolbar: quillToolbar
                }
            });

            window.reportQuillEditors[this.id] = quill;

            quill.on('text-change', () => {
                const autosaveType = $(this).data('autosave-type');
                const serviceId = $(this).data('service-id');
                $(document).trigger('quill-editor-change', [autosaveType, serviceId]);
            });
        });

        // Toggle collapse icons
        $('.collapse').on('show.bs.collapse', function() {
            const id = $(this).attr('id');
            $(`button[data-bs-target="#${id}"] i`).removeClass('bi-chevron-down').addClass('bi-chevron-up');
        });
        $('.collapse').on('hide.bs.collapse', function() {
            const id = $(this).attr('id');
            $(`button[data-bs-target="#${id}"] i`).removeClass('bi-chevron-up').addClass('bi-chevron-down');
        });
    });

    function updateDescription(service_id) {
        const rawHtml = getServiceEditorHtml(service_id);
        const normalizedHtml = normalizeImageSizesForPdf(rawHtml);

        var description = {
            service_id: parseInt(service_id),
            text: normalizedHtml,
            can_propagate: $(`#service${service_id}-can-propagate`).is(':checked'),
            order_id: parseInt($('#order-id').val()),
        };

        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        if (!csrfToken) {
            alert('Error: No se encontró el token CSRF. Por favor, recarga la página.');
            return;
        }

        var new_formdata = new FormData();
        new_formdata.append('description', JSON.stringify(description));
        new_formdata.append('_token', csrfToken);

        showSpinner();

        $.ajax({
            type: 'POST',
            url: '/report/description/update',
            data: new_formdata,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Descripción actualizada!');
                } else {
                    alert('Error al actualizar la descripción: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Error al actualizar la descripción';

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

                alert(errorMsg);
            },
            complete: function() {
                hideSpinner();
            }
        });
    }

    function updateNotes() {
        var notesHtml = getNotesEditorHtml();
        notesHtml = compressBase64Images(notesHtml);

        var notes = {
            text: notesHtml,
            order_id: parseInt($('#order-id').val()),
        };

        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        if (!csrfToken) {
            alert('Error: No se encontró el token CSRF. Por favor, recarga la página.');
            return;
        }

        var new_formdata = new FormData();
        new_formdata.append('notes', JSON.stringify(notes));
        new_formdata.append('_token', csrfToken);

        showSpinner();

        $.ajax({
            type: 'POST',
            url: '/report/notes/update',
            data: new_formdata,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#notes').val(notes.text);
                    alert('Notas actualizada!');
                } else {
                    alert('Error al actualizar las notas: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Error al actualizar las notas';

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

                alert(errorMsg);
            },
            complete: function() {
                hideSpinner();
            }
        });
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
                    recs: getRecommendationEditorHtml(service.id),
                };
            });

            $('#summary-services').val(JSON.stringify(summaryData));
            console.log('Summary data guardado correctamente');
            return true;

        } catch (error) {
            //console.error('Error en setSummary:', error);
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

    // Actualizar badge de aprobado cuando cambia el estado
    $(document).ready(function() {
        $('#order-status').on('change', function() {
            const statusId = parseInt($(this).val());
            const approvedBadge = $('#approved-badge');
            
            if (statusId === 5) {
                approvedBadge.fadeIn();
            } else {
                approvedBadge.fadeOut();
            }
        });
    });
</script>

<script>
    (function() {
        const autosaveTimers = {};
        const autosaveDelayMs = 1400;
        let autosaveReady = false;

        setTimeout(function() {
            autosaveReady = true;
        }, 2500);

        function setAutosaveStatus(scope, state, text) {
            const statusEl = $(scope);
            if (!statusEl.length) {
                return;
            }

            statusEl.removeClass('is-saving is-saved is-error');
            if (state) {
                statusEl.addClass(state);
            }
            if (text) {
                statusEl.text(text);
            }
        }

        function postSilent(url, payloadKey, payload, onSuccess, onError) {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            if (!csrfToken) {
                if (typeof onError === 'function') {
                    onError('No se encontró el token CSRF');
                }
                return;
            }

            const formData = new FormData();
            formData.append(payloadKey, JSON.stringify(payload));
            formData.append('_token', csrfToken);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response && response.success) {
                        if (typeof onSuccess === 'function') {
                            onSuccess(response);
                        }
                    } else if (typeof onError === 'function') {
                        onError((response && response.message) || 'Error de guardado');
                    }
                },
                error: function(xhr) {
                    let msg = 'Error de guardado';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof onError === 'function') {
                        onError(msg);
                    }
                },
            });
        }

        function autosaveServiceDescription(serviceId) {
            const statusSelector = `#autosave-status-service-${serviceId}`;
            setAutosaveStatus(statusSelector, 'is-saving', 'Guardando...');

            const rawHtml = getServiceEditorHtml(serviceId);
            const normalizedHtml = typeof normalizeImageSizesForPdf === 'function'
                ? normalizeImageSizesForPdf(rawHtml)
                : rawHtml;

            const payload = {
                service_id: parseInt(serviceId, 10),
                text: normalizedHtml,
                can_propagate: $(`#service${serviceId}-can-propagate`).is(':checked'),
                order_id: parseInt($('#order-id').val(), 10),
            };

            postSilent('/report/description/update', 'description', payload,
                function() {
                    setAutosaveStatus(statusSelector, 'is-saved', 'Guardado automático');
                },
                function() {
                    setAutosaveStatus(statusSelector, 'is-error', 'Error al guardar');
                }
            );
        }

        function autosaveNotes() {
            const statusSelector = '#autosave-status-notes';
            setAutosaveStatus(statusSelector, 'is-saving', 'Guardando...');

            let notesHtml = getNotesEditorHtml();
            if (typeof compressBase64Images === 'function') {
                notesHtml = compressBase64Images(notesHtml);
            }

            const payload = {
                text: notesHtml,
                order_id: parseInt($('#order-id').val(), 10),
            };

            postSilent('/report/notes/update', 'notes', payload,
                function() {
                    $('#notes').val(notesHtml);
                    setAutosaveStatus(statusSelector, 'is-saved', 'Guardado automático');
                },
                function() {
                    setAutosaveStatus(statusSelector, 'is-error', 'Error al guardar');
                }
            );
        }

        function autosaveRecommendations(serviceId) {
            const statusSelector = `#autosave-status-recommendation-${serviceId}`;
            setAutosaveStatus(statusSelector, 'is-saving', 'Guardando...');

            const recommendationsHtml = getRecommendationEditorHtml(serviceId);
            const payload = {
                service_id: parseInt(serviceId, 10),
                text: recommendationsHtml,
                order_id: parseInt($('#order-id').val(), 10),
            };

            postSilent('/report/recommendations/update', 'recommendations', payload,
                function() {
                    setAutosaveStatus(statusSelector, 'is-saved', 'Guardado automático');
                },
                function() {
                    setAutosaveStatus(statusSelector, 'is-error', 'Error al guardar');
                }
            );
        }

        function queueAutosave(key, callback) {
            clearTimeout(autosaveTimers[key]);
            autosaveTimers[key] = setTimeout(callback, autosaveDelayMs);
        }

        $(document).on('quill-editor-change', function(event, autosaveType, serviceId) {
            if (!autosaveReady) {
                return;
            }

            if (autosaveType === 'notes') {
                setAutosaveStatus('#autosave-status-notes', '', 'Cambios pendientes...');
                queueAutosave('notes', autosaveNotes);
                return;
            }

            if (autosaveType === 'service' && serviceId) {
                const statusSelector = `#autosave-status-service-${serviceId}`;
                setAutosaveStatus(statusSelector, '', 'Cambios pendientes...');
                queueAutosave(`service-${serviceId}`, function() {
                    autosaveServiceDescription(serviceId);
                });
                return;
            }

            if (autosaveType === 'recommendation' && serviceId) {
                const statusSelector = `#autosave-status-recommendation-${serviceId}`;
                setAutosaveStatus(statusSelector, '', 'Cambios pendientes...');
                queueAutosave(`recommendation-${serviceId}`, function() {
                    autosaveRecommendations(serviceId);
                });
            }
        });
    })();
</script>
