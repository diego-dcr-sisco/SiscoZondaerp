<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimientos</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 18px;
            color: #1f2937;
            font-size: 11px;
        }

        .header {
            border-bottom: 2px solid #0a2986;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .title {
            margin: 0;
            color: #0a2986;
            font-size: 18px;
            font-weight: 700;
        }

        .subtitle {
            margin: 4px 0 0;
            color: #475569;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background: #0a2986;
            color: #fff;
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            font-size: 10px;
            text-align: center;
        }

        tbody td {
            border: 1px solid #d1d5db;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .col-client { width: 17%; }
        .col-date { width: 9%; text-align: center; }
        .col-service { width: 12%; }
        .col-cost { width: 8%; text-align: right; }
        .col-desc { width: 40%; }
        .col-reschedule { width: 14%; text-align: center; }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #64748b;
            text-align: right;
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
                    <td class="col-date">{{ $tracking->next_date ? \Carbon\Carbon::parse($tracking->next_date)->format('d/m/Y') : '' }}</td>
                    <td>{{ $tracking->service->name ?? '' }}</td>
                    <td class="col-cost">{{ $tracking->cost !== null ? number_format((float) $tracking->cost, 2) : '' }}</td>
                    <td>{{ $tracking->description ?? '' }}</td>
                    <td class="col-reschedule"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 12px;">Sin seguimientos para exportar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total de registros: {{ $trackings->count() }}
    </div>
</body>
</html>
