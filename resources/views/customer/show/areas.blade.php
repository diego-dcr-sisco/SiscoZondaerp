@extends('layouts.app')
@section('content')

@php
    $area_colors = [
        'F5F5F4',
        'A6A09B',
        '0C0A09'
    ]    
@endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index.sedes') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                AREAS DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <div class="p-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#areaCreateModal">Agregar area</button>
            </div>
            <div class="table-responsive">
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
