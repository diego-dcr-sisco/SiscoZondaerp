@extends('layouts.app')
@section('content')
    @php
        $offset = ($services->currentPage() - 1) * $services->perPage();
    @endphp
    @include('components.page-header', [
        'title' => 'SERVICIOS',
        'icon' => 'bi-gear',
        'actionRoute' => route('service.create'),
        'actionText' => __('service.button.create'),
    ])
    <div class="container-fluid">
        <div class="overflow-auto w-100">
            <table class="table table-bordered table-striped table-sm">
                <caption class="border rounded-top p-2 text-dark bg-white caption-top">
                    <form action="{{ route('service.search') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-2 col-12">
                                <label for="name" class="form-label">Nombre</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                                    <input type="text" class="form-control form-control-sm" name="name"
                                        value="{{ request('name') }}" placeholder="Buscar por nombre..." />
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="type_id" class="form-label">Tipo de servicio</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-gear-fill"></i></span>
                                    <select class="form-select form-select-sm" name="type_id">
                                        <option value="">Todos</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}"
                                                {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="prefix" class="form-label">Prefijo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <select class="form-select form-select-sm" id="prefix" name="prefix">
                                        <option value="">Todos</option>
                                        @foreach ($prefix as $p)
                                            <option value="{{ $p->id }}"
                                                {{ request('prefix') == $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label class="form-label">Ordenar / Mostrar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                        </option>
                                    </select>
                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select form-select-sm" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-end g-3 mb-0 mt-0">
                            <div class="col-lg-1 col-6">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-lg-1 col-6">
                                <a href="{{ route('service.index') }}" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
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
