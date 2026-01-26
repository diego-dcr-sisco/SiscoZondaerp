<table class="table table-sm table-bordered table-striped caption-top">
    <caption class="border rounded-top p-3 text-dark bg-light">
                    <form id="filter-form" action="{{ route('quality.devices', $customer->id) }}" method="GET">
                        <div class="row g-2 mb-0">
                            <!-- Nombre -->
                            <div class="col-lg-4">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="name" name="name"
                                    value="{{ request('name') }}" placeholder="Buscar nombre">
                            </div>
                            <!-- Código -->
                            <div class="col-lg-4">
                                <label for="code" class="form-label">Código</label>
                                <input type="text" class="form-control form-control-sm" id="code" name="code"
                                    value="{{ request('code') }}" placeholder="Buscar código">
                            </div>
                            <!-- Botones -->
                            <div class="col-lg-12 d-flex justify-content-end m-0">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                                <a href="{{ route('quality.devices', $customer->id) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </caption>
    <thead>

            <tr>
                <th class="fw-bold" scope="col">#</th>
                <th class="fw-bold" scope="col"> Nombre
                </th>
                <th class="fw-bold" scope="col">Cantidad Total
                </th>
                <th class="fw-bold" scope="col">Código
                </th>
                <th class="fw-bold" scope="col">Planos
                </th>
                <th class="fw-bold" scope="col">Zonas
                </th>
            </tr>
    </thead>
    <tbody>
        @php
            $count = 1; 
        @endphp
        @forelse ($deviceSummary as $index => $device)
            <tr id="device-{{ $device['id'] }}">
                <td>{{ $count++ }}</td>
                <td> {{ $device['name'] }} </td>
                <td> {{ $device['count'] }} </td>
                <td> {{ $device['code'] }} </td>
                <td>
                    <ul>
                        @foreach ($device['floorplans'] as $floorplan)
                            <li>{{ $floorplan }}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <ul>
                        @foreach ($device['zones'] as $zone)
                            <li>{{ $zone }}</li>
                        @endforeach
                    </ul>
                </td>
                @empty
                    <td colspan="8"  class="text-center text-danger">No hay dispositivos por el momento.</td>
                @endforelse
            </tr>
    </tbody>
</table>


<script>
    $(function() {
        $('#search-date').daterangepicker({
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
            alwaysShowCalendars: true,
            autoUpdateInput: false,
        });
    });

    $('#search-date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });
</script>
