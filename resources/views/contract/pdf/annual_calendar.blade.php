<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Calendario Anual - {{ $contract->customer->code }}</title>
    <style>
        /* ============================================
           RESET Y CONFIGURACIÓN BASE
           ============================================ */
        
        /* Reset de estilos por defecto del navegador */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box; /* Incluye padding y border en el ancho/alto total */
        }

        /* Estilos del documento principal */
        body {
            font-family: Arial, sans-serif; /* Fuente compatible con DomPDF */
            font-size: 6px; /* Tamaño base pequeño para que quepan 12 meses */
            color: #000; /* Color de texto gris oscuro */
            background: white;
            padding: 5mm; /* Espaciado interno del documento */
        }

        /* Configuración de la página para PDF */
        @page {
            size: A4 portrait; /* Página horizontal para aprovechar el ancho */
            margin: 8mm; /* Márgenes de la página */
        }

        /* ============================================
           SECCIÓN DE ENCABEZADO
           ============================================ */
        
        .header-container {
            margin-bottom: 2mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #333;
            height: 15mm; /* Altura fija para el encabezado */
            overflow: hidden; /* Clearfix para los floats */
        }

        .header-text {
            float: left;
            width: 70%;
            text-align: left;
        }

        .header-logo {
            float: right;
            width: 28%;
            text-align: right;
        }

        .header-logo img {
            width: 150px;
            max-width: 100%;
            margin: 0;
        }

        .header-text h1 {
            font-size: 20px; /* Título principal grande */
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* Información del contrato (cliente, código, año, período) */
        .header-info {
            font-size: 10px;
            margin-top: 2px;
        }

        .header-info span {
            margin: 0 5px; /* Separación entre elementos */
        }

        /* ============================================
           LEYENDA DE SERVICIOS
           ============================================ */
        
        .legend {
            margin-top: 3mm;
            margin-bottom: 2mm;
            padding: 2mm;
            background-color: #f5f5f5; /* Fondo gris claro */
            border: 1px solid #ccc;
            font-size: 10px;
        }

        .legend-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 1mm;
            text-align: center;
        }

        .legend-item {
            display: block;
            margin-bottom: 1mm;
            padding: 0.5mm 0;
        }

        /* Cuadrito de color que representa cada servicio */
        .color-box {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #999;
            vertical-align: middle; /* Alineación con el texto */
            margin-right: 3px;
        }

        /* ============================================
           CONTENEDOR DE CALENDARIOS MENSUALES
           ============================================ */
        
        /* Contenedor principal - usa float para compatibilidad con DomPDF */
        .months-container {
            width: 100%;
            clear: both; /* Limpia cualquier float anterior */
        }

        /* Cada bloque de mes - float left para posicionamiento horizontal */
        .month-block {
            float: left; /* Posiciona los meses lado a lado */
            width: 32%; /* Ancho de cada mes (4 columnas: 24% x 4 + 1% x 3 gaps = 99%) */
            margin-right: 2%; /* Espacio entre calendarios */
            margin-bottom: 1.5mm; /* Espacio vertical entre filas */
            page-break-inside: avoid; /* Evita que un mes se divida entre páginas */
        }

        /* Elimina margen derecho en el último calendario de cada fila */
        .month-block.last-in-row {
            margin-right: 0;
        }

        /* Clase auxiliar para limpiar floats */
        .clearfix {
            clear: both;
        }

        /* Wrapper interno del mes */
        .month-wrapper {
            width: 100%;
        }

        /* ============================================
           ESTILOS DEL CALENDARIO
           ============================================ */
        
        /* Título del mes (ej: "ENERO 2026") */
        .month-title {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: white; /* Texto blanco */
            background: #002641; /* Fondo gris oscuro */
            padding: 0.5mm;
            margin-bottom: 0.5px;
        }

        /* Tabla del calendario */
        .calendar-table {
            width: 100%;
            border-collapse: collapse; /* Sin espacios entre celdas */
            border: 1px solid #002641;
            background: white;
            font-size: 8px;
        }

        /* Encabezado de la tabla (días de la semana) */
        .calendar-table thead {
            background-color: #092885; /* Fondo gris oscuro */
        }

        /* Celdas del encabezado (L, M, M, J, V, S, D) */
        .calendar-table th {
            color: white;
            padding: 0.5px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 0.5px solid #999;
            height: 2.5mm;
        }

        /* Celdas de los días del mes */
        .calendar-table td {
            border: 0.5px solid #ccc;
            height: 5mm; /* Altura fija para uniformidad */
            width: 14.28%; /* 100% / 7 días = 14.28% cada celda */
            vertical-align: middle;
            padding: 0;
            text-align: center;
        }

        /* Celdas vacías (días que no pertenecen al mes) */
        .calendar-table td.empty {
            background-color: #f0f0f0; /* Gris muy claro */
        }

        /* Número del día dentro de la celda */
        .day-number {
            font-weight: bold;
            font-size: 10px;
            line-height: 3mm; /* Centrado vertical */
        }

        /* ============================================
           PIE DE PÁGINA
           ============================================ */
        
        footer {
            margin-top: 2mm;
            padding-top: 1mm;
            border-top: 1px solid #ddd; /* Línea superior */
            text-align: center;
            font-size: 8px;
            color: #888; /* Gris claro */
        }

        /* Resumen oculto (no se muestra en el PDF) */
        .summary {
            display: none;
        }

    </style>
