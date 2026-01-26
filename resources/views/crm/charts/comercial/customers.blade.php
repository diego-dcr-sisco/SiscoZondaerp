@php
    use Carbon\Carbon;    
    $date = Carbon::now();
@endphp

<div class="col-12"> {{-- Total de Clientes por Categoria --}}
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title fw-bold d-flex justify-content-between">
                <span>Total de Clientes por Categoria</span>
                <div class="input-group w-25">
                    {{-- Selector de año para la nueva gráfica --}}
                    <select class="form-select" id="yearCategorySelector" onchange="refreshCategoryChart()">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <!-- Botón para abrir el modal -->
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCustomersModal">
                    Ver Nuevos Clientes por Mes
                </button>
            </h5>
            <div id="categoryChart">
                {!! $categoryChart->container() !!}
            </div>
        </div>
    </div>
</div>
 
{{-- Nuevos Clientes por mes --}} 
@include('crm.charts.comercial.modals.new-monthly-customers-modal')


<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/4.0.2/echarts-en.min.js" charset="utf-8"></script>
    {!! $categoryChart->script() !!}    
    {!! $chart->script() !!}
    
<script>
    var category_chart_api_url = {{ $categoryChart->id }}_api_url;

    function refreshCategoryChart() {
        const year = $('#yearCategorySelector').val();

        {{ $categoryChart->id }}_refresh(category_chart_api_url + '/update' + "?year=" + year);
    }
</script>

