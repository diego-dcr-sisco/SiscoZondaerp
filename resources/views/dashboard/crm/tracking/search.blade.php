<form action="{{ route('crm.tracking.search') }}" method="GET">
    <div class="row align-items-end mb-2">
        <!-- Campo Nombre -->
        <div class="col-6">
            <label for="name" class="form-label">Nombre</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="name" name="name" placeholder="Buscar por nombre"
                    value="{{ request('name') }}">
            </div>
        </div>

        <div class="col-auto">
            <label for="name" class="form-label">Tipo</label>
            <select class="form-select form-select-sm" id="service-type" name="service_type_id">
                <option value="" {{ request('service_type_id') == '' ? 'selected' : '' }}>Sin opción
                </option>
                <option value="1" {{ request('service_type_id') == '1' ? 'selected' : '' }}>Doméstico
                </option>
                <option value="2" {{ request('service_type_id') == '2' ? 'selected' : '' }}>Comercial
                </option>
                <option value="3" {{ request('service_type_id') == '3' ? 'selected' : '' }}>
                    Industrial/Planta</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="name" class="form-label">Rango de fechas</label>
            <input type="text" class="form-control form-control-sm" id="date-range" name="date"
                value="{{ request('date') }}" />
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-search me-1"></i> Buscar
            </button>
        </div>
    </div>

    <input type="hidden" name="view" value="customers" />
</form>

<script>
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
    });

    $('#date-range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });
</script>
