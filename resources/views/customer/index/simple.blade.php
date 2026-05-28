@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'CLIENTES',
        'icon' => 'bi-people',
        'actionRoute' => route('customer.create'),
        'actionText' => __('customer.title.create'),
    ])
    <div class="container-fluid">
        <!-- Buscador -->
        <div class="mb-3">
            @include('customer.search.simple')
        </div>

        <div class="table-responsive">
            <!-- Tabla de clientes -->
            <table class="table table-sm table-bordered table-striped">
                @php
                    $offset = ($customers->currentPage() - 1) * $customers->perPage();
                @endphp
                <thead>
                    <tr>
                        <th class="fw-bold" scope="col">#</th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.name') }} </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.code') }} </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.phone') }} </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.email') }} </th>
                        <th class="fw-bold" scope="col"> {{ __('customer.data.type') }}</th>
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
                            <td>{{ $customer->code }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->serviceType->name }}</td>
                            <td> {{ $customer->contactMedium() }} </td>
                            <td>
                                {{ Carbon\Carbon::parse($customer->created_at, 'UTC')->setTimezone('America/Mexico_City')->format('Y-m-d H:i:s') }}
                                {{-- $customer->created_at --}}
                            </td>
                            <td>
                                @can('write_customer')
                                    @if (!$customer->hasSedes() && $customer->service_type_id != 3)
                                        <a href="{{ route('customer.quote', ['id' => $customer->id, 'class' => 'customer']) }}"
                                            class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Cotizaciones">
                                            <i class="bi bi-calculator-fill"></i>
                                        </a>

                                        <a href="{{ route('customer.graphics', ['id' => $customer->id]) }}"
                                            class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Estadisticas">
                                            <i class="bi bi-bar-chart-fill"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('customer.edit', ['id' => $customer->id]) }}"
                                        class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        data-bs-title="Editar cliente">
                                        <i class="bi bi-pencil-square"></i>
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

    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            $('input[name="date_range"]').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                },
                opens: 'left',
                autoUpdateInput: false
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
