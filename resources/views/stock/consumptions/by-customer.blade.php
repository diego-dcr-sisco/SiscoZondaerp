@extends('layouts.app')
@section('content')
    @php
        $divisionLabels = [
            'day' => 'Dia',
            'week' => 'Semana',
            'month' => 'Mes',
            'year' => 'Anio',
        ];
    @endphp

    @include('components.page-header', [
        'title' => 'CONSUMOS POR CLIENTE',
        'icon' => 'bi-people',
    ])

    <div class="container-fluid p-0">
        <div class="m-3">
            <form action="{{ route('stock.consumptions.by-customer') }}" method="GET" class="mb-3">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                            </h5>
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target=".customer-consumption-search-collapse" aria-expanded="true"
                                aria-controls="customerConsumptionSearchFilters customerConsumptionSearchFooter">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body collapse show customer-consumption-search-collapse"
                        id="customerConsumptionSearchFilters">
                        <div class="row g-3">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="customer" class="form-label is-required">Cliente</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input type="text" class="form-control" id="customer" name="customer"
                                        value="{{ request('customer') }}" placeholder="Nombre del cliente" required>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <label for="date-range" class="form-label is-required">Rango de fecha</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                    <input type="text" class="form-control" id="date-range" name="date_range"
                                        value="{{ request('date_range') }}" placeholder="Fecha de las ordenes"
                                        autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-lg-2 col-sm-6 col-12">
                                <label for="division" class="form-label">Division</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <select class="form-select" id="division" name="division">
                                        @foreach ($divisionLabels as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $division == $value ? 'selected' : '' }}
                                                {{ !in_array($value, $availableDivisions, true) ? 'disabled' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-2 col-sm-6 col-12">
                                <label for="size" class="form-label">Mostrar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                    <select class="form-select" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size', 50) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer collapse show customer-consumption-search-collapse"
                        id="customerConsumptionSearchFooter">
                        <div class="row justify-content-end">
                            <div class="col-lg-1 col-6">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                            </div>
                            <div class="col-lg-1 col-6">
                                <a href="{{ route('stock.consumptions.by-customer') }}"
                                    class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <form action="{{ route('stock.consumptions.by-customer.export') }}" method="GET" class="mb-3">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0">
                                <i class="bi bi-file-earmark-excel-fill"></i> Excel por cliente y producto
                            </h5>
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target=".customer-consumption-export-collapse" aria-expanded="true"
                                aria-controls="customerConsumptionExportFilters customerConsumptionExportFooter">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body collapse show customer-consumption-export-collapse"
                        id="customerConsumptionExportFilters">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-4 col-12">
                                <label for="date-range-export" class="form-label is-required">Rango de fecha</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                    <input type="text" class="form-control" id="date-range-export" name="date_range"
                                        value="{{ request('date_range') }}" placeholder="Fecha de las ordenes"
                                        autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-lg-8 col-12">
                                <label class="form-label">Tipo de cliente</label>
                                <div class="d-flex align-items-center gap-3 flex-wrap border rounded p-2 bg-white">
                                    @php
                                        $selectedServiceTypes = collect(request('service_type_ids', [1, 2, 3]))->map(fn ($value) => (string) $value);
                                        $serviceTypeOptions = [
                                            1 => 'Domestico',
                                            2 => 'Comercial',
                                            3 => 'Industrial/Planta',
                                        ];
                                    @endphp
                                    @foreach ($serviceTypeOptions as $typeId => $typeLabel)
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="service_type_ids[]"
                                                id="service-type-{{ $typeId }}" value="{{ $typeId }}"
                                                {{ $selectedServiceTypes->contains((string) $typeId) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="service-type-{{ $typeId }}">
                                                {{ $typeLabel }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer collapse show customer-consumption-export-collapse"
                        id="customerConsumptionExportFooter">
                        <div class="row justify-content-end">
                            <div class="col-lg-2 col-md-3 col-12">
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-file-earmark-excel-fill"></i> Generar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row g-3 mb-3">
                <div class="col-xl-5 col-12">
                    <div class="table-responsive bg-white border rounded h-100">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Tipo de dispositivo</th>
                                    <th scope="col" class="text-end">Dispositivos</th>
                                    <th scope="col" class="text-end">Registros</th>
                                    <th scope="col" class="text-end">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($deviceSummaries as $deviceSummary)
                                    <tr>
                                        <td class="fw-semibold">{{ $deviceSummary->device_type }}</td>
                                        <td class="text-end">{{ number_format($deviceSummary->devices_count) }}</td>
                                        <td class="text-end">{{ number_format($deviceSummary->consumptions_count) }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($deviceSummary->total_quantity, 2) }}
                                            <small class="text-muted">{{ $deviceSummary->metric_name }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aplica un filtro para consultar dispositivos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-xl-7 col-12">
                    <div class="table-responsive bg-white border rounded h-100">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Servicio</th>
                                    <th scope="col" class="text-end">Prefijo</th>
                                    <th scope="col">Producto utilizado</th>
                                    <th scope="col" class="text-end">Cantidad total</th>
                                    <th scope="col" class="text-end">Ordenes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($serviceSummaries as $serviceSummary)
                                    <tr>
                                        <td class="fw-semibold">{{ $serviceSummary->service_name }}</td>
                                        <td class="text-end">
                                            <span class="badge text-bg-light border">{{ $serviceSummary->service_prefix ?? '-' }}</span>
                                        </td>
                                        <td>{{ $serviceSummary->product_name }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($serviceSummary->total_quantity, 2) }}
                                            <small class="text-muted">{{ $serviceSummary->metric_name }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($serviceSummary->orders_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aplica un filtro para consultar productos utilizados por servicio.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="table-responsive bg-white border rounded">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Producto utilizado</th>
                            <th scope="col">Lote</th>
                            <th scope="col" class="text-end">Cantidad total</th>
                            <th scope="col" class="text-end">Ordenes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productSummaries as $productSummary)
                            <tr>
                                <td class="fw-semibold">{{ $productSummary->product_name }}</td>
                                <td>
                                    <span class="badge text-bg-light border">{{ $productSummary->lot_name }}</span>
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ number_format($productSummary->total_quantity, 2) }}
                                    <small class="text-muted">{{ $productSummary->metric_name }}</small>
                                </td>
                                <td class="text-end">{{ number_format($productSummary->orders_count) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{ $hasAppliedFilters ? 'No hay productos utilizados para los filtros seleccionados.' : 'Aplica un filtro para consultar los productos utilizados.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(function() {
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

            $('#date-range, #date-range-export').daterangepicker(commonOptions);

            $('#date-range, #date-range-export').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });
    </script>
@endsection
