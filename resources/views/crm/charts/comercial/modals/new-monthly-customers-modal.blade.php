@php
    use Carbon\Carbon;
    $date = Carbon::now();
@endphp

<style>
    #chartModal {
        min-height: 400px;
    }
</style>

<!-- Modal -->
<div class="modal fade" id="newCustomersModal" tabindex="-1" aria-labelledby="newCustomersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCustomersModalLabel">Nuevos Clientes por Mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group w-50 mb-3">
                    <!-- Selectores de mes y año -->
                    <select class="form-select" id="yearSelector" onchange="refreshChart()">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="" {{ $i == now()->year ? 'selected' : '' }}>
                                {{ $i }}</option>
                        @endfor
                    </select>
                    <select class="form-select" id="monthSelector" onchange="refreshChart()">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                {{ Carbon::create()->month($i)->locale('es')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <!-- Contenedor de la gráfica -->
                <div id="chartModal">
                    {!! $chart->container() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Ejecuta esto cuando el modal se muestre
    $('#newCustomersModal').on('shown.bs.modal', function() {
        if (typeof window['{{ $chart->id }}_refresh'] === 'function') {
            const route_api_url = '{{ $chart->id }}_api_url';
            // Refrescar la gráfica para asegurarse de que se renderiza correctamente
            window['{{ $chart->id }}_refresh'](route_api_url);
        }
    });
</script>

<script>
    var route_api_url = '';

    function refreshChart() {
        const month = $('#monthSelector').val();
        const year = $('#yearSelector').val();
        console.log(year);
        console.log(month);
        if (!route_api_url) {
            route_api_url = {{ $chart->id }}_api_url;
        }
        // if (!route_api_url || route_api_url === '') {
        //     route_api_url = '{{ $chart->id }}_api_url';
        // }

        console.log(route_api_url);
        {{ $chart->id }}_refresh(route_api_url + '/update' + "?month=" + month + "&year=" + year);
    }
</script>
