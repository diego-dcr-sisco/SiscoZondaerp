@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-bold d-flex justify-content-between">
            <span class="fs-5 fw-bold">Plagas más presentadas</span>
            <div class="input-group w-50">
                <div class="input-group w-100 mb-3">
                    <select class="form-select" id="yearPestsSelector" onchange="refreshPestsDonutChart()">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <select class="form-select" id="monthPestsSelector" onchange="refreshPestsDonutChart()">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                {{ Carbon::create()->month($i)->locale('es')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </h5>
        <div id="pestsDonutChart">
            {!! $pestsDonutChart->container() !!}
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js" charset="utf-8"></script>
{!! $pestsDonutChart->script() !!}

<script>
    var pests_chart_api_url = '';

    function refreshPestsDonutChart() {
        const month = $('#monthPestsSelector').val();
        const year = $('#yearPestsSelector').val();
        
        if (!pests_chart_api_url) {
            pests_chart_api_url = {{ $pestsDonutChart->id }}_api_url;
        }

        const apiUrl = pests_chart_api_url + '/update' + "?month=" + month + "&year=" + year;
        console.log('🔄 Actualizando gráfica de plagas:', apiUrl);

        {{ $pestsDonutChart->id }}_refresh(apiUrl);
    }
</script>
