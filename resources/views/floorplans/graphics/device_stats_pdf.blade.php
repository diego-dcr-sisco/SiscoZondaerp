<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estadísticas del Dispositivo - {{ $device->code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            background: white;
            padding: 10mm;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        .header-container {
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 2px solid #DE523B;
        }

        .header-text h1 {
            font-size: 20px;
            font-weight: bold;
            color: #DE523B;
            margin-bottom: 3px;
        }

        .header-info {
            font-size: 11px;
            margin-top: 3px;
        }

        .header-info span {
            margin: 0 8px;
        }

        .section {
            margin-bottom: 5mm;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #DE523B;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #ddd;
        }

        .chart-container {
            width: 100%;
            margin-bottom: 5mm;
        }

        .chart-container img {
            width: 100%;
            height: auto;
            max-height: 180mm;
        }

        .reviews-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
            font-size: 9px;
        }

        .reviews-table th {
            background-color: #DE523B;
            color: white;
            padding: 3px 5px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }

        .reviews-table td {
            padding: 3px 5px;
            border: 1px solid #ddd;
        }

        .reviews-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        footer {
            position: fixed;
            bottom: 5mm;
            left: 10mm;
            right: 10mm;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 2mm;
        }

        .page-number:after {
            content: counter(page);
        }

        .two-columns {
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }

        .column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .column:first-child {
            padding-right: 2%;
        }

        .column:last-child {
            padding-left: 2%;
        }

        .stats-box {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 3mm;
            margin-bottom: 3mm;
        }

        .stats-box h3 {
            font-size: 12px;
            color: #DE523B;
            margin-bottom: 2mm;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header-container">
        <div class="header-text">
            <h1>Estadísticas del Dispositivo</h1>
            <div class="header-info">
                <span><strong>Dispositivo:</strong> {{ $device->code }}</span> |
                <span><strong>Plano:</strong> {{ $floorplan->filename }}</span> |
                <span><strong>Ubicación:</strong> {{ $device->location ?? 'N/A' }}</span>
                @if($startDate && $endDate)
                    | <span><strong>Período:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                @else
                    | <span><strong>Año:</strong> {{ $year }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Sección de Gráficas -->
    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">Incidencias por Tipo de Plaga</div>
                <div class="chart-container">
                    <img src="{{ $pestsChartImage }}" alt="Gráfica de plagas">
                </div>
            </div>
        </div>
        
        <div class="column">
            <div class="section">
                <div class="section-title">Tendencia Anual</div>
                <div class="chart-container">
                    <img src="{{ $trendChartImage }}" alt="Gráfica de tendencia">
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Resumidas -->
    <div class="section">
        <div class="section-title">Resumen Estadístico</div>
        <div class="two-columns">
            <div class="column">
                <div class="stats-box">
                    <h3>Total de Incidencias</h3>
                    <p style="font-size: 18px; font-weight: bold; color: #DE523B;">{{ array_sum($graph_per_pests['data']) }}</p>
                </div>
            </div>
            <div class="column">
                <div class="stats-box">
                    <h3>Plagas Detectadas</h3>
                    <p style="font-size: 18px; font-weight: bold; color: #DE523B;">{{ count(array_filter($graph_per_pests['data'])) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Revisiones -->
    @if(isset($reviews) && $reviews->count())
    <div class="section">
        <div class="section-title">Últimas 10 Revisiones</div>
        <table class="reviews-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 30%;">Pregunta</th>
                    <th style="width: 40%;">Respuesta</th>
                    <th style="width: 15%;">Orden</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $rev)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($rev->updated_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $rev->question?->text ?? 'Pregunta' }}</td>
                    <td>{{ $rev->answer }}</td>
                    <td>{{ $rev->order ? '#' . $rev->order->folio : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Pie de página -->
    <footer>
        <p>Documento generado automáticamente • {{ now()->format('d/m/Y H:i') }} • Página <span class="page-number"></span></p>
    </footer>
</body>
</html>
