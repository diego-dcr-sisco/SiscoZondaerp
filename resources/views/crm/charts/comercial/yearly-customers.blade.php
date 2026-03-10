@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
            <span class="fs-5 fw-bold">Clientes por mes</span>
            <div>
                <select class="form-select text-center" id="yearSelectorCustomers" name="year">
                    @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $i == $actualYear ? 'selected' : '' }}>{{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        </h5>
        <div id="customersYearlyChartContainer" class="position-relative">
            <div id="customersSpinner" class="d-none" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <canvas id="customersYearlyChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let customersChart;
    let customersYear;

    function fetchCustomersData(year) {
        const spinner = document.getElementById('customersSpinner');
        if (spinner) spinner.classList.remove('d-none');
        
        fetch(`/crm/chart/customers-by-month?year=${year}`)
            .then(response => response.json())
            .then(data => {
                renderCustomersChart(data);
            })
            .catch(error => {
                //console.error('Error fetching customers data:', error);
                if (spinner) spinner.classList.add('d-none');
            });
    }

    function renderCustomersChart(data) {
        const ctx = document.getElementById('customersYearlyChart').getContext('2d');
        if (customersChart) customersChart.destroy();
        customersChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                        label: 'Domésticos',
                        data: data.domestics,
                        borderColor: '#0A2986',
                        backgroundColor: 'rgba(10, 41, 134, 0.2)',
                        fill: true
                    },
                    {
                        label: 'Comerciales',
                        data: data.comercials,
                        borderColor: '#512A87',
                        backgroundColor: 'rgba(81, 42, 135, 0.2)',
                        fill: true
                    },
                    {
                        label: 'Industrial/Planta',
                        data: data.industrials,
                        borderColor: '#DE523B',
                        backgroundColor: 'rgba(222, 82, 59, 0.2)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Clientes por mes'
                    }
                }
            }
        });

        const spinner = document.getElementById('customersSpinner');
        if (spinner) spinner.classList.add('d-none');
    }

    customersYear = document.getElementById('yearSelectorCustomers').value;
    fetchCustomersData(customersYear);

    document.getElementById('yearSelectorCustomers').addEventListener('change', function() {
        fetchCustomersData(this.value);
    });
</script>
