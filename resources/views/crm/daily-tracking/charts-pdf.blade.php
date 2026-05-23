<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Analisis</title>
    <style>
        @page {
            margin: 6mm;
        }

        body {
            font-family: Arial, sans-serif;
            color: #1d2c45;
            margin: 0;
            line-height: 1.25;
            font-size: 10px;
        }

        .header {
            margin-bottom: 8px;
            border-bottom: 2px solid #1f4a87;
            padding-bottom: 5px;
        }

        .header h1 {
            margin: 0;
            color: #123a72;
            font-size: 15px;
        }

        .header p {
            margin: 3px 0 0 0;
            color: #4c5f7f;
            font-size: 9px;
        }

        .meta-box {
            background: #f4f7fc;
            border: 1px solid #d6e0ef;
            border-radius: 6px;
            padding: 7px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .meta-title {
            font-size: 11px;
            font-weight: bold;
            color: #123a72;
            margin-bottom: 4px;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .meta-grid td {
            padding: 3px 5px;
            border-bottom: 1px solid #e3eaf5;
        }

        .meta-grid td:first-child {
            width: 35%;
            color: #4c5f7f;
            font-weight: bold;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #123a72;
            margin-bottom: 4px;
            border-bottom: 2px solid #d85a42;
            padding-bottom: 2px;
        }

        .split-layout {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
        }

        .split-layout td {
            vertical-align: top;
            padding: 0;
        }

        .split-left {
            width: 75%;
            padding-right: 6px !important;
        }

        .split-right {
            width: 25%;
            padding-left: 6px !important;
        }

        .chart-box {
            background: white;
            padding: 7px;
            border: 1px solid #d6e0ef;
            border-radius: 4px;
            page-break-inside: avoid;
            margin-bottom: 8px;
        }

        .chart-image {
            width: 100%;
            height: 185px;
            display: block;
            border: 1px solid #d6e0ef;
            border-radius: 4px;
        }

        .analytics-box {
            border: 1px solid #d6e0ef;
            border-radius: 4px;
            overflow: hidden;
            page-break-inside: avoid;
            margin-bottom: 0;
        }

        .analytics-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .analytics-box th {
            background: #123a72;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
        }

        .analytics-box td {
            padding: 5px 6px;
            border-bottom: 1px solid #e3eaf5;
        }

        .analytics-box tr:nth-child(even) {
            background: #f8fbff;
        }

        .muted {
            color: #4c5f7f;
        }

        .footer {
            text-align: center;
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #d6e0ef;
            font-size: 8px;
            color: #4c5f7f;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Graficas de Analisis</h1>
        <p>Generado el {{ $generatedAt }}</p>
    </div>

    <div class="meta-box">
        <div class="meta-title">Contexto del analisis exportado</div>
        <table class="meta-grid">
            <tr>
                <td>Que se analiza</td>
                <td>{{ $analysisType }}</td>
            </tr>
            <tr>
                <td>Division temporal</td>
                <td>{{ $divisionLabel }}</td>
            </tr>
            <tr>
                <td>Tipo de grafica</td>
                <td>{{ $chartTypeLabel }}</td>
            </tr>
            <tr>
                <td>Rango de fechas</td>
                <td>{{ $dateRangeLabel }}</td>
            </tr>
            <tr>
                <td>Estatus aplicado</td>
                <td>{{ $statusLabel }}</td>
            </tr>
        </table>
    </div>

    <table class="split-layout">
        <tr>
            <td class="split-left">
                <div class="section-title">Grafica exportada</div>
                <div class="chart-box">
                    <p style="margin:0 0 4px 0;"><strong>{{ $chartTitle }}</strong></p>
                    <p class="muted" style="margin:0 0 6px 0;">{{ $chartSubtitle }}</p>
                    <img src="{{ $chartImage }}" alt="Grafica exportada" class="chart-image">
                </div>
            </td>
            <td class="split-right">
                <div class="section-title">Resumen y analiticas</div>
                <div class="analytics-box">
                    <table>
                        <thead>
                            <tr>
                                <th>Indicador</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics as $metric)
                                <tr>
                                    <td>{{ $metric['label'] }}</td>
                                    <td>{{ $metric['value'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="muted">No hay datos suficientes para generar analiticas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automaticamente por el sistema SISCO ZONDA</p>
    </div>
</body>
</html>
