<!-- Filter Form -->
<form method="GET" action="{{ route('quality.deviceConsumptionTable', $customer->id) }}" class="mb-4">
    <div class="btn-group" role="group">
        <input type="text" 
               name="date_range" 
               class="form-control date-range-picker form-select-sm " 
               id="dateRangePicker"
               value="{{ request('date_range', now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y')) }}"
               placeholder="Selecciona un rango"
               style="width: 300px">

        <select name="service_id" class="form-select form-select-sm w-50" onchange="this.form.submit()">
            <option value="">Todos los servicios</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" 
                        {{ request('service_id') == $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary btn-sm w-25">
            <i class="bi bi-filter"></i> Filtrar
        </button>
    </div>
</form>

<!-- Consumption Table -->
<div class="table-responsive">
    @if(isset($consumptionData['consumption']))
        @if($consumptionData['consumption']['error'] ?? false)
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> 
                {{ $consumptionData['consumption']['message'] }}
            </div>
        @elseif($consumptionData['consumption']['has_data'] ?? false)
            @php
                $devices = collect($consumptionData['consumption']['devices'] ?? []);
                $devicesByType = $devices->groupBy('type');
            @endphp
            
            <table class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Dispositivo</th>
                        <th class="text-center">Código</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Revisiones</th>
                        <th class="text-center">Consumo Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devicesByType as $typeId => $typeDevices)
                        @php
                            $firstDevice = $typeDevices->first();
                            $typeName = $firstDevice['name'] ?? 'Tipo ' . $typeId;
                        @endphp
                        
                        <tr class="table-secondary">
                            <td colspan="5" class="fw-bold">
                                <i class="bi bi-folder"></i> {{ $typeName }}
                            </td>
                        </tr>
                        
                        @foreach($typeDevices->sortBy('code') as $device)
                            <tr>
                                <td>{{ $device['name'] ?? 'Dispositivo sin nombre' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $device['code'] }}</span>
                                </td>
                                <td>{{ $device['type'] }}</td>
                                <td>
                                    @if(!empty($device['consumptions']))
                                        <div class="d-flex justify-content-center gap-1">
                                            @foreach($device['consumptions'] as $consumption)
                                                @php
                                                    $level = '';
                                                    $color = 'secondary';
                                                    if ($consumption == 0) {
                                                        $level = 'Nulo';
                                                        $color = 'light text-dark';
                                                    } elseif ($consumption <= 0.25) {
                                                        $level = 'Bajo';
                                                        $color = 'success';
                                                    } elseif ($consumption <= 0.5) {
                                                        $level = 'Medio';
                                                        $color = 'warning';
                                                    } elseif ($consumption <= 0.75) {
                                                        $level = 'Alto';
                                                        $color = 'orange text-white';
                                                    } else {
                                                        $level = 'Total';
                                                        $color = 'danger';
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $color }}" title="Consumo: {{ $level }}">
                                                    {{ number_format($consumption, 2) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">Sin datos</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">
                                    @php
                                        $total = $device['total_consumption'] ?? 0;
                                        $avgConsumption = count($device['consumptions']) > 0 ? $total / count($device['consumptions']) : 0;
                                        $progressColor = 'bg-secondary';
                                        if ($avgConsumption <= 0.25) $progressColor = 'bg-success';
                                        elseif ($avgConsumption <= 0.5) $progressColor = 'bg-warning';
                                        elseif ($avgConsumption <= 0.75) $progressColor = 'bg-orange';
                                        else $progressColor = 'bg-danger';
                                    @endphp
                                    
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress me-2" style="width: 60px; height: 20px;">
                                            <div class="progress-bar {{ $progressColor }}" 
                                                 style="width: {{ min(100, ($avgConsumption * 100)) }}%"
                                                 title="Promedio: {{ number_format($avgConsumption, 2) }}">
                                            </div>
                                        </div>
                                        <span class="badge bg-dark">{{ number_format($total, 2) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            
            <!-- Summary Card -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> Resumen de Consumos
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary">{{ $devices->count() }}</div>
                                <small class="text-muted">Dispositivos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success">{{ $devices->sum('total_consumption') }}</div>
                                <small class="text-muted">Consumo Total</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info">{{ $devices->avg('total_consumption') ? number_format($devices->avg('total_consumption'), 2) : '0.00' }}</div>
                                <small class="text-muted">Promedio</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-warning">{{ $devices->flatMap(function($device) { return $device['consumptions']; })->count() }}</div>
                                <small class="text-muted">Total Revisiones</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                No hay datos de consumo para mostrar en el rango seleccionado.
                <br><small class="text-muted">
                    Verifica que existan órdenes aprobadas con revisiones de dispositivos en este período.
                </small>
            </div>
        @endif
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> 
            No se recibieron datos de consumo del servidor.
            <br><small class="text-muted">
                Puede ser un problema temporal. Intenta recargar la página.
            </small>
        </div>
    @endif
</div>

<style>
.bg-orange {
    background-color: #fd7e14 !important;
}
</style>

<script>
$(document).ready(function() {
    // Initialize date range picker
    $('#dateRangePicker').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        },
        autoUpdateInput: false,
        showDropdowns: true,
        ranges: {
           'Este Mes': [moment().startOf('month'), moment().endOf('month')],
           'Mes Anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'Últimos 3 Meses': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
           'Últimos 6 Meses': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
           'Este Año': [moment().startOf('year'), moment().endOf('year')],
           'Año Anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
    });

    $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $(this).closest('form').submit();
    });
});
</script>