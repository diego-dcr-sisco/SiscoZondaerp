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
        <div class="mb-3">
            @include('service.search')
        </div>

        <div class="overflow-auto w-100">
            <table class="table table-bordered table-striped table-sm">
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
