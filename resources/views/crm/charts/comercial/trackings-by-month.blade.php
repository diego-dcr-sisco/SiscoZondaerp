@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
            <span class="fs-5 fw-bold">Seguimientos programados por mes</span>
            <div>
                <select class="form-select text-center" id="yearSelectorTrackings" name="year">
                    @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $i == now()->year ? 'selected' : '' }}>{{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        </h5>
        <div id="trackingsYearlyChartContainer">
            <canvas id="trackingsYearlyChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let trackingsChart;
    let trackingsYear;

    function fetchTrackingsData(year) {
        fetch(`/crm/chart/trackings-by-month?year=${year}`)
            .then(response => response.json())
            .then(data => {
                renderTrackingsChart(data);
            })
            .catch(error => console.error('Error fetching trackings data:', error));
    }

    function renderTrackingsChart(data) {
        const ctx = document.getElementById('trackingsYearlyChart').getContext('2d');
        if (trackingsChart) trackingsChart.destroy();
        trackingsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Seguimientos programados',
                    data: data.data,
                    backgroundColor: 'rgba(156, 39, 176, 0.2)',
                    borderColor: '#9C27B0',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Seguimientos de clientes por mes'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    trackingsYear = document.getElementById('yearSelectorTrackings').value;
    fetchTrackingsData(trackingsYear);

    document.getElementById('yearSelectorTrackings').addEventListener('change', function() {
        fetchTrackingsData(this.value);
    });
</script>
