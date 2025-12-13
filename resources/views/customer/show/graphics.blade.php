@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('customer.index.sedes') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                GRAFICAS DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
            </span>
        </div>

        <div class="p-3">
            <div class="border rounded p-3 text-dark bg-light mb-3">
                <form id="filter-form" action="{{ route('customer.graphics', ['id' => $customer->id]) }}" method="GET">
                    <div class="row g-2 mb-0">
                        <!-- Cliente -->
                        <div class="col-lg-3">
                            <label for="customer" class="form-label is-required">Cliente</label>
                            <input type="text"
                                class="form-control form-control-sm {{ isset($customer) ? 'bg-secondary-subtle' : '' }}"
                                id="customer" name="customer"
                                value="{{ request('customer') ?? isset($customer) ? $customer->name : '' }}"
                                placeholder="Nombre del cliente" {{ isset($customer) ? 'readonly' : '' }} required>
                        </div>

                        <!-- Rango de fecha -->
                        <div class="col-lg-3">
                            <label for="date_range" class="form-label is-required">Rango de Fechas</label>
                            <input type="text" class="form-control form-control-sm date-range-picker" id="date-range"
                                name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                autocomplete="off" required>
                        </div>

                        <!-- Servicio -->
                        <div class="col-lg-3">
                            <label for="service" class="form-label">Servicio</label>
                            <input type="text" class="form-control form-control-sm" id="service" name="service"
                                value="{{ request('service') }}" placeholder="Buscar servicio">
                        </div>

                        <!-- Area -->
                        <div class="col-lg-3">
                            <label for="area" class="form-label">Area</label>
                            <select class="form-select form-select-sm" id="area" name="area">
                                <option value="" {{ request('area') == null ? 'selected' : '' }}>Todas</option>
                                @foreach ($app_areas as $area)
                                    <option value="{{ $area->id }}"
                                        {{ request('area') == $area->name ? 'selected' : '' }}>{{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Punto de Control -->
                        <div class="col-lg-2">
                            <label for="device_type" class="form-label">Tipo de dispositivo</label>
                            <select class="form-select form-select-sm" id="control_point" name="control_point">
                                <option value="" {{ request('control_point') == null ? 'selected' : '' }}>Todas
                                </option>
                                @foreach ($control_points as $cp)
                                    <option value="{{ $cp->id }}"
                                        {{ request('control_point') == $cp->code ? 'selected' : '' }}>{{ $cp->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Plaga -->
                        <div class="col-lg-2">
                            <label for="pest" class="form-label">Plaga</label>
                            <input type="text" class="form-control form-control-sm" id="pest" name="pest"
                                value="{{ request('pest') }}" placeholder="Buscar por plaga">
                        </div>

                        <div class="col-lg-2">
                            <label for="graph_type" class="form-label is-required">Tipo de grafica</label>
                            <select class="form-select form-select-sm" id="graph_type" name="graph_type" required>
                                <option value="" {{ request('graph_type') == null ? 'selected' : '' }}> Ninguno
                                </option>
                                @foreach ($graphs_types as $key => $graphs_type)
                                    <option value="{{ $key }}"
                                        {{ request('graph_type') == $key ? 'selected' : '' }}>{{ $graphs_type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-lg-12 d-flex justify-content-end m-0 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                            <a href="{{ route('order.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="fw-bold" scope="col">#</th>
                            <th class="fw-bold" scope="col">Servicio</th>
                            <th class="fw-bold" scope="col">Area</th>
                            <th class="fw-bold" scope="col">Dispositivo</th>
                            @foreach ($data['headers'] as $header)
                                <th class="fw-bold" scope="col">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['detections'] as $index => $d)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $d['service'] }}</td>
                                <td>{{ $d['area_name'] }}</td>
                                <td>{{ $d['device_name'] }}</td>
                                @if (request('graph_type') == 'cptr')
                                    @foreach ($data['headers'] as $header)
                                        <td>{{ $d['pest_total_detections'][$header] }}</td>
                                    @endforeach
                                @elseif (request('graph_type') == 'cnsm')
                                    <td>{{ $d['consumption_value'] }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td class="fw-bold text-danger" colspan="{{ 4 + count($data['headers']) }}">Utiliza los
                                    filtros para obtener resultados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover',
        }))

        $(function() {
            // Configuración común para ambos datepickers
            const commonOptions = {
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY'
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                },
                showDropdowns: true,
                alwaysShowCalendars: true,
                autoUpdateInput: false
            };

            $('#date-range').daterangepicker(commonOptions);
            $('#date-range-technician').daterangepicker(commonOptions);

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });

            $('#date-range-technician').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });
    </script>
@endsection
