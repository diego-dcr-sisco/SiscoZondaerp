<?php
use Carbon\Carbon;
?>

<div class="col-12"> {{-- Total de Clientes por Categoria --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-between">
                <span class="fs-5 fw-bold">Clientes por año</span>
                <form action="{{ route('crm.chart.totalCustomers') }}" method="GET" id="yearForm">
                    <select class="form-select text-center" id="yearSelector" name="year" onchange="document.getElementById('yearForm').submit()">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="{{ $i }}" {{ $i == $actualYear ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </form>
            </h5>
            <div id="anualCustomersChart">
                {!! $anualCustomersChart->container() !!}
            </div>
        </div>
    </div>
</div>

{!! $anualCustomersChart->script() !!}
<script>
    function updateChart() {
        const year = document.getElementById('yearSelector').value;
        window.location.href = `?year=${year}`;
    }
</script>
