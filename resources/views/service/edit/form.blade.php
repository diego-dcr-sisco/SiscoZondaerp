@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('service.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR SERVICIO <span class="ms-2 fs-4"> {{ $service->name }}</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('service.update', ['id' => $service->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="fw-bold mb-2 fs-5">Datos del servicio</div>
                    <div class="col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('service.data.name') }}</label>
                        <div class="input-group">
                            <select class="input-group-text bg-warning" id="prefix" name="prefix">
                                @foreach ($prefixes as $prefix)
                                    <option value="{{ $prefix->id }}"
                                        {{ $prefix->id == $service->prefix ? 'selected' : '' }}>
                                        {{ $prefix->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control rounded-end" id="name" name="name"
                                placeholder="{{ __('service.data.input.name') }}" value="{{ $service->name }}"
                                autocomplete="off" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('service.data.service_type') }} </label>
                        <select class="form-select" id="service_type" name="service_type_id" required>
                            @foreach ($service_types as $service_type)
                                <option value="{{ $service_type->id }}"
                                    {{ $service_type->id == $service->service_type_id ? 'selected' : '' }}>
                                    {{ $service_type->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-lg-3 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('service.data.business_line') }}</label>
                        <select class="form-select " id="business_line" name="business_line_id" required>
                            <option value="" selected disabled>Selecciona una opción</option>
                            @foreach ($business_lines as $business_line)
                                <option value="{{ $business_line->id }}" @if ($business_line->id == $service->business_line_id) selected @endif>
                                    {{ $business_line->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="description" class="form-label is-required">Descripción del servicio</label>
                        <div class="summernote" style="font-size:12px;">
                            {!! $service->description !!}
                        </div>
                        <input type="hidden" id="description" name="description" value="{{ $service->description }}" />
                    </div>

                    <div class="col-lg-2 col-12 mb-3">
                        <label for="name" class="form-label">Costo del servicio</label>
                        <div class="input-group mb-0">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="cost" name="cost"
                                value="{{ $service->cost }}" min="0" placeholder="0" step="0.01" />
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.update') }}
            </button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 250,
                lang: 'es-ES',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['insert', ['table', 'link', 'picture']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['fontsize', ['fontsize']],
                ],
                buttons: {
                    imageSizeCustom: function(context) {
                        const ui = $.summernote.ui;

                        return ui.button({
                            contents: '<span class="note-icon-magic"></span>',
                            tooltip: 'Tamaño personalizado',
                            click: function() {
                                const editable = context.layoutInfo.editable;
                                const target = editable.data('target');

                                if (!target || target[0].tagName !== 'IMG') {
                                    return;
                                }

                                const currentWidth = parseInt(target.css('width'), 10) ||
                                    parseInt(target.attr('width'), 10) || 100;

                                const input = prompt(
                                    'Ingresa el ancho de la imagen (en px). Ejemplo: 320',
                                    currentWidth
                                );

                                if (input === null) {
                                    return;
                                }

                                const width = parseInt(input, 10);
                                if (Number.isNaN(width) || width <= 0) {
                                    alert('Ingresa un valor valido mayor a 0.');
                                    return;
                                }

                                target.css({
                                    width: width + 'px',
                                    height: 'auto',
                                    maxWidth: '100%'
                                });
                                target.attr('width', width);
                            }
                        }).render();
                    }
                },
                popover: {
                    image: [
                        ['imagesize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'imageSizeCustom']],
                        ['float', ['floatLeft', 'floatRight', 'floatNone']],
                        ['remove', ['removeMedia']]
                    ]
                },
                fontSize: ['8', '10', '12', '14', '16'],
                lineHeights: ['0.25', '0.5', '1', '1.5', '2'],
                callbacks: {
                    onChange: function(contents, $editable) {
                        $('#description').val(contents);
                    }
                }
            });
        });
    </script>
@endsection
