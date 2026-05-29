@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'VER CLIENTE - AREAS',
        'icon' => 'bi-people',
        'backRoute' => url()->previous(),
    ])
<div class="container-fluid p-0">
<div class="p-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#areaCreateModal">Agregar area</button>
            </div>
            <div class="overflow-auto w-100">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="fw-bold" scope="col">#</th>
                            <th class="fw-bold" scope="col">Nombre</th>
                            <th class="fw-bold" scope="col">Tipo</th>
                            <th class="fw-bold" scope="col">Area [m²]</th>
                            <th class="fw-bold" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->applicationAreas as $index => $area)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="">{{ $area->name }}</td>
                                <td class="">{{ $area->zoneType->name ?? '-' }}</td>
                                <td class="">{{ $area->m2 }}</td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                        data-area="{{ $area }}" data-bs-target="#areaEditModal"
                                        onclick="setInputs(this)">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="{{ route('area.destroy', ['id' => $area->id]) }}" class="btn btn-danger btn-sm"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar área"
                                        onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('customer.modals.area.create')
    @include('customer.modals.area.edit')
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
        }))
    </script>
@endsection
