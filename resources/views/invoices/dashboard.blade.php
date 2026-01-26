@extends('layouts.app')
@section('content')
    
    <div class="row w-100 justify-content-between m-0 h-100">

        <!-- Contenido principal -->
        <div class="col-12 p-0" style="background-color: #FFF;">
            <div class="d-flex align-items-center border-bottom ps-4 p-2">
                <span class="text-black fw-bold fs-4">
                    MÓDULO DE PAGOS Y FACTURACIÓN
                </span>
            </div>

            @include('invoices.dashboard-content')
        </div>
    </div>

    <!-- Chart.js desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src=" https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js "></script>

    <script>
        // Gráfico de Ingresos Mensuales con datos reales
        const incomeChart = new Chart(document.getElementById('incomeChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyData['months']),
                datasets: [{
                    label: 'Ingresos (MXN)',
                    data: @json($monthlyData['amounts']),
                    borderColor: 'var(--jasper)',
                    backgroundColor: 'rgba(195, 82, 62, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
    </script>
    <!-- FullCalendar JS desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/es.js"></script>

    <!-- Script para inicializar FullCalendar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                events: '{{ route('invoices.events') }}',
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    if (props.type === 'payment_day') {
                        const statusText = {
                            'facturable': 'Facturable',
                            'moroso': 'Moroso',
                            'no_facturable': 'No Facturable'
                        };
                        
                        alert(`
                            Cliente: ${props.customer_name}
                            Método de Pago: ${props.payment_method}
                            Estado: ${statusText[props.status] || props.status}
                            Fecha: ${info.event.start.toLocaleDateString('es-ES')}
                        `);
                    }
                },
                eventDidMount: function(info) {
                    // Agregar tooltip con información adicional
                    const props = info.event.extendedProps;
                    if (props.type === 'payment_day') {
                        info.el.setAttribute('title', 
                            `Cliente: ${props.customer_name}\nEstado: ${props.status}`
                        );
                    }
                },
                dayMaxEvents: 3, // Limitar eventos por día
                moreLinkClick: 'popover' // Mostrar más eventos en popover
            });
            calendar.render();
        });
    </script>
@endsection
