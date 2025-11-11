// Configuración común para gráficas
const chartConfig = {
    monthlyOrders: {
        type: 'line',
        selector: '#monthlyOrdersChart',
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Órdenes Mensuales'
                }
            }
        }
    },
    services: {
        type: 'pie',
        selector: '#servicesChartCanvas',
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Distribución de Servicios'
                }
            }
        }
    },
    technicians: {
        type: 'bar',
        selector: '#techniciansChartCanvas',
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Técnicos Asignados'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    },
    deviceConsumption: {
        type: 'bar',
        selector: '#deviceConsumptionChartCanvas',
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Consumo Mensual por Dispositivo'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de Órdenes'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Mes'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    }
};

// Inicializar gráficas
function initCharts(monthlyOrdersData, serviceStatsData, deviceConsumptionData) {
    // Gráfica de órdenes mensuales
    const monthlyOrdersCanvas = document.querySelector(chartConfig.monthlyOrders.selector);
    if (monthlyOrdersCanvas) {
        new Chart(
            monthlyOrdersCanvas.getContext('2d'),
            {
                type: chartConfig.monthlyOrders.type,
                data: {
                    labels: monthlyOrdersData.labels,
                    datasets: [{
                        label: 'Órdenes por Mes',
                        data: monthlyOrdersData.data,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        fill: true,
                        backgroundColor: 'rgba(75, 192, 192, 0.1)'
                    }]
                },
                options: chartConfig.monthlyOrders.options
            }
        );
    }

    // Gráfica de servicios
    const servicesCanvas = document.querySelector(chartConfig.services.selector);
    if (servicesCanvas) {
        new Chart(
            servicesCanvas.getContext('2d'),
            {
                type: chartConfig.services.type,
                data: {
                    labels: serviceStatsData.labels,
                    datasets: [{
                        data: serviceStatsData.data,
                        backgroundColor: [
                            '#0047AB', '#DC3545', '#28A745', '#FFC107', 
                            '#6F42C1', '#17A2B8', '#FF7F50', '#800080',
                            '#008080', '#FFA500', '#DB7093', '#9ACD32'
                        ],
                        borderWidth: 1
                    }]
                },
                options: chartConfig.services.options
            }
        );
    }

    // Grafica de consumo por dispositivo
    const deviceConsumptionCanvas = document.querySelector(chartConfig.deviceConsumption.selector);
    if (deviceConsumptionCanvas) {
        new Chart(
            deviceConsumptionCanvas.getContext('2d'),
            {
                type: chartConfig.deviceConsumption.type,
                data: {
                    labels: deviceConsumptionData.labels,
                    datasets: deviceConsumptionData.data.map((data, index) => ({
                        label: data.label,
                        data: data.data,
                        backgroundColor: getColor(index),
                        borderWidth: 1
                    }))
                },
                options: chartConfig.deviceConsumption.options
            }
        );
    }

}

// Helper function to generate colors
function getColor(index) {
    const colors = [
        '#0047AB', '#DC3545', '#28A745', '#FFC107', 
        '#6F42C1', '#17A2B8', '#FF7F50', '#800080',
        '#008080', '#FFA500', '#DB7093', '#9ACD32'
    ];
    return colors[index % colors.length];
}

// Date Range Picker
function initDateRangePicker() {
    $('#dateRangePicker').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            firstDay: 1
        },
        autoUpdateInput: false,
        showDropdowns: true,
        ranges: {
            'Este Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes Anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Últimos 3 Meses': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
            'Últimos 6 Meses': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
            'Este Año': [moment().startOf('year'), moment().endOf('year')],
            'Año Anterior': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
    });

    $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $(this).closest('form').submit();
    });
}

// Collapse animation
function initCollapseAnimation() {
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(header => {
        header.addEventListener('click', function() {
            const icon = this.querySelector('.bi-chevron-down');
            if (icon) {
                icon.style.transition = 'transform 0.2s';
                icon.style.transform = this.getAttribute('aria-expanded') === 'true' ? 
                    'rotate(0deg)' : 'rotate(-180deg)';
            }
        });
    });
}

// let deviceConsumptionChart = null;

function initDeviceConsumptionChart() {
    const ctx = document.getElementById('deviceSummaryChart');
    if (!ctx) return;

    const deviceData = window.deviceSummaryData;
    if (!deviceData || !deviceData.devices || deviceData.devices.length === 0) {
        ctx.parentElement.innerHTML = '<div class="alert alert-info">No hay datos de consumo para mostrar en la gráfica.</div>';
        return;
    }

    // Ordenar por tipo y por número extraído del código
    const devices = deviceData.devices.slice().sort((a, b) => {
        if ((a.type ?? 0) !== (b.type ?? 0)) {
            return (a.type ?? 0) - (b.type ?? 0);
        }
        // Extraer número del código (asume formato tipo "CE-1")
        const numA = parseInt((a.code || '').replace(/\D/g, '')) || 0;
        const numB = parseInt((b.code || '').replace(/\D/g, '')) || 0;
        return numA - numB;
    });

    // Labels: código del dispositivo (ejemplo: "CE-1")
    const labels = devices.map(d => d.code);
    const data = devices.map(d => d.totalConsumption);

    // Destruir gráfica previa si existe
    if (window.deviceSummaryChartInstance) {
        window.deviceSummaryChartInstance.destroy();
    }

    window.deviceSummaryChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Consumo Total',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Consumo total por dispositivo (ordenado por tipo y número)'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Consumo: ${context.raw.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Dispositivo'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Consumo'
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}

// Manejar cambios en los filtros
function handleFilterChanges() {
    const serviceFilter = document.querySelector('select[name="service_id"]');
    const dateFilter = document.querySelector('input[name="date_range"]');
    
    if (serviceFilter) {
        serviceFilter.addEventListener('change', function() {
            setTimeout(initDeviceConsumptionChart, 300);
        });
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            setTimeout(initDeviceConsumptionChart, 300);
        });
    }
}


// Initialize all components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts with data from the page
    initCharts(
        {
            labels: window.monthlyOrdersLabels,
            data: window.monthlyOrdersData
        },
        {
            labels: window.serviceStatsLabels,
            data: window.serviceStatsData
        },
        {
            labels: window.technicianStatsLabels,
            data: window.technicianStatsData
        },
        {
            labels: window.deviceConsumptionLabels,
            data: window.deviceConsumptionData
        }
    );
    
    initDateRangePicker();
    initCollapseAnimation();

    if (document.getElementById('deviceSummaryChart')) {
        console.log('pasa por aqui desde la funcion init');
        initDeviceConsumptionChart();
        
        // Actualizar la gráfica cuando cambie el filtro de servicio
        const serviceFilter = document.querySelector('select[name="service_id"]');
        if (serviceFilter) {
            serviceFilter.addEventListener('change', function() {
                // Pequeño retraso para asegurar que los datos se hayan actualizado
                setTimeout(initDeviceConsumptionChart, 100);
            });
        }
        handleFilterChanges();
    }

}); 