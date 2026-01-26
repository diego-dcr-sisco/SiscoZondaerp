@php
     if (!function_exists('getBadgeClass')) {
    function getBadgeClass($value) {
        if ($value == 0) return 'bg-secondary text-white';
        elseif ($value <= 0.25) return 'bg-success text-white';
        elseif ($value <= 0.5) return 'bg-warning text-dark';
        elseif ($value <= 0.75) return 'bg-danger text-white';
        else return 'bg-danger text-white';
    }
}

@endphp

<div class="container-fluid">

    <h5 class="mb-3">
        <i class="bi bi-cpu"></i>
        Consumo de dispositivos – {{ $customer->name ?? 'Cliente' }}
    </h5>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('quality.analytics.filterDeviceConsumption', $customer->id) }}" class="mb-4" id="consumptionFilterForm">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="dateRangePicker" class="col-form-label col-form-label-sm">Rango de fechas <span class="text-danger">*</span></label>
            </div>
            <div class="col-auto">
                <input
                    type="text"
                    name="date_range"
                    class="form-control form-control-sm"
                    id="dateRangePicker"
                    value="{{ request('date_range', ($start_date ?? now()->startOfMonth()->format('d/m/Y')) . ' - ' . ($end_date ?? now()->endOfMonth()->format('d/m/Y'))) }}"
                    placeholder="DD/MM/AAAA - DD/MM/AAAA"
                    autocomplete="off"
                    style="min-width:100px"
                    required
                >
            </div>
            <div class="col-auto">
                <label class="col-form-label col-form-label-sm">Tipo de Reporte</label>
            </div>
            <div class="col-auto">
                <select name="report_type" class="form-select form-select-sm" style="min-width:20px" required>
                    <option value="weekly" {{ request('report_type', 'weekly') == 'weekly' ? 'selected' : '' }}>Semanal</option>
                    <option value="daily" {{ request('report_type') == 'daily' ? 'selected' : '' }}>Diario</option>
                </select>
            </div>

            <div class="col-auto">
                <label for="serviceSelect" class="col-form-label col-form-label-sm">Servicio <span class="text-danger">*</span></label>
            </div>
            <div class="col-auto">
                <select name="service_id" id="serviceSelect" class="form-select form-select-sm" style="min-width:260px" required>
                    <option value="">-- Selecciona un servicio --</option>
                    @foreach($allServices as $service)
                    <option value="{{ $service->id }}" 
                        {{ request('service_id') == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                    @endforeach
                </select>
                @if($allServices->isEmpty())
                    <small class="text-warning d-block mt-1">
                        <i class="bi bi-exclamation-triangle"></i> No hay servicios disponibles para este cliente
                    </small>
                @endif
            </div>
            <div class="col-auto">
                <label for="weekDaySelect" class="col-form-label col-form-label-sm">Día de corte semanal</label>
            </div>
            <div class="col-auto">
                <select name="week_day" id="weekDaySelect" class="form-select form-select-sm">
                    <option value="1" {{ (request('week_day', 5) == 1 )? 'selected' : '' }}>Lunes</option>
                    <option value="2" {{ (request('week_day', 5) == 2 )? 'selected' : '' }}>Martes</option>
                    <option value="3" {{ (request('week_day', 5) == 3 )? 'selected' : '' }}>Miércoles</option>
                    <option value="4" {{ (request('week_day', 5) == 4 )? 'selected' : '' }}>Jueves</option>
                    <option value="5" {{ (request('week_day', 5) == 5 )? 'selected' : '' }}>Viernes</option>
                    <option value="6" {{ (request('week_day', 5) == 6 )? 'selected' : '' }}>Sábado</option>
                    <option value="0" {{ (request('week_day', 5) == 0 )? 'selected' : '' }}>Domingo</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm" @if($allServices->isEmpty()) disabled @endif>
                    <i class="bi bi-funnel-fill"></i> Filtrar
                </button>
                <a href="{{ route('quality.analytics', $customer->id) }}" class="btn btn-outline-secondary btn-sm">
                    Limpiar
                </a>
            </div>
        </div>
    </form>

    {{-- Mensajes informativos --}}
    @if($errors->has('service_id'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> 
            <strong>Error:</strong> {{ $errors->first('service_id') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($error))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> 
            <strong>Aviso:</strong> {{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($message))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i> 
            <strong>Información:</strong> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$selectedService && !$errors->has('service_id') && !isset($error) && !isset($message))
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i> 
            <strong>Por favor,</strong> selecciona un servicio y haz clic en "Filtrar" para ver los datos de consumo.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabla de consumo por semanas --}}
    <div id="consumptionResultsContainer">
        @if(!empty($timeKeys) && !empty($table))
        @if($reportType === 'daily')
            <!-- TABLA DIARIA -->
            <h4 class="mb-3">Reporte Diario de Consumo</h4>
            <table class="table table-striped table-bordered table-sm table-sticky align-middle">
                <thead class="table-success">
                    <tr>
                        <th style="min-width:160px">DISPOSITIVO</th>
                        @foreach($timeKeys as $day)
                            <th class="text-center">{{ $day }}</th>
                        @endforeach
                        <th class="text-center">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table as $deviceLabel => $row)
                        <tr>
                            <td class="fw-semibold">{{ $deviceLabel }}</td>
                            @foreach($timeKeys as $day)
                                @php
                                    $val = (float)($row[$day] ?? 0);
                                    $badge = getBadgeClass($val); // Llamada a la función local
                                @endphp
                                <td>
                                    <span class="badge {{ $badge }}">{{ number_format($val, 2) }}</span>
                                </td>
                            @endforeach
                            <td class="text-center fw-bold">{{ number_format((float)($row['TOTAL'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th>Total general</th>
                        @foreach($timeKeys as $day)
                            @php
                                $sum = collect($table)->sum(fn($r) => (float)($r[$day] ?? 0));
                            @endphp
                            <th class="text-center">{{ number_format($sum, 2) }}</th>
                        @endforeach
                        <th class="text-center">
                            {{ number_format(collect($table)->sum(fn($r) => (float)($r['TOTAL'] ?? 0)), 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        @else
            <!-- TABLA SEMANAL -->
             <h4 class="mb-3" style="width: 100%; text-align: center;">Reporte Semanal de Consumo</h4>
            <table class="table table-striped table-bordered table-sm table-sticky align-middle">
                <thead class="table-success">
                    <tr>
                        <th style="min-width:160px">DISPOSITIVO</th>
                        @foreach($timeKeys as $week)
                            <th class="text-center">{{ $week }}</th>
                        @endforeach
                        <th class="text-center">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table as $deviceLabel => $row)
                        <tr>
                            <td class="fw-semibold">{{ $deviceLabel }}</td>
                            @foreach($timeKeys as $week)
                                @php
                                    $val = (float)($row[$week] ?? 0);
                                    $badge = getBadgeClass($val); 
                                @endphp
                                <td>
                                    <span class="badge {{ $badge }}">{{ number_format($val, 2) }}</span>
                                </td>
                            @endforeach
                            <td class="text-center fw-bold">{{ number_format((float)($row['TOTAL'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th>Total general</th>
                        @foreach($timeKeys as $week)
                            @php
                                $sum = collect($table)->sum(fn($r) => (float)($r[$week] ?? 0));
                            @endphp
                            <th class="text-center">{{ number_format($sum, 2) }}</th>
                        @endforeach
                        <th class="text-center">
                            {{ number_format(collect($table)->sum(fn($r) => (float)($r['TOTAL'] ?? 0)), 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        @endif
        
      @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            No hay datos de consumo para el rango y servicio seleccionados.
        </div>
      @endif
    </div>

    
</div>



