@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
            <span class="fs-5 fw-bold">Leads por mes</span>
            <div>
                <select class="form-select text-center" id="yearSelectorLeads" name="year">
                    @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $i == $actualYear ? 'selected' : '' }}>{{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        </h5>
        <div id="leadsYearlyChartContainer">
            <canvas id="leadsYearlyChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let leadsChart;
    let leadsYear;

    function fetchLeadsData(year) {
        fetch(`/crm/chart/leads-by-month?year=${year}`)
            .then(response => response.json())
            .then(data => {
                renderLeadsChart(data);
            })
            .catch(error => console.error('Error fetching leads data:', error));
    }

    function renderLeadsChart(data) {
        const ctx = document.getElementById('leadsYearlyChart').getContext('2d');
        if (leadsChart) leadsChart.destroy();
        leadsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                        label: 'Domésticos',
                        data: data.domestics,
                        borderColor: '#039BE5',
                        backgroundColor: 'rgba(3, 155, 229, 0.2)',
                        fill: true
                    },
                    {
                        label: 'Comerciales',
                        data: data.comercials,
                        borderColor: '#1A237E',
                        backgroundColor: 'rgba(26, 35, 126, 0.2)',
                        fill: true
                    },
                    {
                        label: 'Industrial/Planta',
                        data: data.industrials,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
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
                        text: 'Leads por mes'
                    }
                }
            }
        });
    }

    leadsYear = document.getElementById('yearSelectorLeads').value;
    fetchLeadsData(leadsYear);

    document.getElementById('yearSelectorLeads').addEventListener('change', function() {
        fetchLeadsData(this.value);
    });
</script>
