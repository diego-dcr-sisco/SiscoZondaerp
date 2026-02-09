@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-bold d-flex justify-content-between">
            <span class="fs-5 fw-bold">Plagas más presentadas</span>
            <div class="input-group w-50">
                <div class="input-group w-100 mb-3">
                    <select class="form-select" id="yearPestsSelector">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <select class="form-select" id="monthPestsSelector">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                {{ Carbon::create()->month($i)->locale('es')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </h5>
        <div id="pestsDonutChartContainer">
            <canvas id="pestsDonutChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let pestsChart;
    let pestsYear;
    let pestsMonth;

    function fetchPestsData(year, month) {
        fetch(`/crm/chart/pests-by-customer?year=${year}&month=${month}`)
            .then(response => response.json())
            .then(data => {
                renderPestsChart(data);
            })
            .catch(error => console.error('Error fetching pests data:', error));
    }

    function renderPestsChart(data) {
        const ctx = document.getElementById('pestsDonutChart').getContext('2d');
        if (pestsChart) pestsChart.destroy();

        // Generar colores dinámicos
        const backgroundColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];

        pestsChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Incidencias',
                    data: data.data,
                    backgroundColor: backgroundColors.slice(0, data.labels.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            padding: 10
                        }
                    },
                    title: {
                        display: true,
                        text: 'Top 10 plagas más presentadas'
                    }
                }
            }
        });
    }

    pestsYear = document.getElementById('yearPestsSelector').value;
    pestsMonth = document.getElementById('monthPestsSelector').value;
    fetchPestsData(pestsYear, pestsMonth);

    document.getElementById('yearPestsSelector').addEventListener('change', function() {
        fetchPestsData(this.value, document.getElementById('monthPestsSelector').value);
    });
    document.getElementById('monthPestsSelector').addEventListener('change', function() {
        fetchPestsData(document.getElementById('yearPestsSelector').value, this.value);
    });
</script>
