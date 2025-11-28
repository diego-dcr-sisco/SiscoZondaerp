@extends('layouts.app')
@section('content')
@php
    $offset = ($services->currentPage() - 1) * $services->perPage();
@endphp
    <div class="container-fluid">
        <div class="py-3">
            @can('write_customer')
                <a class="btn btn-primary btn-sm" href="{{ route('service.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> {{ __('service.button.create') }}
                </a>
            @endcan
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <caption class="border rounded-top p-2 text-dark bg-light caption-top">
                    <form action="{{ route('service.search') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-4 col-12">
                                <label for="version" class="form-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm" name="name" value="{{ request('name') }}"/>
                            </div>

                            <div class="col-auto">
                                <label for="control-point" class="form-label">Tipo de servicio</label>
                                <select class="form-select form-select-sm" name="type_id">
                                    <option value="">Todos</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="app_area" class="form-label">Prefijo</label>
                                <select class="form-select form-select-sm" id="prefix" name="prefix">
                                    <option value="">Todos</option>
                                    @foreach ($prefix as $p)
                                        <option value="{{ $p->id }}" {{ request('prefix') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="signature_status" class="form-label">Dirección</label>
                                <select class="form-select form-select-sm" id="direction" name="direction">
                                    <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                    </option>
                                    <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="order_type" class="form-label">Total</label>
                                <select class="form-select form-select-sm" id="size" name="size">
                                    <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                    <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="col-lg-12 d-flex justify-content-end m-0">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('service.data.name') }}</th>
                        <th scope="col">Id</th>
                        <th scope="col">{{ __('service.data.type') }}</th>
                        <th scope="col">{{ __('service.data.prefix') }}</th>
                        <th scope="col">{{ __('service.data.cost') }} ($)</th>
                        <th scope="col">Plagas</th>
                        <th scope="col">Métodos de aplicación</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $index => $service)
                        <tr>
                            <th scope="row">{{ $offset + $index + 1 }}</th>
                            <td>{{ $service->name }}</td>
                            <th scope="row">{{ $service->id }}</th>
                            <td>
                                {{ $service->serviceType->name }}
                            </td>
                            <td>{{ $service->prefixType->name }}</td>
                            <td>${{ $service->cost }}</td>
                            <td class="fw-bold {{ $service->has_pests ? 'text-success' : 'text-danger' }}">
                                {{ $service->has_pests ? 'Si' : 'No' }}
                            </td>

                            <td class="fw-bold {{ $service->has_application_methods ? 'text-success' : 'text-danger' }}">
                                {{ $service->has_application_methods ? 'Si' : 'No' }}
                            </td>
                            <td>
                                @can('write_service')
                                    <a href="{{ route('service.edit', ['id' => $service->id]) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-title="Editar servicio"
                                        class="btn btn-secondary btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('service.destroy', ['id' => $service->id]) }}"
                                        class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Eliminar servicio"
                                        onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $services->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
