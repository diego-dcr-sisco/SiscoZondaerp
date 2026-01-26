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
                CREAR PLAGA
            </span>
        </div>

        <form class="m-3" method="POST" action="{{ route('pest.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-3 mb-3">
                    <div class="card border rounded shadow" style="height: 100%;">
                        <div class="card-header">Imagen</div>
                        <img src="{{ asset('img/default.jpg') }}"
                            alt="pest-img">
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
                                    placeholder="Nombre popular (Nombre científico)" required>
                            </div>
                            <div class="col-lg-4 col-12 mb-3">
                                <label for="pcode" class="form-label">{{ __('pest.data.code') }}:
                                </label>
                                <input type="text" class="form-control" id="pest-code" name="pest_code"
                                    placeholder="Ejemplo (Abeja => ABJ-12)">
                            </div>
                            <div class="col-lg-8 col-12 mb-3">
                                <label for="categid" class="form-label is-required">{{ __('pest.data.category') }}:
                                </label>
                                <select class="form-select " id="pest-category-ID" name="pest_category_id">
                                    @foreach ($pest_categories as $pestCategory)
                                        <option value="{{ $pestCategory->id }}">{{ $pestCategory->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="desc" class="form-label">{{ __('pest.data.description') }}:
                                </label>
                                <textarea class="form-control" id="description" name="description"
                                    placeholder="Describe las características observadas de la plaga, comportamiento, ubicación detectada, y cualquier otro detalle relevante..."
                                    ></textarea>
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

    <script>
        $(document).ready(function() {});

        function showToxic(value) {
            const is_toxic = parseInt(value);
            if (is_toxic) {
                $('#toxic').show();
            } else {
                $('#toxic').hide();
            }
        }

        function getCheckedCheckboxIDs(arr) {
            if (arr !== null) {
                return arr
                    .toArray()
                    .filter((checkbox) => checkbox.checked)
                    .map((checkbox) => flush_id(checkbox.id));
            } else {
                return [];
            }
        }

        function storeInputJSON(e) {
            pest_arr = getCheckedCheckboxIDs($(".pest"))
            if (pest_arr.length === 0) {
                e.preventDefault();
                $('#toast-simple-message').html(
                    `<span class="">
                    Por favor seleccione al menos 1 plaga
                </span>`
                );
                handle_toast();
            } else {
                $('#pests').val(JSON.stringify(pest_arr));
            }
        }

        function flush_id(str) {
            return str.replace(/\D/g, "");
        }

        function handle_toast() {
            const toastLiveExample = document.getElementById('toast-simple-notification');
            const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample);
            toastBootstrap.show();
        }
    </script>
@endsection
