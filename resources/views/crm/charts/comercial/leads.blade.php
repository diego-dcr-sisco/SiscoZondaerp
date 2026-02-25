@php
    use Carbon\Carbon;
    $date = Carbon::now();
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-bold d-flex justify-content-between">
            <span class="fs-5 fw-bold">Clientes potenciales por mes</span>
            <form id="filterForm">
                <select class="form-select text-center" id="yearSelector" name="year"
                    style="width: 10vw; display: inline-block;">
                    @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $i == $actualYear ? 'selected' : '' }}>
                            {{ $i }}</option>
                    @endfor
                </select>
                <select class="form-select text-center" id="monthSelector" name="month"
                    style="width: 10vw; display: inline-block;">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == $actualMonth ? 'selected' : '' }}>
                            {{ Carbon::create()->month($i)->locale('es')->monthName }}
                        </option>
                    @endfor
                </select>
            </form>
        </h5>
        <div id="newLeadsChart">
            {!! $leadsChart->container() !!}
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js" charset="utf-8"></script>
{!! $leadsChart->script() !!}

<script>
    $(document).ready(function() {
        $('#yearSelector, #monthSelector').change(function() {
            var year = $('#yearSelector').val();
            var month = $('#monthSelector').val();
            $.ajax({
                url: '{{ route('crm.chart.newLeadsByMonth') }}',
                type: 'GET',
                data: {
                    year: year,
                    month: month
                },
                success: function(data) {
                    var chart = echarts.getInstanceByDom(document.getElementById(
                        'newLeadsChart'));
                    chart.setOption(data);
                },
                error: function(xhr, status, error) {
                    //console.error('Error al actualizar la gráfica:', error);
                }
            });
        });
    });
</script>
