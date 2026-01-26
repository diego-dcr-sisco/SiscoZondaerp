@php
    use Carbon\Carbon;
    $date = Carbon::now();
@endphp

<div class="col mb-3">
    <div class="card shadow-sm border-dark">
        <div class="card-body">
            <h5 class="card-title fw-bold d-flex justify-content-between">
                <span> Domesticos agendados </span>
                <select class="form-select  w-25" onchange="updateChart(this.value)">
                    @foreach ($months as $i => $month)
                        <option value="{{ $i + 1 }}" {{ $date->month == $i + 1 ? 'selected' : '' }}>
                            {{ $month }} </option>
                    @endforeach
                </select>
            </h5>
            <div id="chart">
                {!! $charts['domestic']->container() !!}
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue"></script>
<script>
    var chart = new Vue({
        el: '#chart',
    });
</script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js charset=utf-8></script>
{!! $charts['domestic']->script() !!}
