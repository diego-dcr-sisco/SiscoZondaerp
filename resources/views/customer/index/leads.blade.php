@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="py-3">
            @can('write_customer')
                <a class="btn btn-primary btn-sm" href="{{ route('customer.create.lead') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear cliente potencial
                </a>
            @endcan
        </div>

        
        <div class="table-responsive">
            <!-- Tabla de clientes -->
            <table class="table table-sm table-bordered table-striped caption-top">
                @php
                    $offset = ($customers->currentPage() - 1) * $customers->perPage();
                @endphp
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
                                <select class="form-select form-select-sm" name="service_type">
                                    <option value="">Todos</option>
                                    @foreach ($service_types as $service_type)
                                        <option value="{{ $service_type->id }}"
                                            {{ request('service_type') == $service_type->id ? 'selected' : '' }}>
                                            {{ $service_type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="time" class="form-label">Categoria</label>
                                <select class="form-select form-select-sm" name="category">
                                    @foreach ($categories as $key => $category)
                                        <option value="{{ $key }}"
                                            {{ request('category') == $key || $key == 3 ? 'selected' : '' }}>
                                            {{ $category }}
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
                        <th class="fw-bold" scope="col"> {{ __('customer.data.phone') }} </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.email') }} </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.type') }}</th>
                        <th class="fw-bold" scope="col">{{ __('customer.data.reason') }}</th>
                        <th class="fw-bold" scope="col"> Método de contacto </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.created_at') }}</th>
                        <th class="fw-bold" scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $index => $customer)
                        <tr>
                            <th scope="row">{{ $offset + $index + 1 }}</th>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->serviceType->name ?? '-' }}</td>
                            <td>{{ $customer->reason ?? 'S/N' }}</td>
                            <td> {{ $customer->contactMedium() }} </td>
                            <td>
                                {{ Carbon\Carbon::parse($customer->created_at, 'UTC')->setTimezone('America/Mexico_City')->format('Y-m-d H:i:s') }}
                                {{-- $customer->created_at --}}
                            </td>
                            <td class="text-center">
                                @can('write_customer')
                                    <a href="{{ route('customer.quote', ['id' => $customer->id, 'class' => 'lead']) }}"
                                        class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Cotizaciones">
                                        <i class="bi bi-clipboard-data-fill"></i>
                                    </a>

                                    <a href="{{ route('customer.edit.lead', ['id' => $customer->id]) }}"
                                        class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Editar cliente potencial">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="{{ route('customer.convert', ['id' => $customer->id]) }}"
                                        class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Convertir a cliente"
                                        onclick="return confirm('Deseas convertir a cliente?')">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a href="{{ route('customer.destroy.lead', ['id' => $customer->id]) }}"
                                        class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Eliminar cliente potencial"
                                        onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Incluir DateRangePicker -->
            <link rel="stylesheet" type="text/css"
                href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
        </div>
        {{ $customers->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
