@extends('layouts.app')
@section('content')
    @php
        function truncate_string($string, $limit = 80)
        {
            return mb_strlen($string, 'UTF-8') > $limit ? mb_substr($string, 0, $limit) . '...' : $string;
        }

        function compareDateWithToday($receivedDate)
        {
            $today = new DateTime();
            $date = new DateTime($receivedDate);

            // Normalizar fechas (solo fecha, sin hora)
            $normalizedToday = new DateTime($today->format('Y-m-d'));
            $normalizedDate = new DateTime($date->format('Y-m-d'));

            if ($normalizedToday < $normalizedDate) {
                return ['text-success', 'Vigente/Dentro de fecha'];
            } elseif ($normalizedToday == $normalizedDate) {
                return ['text-warning', 'Previo a expirar/terminar'];
            } else {
                return ['text-danger', 'Expirado/Extemporaneo'];
            }
        }

    @endphp
    <div class="container-fluid">
        <div class="py-3">
            @can('write_order')
                <a class="btn btn-primary btn-sm" href="{{ route('contract.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> {{ __('contract.title.create') }}
                </a>
            @endcan
        </div>

        
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    <form action="{{ route('contract.search') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-4 col-12">
                                <label for="customer" class="form-label">Cliente</label>
                                <input type="text" class="form-control form-control-sm" id="name" name="name"
                                    value="{{ request('name') }}" placeholder="Buscar nombre">
                            </div>

                            <div class="col-lg-4 col-12">
                                <label for="date_range" class="form-label">Periodo (Fecha)</label>
                                <input type="text" class="form-control form-control-sm date-range-picker" id="date-range"
                                    name="date_range" value="{{ request('date_range') }}" placeholder="Selecciona un rango"
                                    autocomplete="off">
                            </div>

                            <div class="col-auto">
                                <label for="signature_status" class="form-label">Dirección</label>
                                <select class="form-select form-select-sm" id="direction" name="direction">
                                    <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
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
                                <a href="{{ route('contract.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>

                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('contract.data.customer') }}</th>
                        <th scope="col">Duración del contrato</th>
                        <th scope="col">{{ __('contract.title.technicians') }}</th>
                        <th scope="col">Estado de uso</th>
                        <th scope="col">Estado por tiempo</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contracts as $index => $contract)
                        <tr>
                            <th scope="row">{{ ++$index }}</th>
                            <td> {{ $contract->customer->name ?? '-' }} </td>
                            <td>{{ \Carbon\Carbon::parse($contract->startdate)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($contract->enddate)->format('d/m/Y') }}</td>
                            <td>
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($contract->technicianNames() as $tech)
                                        <li>{{ $tech }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                <span
                                    class="fw-bold {{ $contract->status == 1 ? 'text-success' : ($contract->status == 0 ? 'text-danger' : 'text-warning') }}">
                                    {{ $contract->status == 1 ? __('contract.status.active') : ($contract->status == 0 ? __('contract.status.finalized') : __('contract.status.to_finalize')) }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold">
                                    @php
                                        $results = compareDateWithToday($contract->enddate);
                                    @endphp
                                    <span
                                        class="fw-bold {{ $results[0] }}">
                                        {{ $results[1] }}
                                    </span>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-info btn-sm"
                                    href="{{ route('contract.show', ['id' => $contract->id, 'section' => 1]) }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Ordenes de servicio">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @can('write_order')
                                    <a href="{{ route('contract.edit', ['id' => $contract->id]) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Editar contrato" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <a class="btn btn-success btn-sm"
                                        href="{{ route('contract.renew', ['id' => $contract->id]) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Renovar contrato">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <a class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Plan de rotación"
                                        href="{{ $contract->hasRotationPlan() ? route('rotation.edit', ['id' => $contract->rotationPlan()->id]) : route('rotation.create', ['contractId' => $contract->id]) }}">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                    {{-- <a class="btn btn-warning btn-sm"
                                        href="{{ route('quality.opportunity-area', ['id' => $contract->customer->id]) }}">
                                        <i class="bi bi-lightbulb-fill"></i>
                                    </a> --}}
                                    <a href="{{ route('contract.destroy', ['id' => $contract->id]) }}"
                                        class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Eliminar contrato"
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
        {{ $contracts->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        $(function() {
            $('#date-range').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY' // Cambiar el formato aquí
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este año': [moment().startOf('year'), moment().endOf('year')],
                },
                alwaysShowCalendars: true,
                autoUpdateInput: false,
            });

            $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });
    </script>
@endsection
