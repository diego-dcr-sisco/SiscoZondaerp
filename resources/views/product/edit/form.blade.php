@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }

        function extractFileName($filePath)
        {
            $fileNameWithExtension = basename($filePath);
            $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

            return $fileName;
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR PRODUCTO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $product->name }}</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('product.update', ['id' => $product->id]) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-3 mb-3">
                    <div class="card border rounded shadow" style="height: 100%;">
                        <div class="card-header">Imagen</div>
                        <img src="{{ $product->image_path ? route('image.show', $product->image_path) : asset('img/default.jpg') }}"
                            alt="product-img">
                        <div class="card-body">

                        </div>
                        <div class="card-footer">
                            <div class="mb-3">
                                <label for="formFile" class="form-label">Agregar archivo</label>
                                <input class="form-control" type="file" id="image" name="image"
                                    accept=".jpg, .png, .jpeg">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-9 mb-3">
                    <div class="border rounded shadow p-3">
                        <div class="fw-bold mb-2 fs-5">Datos del producto</div>
                        <div class="row">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="name" class="form-label is-required">{{ __('product.data.name') }}</label>
                                <input type="text" class="form-control" name="name" value="{{ $product->name }}"
                                    maxlength="50">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="business_name" class="form-label">{{ __('product.data.business_name') }}</label>
                                <input type="text" class="form-control" name="business_name"
                                    value="{{ $product->business_name }}" maxlength="50">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="manufacturer"
                                    class="form-label">{{ __('product.data.manufacturer') }}/distribuidor
                                </label>
                                <input type="text" class="form-control" name="manufacturer"
                                    value="{{ $product->manufacturer }}">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="register_number" class="form-label">{{ __('product.data.register_number') }}:
                                </label>
                                <input type="text" class="form-control" name="register_number"
                                    value="{{ $product->register_number }}">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="presentation"
                                    class="form-label is-required">{{ __('product.data.presentation') }}</label>
                                <select class="form-select " name="presentation_id" id="presentation">
                                    @foreach ($presentations as $presentation)
                                        <option value="{{ $presentation->id }}"
                                            {{ $presentation->id == $product->presentation_id ? 'selected' : '' }}>
                                            {{ $presentation->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4 col-12 mb-3">
                                <label for="linebusiness"
                                    class="form-label is-required">{{ __('product.data.line_business') }}</label>
                                <select class="form-select " name="linebusiness_id" id="linebusiness">
                                    @foreach ($line_business as $line)
                                        <option value="{{ $line->id }}"
                                            {{ $line->id == $product->linebusiness_id ? 'selected' : '' }}>
                                            {{ $line->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4 col-12 mb-3">
                                <label for="application_method"
                                    class="form-label is-required">{{ __('product.data.metric') }}</label>
                                <select class="form-select " name="metric_id" id="metric">
                                    @foreach ($metrics as $metric)
                                        <option value="{{ $metric->id }}"
                                            {{ $metric->id == $product->metric_id ? 'selected' : '' }}>
                                            {{ $metric->value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 col-12 mb-3">
                                <label for="valid_date" class="form-label">{{ __('product.data.validity_date') }}</label>
                                <input type="date" class="form-control" name="validity_date"
                                    value="{{ $product->validity_date }}">
                            </div>

                            <div class="col-lg-2 col-12 mb-3">
                                <label for="purpose"
                                    class="form-label is-required">{{ __('product.data.purpose') }}</label>
                                <select class="form-select " name="purpose" id="purpose">
                                    @foreach ($purposes as $purpose)
                                        <option value="{{ $purpose->id }}"
                                            {{ $purpose->id == $product->purpose_id ? 'selected' : '' }}>
                                            {{ $purpose->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-8 col-12 mb-3">
                                <label for="biocide"
                                    class="form-label is-required">{{ __('product.data.biocide') }}</label>
                                <select class="form-select " name="biocide_id" id="biocide" required>
                                    @foreach ($biocides as $biocide)
                                        <option value="{{ $biocide->id }}"
                                            {{ $product->biocide_id == $biocide->id ? 'selected' : '' }}>
                                            ({{ $biocide->group }})
                                            {{ $biocide->type }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="description" class="form-label">{{ __('product.data.description') }}
                                </label>
                                <textarea class="form-control" name="description" id="description" rows="4"> {{ $product->description }} </textarea>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="execution_indications"
                                    class="form-label">{{ __('product.data.execution_indications') }}</label>
                                <textarea class="form-control" name="execution_indications" id="execution_indications" rows="4"> {{ $product->execution_indications }} </textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-12">
                    <div class="border rounded shadow p-3">
                        <div class="fw-bold mb-2 fs-5">Detalles técnicos de uso</div>
                        <div class="row">
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="active_ingredient"
                                    class="form-label">{{ __('product.data.active_ingredient') }}
                                    :</label>
                                <input type="text" class="form-control" name="active_ingredient"
                                    value="{{ $product->active_ingredient }}">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="per_active_ingredient"
                                    class="form-label">{{ __('product.data.per_active_ingredient') }}
                                    :</label>
                                <input type="number" step="0.0001" class="form-control" name="per_active_ingredient"
                                    value="{{ $product->per_active_ingredient }}" min=0>
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="dosage" class="form-label">{{ __('product.data.dosage') }} :</label>
                                <input type="text" class="form-control" name="dosage"
                                    value="{{ $product->dosage }}">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="safety_period" class="form-label">{{ __('product.data.safety_period') }}
                                    :</label>
                                <input type="text" class="form-control" name="safety_period"
                                    value="{{ $product->safety_period }}" maxlength="50">
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="residual_effect" class="form-label">{{ __('product.data.residual_effect') }}
                                    :</label>
                                <input type="text" class="form-control" name="residual_effect"
                                    value="{{ $product->residual_effect }}">
                            </div>

                            <div class="col-lg-4 col-12 mb-3">
                                <label for="bar_code" class="form-label">{{ __('product.data.bar_code') }}</label>
                                <input type="number" class="form-control" name="bar_code"
                                    value="{{ $product->bar_code }}" maxlength="50">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
        </form>
    </div>
@endsection
