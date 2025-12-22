@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index.sedes') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                SEDES DEL CLIENTE</span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <div class="p-3">
            <div class="mb-3">
                @can('write_customer')
                    <a class="btn btn-primary btn-sm" href="{{ route('customer.create.sede', ['matrix' => $customer->id]) }}">
                        <i class="bi bi-plus-lg fw-bold"></i> Crear sede
                    </a>
                @endcan
            </div>
            <div class="table-responsive">
                <!-- Tabla de clientes -->
                <table class="table table-sm table-bordered table-striped caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">
                        <form action="{{ route('customer.search') }}" method="GET">
                            @csrf
                            <div class="row g-3 mb-0">
                                <div class="col-lg-6 col-12">
                                    <label for="customer" class="form-label">Nombre</label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name"
                                        value="{{ request('name') }}" placeholder="Buscar nombre">
                                </div>

                                <div class="col-auto">
                                    <label for="date_range" class="form-label">Tipo</label>
                                    <select class="form-select form-select-sm" name="type">
                                        @foreach ($service_types as $service_type)
                                            <option value="{{ $service_type->id }}"
                                                {{ $customer->service_type_id == $service_type->id ? 'selected' : '' }}>
                                                {{ $service_type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="signature_status" class="form-label">Dirección</label>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                            DESC
                                        </option>
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
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
                            <th class="fw-bold" scope="col">#</th>
                            <th class="fw-bold" scope="col"> {{ __('customer.data.name') }} </th>
                            <th class="fw-bold" scope="col"> {{ __('customer.data.code') }} </th>
                            <th class="fw-bold" scope="col"> {{ __('customer.data.phone') }} </th>
                            <th class="fw-bold" scope="col"> {{ __('customer.data.email') }} </th>
                            <th class="fw-bold" scope="col"> {{ __('customer.data.type') }}</th>
                            <th class="fw-bold" scope="col">{{ __('customer.data.origin') }}</th>
                            <th class="fw-bold" scope="col"> Método de contacto </th>
                            <th class="fw-bold" scope="col"> {{ __('customer.data.created_at') }}</th>
                            <th class="fw-bold" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->sedes as $index => $sede)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $sede->name }}</td>
                                <td>{{ $sede->code }}</td>
                                <td>{{ $sede->phone }}</td>
                                <td>{{ $sede->email }}</td>
                                <td>{{ $sede->serviceType->name }}</td>
                                <td>
                                    @isset($sede->matrix->name)
                                        <a href="{{ route('customer.edit', ['id' => $sede->matrix->id]) }}">
                                            {{ $sede->matrix->name }} [{{ $sede->matrix->id }}]
                                        </a>
                                    @else
                                        Matriz
                                    @endisset
                                </td>
                                <td> {{ $sede->contactMedium() }} </td>
                                <td>
                                    {{ Carbon\Carbon::parse($sede->created_at, 'UTC')->setTimezone('America/Mexico_City')->format('Y-m-d H:i:s') }}
                                    {{-- $sede->created_at --}}
                                </td>
                                <td>
                                    @can('write_customer')
                                        <a href="{{ route('customer.quote', ['id' => $customer->id, 'class' => 'customer']) }}"
                                            class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Cotizaciones">
                                            <i class="bi bi-calculator-fill"></i>
                                        </a>

                                        <a href="{{ route('customer.edit.sede', ['id' => $sede->id]) }}"
                                            class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Editar sede">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="{{ route('customer.destroy', ['id' => $customer->id]) }}"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"
                                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar sede">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
