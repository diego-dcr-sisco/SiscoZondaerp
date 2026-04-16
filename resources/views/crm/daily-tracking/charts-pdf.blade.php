<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficas de Análisis - Reporte</title>
    <style>
        :root {
            --deep-space-blue: #012640;
            --deep-navy: #02265A;
            --true-cobalt: #0A2986;
            --indigo-velvet: #512A87;
            --velvet-purple: #793775;
            --dusty-mauve: #B74453;
            --fiery-terracotta: #DD513A;
            --surface-soft: #f5f7fb;
            --line-soft: #d6deea;
            --text-main: #012640;
            --text-muted: #4d5f78;
        }

        body {
            font-family: Arial, sans-serif;
            color: var(--text-main);
            margin: 15px;
            line-height: 1.4;
            font-size: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid var(--fiery-terracotta);
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            color: var(--deep-space-blue);
            font-size: 18px;
        }
        
        .header p {
            margin: 3px 0 0 0;
            color: var(--text-muted);
            font-size: 9px;
        }
        
        .filters-info {
            background: var(--surface-soft);
            padding: 8px;
            margin-bottom: 15px;
            border-left: 4px solid var(--true-cobalt);
            font-size: 9px;
            color: var(--text-muted);
        }
        
        .chart-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .chart-title {
            font-size: 12px;
            font-weight: bold;
            color: var(--deep-space-blue);
            margin-bottom: 8px;
            border-bottom: 2px solid var(--dusty-mauve);
            padding-bottom: 4px;
        }
        
        .chart-container {
            background: white;
            padding: 8px;
            border: 1px solid var(--line-soft);
            border-radius: 4px;
        }

        .chart-image {
            width: 100%;
            height: auto;
            display: block;
            border: 1px solid var(--line-soft);
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 5px;
        }
        
        table th {
            background: var(--deep-navy);
            color: #ffffff;
            padding: 5px;
            text-align: left;
            border-bottom: 2px solid var(--true-cobalt);
            font-weight: bold;
        }
        
        table td {
            padding: 4px 5px;
            border-bottom: 1px solid var(--line-soft);
        }
        
        table tr:nth-child(even) {
            background: var(--surface-soft);
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
            border-top: 1px solid var(--line-soft);
            font-size: 8px;
            color: var(--text-muted);
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .highlight {
            color: var(--velvet-purple);
            font-weight: bold;
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
            <img src="{{ $contactChartImage }}" alt="Grafica de medio de contacto" class="chart-image">
        </div>
    </div>

    {{-- Chart 2 & 3: Side by side --}}
    <div class="row">
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">2) Montos facturados ($) por período</div>
                <div class="chart-container">
                    <img src="{{ $amountChartImage }}" alt="Grafica de montos" class="chart-image">
                </div>
            </div>
        </div>

        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">3) Clientes ingresados por semana</div>
                <div class="chart-container">
                    <img src="{{ $clientsChartImage }}" alt="Grafica de clientes" class="chart-image">
                </div>
            </div>
        </div>
    </div>

    {{-- Chart 4 Image --}}
    <div class="chart-section">
        <div class="chart-title">4) Tasa de Conversión por Período (%)</div>
        <div class="chart-container">
            <img src="{{ $conversionChartImage }}" alt="Grafica de conversion" class="chart-image">
        </div>
    </div>

    {{-- Tablas de respaldo / detalle --}}
    <div class="chart-section">
        <div class="chart-title">Detalle 1) Datos de medio de contacto</div>
        <div class="chart-container">
            <table>
                <thead>
                    <tr>
                        <th>Medio de Contacto</th>
                        <th style="text-align: center;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contactMethodsData as $item)
                        <tr>
                            <td>{{ $contactMethodLabels[$item->contact_method] ?? $item->contact_method }}</td>
                            <td class="text-center">{{ $item->count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center" style="color: #4d5f78;">Sin datos disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Detalle 2 & 3: Side by side --}}
    <div class="row">
        <div class="col-50">
            <div class="chart-section">
                <div class="chart-title">Detalle 2) Datos de montos facturados</div>
                <div class="chart-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th class="text-right">Monto ($)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amountsData as $item)
                                <tr>
                                    <td>{{ $item->period }}</td>
                                    <td class="text-right">{{ number_format((float)$item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center" style="color: #4d5f78;">Sin datos disponibles</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-50">
            <div class="chart-section">
                        <div class="chart-title">Detalle 3) Datos de clientes ingresados</div>
                <div class="chart-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Año</th>
                                <th class="text-center">Semana</th>
                                <th class="text-center">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientsData as $item)
                                <tr>
                                    <td>{{ $item->year }}</td>
                                    <td class="text-center">{{ $item->week }}</td>
                                    <td class="text-center">{{ $item->count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="color: #4d5f78;">Sin datos disponibles</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Chart 4: Conversion Rate (detail table) --}}
    <div class="chart-section">
        <div class="chart-title">Detalle 4) Datos de tasa de conversión</div>
        <div class="chart-container">
            <table>
                <thead>
                    <tr>
                        <th>Período</th>
                        <th class="text-center">Cotizados</th>
                        <th class="text-center">Cerrados</th>
                        <th class="text-center">Tasa (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversionData as $index => $rate)
                        <tr>
                            <td>{{ $conversionLabels[$index] ?? '-' }}</td>
                            <td class="text-center">{{ $conversionQuotedCounts[$index] ?? '-' }}</td>
                            <td class="text-center">{{ $conversionClosedCounts[$index] ?? '-' }}</td>
                            <td class="text-center highlight">{{ $rate }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center" style="color: #4d5f78;">Sin datos disponibles</td>
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
