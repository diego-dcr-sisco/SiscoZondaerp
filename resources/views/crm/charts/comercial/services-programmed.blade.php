@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-bold d-flex justify-content-between">
            <span class="fs-5 fw-bold">Tipo de servicio por mes</span>
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

        // Definir colores para cada tipo de servicio - Paleta corporativa
        const colors = [
            '#012640', // Deep Space Blue
            '#DE523B', // Fiery Terracotta
            '#02265A', // Deep Navy
            '#B74453', // Dusty Mauve
            '#0A2986', // True Cobalt
            '#512A87', // Indigo Velvet
            '#773774', // Velvet Purple
        ];

        // Asignar colores a cada tipo de servicio
        const backgroundColors = data.labels.map((label, index) => colors[index % colors.length]);

        servicesProgrammedChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Tipo de servicio por mes'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
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
