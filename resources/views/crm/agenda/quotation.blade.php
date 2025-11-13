@extends('layouts.app')
@section('content')
    @php
        $count = 0;
        function formatCurrency(?float $amount): string
        {
            $formatter = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 2);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

            return $formatter->format($amount ?? 0);
        }

        function checkDateStatus($date)
        {
            $targetDate = new DateTime($date);
            $currentDate = new DateTime();
            $difference = $currentDate->diff($targetDate);
            if ($currentDate >= $targetDate) {
                return 'text-danger';
            }
            $daysRemaining = $difference->days;
            if ($daysRemaining <= 7) {
                return 'text-warning';
            }
            return 'text-success';
        }
    @endphp
    <div class="container-fluid font-small p-3">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $nav == 'c' ? 'active' : '' }}" aria-current="page"
                    href="{{ route('crm.agenda') }}">Calendario</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $nav == 't' ? 'active' : '' }}" href="{{ route('crm.tracking') }}">Seguimientos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $nav == 'q' ? 'active' : '' }}" href="{{ route('crm.quotation') }}">Cotizaciones</a>
            </li>
        </ul>

        <div class="border p-2 text-dark rounded bg-light mb-3">
            <form action="{{ route('crm.quotation') }}" method="GET">
                @csrf
                <div class="row g-2 mb-0">

                    <!-- Cliente/Lead -->
                    <div class="col-lg-3">
                        <label class="form-label" for="trackable-id">Nombre del cliente/lead</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-person-circle"></i></span>
                            <input type="text" class="form-control form-control-sm" id="trackable" name="trackable"
                                value="{{ request('trackable') }}" placeholder="Buscar por nombre del cliente..."/>
                        </div>
                    </div>

                    <!-- Rango de fechas -->
                    <div class="col-lg-3">
                        <label class="form-label" for="date-range">Rango de fechas</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week-fill"></i></span>
                            <input type="text" class="form-control form-control-sm" id="date-range" name="date-range"
                                value="{{ request('date-range') }}" placeholder="Rango de fechas"/>
                        </div>
                    </div>

                    <!-- Servicio -->
                    <div class="col-lg-3">
                        <label class="form-label" for="service">Servicio</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-gear-fill"></i></span>
                            <input type="text" class="form-control form-control-sm" id="service" name="service"
                                value="{{ request('service') }}" placeholder="Tipo de servicio..."/>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <label for="signature_status" class="form-label">Dirección</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-arrow-down-up"></i></span>
                            <select class="form-select form-select-sm" id="direction" name="direction">
                                <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                    DESC
                                </option>
                                <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                    ASC
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-1">
                        <label for="order_type" class="form-label">Total</label>
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-list-ol"></i></span>
                            <select class="form-select form-select-sm" id="size" name="size">
                                <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25
                                </option>
                                <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50
                                </option>
                                <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100
                                </option>
                                <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200
                                </option>
                                <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Botón Buscar -->
                    <div class="col-lg-12 d-flex justify-content-end px-3">
                        <button type="submit" class="btn btn-primary btn-sm" id="search" name="search">
                            <i class="bi bi-funnel-fill"></i> Buscar
                        </button>
                    </div>
                </div>

                <input type="hidden" name="view" value="agenda" />
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente/Cliente potencial</th>
                        <th>Servicio</th>
                        <th>Inicio</th>
                        <th>Fin estimada</th>
                        <th>Válido hasta</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Valor</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotes as $i => $quote)
                        @php
                            $count += $quote->value;
                        @endphp
                        <tr>
                            <th>{{ $i + 1 }}</th>
                            <td>{{ $quote->model->name ?? '-' }}</td>
                            <td>{{ $quote->service->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($quote->start_date)->translatedFormat('d-m-Y') }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($quote->end_date)->translatedFormat('d-m-Y') }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($quote->valid_until)->translatedFormat('d-m-Y') }}
                            </td>
                            <td class="{{ $quote->priority->class() }} fw-bold">
                                <i class="bi {{ $quote->priority->icon() }}"></i>
                                {{ $quote->priority->label() }}
                            </td>
                            <td>
                                <span class="{{ $quote->status->class() }} fw-bold">
                                    {{ $quote->status->label() }}
                                </span>
                            </td>
                            <td class="fw-bold">{{ formatCurrency($quote->value) }}</td>
                            <td class="text-center">
                                @if (!$quote->file)
                                    <span class="text-danger fw-bold">Sin archivo PDF</span>
                                @else
                                    <a href="{{ route('customer.quote.download', ['id' => $quote->id]) }}"
                                        class="btn btn-sm btn-link" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Archivo PDF cotización">
                                        <i class="bi bi-file-earmark-arrow-down-fill"></i> Archivo PDF
                                    </a>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('customer.quote.edit', ['id' => $quote->id]) }}"
                                    class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Editar cotización">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="{{ route('customer.quote.destroy', ['id' => $quote->id]) }}"
                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Eliminar cotización">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-danger"> Sin cotizaciones agregadas </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $quotes->links('pagination::bootstrap-5') }}
    </div>

    <script>
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

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                    'DD/MM/YYYY'));
            });
        });
    </script>
@endsection
