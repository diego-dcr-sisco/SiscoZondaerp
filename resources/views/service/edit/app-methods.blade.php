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
                    <div class="fw-bold mb-2 fs-5">Métodos de aplicacion seleccionados</div>
                    @foreach ($application_methods as $appMethod)
                        <div class="col-lg-4 col-12">
                            <div class="form-check">
                                <input class="appMethod form-check-input " type="checkbox" value="{{ $appMethod->id }}"
                                    onchange="setAppMethods()"
                                    {{ $service->hasAppMethod($appMethod->id) ? 'checked' : '' }} />
                                <label class="form-check-label" for="app_method-{{ $appMethod->id }}">
                                    {{ $appMethod->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <input type="hidden" id="appMethods-selected" name="appMethods_selected" value="" />
                </div>
            </div>

            <button type="submit" class="btn btn-primary my-3">
                {{ __('buttons.update') }}
            </button>
        </form>
    </div>
    <script src="{{ asset('js/handleSelect.js') }}"></script>
@endsection
