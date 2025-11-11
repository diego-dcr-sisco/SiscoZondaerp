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
            <a href="{{ route('branch.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR SUCURSAL <span class="ms-2 fs-4"> {{ $branch->name }}</span>
            </span>
        </div>

        <form method="POST" action="{{ route('branch.update', ['id' => $branch->id]) }}" class="m-3"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow p-3">
                <div class="row">
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="email" class="form-label">{{ __('modals.branch_data.email') }}: </label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ $branch->email }}">
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="email" class="form-label ">Correo alternativo: </label>
                        <input type="email" class="form-control" id="alt-email" name="alt_email"
                            value="{{ $branch->alt_email }}">
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="phone" class="form-label">{{ __('modals.branch_data.phone') }}: </label>
                        <input type="text" class="form-control" id="phone" name="phone"
                            value="{{ $branch->phone }}">
                    </div>
                    <div class="col-lg-4 col-12 mb-3">
                        <label for="alt_phone" class="form-label">Teléfono alternativo: </label>
                        <input type="text" class="form-control" id="alt_phone" name="alt_phone"
                            value="{{ $branch->alt_phone }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-3"> {{ __('buttons.store') }} </button>
        </form>
    </div>
@endsection
