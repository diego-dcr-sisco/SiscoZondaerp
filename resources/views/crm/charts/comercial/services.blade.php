@php
    use Carbon\Carbon;
    $date = Carbon::now();
@endphp

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title fw-bold d-flex justify-content-between">
                <span class="fs-5 fw-bold">Servicios por mes</span>
                <div class="input-group w-50">
                    <div class="input-group w-100 mb-3">
                        <!-- Selectores de mes y año -->
                        <select class="form-select" id="yearServicesSelector" onchange="refreshMonthlyServicesChart()">
                            @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        <select class="form-select" id="monthServicesSelector" onchange="refreshMonthlyServicesChart()">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                    {{ Carbon::create()->month($i)->locale('es')->monthName }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </h5>
            <div id="monthlyServicesChart">
                {!! $monthlyServicesChart->container() !!}
            </div>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js" charset="utf-8"></script>
    {!! $monthlyServicesChart->script() !!}

<script>
    var route_api_url = '';

    function refreshMonthlyServicesChart() {
        const month = $('#monthServicesSelector').val();
        const year = $('#yearServicesSelector').val();
        if (!route_api_url) {
            route_api_url = {{ $monthlyServicesChart->id }}_api_url;
        }

        {{ $monthlyServicesChart->id }}_refresh(route_api_url + '/update' + "?month=" + month + "&year=" + year);
        console.log(route_api_url + '/update' + "?month=" + month + "&year=" + year);
    }
</script>