</head>

<body>
    <!-- ============================================
         ENCABEZADO DEL DOCUMENTO
         Muestra información del contrato y logo
         ============================================ -->
    <div class="header-container">
        <div class="header-text">
            <h1>Calendario Anual de Servicios</h1>
            <div class="header-info">
                <!-- Variables pasadas desde el controlador: $contract, $year -->
                <span><strong>Cliente:</strong> {{ $contract->customer->name }}</span> |
                <span><strong>Código:</strong> {{ $contract->customer->code }}</span> |
                <span><strong>Año:</strong> {{ $year }}</span> |
                <span><strong>Período:</strong> {{ \Carbon\Carbon::parse($contract->startdate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($contract->enddate)->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="header-logo">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/zonda/landscape_logo.png'))) }}" alt="Logo">
        </div>
    </div>

    <!-- ============================================
         CALENDARIOS MENSUALES
         Loop de 12 meses, cada uno como tabla
         ============================================ -->
    <div class="months-container">
        <!-- Itera del 1 al 12 para generar los 12 meses -->
        @for($month = 1; $month <= 12; $month++)
            @if($month == 4 || $month == 7 || $month == 10)
                <div class="clearfix"></div>
            @endif
            <div class="month-block {{ $month % 3 == 0 ? 'last-in-row' : '' }}">
                <!-- Título del mes (viene de $monthNames del controlador) -->
                <div class="month-title">{{ $monthNames[$month] }} {{ $year }}</div>
                
                <!-- Tabla del calendario mensual -->
                <table class="calendar-table">
                    <thead>
                        <tr>
                            <!-- Días de la semana: Lunes a Domingo -->
                            <th>L</th>
                            <th>M</th>
                            <th>M</th>
                            <th>J</th>
                            <th>V</th>
                            <th>S</th>
                            <th>D</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Obtener el primer día del mes
                            $firstDay = \Carbon\Carbon::create($year, $month, 1);
                            
                            // Total de días en el mes (28-31)
                            $daysInMonth = $firstDay->daysInMonth;
                            
                            // Día de la semana del primer día (0=domingo, 1=lunes, ..., 6=sábado)
                            // Convertir a formato lunes=0: si es domingo (0), convertir a 6, sino restar 1
                            $startDayOfWeek = $firstDay->dayOfWeek === 0 ? 6 : $firstDay->dayOfWeek - 1;
                            
                            // Contadores para el loop
                            $day = 1; // Día actual del mes
                            $cell = 0; // Celda actual en la tabla (0-41 máximo para 6 semanas)
                        @endphp

                        <!-- Loop para generar las semanas -->
                        @while($day <= $daysInMonth || $cell % 7 != 0)
                            <!-- Iniciar nueva fila cada 7 celdas -->
                            @if($cell % 7 == 0)
                                <tr>
                            @endif

                            <!-- Celdas vacías antes del primer día del mes -->
                            @if($cell < $startDayOfWeek && $day == 1)
                                <td class="empty"></td>
                            
                            <!-- Celdas con días del mes -->
                            @elseif($day <= $daysInMonth)
                                @php
                                    // Verificar si hay servicio programado este día
                                    $haService = isset($calendarData[$month][$day]);
                                    
                                    // Obtener el día de la semana actual (0=domingo, 1=lunes, ..., 6=sábado)
                                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                                    $dayOfWeek = $currentDate->dayOfWeek;
                                    
                                    // Colores por día de la semana (mismo array que en leyenda)
                                    $weekDayColors = [
                                        1 => '#FFC107',  // Lunes - Amarillo
                                        2 => '#2196F3',  // Martes - Azul
                                        3 => '#4CAF50',  // Miércoles - Verde
                                        4 => '#FF5722',  // Jueves - Naranja
                                        5 => '#9C27B0',  // Viernes - Morado
                                        6 => '#FF9800',  // Sábado - Naranja claro
                                        0 => '#F44336'   // Domingo - Rojo
                                    ];
                                    
                                    // Color según día de la semana si hay servicio
                                    $cellColor = $haService ? $weekDayColors[$dayOfWeek] : 'white';
                                @endphp
                                
                                <!-- Celda coloreada según día de la semana -->
                                <td style="background-color: {{ $cellColor }}; {{ $haService ? 'color: white;' : 'color: #000;' }}">
                                    <span class="day-number">{{ $day }}</span>
                                </td>
                                
                                @php $day++ @endphp
                            
                            <!-- Celdas vacías después del último día del mes -->
                            @else
                                <td class="empty"></td>
                            @endif

                            <!-- Cerrar fila cada 7 celdas -->
                            @if(($cell + 1) % 7 == 0)
                                </tr>
                            @endif

                            @php $cell++ @endphp
                        @endwhile
                    </tbody>
                </table>
            </div>
        @endfor
        
        <!-- Limpiar floats después de todos los meses -->
        <div class="clearfix"></div>
    </div>

    <!-- ============================================
         SIMBOLOGÍA
         Muestra cada color (día) con sus servicios asociados
         ============================================ -->
    <div class="legend">
        <div class="legend-title">SIMBOLOGÍA</div>
        @php
            // Colores por día de la semana (índice numérico según Carbon)
            $weekDayColorsArray = [
                1 => '#FFC107',  // Lunes - Amarillo
                2 => '#2196F3',  // Martes - Azul
                3 => '#4CAF50',  // Miércoles - Verde
                4 => '#FF5722',  // Jueves - Naranja
                5 => '#9C27B0',  // Viernes - Morado
                6 => '#FF9800',  // Sábado - Naranja claro
                0 => '#F44336'   // Domingo - Rojo
            ];
            
            // Analizar qué servicios se realizan en cada día de la semana
            $colorServices = []; // [dayOfWeek => [array de service_ids]]
            
            foreach ($calendarData as $month => $days) {
                foreach ($days as $day => $serviceIds) {
                    // Obtener día de la semana para esta fecha
                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                    $dayOfWeek = $currentDate->dayOfWeek;
                    
                    if (!isset($colorServices[$dayOfWeek])) {
                        $colorServices[$dayOfWeek] = [];
                    }
                    
                    foreach ($serviceIds as $serviceId) {
                        // Agregar servicio si no está ya en este día
                        if (!in_array($serviceId, $colorServices[$dayOfWeek])) {
                            $colorServices[$dayOfWeek][] = $serviceId;
                        }
                    }
                }
            }
            
            // Ordenar por día de la semana (lunes primero)
            ksort($colorServices);
        @endphp
        
        @foreach($colorServices as $dayOfWeek => $serviceIds)
            <div class="legend-item">
                <span class="color-box" style="background-color: {{ $weekDayColorsArray[$dayOfWeek] }}"></span>
                <strong>
                    @foreach($serviceIds as $index => $serviceId)
                        {{ $serviceColors[$serviceId]['name'] }}{{ $index < count($serviceIds) - 1 ? ', ' : '' }}
                    @endforeach
                </strong>
            </div>
        @endforeach
    </div>

    <!-- ============================================
         PIE DE PÁGINA
         Información de generación del documento
         ============================================ -->
    <footer>
        <p>Documento generado automáticamente • {{ now()->format('d/m/Y H:i') }}</p>
    </footer>
</body>
</html>
