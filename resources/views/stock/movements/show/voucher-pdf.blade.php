<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Voucher de Movimiento</title>
    <style>
        @page {
            margin: 20px 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1d252c;
            line-height: 1.3;
            margin: 0;
        }

        /* ── HEADER ── */
        .topbar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #98A253;
        }

        .logo-cell {
            width: 34%;
            vertical-align: middle;
        }

        .logo {
            width: 240px;
            max-height: 50px;
        }

        .title-cell {
            width: 66%;
            text-align: right;
            vertical-align: middle;
        }

        .document-title {
            font-size: 13px;
            font-weight: bold;
            color: #193524;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .document-subtitle {
            font-size: 9px;
            color: #66736f;
            margin-bottom: 5px;
        }

        .folio-box {
            display: inline-block;
            /*border: 0.5px solid #2f6b3f;
            padding: 2px;*/
            margin-bottom: 5px;
        }

        .folio-label {
            font-size: 9px;
            color: #66736f;
            text-transform: uppercase;
            margin-right: 5px;
        }

        .folio-value {
            font-size: 12px;
            font-weight: bold;
            color: #193524;
        }

        /* ── SECTION TITLES ── */
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: black;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 8px 0 4px;
            border-bottom: 0.5px solid #2B85FF;
            margin-bottom: 8px;
        }

        /* ── INFO GRID ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            width: 50%;
            padding: 4px 0px;
            vertical-align: top;
        }

        .label {
            display: block;
            margin-bottom: 1px;
            color: #66736f;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: bold;
        }

        .value {
            font-size: 10px;
            color: #1d252c;
            font-weight: bold;
        }

        /* ── OBSERVATIONS ── */
        .observations {
            padding: 4px 0px;
            min-height: 30px;
            background: #fbfcfb;
            font-size: 10.5px;
            color: #66736f;
        }

        /* ── PRODUCTS TABLE ── */
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            background: #f0f3f1;
            color: #193524;
            border: 0.5px solid #d8dfdc;
            padding: 5px 7px;
            font-size: 8.5px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .products-table td {
            border: 0.5px solid #d8dfdc;
            padding: 4px;
            vertical-align: top;
            font-size: 10px;
        }

        .products-table tbody tr:nth-child(even) td {
            background: #f8faf9;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        .muted {
            color: #66736f;
            font-size: 10px;
        }

        /* ── SUMMARY ── */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .summary-table td {
            padding: 3px 0;
            font-size: 9.5px;
            color: #66736f;
        }

        /* ── SIGNATURES ── */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
        }

        .signature-table td {
            width: 50%;
            padding: 0 8px;
            vertical-align: bottom;
            text-align: center;
        }

        .signature-table td:first-child {
            padding-left: 0;
        }

        .signature-table td:last-child {
            padding-right: 0;
        }

        .signature-card {
            border: 0.5px solid #d8dfdc;
            padding: 8px 10px;
        }

        .signature-image {
            height: 52px;
            margin-bottom: 6px;
            text-align: center;
            border-bottom: 0.5px solid #d8dfdc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-image img {
            max-height: 48px;
            max-width: 180px;
        }

        .signature-role {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1d252c;
            margin-top: 6px;
        }

        .signature-name {
            margin-top: 2px;
            color: #66736f;
            font-size: 10px;
        }

        /* ── FOOTER ── */
        .footer {
            position: fixed;
            left: 24px;
            right: 24px;
            bottom: 10px;
            border-top: 0.5px solid #d8dfdc;
            padding-top: 5px;
            color: #66736f;
            font-size: 9px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    {{-- ── HEADER ── --}}
    <table class="topbar">
        <tr>
            <td class="logo-cell">
                <img class="logo" src="file://{{ public_path('images/siscoplagas/landscape_logo.png') }}" alt="Logo">
            </td>
            <td class="title-cell">
                <div class="document-title">Voucher de Movimiento</div>
                <div class="document-subtitle">Control de almacén e inventario</div>
                <div class="folio-box">
                    <span class="folio-label">Folio</span>
                    <span class="folio-value">#{{ str_pad((string) $folio, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── DATOS DEL MOVIMIENTO ── --}}
    <div class="section-title">Datos del Movimiento</div>
    <table class="info-table">
        <tr>
            <td>
                <span class="label">Fecha y hora</span>
                <span class="value">{{ $date }}{{ $time ? ' — ' . $time : '' }}</span>
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

    {{-- ── OBSERVACIONES ── --}}
    <div class="section-title">Observaciones</div>
    <div class="observations">
        {{ $observations ?: 'Sin observaciones' }}
    </div>

    {{-- ── PRODUCTOS ── --}}
    @php
        $exitProducts = collect($products)->where('direction', 'Salida')->values();
        $entryProducts = collect($products)->where('direction', 'Entrada')->values();
    @endphp

    <div class="section-title">Salidas</div>
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 5%;"  class="text-center">#</th>
                <th style="width: 40%;">Producto</th>
                <th style="width: 20%;">Lote / Serie</th>
                <th style="width: 23%;">Almacén origen</th>
                <th style="width: 12%;" class="text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($exitProducts as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product['product'] }}</td>
                    <td>{{ $product['lot'] }}</td>
                    <td>{{ $product['warehouse'] }}</td>
                    <td class="text-right">
                        {{ number_format((float) $product['amount'], 2) }} {{ $product['metric'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center muted">Sin salidas registradas para este movimiento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Entradas</div>
    <table class="products-table">
        <thead>
            <tr>
                <th  class="text-center">#</th>
                <th >Producto</th>
                <th >Lote / Serie</th>
                <th >Almacén destino</th>
                <th  class="text-right">Cantidad</th>
                <th >Movimiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entryProducts as $index => $product)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $product['product'] }}</td>
                    <td>{{ $product['lot'] }}</td>
                    <td>{{ $product['warehouse'] }}</td>
                    <td class="text-right">
                        <b>{{ number_format((float) $product['amount'], 2) }}</b> {{ $product['metric'] }}
                    </td>
                    <td>{{ $product['movement'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center muted">Sin entradas registradas para este movimiento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                Salidas: <strong>{{ $exitProducts->count() }}</strong>
                &nbsp;|&nbsp;
                Entradas: <strong>{{ $entryProducts->count() }}</strong>
                &nbsp;|&nbsp;
                Total de partidas: <strong>{{ count($products) }}</strong>
            </td>
            <td class="text-right">Documento generado: {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    {{-- ── FIRMAS ── --}}
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
                    <div class="signature-role">Almacenista</div>
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
                    <div class="signature-role">Técnico / Receptor</div>
                    <div class="signature-name">{{ $technician_name }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <span>Sistema de Inventarios</span>
        <span>Voucher de movimiento #{{ $folio }}</span>
    </div>

</body>
</html>
