@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
            <span class="fs-5 fw-bold">Servicios realizados por mes</span>
            <div>
                <select class="form-select text-center" id="yearSelectorServicesCompleted" name="year">
                    @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $i == now()->year ? 'selected' : '' }}>{{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        </h5>
        <div id="servicesCompletedChartContainer" class="position-relative">
            <div id="servicesCompletedSpinner" class="d-none" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <canvas id="servicesCompletedChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let servicesCompletedChart;
    let servicesCompletedYear;

    function fetchServicesCompletedData(year) {
        const spinner = document.getElementById('servicesCompletedSpinner');
        if (spinner) spinner.classList.remove('d-none');
        
        fetch(`/crm/chart/services-completed-by-month?year=${year}`)
            .then(response => response.json())
            .then(data => {
                renderServicesCompletedChart(data);
            })
            .catch(error => {
                console.error('Error fetching services completed data:', error);
                if (spinner) spinner.classList.add('d-none');
            });
    }

    function renderServicesCompletedChart(data) {
        const ctx = document.getElementById('servicesCompletedChart').getContext('2d');
        if (servicesCompletedChart) servicesCompletedChart.destroy();

        // Encontrar el mes con más servicios
        const maxValue = Math.max(...data.data);
        const maxIndex = data.data.indexOf(maxValue);

        // Crear colores dinámicos, destacando el mes con más servicios
        const backgroundColors = data.data.map((value, index) => {
            if (index === maxIndex) {
                return 'rgba(222, 82, 59, 0.3)'; // Fiery Terracotta - Mes con más servicios
            }
            return 'rgba(10, 41, 134, 0.2)'; // True Cobalt - Otros meses
        });

        const borderColors = data.data.map((value, index) => {
            if (index === maxIndex) {
                return '#DE523B'; // Fiery Terracotta
            }
            return '#0A2986'; // True Cobalt
        });

        servicesCompletedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Servicios realizados',
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: '#0A2986',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: borderColors,
                    pointBorderColor: borderColors,
                    pointRadius: 5,
                    pointHoverRadius: 7
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
                        text: 'Servicios completados por mes'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                if (context.dataIndex === maxIndex) {
                                    return '⭐ Mes con más servicios';
                                }
                                return '';
                            }
                        }
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
        const spinner = document.getElementById('servicesCompletedSpinner');
        if (spinner) spinner.classList.add('d-none');    }

    servicesCompletedYear = document.getElementById('yearSelectorServicesCompleted').value;
    fetchServicesCompletedData(servicesCompletedYear);

    document.getElementById('yearSelectorServicesCompleted').addEventListener('change', function() {
        fetchServicesCompletedData(this.value);
    });
</script>
