@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-bold d-flex justify-content-between">
            <span class="fs-5 fw-bold">Servicios programados</span>
            <div class="input-group w-50">
                <div class="input-group w-100 mb-3">
                    <select class="form-select" id="yearServicesProgrammedSelector">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <select class="form-select" id="monthServicesProgrammedSelector">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                {{ Carbon::create()->month($i)->locale('es')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </h5>
        <div id="servicesProgrammedChartContainer">
            <canvas id="servicesProgrammedChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let servicesProgrammedChart;
    let spYear;
    let spMonth;

    function fetchServicesProgrammedData(year, month) {
        fetch(`/crm/chart/services-programmed?year=${year}&month=${month}`)
            .then(response => response.json())
            .then(data => {
                renderServicesProgrammedChart(data);
            })
            .catch(error => console.error('Error fetching services programmed data:', error));
    }

    function renderServicesProgrammedChart(data) {
        const ctx = document.getElementById('servicesProgrammedChart').getContext('2d');
        if (servicesProgrammedChart) servicesProgrammedChart.destroy();

        // Generar colores dinámicos para cada servicio
        const backgroundColors = data.labels.map((_, index) => {
            const colors = [
                '#012640', '#02265A', '#0A2986', '#512A87', '#773774',
                '#B74453', '#DE523B', '#012640', '#02265A', '#0A2986'
            ];
            return colors[index % colors.length];
        });

        servicesProgrammedChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Órdenes programadas',
                    data: data.data,
                    backgroundColor: backgroundColors,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Servicios programados (órdenes generadas)'
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

    spYear = document.getElementById('yearServicesProgrammedSelector').value;
    spMonth = document.getElementById('monthServicesProgrammedSelector').value;
    fetchServicesProgrammedData(spYear, spMonth);

    document.getElementById('yearServicesProgrammedSelector').addEventListener('change', function() {
        fetchServicesProgrammedData(this.value, document.getElementById('monthServicesProgrammedSelector')
            .value);
    });
    document.getElementById('monthServicesProgrammedSelector').addEventListener('change', function() {
        fetchServicesProgrammedData(document.getElementById('yearServicesProgrammedSelector').value, this
            .value);
    });
</script>
