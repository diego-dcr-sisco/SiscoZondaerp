<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficas de Análisis - Reporte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 20px;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 12px;
        }
        
        .filters-info {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            font-size: 11px;
            color: #555;
        }
        
        .filters-info strong {
            color: #333;
        }
        
        .chart-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .chart-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .chart-container {
            background: white;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 250px;
        }
        
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .col-50 {
            flex: 1;
            min-width: 45%;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Gráficas de Análisis</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    @if($dateRange)
        <div class="filters-info">
            <strong>Rango de fechas aplicado:</strong> {{ $dateRange['start'] }} a {{ $dateRange['end'] }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">1) Medio de contacto con mayor cantidad</div>
                <div class="chart-container">
                    {!! $contactMethodChart->renderHtml() !!}
                </div>
            </div>
        </div>
        
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">2) Grafica de montos ($)</div>
                <div class="chart-container">
                    {!! $amountsChart->renderHtml() !!}
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">3) Clientes ingresados por semana/mes en un año</div>
                <div class="chart-container">
                    {!! $clientsPeriodChart->renderHtml() !!}
                </div>
            </div>
        </div>
        
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">4) Tasa de conversión (%)</div>
                <div class="chart-container">
                    <canvas id="dailyTrackingConversionChart" width="400" height="300"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
                    <script>
                        const ctx = document.getElementById('dailyTrackingConversionChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: {!! json_encode($conversionLabels) !!},
                                datasets: [{
                                    label: 'Tasa de conversión (%)',
                                    data: {!! json_encode($conversionData) !!},
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#28a745'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: { display: true }
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        position: 'left',
                                        max: 100
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema SISCO ZONDA</p>
    </div>
</body>
</html>
