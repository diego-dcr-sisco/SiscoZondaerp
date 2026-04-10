<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 28px 30px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #0ea5e9;
            padding-bottom: 10px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .header-left {
            float: left;
            width: 64%;
        }

        .header-right {
            float: right;
            width: 34%;
            text-align: right;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 6px 0;
        }

        .meta {
            font-size: 10px;
            margin: 2px 0;
        }

        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }

        .panel {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
            min-height: 110px;
        }

        .panel h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 12px;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
        }

        .info-table td:first-child {
            padding-right: 8px;
        }

        .info-table td:last-child {
            padding-left: 8px;
        }

        .label {
            font-weight: bold;
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .services-table th {
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            padding: 6px;
            font-size: 10px;
            text-align: left;
        }

        .services-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            vertical-align: top;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 42%;
            margin-left: auto;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 14px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            font-size: 11px;
            padding: 2px 0;
        }

        .totals-table td:last-child {
            text-align: right;
        }

        .totals-total {
            border-top: 1px solid #cbd5e1;
            margin-top: 6px;
            padding-top: 6px;
            font-weight: bold;
        }

        .notes-box {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
        }

        .notes-title {
            margin: 0 0 6px 0;
            font-weight: bold;
            font-size: 11px;
        }

        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header clearfix">
        <div class="header-left">
            <div class="title">{{ $title }}</div>
            <div class="meta"><span class="label">Folio:</span> {{ $quote_no }}</div>
            <div class="meta"><span class="label">Moneda:</span> {{ $currency }}</div>
        </div>
        <div class="header-right">
            <div class="meta"><span class="label">Fecha emision:</span> {{ $issued_date }}</div>
            <div class="meta"><span class="label">Vigencia:</span> {{ $valid_until }}</div>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="panel">
                    <h4>Empresa emisora</h4>
                    <div><span class="label">Nombre:</span> {{ $company['name'] }}</div>
                    <div><span class="label">RFC:</span> {{ $company['rfc'] }}</div>
                    <div><span class="label">Telefono:</span> {{ $company['phone'] }}</div>
                    <div><span class="label">Email:</span> {{ $company['email'] }}</div>
                    <div><span class="label">Direccion:</span> {{ $company['address'] }}</div>
                </div>
            </td>
            <td>
                <div class="panel">
                    <h4>Cliente</h4>
                    <div><span class="label">Nombre:</span> {{ $customer['name'] }}</div>
                    <div><span class="label">Razon social:</span> {{ $customer['company'] }}</div>
                    <div><span class="label">Atencion:</span> {{ $customer['attn'] }}</div>
                    <div><span class="label">RFC:</span> {{ $customer['rfc'] }}</div>
                    <div><span class="label">Telefono:</span> {{ $customer['phone'] }}</div>
                    <div><span class="label">Email:</span> {{ $customer['email'] }}</div>
                    <div><span class="label">Direccion:</span> {{ $customer['address'] }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="services-table">
        <thead>
            <tr>
                <th style="width: 34%;">Concepto</th>
                <th style="width: 28%;">Descripcion</th>
                <th style="width: 8%;" class="text-right">Cant.</th>
                <th style="width: 10%;">Unidad</th>
                <th style="width: 10%;" class="text-right">P. Unitario</th>
                <th style="width: 10%;" class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ((array) $services as $service)
                <tr>
                    <td>{{ $service['name'] }}</td>
                    <td>{!! nl2br(e((string) $service['description'])) !!}</td>
                    <td class="text-right">{{ number_format((float) $service['qty'], 2) }}</td>
                    <td>{{ $service['unit'] }}</td>
                    <td class="text-right">{{ number_format((float) $service['unit_price'], 2) }}</td>
                    <td class="text-right">{{ number_format((float) $service['line_total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>{{ number_format((float) $subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>IVA ({{ number_format((float) $tax_percent, 2) }}%)</td>
                <td>{{ number_format((float) $tax_amount, 2) }}</td>
            </tr>
            <tr class="totals-total">
                <td><strong>Total</strong></td>
                <td><strong>{{ number_format((float) $total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <div class="notes-box">
        <p class="notes-title">Terminos de pago</p>
        <div>{!! nl2br(e((string) $payment_terms)) !!}</div>

        <p class="notes-title" style="margin-top:8px;">Tiempo de entrega</p>
        <div>{!! nl2br(e((string) $delivery_time)) !!}</div>

        @if (!empty($conditions))
            <p class="notes-title" style="margin-top:8px;">Condiciones</p>
            <div>{!! $conditions !!}</div>
        @endif

        @if (!empty($notes))
            <p class="notes-title" style="margin-top:8px;">Notas</p>
            <div>{!! $notes !!}</div>
        @endif
    </div>

    <div class="footer">
        Esta cotizacion fue generada manualmente desde el sistema y no almacena informacion en base de datos.
    </div>
</body>

</html>
