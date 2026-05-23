@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                CREAR PRODUCTO
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data">
            @csrf
            <!-- Datos generales del producto -->
            <div class="border rounded shadow p-3 mb-3">
                <div class="row">
                    <div class="fw-bold mb-2 fs-5">Datos del producto</div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="name" class="form-label is-required">{{ __('product.data.name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="business_name" class="form-label">{{ __('product.data.business_name') }}
                        </label>
                        <input type="text" class="form-control" id="business-name" name="business_name">
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="manufacturer"
                            class="form-label">{{ __('product.data.manufacturer') }}/distribuidor</label>
                        <input type="text" class="form-control" name="manufacturer" id="manufacturer">
                    </div>

                    <div class="col-lg-4 col-12 mb-3">
                        <label for="presentation" class="form-label is-required">
                            {{ __('product.data.presentation') }}</label>
                        <select class="form-select " name="presentation_id" id="presentation" required>
                            @foreach ($presentations as $presentation)
                                <option value="{{ $presentation->id }}">{{ $presentation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="line-business"
                            class="form-label is-required">{{ __('product.data.line_business') }}</label>
                        <select class="form-select " id="linebusiness" name="linebusiness_id" required>
                            @foreach ($line_business as $line)
                                <option value="{{ $line->id }}" {{ $line->id == 2 ? 'selected' : '' }}>
                                    {{ $line->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-12 mb-3">
                        <label for="application_method"
                            class="form-label is-required">{{ __('product.data.metric') }}</label>
                        <select class="form-select " name="metric_id" id="metric">
                            @foreach ($metrics as $metric)
                                <option value="{{ $metric->id }}">{{ $metric->value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-9 col-12 mb-3">
                        <label for="type_b" class="form-label is-required">{{ __('product.data.biocide') }}</label>
                        <select class="form-select " name="biocide_id" id="biocide" required>
                            @foreach ($biocides as $biocide)
                                <option value="{{ $biocide->id }}"> ({{ $biocide->group }}) {{ $biocide->type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-3">
                        <label for="purpose" class="form-label is-required">{{ __('product.data.purpose') }}</label>
                        <select class="form-select " name="purpose_id" id="purpose" required>
                            @foreach ($purposes as $purpose)
                                <option value="{{ $purpose->id }}">{{ $purpose->type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6 col-12 mb-3">
                        <label for="description" class="form-label">{{ __('product.data.description') }}
                        </label>
                        <textarea class="form-control" name="description" id="description" rows="4"> </textarea>
                    </div>
                    <div class="col-lg-6 col-12 mb-3">
                        <label for="execution_indications"
                            class="form-label">{{ __('product.data.execution_indications') }}</label>
                        <textarea class="form-control" name="execution_indications" id="execution_indications" rows="4"> </textarea>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="image" class="form-label">{{ __('product.data.image') }}</label>
                        <input type="file" class="form-control-file form-control" name="image" id="image">
                    </div>
                </div>
            </div>

            <div class="border rounded shadow p-3 mb-3">
                <div class="accordion accordion-flush" id="accordionPest">
                    <div class="row">
                        @foreach ($pest_categories as $i => $pest_category)
                            <div class="col-lg-4 col-12">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed border-bottom" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $i }}"
                                            aria-expanded="true" aria-controls="collapse{{ $i }}">
                                            {{ $pest_category->category }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $i }}" class="accordion-collapse collapse"
                                        data-bs-parent="#accordionPest">
                                        <div class="accordion-body">
                                            @if (!$pest_category->pests->isEmpty())
                                                @foreach ($pest_category->pests as $pest)
                                                    <div class="form-check">
                                                        <input class="pest form-check-input " type="checkbox"
                                                            value="{{ $pest->id }}" onchange="setPests()"/>
                                                        <label class="form-check-label" for="pest-{{ $pest->id }}">
                                                            {{ $pest->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-danger fw-bold"> No hay plagas asociadas </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="pests-selected" name="pests_selected" value="" />
            </div>

            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="fw-bold mb-2 fs-5">Métodos de aplicacion seleccionados</div>
                    @foreach ($application_methods as $appMethod)
                        <div class="col-lg-4 col-12">
                            <div class="form-check">
                                <input class="appMethod form-check-input " type="checkbox" value="{{ $appMethod->id }}"
                                    onchange="setAppMethods()"/>
                                <label class="form-check-label" for="app_method-{{ $appMethod->id }}">
                                    {{ $appMethod->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <input type="hidden" id="appMethods-selected" name="appMethods_selected" value="" />
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3"> {{ __('buttons.store') }} </button>
        </form>
    </div>

    <script src="{{ asset('js/handleSelect.js') }}"></script>
@endsection
