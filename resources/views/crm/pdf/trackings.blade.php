<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimientos</title>
    <style>
        :root {
            --deep-space-blue: #012640;
            --deep-navy: #02265A;
            --true-cobalt: #0A2986;
            --indigo-velvet: #512A87;
            --velvet-purple: #793775;
            --dusty-mauve: #B74453;
            --fiery-terracotta: #DD513A;
        }

        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 18px;
            color: #000000;
            font-size: 11px;
        }

        .header {
            border-bottom: 2px solid var(--true-cobalt);
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .title {
            margin: 0;
            color: #000000;
            font-size: 18px;
            font-weight: 700;
        }

        .subtitle {
            margin: 4px 0 0;
            color: #000000;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background: var(--true-cobalt);
            color: #000000;
            border: 1px solid var(--deep-navy);
            padding: 6px 5px;
            font-size: 10px;
            text-align: center;
        }

        tbody td {
            border: 1px solid #c7d1de;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        tbody tr:nth-child(even) {
            background: #f3f6fb;
        }

        tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .col-client { width: 14%; }
        .col-phone { width: 12%; }
        .col-email { width: 15%; }
        .col-date { width: 8%; text-align: center; }
        .col-service { width: 11%; }
        .col-cost { width: 8%; text-align: right; }
        .col-desc { width: 22%; }
        .col-reschedule { width: 10%; text-align: center; }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #000000;
            text-align: right;
            border-top: 1px solid var(--fiery-terracotta);
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Exportacion de Seguimientos</h1>
        <p class="subtitle">Fecha: {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-client">Nombre del cliente</th>
                <th class="col-phone">Telefono</th>
                <th class="col-email">Correo</th>
                <th class="col-date">Fecha</th>
                <th class="col-service">Servicio</th>
                <th class="col-cost">Costo</th>
                <th class="col-desc">Descripcion</th>
                <th class="col-reschedule">¿Se reprogramo?</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trackings as $tracking)
                <tr>
                    <td>{{ $tracking->trackable->name ?? '' }}</td>
                    <td>{{ $tracking->trackable->phone ?? $tracking->trackable->tel ?? '' }}</td>
                    <td>{{ $tracking->trackable->email ?? '' }}</td>
                    <td class="col-date">{{ $tracking->next_date ? \Carbon\Carbon::parse($tracking->next_date)->format('d/m/Y') : '' }}</td>
                    <td>{{ $tracking->service->name ?? '' }}</td>
                    <td class="col-cost">{{ $tracking->cost !== null ? number_format((float) $tracking->cost, 2) : '' }}</td>
                    <td>{{ $tracking->description ?? '' }}</td>
                    <td class="col-reschedule"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 12px;">Sin seguimientos para exportar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total de registros: {{ $trackings->count() }}
    </div>
</body>
</html>
