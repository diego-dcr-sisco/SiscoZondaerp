<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispositivos Plano - {{ $customer ?? 'Cliente' }}</title>
    <style>
        @page {
            size: landscape;
            margin: 0.5cm;
        }
        
        body {
            font-family: '{{ $font_family ?? 'Arial' }}', sans-serif;
            color: {{ $font_color ?? '#000000' }};
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-size: 10px;
        }
        
        .container {
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
        }
        
        .header h1 {
            margin: 0 0 3px 0;
            font-size: 16px;
            color: #2c3e50;
            line-height: 1.1;
        }
        
        .header-info {
            width: 100%;
            margin-top: 5px;
            font-size: 9px;
        }
        
        .header-info td {
            text-align: center;
            padding: 0 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .legend-cell {
            width: 20%;
            vertical-align: top;
            border-right: 1px solid #ddd;
            padding-right: 8px;
        }
        
        .image-cell {
            width: 80%;
            vertical-align: top;
            text-align: center;
            padding-left: 8px;
        }
        
        .legend-container {
            padding: 8px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            max-height: 16cm;
            overflow: hidden;
        }
        
        .legend-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 11px;
            color: #2c3e50;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }
        
        .legend-item {
            margin-bottom: 6px;
            padding: 4px;
            background: white;
            border-radius: 3px;
            border: 1px solid #e9ecef;
            page-break-inside: avoid;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
            border: 1px solid #fff;
            box-shadow: 0 0 1px rgba(0,0,0,0.3);
            float: left;
            margin-top: 1px;
        }
        
        .legend-info {
            margin-left: 18px;
            font-size: 8px;
            line-height: 1.2;
        }
        
        .legend-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
            display: block;
        }
        
        .legend-details {
            color: #555;
            font-size: 7px;
            display: block;
        }
        
        .image-container {
            text-align: center;
        }
        
        .image-container img {
            max-width: 100%;
            max-height: 16cm;
            height: auto;
            border: 1px solid #ccc;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Evitar saltos de página dentro de los elementos */
        .legend-item, .image-container {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado compacto -->
        <div class="header">
            <h1>PLANO - {{ strtoupper($customer ?? 'CLIENTE') }}</h1>
            <table class="header-info">
                <tr>
                    <td>
                        <span class="info-label">Nombre del plano:</span> {{ $filename ?? 'N/A' }}
                    </td>
                    <td>
                        <span class="info-label">Versión:</span> {{ $date_version ?? date('d/m/Y') }}
                    </td>
                    <td>
                        <span class="info-label">Total:</span> {{ $device_count ?? 0 }} disp.
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Contenido principal -->
        <table class="content-table">
            <tr>
                <!-- Leyenda (20%) -->
                <td class="legend-cell">
                    <div class="legend-container">
                        <div class="legend-title">SIMBOLOGIA</div>
                        
                        @if(isset($legend) && count($legend) > 0)
                            @foreach($legend as $item)
                                @php
                                    $label = $item['label'] ?? 'Dispositivo';
                                    $labelParts = explode(' - ', $label);
                                    $deviceType = $labelParts[0] ?? $label;
                                    
                                    $totalPoints = '';
                                    $ranges = '';
                                    foreach ($labelParts as $part) {
                                        if (str_contains($part, 'Puntos totales:')) {
                                            $totalPoints = trim(str_replace('Puntos totales:', '', $part));
                                        }
                                        if (str_contains($part, 'Rango(s):')) {
                                            $ranges = trim(str_replace('Rango(s):', '', $part));
                                        }
                                    }
                                    
                                    
                                @endphp
                                
                                <div class="legend-item clearfix">
                                    <div class="legend-color" style="background-color: {{ $item['color'] ?? '#cccccc' }};"></div>
                                    <div class="legend-info">
                                        <span class="legend-label">{{ $deviceType }}</span>
                                        <span class="legend-details">
                                            @if($totalPoints)
                                                <strong>{{ $totalPoints }} pts</strong>
                                            @endif
                                            @if($ranges)
                                                <br>{{ $ranges }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="legend-item clearfix">
                                <div class="legend-color" style="background-color: #cccccc;"></div>
                                <div class="legend-info">
                                    <span class="legend-label">Sin dispositivos</span>
                                    <span class="legend-details">
                                        No hay dispositivos registrados
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </td>
                
                <!-- Imagen (80%) -->
                <td class="image-cell">
                    <div class="image-container">
                        @if(isset($imageBase64))
                            <img src="data:image/png;base64,{{ $imageBase64 }}" alt="Plano de Dispositivos - {{ $customer ?? 'Cliente' }}">
                        @else
                            <div style="text-align: center; color: red; padding: 20px; border: 1px dashed #ccc; font-size: 10px;">
                                <strong>ERROR: IMAGEN NO DISPONIBLE</strong>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Pie de página -->
        <div class="footer">
            Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | {{ $customer ?? 'Cliente' }}
        </div>
    </div>
</body>
</html>