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
            <a href="{{ route('pest.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR PLAGA <span class="ms-2 fs-4"> {{ $pest->name }}</span>
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('pest.update', $pest->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-3 mb-3">
                    <div class="card border rounded shadow" style="height: 100%;">
                        <div class="card-header">Imagen</div>
                        <img src="{{ $pest->image ? route('image.show', $pest->image) : asset('img/default.jpg') }}" alt="pest-img">
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
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="name" class="form-label is-required">{{ __('pest.data.name') }}:
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $pest->name }}" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label for="pcode" class="form-label">{{ __('pest.data.code') }}:
                                </label>
                                <input type="text" class="form-control" id="pest-code" name="pest_code"
                                    value="{{ $pest->pest_code }}">
                            </div>
                            <div class="col-8 mb-3">
                                <label for="categid" class="form-label is-required">{{ __('pest.data.category') }}:
                                </label>
                                <select class="form-select " id="pest-category" name="pest_category_id">
                                    @foreach ($pest_categories as $pest_category)
                                        <option value="{{ $pest_category->id }}"
                                            {{ $pest->pest_category_id == $pest_category->id ? 'selected' : '' }}>
                                            {{ $pest_category->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="desc" class="form-label">{{ __('pest.data.description') }}:
                                </label>
                                <textarea class="form-control" id="description" name="description" placeholder="Descripción de la plaga">{{ $pest->description }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.store') }}
            </button>
        </form>
    </div>
@endsection
