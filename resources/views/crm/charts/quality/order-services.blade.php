@php
    use Carbon\Carbon;
    $date = Carbon::now();
@endphp

<div class="col-12">
    <div class="card shadow-lg">
        <div class="card-body">
            <h5 class="card-title fw-bold d-flex justify-content-between align-middle">
                <span>Servicios realizados en el mes</span>
                <div class="col-4 w-25">
                    <p class="fs-6 fw-light">Seleccione administrativo:</p>
                    <select class="form-select" id="adminUserSelector" onchange="refreshOrderServicesChart()">
                        @foreach ($adminUsers as $user)
                            <option value="{{ $user->id }}" tooltip="Seleccionar administrativo">
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Selector de fecha -->
                <div class="col-4">
                    <p class="fs-6 fw-light">Seleccione un rango de fecha:</p>
                    <div class="input-group w-75">
                        <input type="text" id="date-range" name="date_range" class="form-control" tooltip="Seleccione un rango de fechas">
                        <button class="btn btn-primary" type="button" onclick="refreshOrderServicesChart()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </h5>
            <div id="orderServicesChart">
                {!! $orderServicesChart->container() !!}
            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js" charset="utf-8"></script>
{!! $orderServicesChart->script() !!}

<script>
    var route_api_url = '';

    function refreshOrderServicesChart() {
        const date = searchByDateRange();


        if (!date) {
            // toma la fecha de un mes a ahora por defecto
            var startDate = moment().subtract(1, 'month').startOf('month').format('YYYY/MM/DD');
            var endDate = moment().endOf('month').format('YYYY/MM/DD');
        } else {
            // dar formato a la fecha recibida de date
            var dateRange = date.split('=')[1].split('%2F').join('-').split('+-+');
            var startDate = dateRange[0].trim();
            var endDate = dateRange[1].trim();
        }

        var adminUser = $('#adminUserSelector').val();

        if (!adminUser) {
            adminUser = {{ $adminUsers[0]->id }};
        }

        if (!route_api_url) {
            route_api_url = {{ $orderServicesChart->id }}_api_url;
        }

        {{ $orderServicesChart->id }}_refresh(route_api_url + '/update' + "?start_date=" + startDate + "&end_date=" +
            endDate + "&admin_user=" + adminUser);
        console.log(route_api_url + '/update' + "?start_date=" + startDate + "&end_date=" + endDate + "&admin_user=" +
            adminUser);
    }
</script>

<script>
    $(function() {
        $('#date-range').daterangepicker({
            opens: 'left',
            locale: {
                format: 'YYYY/MM/DD',
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

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format(
                'YYYY/MM/DD'));
        });
    });

    function searchByDateRange() {
        const dateRange = document.getElementById('date-range').value;

        const params = new URLSearchParams({
            date_range: dateRange
        });

        return params.toString();
        // window.location.href = `?${params.toString()}`;
    }
</script>
