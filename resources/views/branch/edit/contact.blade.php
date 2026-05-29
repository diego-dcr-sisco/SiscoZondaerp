@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'EDITAR SUCURSAL',
        'icon' => 'bi-diagram-3',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid p-0">
<div class="px-3 mt-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ($navigation as $label => $route)
                            <a href="{{ $route }}" class="btn {{ $label === 'Contacto' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('branch.update', ['id' => $branch->id]) }}" class="m-3"
            enctype="multipart/form-data">
            @csrf
            <div class="border rounded shadow-sm p-3">
                <div class="row g-3">
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
            <button type="submit" class="btn btn-primary my-3">
                <i class="bi bi-save"></i> {{ __('buttons.store') }}
            </button>
        </form>
        @include('components.danger-action', [
            'actionRoute' => route('branch.destroy', ['id' => $branch->id]),
            'title' => 'Zona de peligro',
            'description' => 'Elimina esta sucursal desde su pantalla de edición.',
            'buttonText' => 'Eliminar sucursal',
        ])
    </div>
@endsection
