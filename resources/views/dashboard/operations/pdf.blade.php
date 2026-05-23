<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Operaciones</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .header .info {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 10px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            display: inline-block;
        }
        
        .color-vencido { background-color: #C7170A; }
        .color-hoy { background-color: #761D86; }
        .color-proximo { background-color: #F57C00; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table thead {
            background-color: #343a40;
            color: white;
        }
        
        table th {
            padding: 8px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        
        table td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            font-size: 8px;
            vertical-align: top;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        .folio {
            font-weight: bold;
            color: #0d6efd;
        }
        
        .cliente {
            font-weight: bold;
        }
        
        .fecha-cell {
            color: white !important;
            font-weight: bold;
            text-align: center;
            padding: 6px 4px;
        }
        
        .bg-vencido { background-color: #C7170A; }
        .bg-hoy { background-color: #761D86; }
        .bg-proximo { background-color: #F57C00; }
        
        .text-muted {
            color: #6c757d;
            font-size: 7px;
        }
        
        ul {
            margin: 0;
            padding-left: 12px;
            list-style-type: disc;
        }
        
        ul li {
            margin: 2px 0;
        }
        
        .footer {
            position: fixed;
            bottom: 10px;
            right: 10px;
            font-size: 7px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTROL DE OPERACIONES - REPORTES PENDIENTES</h1>
        <div class="info">
            <strong>Fecha de generación:</strong> {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY [a las] HH:mm') }}
            <br>
            <strong>Total de reportes:</strong> {{ $orders->count() }}
        </div>
    </div>
    
    <div class="legend">
        <div class="legend-item">
            <span class="legend-color color-vencido"></span>
            <span>Vencido</span>
        </div>
        <div class="legend-item">
            <span class="legend-color color-hoy"></span>
            <span>Hoy</span>
        </div>
        <div class="legend-item">
            <span class="legend-color color-proximo"></span>
            <span>Próximo</span>
        </div>
    </div>
    
    @if($orders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 6%;"># (Folio)</th>
                    <th style="width: 18%;">Cliente</th>
                    <th style="width: 4%;">ID</th>
                    <th style="width: 10%;">Hora</th>
                    <th style="width: 8%;">Fecha</th>
                    <th style="width: 8%;">Tipo</th>
                    <th style="width: 20%;">Servicio(s)</th>
                    <th style="width: 18%;">Técnico(s)</th>
                    <th style="width: 8%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $index => $order)
                    @php
                        $programmedDate = $order->programmed_date ? \Carbon\Carbon::parse($order->programmed_date) : null;
                        $today = \Carbon\Carbon::today();
                        
                        // Determinar color del semáforo
                        if ($programmedDate && $programmedDate->isToday()) {
                            $bgColor = 'bg-hoy';
                        } elseif ($programmedDate && $programmedDate->isFuture()) {
                            $bgColor = 'bg-proximo';
                        } elseif ($programmedDate) {
                            $bgColor = 'bg-vencido';
                        } else {
                            $bgColor = '';
                        }
                        
                        $orderTechnicians = $order->getNameTechnicians();
                    @endphp
                    <tr>
                        <td>
                            {{ $index + 1 }}<br>
                            <span class="folio">({{ $order->folio }})</span>
                        </td>
                        <td>
                            <span class="cliente">{{ $order->customer->name ?? 'Sin cliente' }}</span>
                            @if($order->customer && $order->customer->type == 2)
                                <br><span class="text-muted">Sede de: {{ $order->customer->matrix->name ?? '-' }}</span>
                            @endif
                        </td>
                        <td>{{ $order->id }}</td>
                        <td>
                            @if($order->start_time)
                                {{ \Carbon\Carbon::parse($order->start_time)->format('H:i') }}
                                @if($order->end_time)
                                    - {{ \Carbon\Carbon::parse($order->end_time)->format('H:i') }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="fecha-cell {{ $bgColor }}">
                            @if($order->programmed_date)
                                {{ \Carbon\Carbon::parse($order->programmed_date)->format('d/m/Y') }}
                            @else
                                Sin fecha
                            @endif
                        </td>
                        <td>{{ $order->contract_id > 0 ? 'MIP' : 'Seguimiento' }}</td>
                        <td>
                            @if($order->services->count() > 0)
                                @foreach($order->services as $service)
                                    • {{ $service->name }}<br>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($orderTechnicians->count() > 0)
                                <ul>
                                    @foreach($orderTechnicians as $tech)
                                        <li>{{ $tech->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">Sin técnico asignado</span>
                            @endif
                        </td>
                        <td>{{ $order->status->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No se encontraron reportes pendientes con los filtros aplicados.</p>
        </div>
    @endif
    
    <div class="footer">
        Generado por SISCO ZONDA ERP | Página {PAGENO} de {nbpg}
    </div>
</body>
</html>
