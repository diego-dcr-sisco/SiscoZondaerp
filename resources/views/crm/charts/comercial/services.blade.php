@php
    use Carbon\Carbon;
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-bold d-flex justify-content-between">
            <span class="fs-5 fw-bold">Servicios por mes</span>
            <div class="input-group w-50">
                <div class="input-group w-100 mb-3">
                    <select class="form-select" id="yearServicesSelector">
                        @for ($i = Carbon::now()->year; $i >= Carbon::now()->year - 5; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <select class="form-select" id="monthServicesSelector">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                {{ Carbon::create()->month($i)->locale('es')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
        </h5>
        <div id="monthlyServicesChartContainer">
            <canvas id="monthlyServicesChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let monthlyServicesChart;
    let msYear;
    let msMonth;

    function fetchMonthlyServicesData(year, month) {
        fetch(`/crm/chart/services-by-type?year=${year}&month=${month}`)
            .then(response => response.json())
            .then(data => {
                // Transformar los datos al formato esperado
                const chartData = {
                    labels: ['Domésticos', 'Comerciales', 'Industrial/Planta'],
                    data: [data.domestics, data.comercials, data.industrials]
                };
                renderMonthlyServicesChart(chartData);
            })
            .catch(error => console.error('Error fetching monthly services data:', error));
    }

    function renderMonthlyServicesChart(data) {
        const ctx = document.getElementById('monthlyServicesChart').getContext('2d');
        if (monthlyServicesChart) monthlyServicesChart.destroy();

        monthlyServicesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Servicios',
                    data: data.data,
                    backgroundColor: ['#0A2986', '#512A87', '#DE523B'],
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
                        text: 'Servicios por tipo'
                    }
                }
            }
        });
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelector = document.getElementById('yearServicesSelector');
        const monthSelector = document.getElementById('monthServicesSelector');
        
        if (yearSelector && monthSelector) {
            msYear = yearSelector.value;
            msMonth = monthSelector.value;
            fetchMonthlyServicesData(msYear, msMonth);

            yearSelector.addEventListener('change', function() {
                fetchMonthlyServicesData(this.value, monthSelector.value);
            });
            
            monthSelector.addEventListener('change', function() {
                fetchMonthlyServicesData(yearSelector.value, this.value);
            });
        }
    });
</script>
