<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Voucher de Movimiento</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1d252c;
            line-height: 1.35;
            margin: 0;
        }

        .topbar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2f6b3f;
        }

        .logo-cell {
            width: 34%;
            vertical-align: top;
        }

        .logo {
            width: 150px;
            max-height: 58px;
        }

        .title-cell {
            width: 66%;
            text-align: right;
            vertical-align: top;
        }

        .document-title {
            font-size: 19px;
            font-weight: bold;
            color: #193524;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .document-subtitle {
            font-size: 11px;
            color: #60706a;
            margin-bottom: 8px;
        }

        .folio-box {
            display: inline-block;
            border: 1px solid #2f6b3f;
            background: #eef7f0;
            padding: 6px 10px;
            text-align: left;
        }

        .folio-label {
            font-size: 9px;
            color: #60706a;
            text-transform: uppercase;
        }

        .folio-value {
            font-size: 15px;
            font-weight: bold;
            color: #193524;
        }

        .section-title {
            margin: 12px 0 6px;
            padding: 5px 8px;
            background: #193524;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table,
        .products-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            width: 50%;
            border: 1px solid #d8dfdc;
            padding: 7px 8px;
            vertical-align: top;
        }

        .label {
            display: block;
            margin-bottom: 2px;
            color: #66736f;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .value {
            font-size: 11px;
            color: #1d252c;
            font-weight: bold;
        }

        .observations {
            border: 1px solid #d8dfdc;
            border-left: 4px solid #2f6b3f;
            padding: 8px 10px;
            min-height: 38px;
            background: #fbfcfb;
        }

        .products-table th {
            background: #e7eee9;
            color: #193524;
            border: 1px solid #cbd6d0;
            padding: 7px 8px;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
        }

        .products-table td {
            border: 1px solid #d8dfdc;
            padding: 7px 8px;
            vertical-align: top;
        }

        .products-table tbody tr:nth-child(even) td {
            background: #f8faf9;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .muted {
            color: #66736f;
            font-size: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .summary-table td {
            padding: 4px 0;
            font-size: 10px;
            color: #66736f;
        }

        .signature-table {
            margin-top: 18px;
            table-layout: fixed;
        }

        .signature-table td {
            width: 50%;
            padding: 0 12px;
            vertical-align: bottom;
            text-align: center;
        }

        .signature-card {
            min-height: 122px;
            border: 1px solid #d8dfdc;
            padding: 10px;
        }

        .signature-image {
            height: 62px;
            margin-bottom: 8px;
            text-align: center;
        }

        .signature-image img {
            max-height: 62px;
            max-width: 210px;
        }

        .signature-line {
            border-top: 1px solid #1d252c;
            padding-top: 6px;
            margin-top: 12px;
            font-weight: bold;
        }

        .signature-name {
            margin-top: 3px;
            color: #66736f;
            font-size: 10px;
        }

        .footer {
            position: fixed;
            left: 28px;
            right: 28px;
            bottom: 12px;
            border-top: 1px solid #d8dfdc;
            padding-top: 6px;
            color: #66736f;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <table class="topbar">
        <tr>
            <td class="logo-cell">
                <img class="logo" src="file://{{ public_path('images/logo.png') }}" alt="Logo">
            </td>
            <td class="title-cell">
                <div class="document-title">Voucher de Movimiento</div>
                <div class="document-subtitle">Control de almacén e inventario</div>
                <div class="folio-box">
                    <div class="folio-label">Folio</div>
                    <div class="folio-value">#{{ str_pad((string) $folio, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Datos del Movimiento</div>
    <table class="info-table">
        <tr>
            <td>
                <span class="label">Fecha y hora</span>
                <span class="value">{{ $date }} {{ $time ? ' - ' . $time : '' }}</span>
            </td>
            <td>
                <span class="label">Tipo de movimiento</span>
                <span class="value">{{ $movement_type }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Almacén origen</span>
                <span class="value">{{ $origin }}</span>
            </td>
            <td>
                <span class="label">Almacén destino</span>
                <span class="value">{{ $destination }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Registrado por</span>
                <span class="value">{{ $created_by }}</span>
            </td>
            <td>
                <span class="label">Técnico / receptor</span>
                <span class="value">{{ $technician_name }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Observaciones</div>
    <div class="observations">
        {{ $observations ?: 'Sin observaciones' }}
    </div>

    <div class="section-title">Productos</div>
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">#</th>
                <th style="width: 54%;">Producto</th>
                <th style="width: 24%;">Lote / Serie</th>
                <th style="width: 16%;" class="text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product['product'] }}</td>
                    <td>{{ $product['lot'] }}</td>
                    <td class="text-right">{{ number_format((float) $product['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center muted">Sin productos registrados para este movimiento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td>Total de partidas: <strong>{{ count($products) }}</strong></td>
            <td class="text-right">Documento generado: {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="section-title">Firmas</div>
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-card">
                    <div class="signature-image">
                        @if ($storekeeper_signature)
                            <img src="file://{{ $storekeeper_signature }}" alt="Firma almacenista">
                        @endif
                    </div>
                    <div class="signature-line">Almacenista</div>
                    <div class="signature-name">{{ $created_by }}</div>
                </div>
            </td>
            <td>
                <div class="signature-card">
                    <div class="signature-image">
                        @if ($technician_signature)
                            <img src="file://{{ $technician_signature }}" alt="Firma técnico">
                        @endif
                    </div>
                    <div class="signature-line">Técnico / Receptor</div>
                    <div class="signature-name">{{ $technician_name }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Sistema de Inventarios | Voucher de movimiento #{{ $folio }}
    </div>
</body>
</html>
