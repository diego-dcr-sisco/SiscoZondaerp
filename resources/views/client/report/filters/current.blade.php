<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Filtrar Reportes Actuales</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('client.report.filter') }}">
            @csrf
            <div class="row g-3">
                <!-- Sede -->
                <div class="col-lg-3">
                    <label for="sede" class="form-label">Sede</label>
                    <select class="form-select" id="sede" name="sede" required>
                        <option value="">Seleccionar sede</option>
                        @if ($user->role_id == 5)
                            @foreach ($user->customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('sede') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        @else
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede->id }}" {{ request('sede') == $sede->id ? 'selected' : '' }}>
                                    {{ $sede->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Línea de Negocio -->
                <div class="col-lg-3">
                    <label for="business_line" class="form-label">Línea de Negocio</label>
                    <select class="form-select" id="business_line" name="business_line" required>
                        @foreach ($business_lines as $business_line)
                            <option value="{{ $business_line->id }}" 
                                {{ (request('business_line') == $business_line->id || $business_line->id == 2) ? 'selected' : '' }}>
                                {{ $business_line->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Número de Reporte -->
                <div class="col-lg-2">
                    <label for="report" class="form-label">No. Reporte</label>
                    <input type="number" class="form-control" id="report" name="report" 
                           value="{{ request('report') }}" placeholder="Ej. 123" min="1">
                </div>

                <!-- Servicio -->
                <div class="col-lg-4">
                    <label for="service" class="form-label">Servicio</label>
                    <input type="text" class="form-control" id="service" name="service" 
                           value="{{ request('service') }}" placeholder="Control de roedores, aplicación química...">
                </div>

                <!-- Rango de Fechas -->
                <div class="col-lg-3">
                    <label for="date_range" class="form-label">Rango de Fechas</label>
                    <input type="text" class="form-control" id="date_range" name="date" 
                           value="{{ request('date') }}" placeholder="Selecciona un rango">
                </div>

                <!-- Hora -->
                <div class="col-lg-2">
                    <label for="time" class="form-label">Hora</label>
                    <input type="time" class="form-control" id="time" name="time" 
                           value="{{ request('time') }}">
                </div>

                <!-- Tipo de Seguimiento -->
                <div class="col-lg-3">
                    <label class="form-label">Tipo de Servicio</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="tracking_type" id="is-mip" 
                               value="1" {{ request('tracking_type', '1') == '1' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary btn-sm" for="is-mip">Programación MIP</label>

                        <input type="radio" class="btn-check" name="tracking_type" id="is-tracking" 
                               value="0" {{ request('tracking_type') == '0' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary btn-sm" for="is-tracking">Seguimiento</label>
                    </div>
                </div>

                <!-- Botones -->
                <div class="col-lg-12 d-flex justify-content-end gap-2 m-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                    <a href="{{ route('client.reports') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script para DateRangePicker -->
<script>
    $(document).ready(function() {
        $('#date_range').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
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
            autoUpdateInput: false
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });
    });
</script>
