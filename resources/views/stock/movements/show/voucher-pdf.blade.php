<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Movimiento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 15px;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 0.5px solid #000;
        }

        .header-row {
            display: table-row;
        }

        .logo-container {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: left;
        }

        .logo-placeholder {
            width: 120px;
            height: 60px;
            border: 1px dashed #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-style: italic;
            color: #666;
        }

        .document-info {
            display: table-cell;
            width: 70%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .document-details {
            font-size: 11px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 110px;
        }

        .info-value {
            display: inline-block;
        }

        .observations {
            margin: 10px 0;
            padding: 8px;
            background-color: #f0f0f0;
            border-left: 2px solid #666;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            page-break-inside: avoid;
        }

        .products-table th {
            background-color: #ddd;
            padding: 2px;
            text-align: left;
            border: 0.5px solid #000;
            font-weight: bold;
            font-size: 11px;
        }

        .products-table td {
            font-size: 11px;
            padding: 2px;
            border: 0.5px solid #000;
        }

        .signatures-container {
            width: 100%;
            margin-top: 10px;
        }

        .signatures {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-top: 10px;
        }

        .signature-content {
            width: 80%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            margin: 0 auto;
            text-align: center;
            min-height: 100px;
        }

        .signature-title {
            margin-top: 5px;
            font-weight: bold;
            text-align: center;
            width: 100%;
        }

        .signature-name {
            margin-top: 4px;
            text-align: center;
            width: 100%;
        }

        .signature-image {
            margin-top: 5px;
            text-align: center;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 0.5px solid #000;
            padding-top: 8px;
        }

        @page {
            margin: 1cm;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-row">
            <div class="logo-container">
                <img src="file://{{ public_path('images/logo.png') }}" style="width: 300px; margin: 0;">
            </div>
            <div class="document-info">
                <div class="company-name">Sistema de Inventarios</div>
                <div class="report-title">Reporte de Movimiento</div>
                <div class="document-details">
                    <div>Folio: <strong>{{ $folio }}</strong></div>
                    <div>Fecha: {{ $date }} | Hora: {{ $time }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Origen:</span>
            <span class="info-value">{{ $origin }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Destino:</span>
            <span class="info-value">{{ $destination }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Tipo movimiento:</span>
            <span class="info-value">{{ $movement_type }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Registrado por:</span>
            <span class="info-value">{{ $created_by }}</span>
        </div>
    </div>

    <div class="observations">
        <div><strong>Observaciones:</strong> {{ $observations }}</div>
    </div>

    <table class="products-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Lote/Serie</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product['product'] }}</td>
                    <td>{{ $product['lot'] }}</td>
                    <td>{{ $product['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures-container">
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-content">
                    <div class="signature-title">Firma del Almacenista</div>
                    @if ($storekeeper_signature)
                        <div class="signature-image">
                            <img src="file://{{ $storekeeper_signature }}" alt="Firma almacenista" style="max-height: 100px;">
                        </div>
                    @endif
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-content">
                    <div class="signature-title">Firma del Técnico</div>
                    <div class="signature-name">{{ $technician_name }}</div>
                    @if ($technician_signature)
                        <div class="signature-image">
                            <img src="file://{{ $technician_signature }}" alt="Firma técnico" style="max-height: 100px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Documento generado el {{ date('d/m/Y H:i') }} | Sistema de Inventarios
    </div>
</body>
</html>
