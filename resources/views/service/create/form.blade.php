<form class="m-3" method="POST" action="{{ route('service.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="border rounded shadow p-3 mb-3">
        <div class="row">
            <div class="fw-bold mb-2 fs-5">Datos del servicio</div>
            <div class="col-12 mb-3">
                <label for="name" class="form-label is-required">{{ __('service.data.name') }}</label>
                <div class="input-group">
                    <select class="input-group-text bg-warning" id="prefix" name="prefix">
                        @foreach ($prefixes as $prefix)
                            <option value="{{ $prefix->id }}" {{ $prefix->id == 1 ? 'selected' : '' }}>
                                {{ $prefix->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" class="form-control rounded-end " id="name" name="name"
                        placeholder="Control de plagas" value="{{ old('name') }}" autocomplete="off" required />
                </div>
            </div>

            <div class="col-lg-3 col-12 mb-3">
                <label for="name" class="form-label is-required">{{ __('service.data.type') }}</label>
                <select class="form-select " id="service_type_id" name="service_type_id" required>
                    @foreach ($service_types as $service_type)
                        <option value="{{ $service_type->id }}">{{ $service_type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-12 mb-3">
                <label for="name" class="form-label is-required">{{ __('service.data.business_line') }}</label>
                <select class="form-select " id="business_line_id" name="business_line_id" required>
                    @foreach ($business_lines as $business_line)
                        <option value="{{ $business_line->id }}" {{ $business_line->id == 2 ? 'selected' : '' }}>
                            {{ $business_line->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label for="description" class="form-label is-required">Descripción del servicio</label>
                <div class="summernote" id="summary-describe" style="font-size:12px;">
                </div>
                <input type="hidden" id="description" name="description" />
            </div>

            <div class="col-lg-2 col-12 mb-3">
                <label for="name" class="form-label">Costo del servicio</label>
                <div class="input-group mb-0">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="cost" name="cost" value=""
                        min="0" placeholder="0" step="0.01" />
                </div>
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3 mb-3">
        <div class="row">
            <div class="fw-bold mb-2 fs-5">Plagas</div>
            <div class="accordion accordion-flush row" id="accordionPest">
                @foreach ($pest_categories as $i => $pest_category)
                    <div class="accordion-item col-lg-4 col-12 border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed border-bottom" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}"
                                aria-expanded="true" aria-controls="collapse{{ $i }}">
                                {{ $pest_category->category }}
                            </button>
                        </h2>
                        @if (!$pest_category->pests->isEmpty())
                            <div id="collapse{{ $i }}" class="accordion-collapse collapse"
                                data-bs-parent="#accordionPest">
                                <div class="accordion-body">
                                    @foreach ($pest_category->pests as $pest)
                                        <div class="form-check">
                                            <input class="pest form-check-input" type="checkbox"
                                                value="{{ $pest->id }}" onchange="setPests()" />
                                            <label class="form-check-label" for="pest-{{ $pest->id }}">
                                                {{ $pest->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div id="collapse{{ $i }}" class="accordion-collapse collapse"
                                data-bs-parent="#accordionPest">
                                <div class="accordion-body text-danger fw-bold">
                                    No hay plagas asociadas.
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <input type="hidden" id="pests-selected" name="pests_selected" value="" />
    </div>

    <div class="border rounded shadow p-3">
        <div class="row">
            <div class="fw-bold mb-2 fs-5">Métodos de aplicación</div>
            @foreach ($application_methods as $app_method)
                <div class="col-lg-4 col-12">
                    <div class="form-check">
                        <input class="appMethod form-check-input" type="checkbox" value="{{ $app_method->id }}"
                            onchange="setAppMethods()" />
                        <label class="form-check-label" for="app_method-{{ $app_method->id }}">
                            {{ $app_method->name }}
                        </label>
                    </div>
                </div>
            @endforeach
            <input type="hidden" id="appMethods-selected" name="appMethods_selected" value="" />
        </div>
    </div>

    <button type="submit" class="btn btn-primary my-3">
        {{ __('buttons.store') }}
    </button>

</form>

<script src="{{ asset('js/handleSelect.js') }}"></script>

<script>
    $(document).ready(function() {
        $('input[type="checkbox"]').prop('checked', false);
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
            fontSize: ['8', '10', '12', '14', '16'],
            lineHeights: ['0.25', '0.5', '1', '1.5', '2'],
            callbacks: {
                onChange: function(contents, $editable) {
                    $('#description').val(contents);
                }
            }
        });
    });

    function set_instructions() {
        var description = ``;
        var value = $('#prefix').val();

        if (value == 2) {
            description = `
            ANTES DE LA APLICACIÓN QUÍMICA
                - Identificar la plaga a controlar.
                - No debe encontrarse personal en el área.
                - No debe de haber materia prima expuesta.
                - Asegurar que la aplicación no afecte el proceso, producción o a terceros.

            DURANTE DE LA APLICACIÓN QUÍMICA
                - En el área solo debe de encontrarse el técnico aplicador.

            DESPUÉS DE LA APLICACIÓN QUÍMICA
                - Respetar el tiempo de reentrada conforme a la etiqueta del producto a utilizar.
                - Realizar recolección de plaga o limpieza necesaria al tipo de área.
                
        `;
        }

        $('#description').html(description);
    }
</script>
