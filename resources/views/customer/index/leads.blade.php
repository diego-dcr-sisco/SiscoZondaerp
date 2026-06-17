@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CLIENTES POTENCIALES',
        'icon' => 'bi-person-plus',
        'actionRoute' => route('customer.create.lead'),
        'actionText' => 'Crear cliente potencial',
    ])
    <div class="container-fluid">
        <div class="mb-3">
            @include('customer.search.leads')
        </div>
        
        <div class="table-responsive">
            <!-- Tabla de clientes -->
            <table class="table table-sm table-bordered table-striped caption-top">
                @php
                    $offset = ($customers->currentPage() - 1) * $customers->perPage();
                @endphp
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
                            <td>
                                @can('write_customer')
                                    <a href="{{ route('customer.quote', ['id' => $customer->id, 'class' => 'lead']) }}"
                                        class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Cotizaciones">
                                        <i class="bi bi-calculator-fill"></i>
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
