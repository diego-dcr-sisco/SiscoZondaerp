<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Filtrar Historial de Reportes</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('client.report.search.backup') }}">
            @csrf
            <div class="row g-3">
                <!-- Sede -->
                <div class="col-lg-4">
                    <label for="sede" class="form-label">Sede</label>
                    <select class="form-select" id="sede" name="sede" required>
                        <option value="">Seleccionar sede</option>
                        @foreach ($user->customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('sede') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Número de Reporte -->
                <div class="col-lg-3">
                    <label for="report_id" class="form-label">No. Reporte</label>
                    <input type="number" class="form-control" id="report_id" name="report_id" 
                           value="{{ request('report_id') }}" placeholder="Ej. 123" min="1">
                </div>

                <!-- Fecha -->
                <div class="col-lg-3">
                    <label for="date" class="form-label">Fecha</label>
                    <input type="text" class="form-control date-picker" id="date" name="date" 
                           value="{{ request('date') }}" placeholder="DD/MM/YYYY">
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

<!-- Script para DatePicker -->
<script>
    $(document).ready(function() {
        $('.date-picker').daterangepicker({
            singleDatePicker: true,
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
            },
            autoUpdateInput: false
        });

        $('.date-picker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY'));
        });
    });
</script>
