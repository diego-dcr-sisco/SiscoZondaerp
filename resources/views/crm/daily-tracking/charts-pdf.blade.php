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
            margin: 15px;
            line-height: 1.4;
            font-size: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            color: #dc3545;
            font-size: 18px;
        }
        
        .header p {
            margin: 3px 0 0 0;
            color: #666;
            font-size: 9px;
        }
        
        .filters-info {
            background: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #dc3545;
            font-size: 9px;
            color: #555;
        }
        
        .chart-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .chart-title {
            font-size: 12px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 8px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 4px;
        }
        
        .chart-container {
            background: white;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 5px;
        }
        
        table th {
            background: #f0f0f0;
            color: #333;
            padding: 5px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        
        table td {
            padding: 4px 5px;
            border-bottom: 1px solid #eee;
        }
        
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .col-50 {
            flex: 1;
            min-width: 45%;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #999;
        }
        
        .stat-box {
            background: #f0f0f0;
            padding: 8px;
            margin: 5px 0;
            border-left: 3px solid #dc3545;
        }
        
        .stat-value {
            font-weight: bold;
            color: #dc3545;
            font-size: 11px;
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
    
    {{-- Chart 1: Contact Methods --}}
    <div class="chart-section">
        <div class="chart-title">1) Medio de contacto con mayor cantidad</div>
        <div class="chart-container">
            {!! $contactMethodChart->renderHtml() !!}
        </div>
    </div>
    
    {{-- Chart 2: Amounts --}}
    <div class="row">
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">2) Montos facturados ($) por periodo</div>
                <div class="chart-container">
                    {!! $amountsChart->renderHtml() !!}
                </div>
            </div>
        </div>
        
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">3) Clientes ingresados por semana</div>
                <div class="chart-container">
                    {!! $clientsPeriodChart->renderHtml() !!}
                </div>
            </div>
        </div>
    </div>
    
    {{-- Chart 4: Conversion Rate --}}
    <div class="chart-section">
        <div class="chart-title">4) Tasa de Conversión por Período (%)</div>
        <div class="chart-container">
            <table>
                <thead>
                    <tr>
                        <th>Período</th>
                        <th style="text-align: center;">Cotizados</th>
                        <th style="text-align: center;">Cerrados</th>
                        <th style="text-align: center;">Tasa (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversionData as $index => $rate)
                        <tr>
                            <td>{{ $conversionLabels[$index] ?? '-' }}</td>
                            <td style="text-align: center;">{{ $conversionQuotedCounts[$index] ?? '-' }}</td>
                            <td style="text-align: center;">{{ $conversionClosedCounts[$index] ?? '-' }}</td>
                            <td style="text-align: center; font-weight: bold; color: #dc3545;">{{ $rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999;">Sin datos disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema SISCO ZONDA</p>
    </div>
</body>
</html>
