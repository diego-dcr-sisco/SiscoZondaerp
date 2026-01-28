<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
    <style>
        /* Fuente compatible con DOM PDF (evita Google Fonts dinámicos) */
        body {
            font-family: "Helvetica", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Reset de márgenes para evitar saltos de página no deseados */
        * {
            font-size: 11px;
            margin: 10px;
            padding: 0;
            box-sizing: border-box;
        }

        .page-break {
            page-break-after: always;
            /* Salto de página después */
            /* O alternativamente: */
            /* page-break-before: always; */
            /* Salto de página antes */
        }


        /* row ajustado para DOM PDF */
        .row {
            width: 100%;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .row div {
            margin-bottom: 1px;
            margin: 0px;
        }

        .row span {
            margin: 0px;
        }

        .row::after {
            content: "";
            display: block;
            clear: both;
        }

        .title {
            width: 49%;
            float: left;
            text-align: left;
            margin: 0;

        }

        .logo {
            width: 40%;
            float: right;
            text-align: center;
            margin: 0;

        }

        .logo div {
            font-size: 8px;
            margin-bottom: 1px;
        }

        .middle-row {
            width: 49%;
            box-sizing: border-box;
            margin: 0;
            margin-bottom: 10px;
        }

        .middle-row.left {
            float: left;
        }

        .middle-row.right {
            float: right;
        }

        .bg-blue {
            background-color: #79C4F2;
            /*{{-- background-color: {{ $primaryColor }}; --}}*/
            font-weight: bold;
            padding-left: 5px;
            width: 100%;
            color: #000;
        }

        .square {
            display: inline-block;
            width: 12px;
            height: 12px;
            background-color: #ACC011;
            vertical-align: middle;
            /* Alinea verticalmente */
        }

        .square-title {
            display: inline-block;
            vertical-align: middle;
            /* Alinea con el cuadro */
            font-weight: bold;
            font-size: 14px;
        }

        /* Tablas compatibles */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            margin-top: 5px;
        }

        .product-table thead tr {
            background-color: #b0bec5;
            /*{{-- background-color: {{ $secondaryColor }}; --}}*/
            text-align: left;
        }

        .product-table th,
        .product-table td {
            padding: 2px;
            border: 1px solid #fff;
            font-size: 9px;
        }

        .product-table tbody tr:nth-child(odd) td {
            background-color: #f2f3f4;
        }

        /* Firmas centradas sin flexbox */
        .signature {
            text-align: center;
            margin-top: 20px;
        }

        .signature img {
            margin: 0 auto;
            display: block;
        }

        /* Ajustes para imágenes */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Texto justificado */
        .text-justify {
            text-align: justify;
        }

        .signature-section {

            margin-top: 20px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Fuerza el mismo ancho para ambas celdas */
        }

        .signature-cell {
            width: 50%;
            padding: 15px 10px;
            vertical-align: bottom;
            /* Solo para debug (puedes quitarlo después) */
        }

        .signature-container {
            text-align: center;
            margin: 0 auto;
            padding: 10px;
            width: 200px;

        }

        .signature-image {
            width: 100px;
            height: 60px;
            object-fit: contain;
        }

        .no-signature {
            width: 100px;
            height: 80px;
            object-fit: contain;

        }

        .signature-container div {
            max-width: 200px;
            margin: 0;
            word-wrap: break-word;
        }

        .signature-title {
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        .signature-name {
            margin: 5px 0;
        }

        .signature-rfc {
            margin: 5px 0;
            font-style: italic;
        }

        .render-html table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 15px 5px;
            border: none !important;
        }

        .render-html td {
            word-break: break-word;
        }

        .render-html td[data-row="27"],
        .render-html td[data-row="12"] {
            white-space: normal !important;
        }

        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            text-align: center;
            pointer-events: none;
            z-index: -1;
            opacity: 0.1;
            /* {{-- opacity: {{ $watermarkOpacity }}; --}} */
        }

        .watermark img {
            display: inline-block;
            margin: auto;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }


        /* Nuevos estilos para evidencias fotográficas */
        .photo-evidence-section {
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .photo-evidence-grid {
            display: block;
            width: 100%;
            margin-top: 10px;
        }

        .photo-evidence-item {
            display: inline-block;
            width: 48%;
            margin: 1%;
            vertical-align: top;
            page-break-inside: avoid;
        }

        .photo-evidence-image {
            width: 100%;
            max-width: 200px;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ddd;
            display: block;
            margin: 0 auto 5px auto;
        }

        .photo-evidence-description {
            font-size: 9px;
            text-align: center;
            padding: 5px;
            word-wrap: break-word;
        }

        .evidence-placeholder {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 10px;
        }

        p {
            line-height: 1.3;
            margin: 0 0 6px 0;
        }

        b {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="watermark">
        <img
            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/siscoplagas/watermark.png'))) }}">
    </div>

    <div class="row">
        <div class="title">
            <h1 style="font-size: 22px; margin: 0;">{{ $title }}</h1>
        </div>
        <div class="logo">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/siscoplagas/landscape_logo.png'))) }} style="width:
                150px; margin: 0;">
        </div>
    </div>

    <div class="row">
        <div class="title">
            <div>CONTROL DE PLAGAS</div>
            <div style="font-weight: bold;">Fecha de ejecución: {{ $order['programmed_date'] }}</div>
        </div>
        <div class="logo">
            <div>{{ $branch['name'] }} {{ $branch['sede'] }}</div>
            <div>{{ $branch['address'] }}</div>
            <div>{{ $branch['email'] }}, {{ $branch['phone'] }}</div>
            <div>Licencia sanitaria nº: {{ $branch['no_license'] }}</div>
        </div>
    </div>

    <div class="row">
        <div class="middle-row">
            <div class="bg-blue">FECHA Y HORA</div>
            <div><span style="font-weight: bold;">Fecha de Comienzo</span>: {{ $order['start'] }}
            </div>
            <div><span style="font-weight: bold;">Fecha de Finalización</span>: {{ $order['end'] }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="middle-row left">
            <div class="bg-blue">DATOS DEL CLIENTE Y SU SEDE</div>
            <div><span style="font-weight: bold;">Razón social</span>:
                {{ $customer['social_reason'] }}</div>
            <div><span style="font-weight: bold;">Sede</span>: {{ $customer['name'] }}</div>
            <div><span style="font-weight: bold;">Dirección</span>: {{ $customer['address'] }}</div>
            <div><span style="font-weight: bold;">Municipio</span>: {{ $customer['city'] }}</div>
            <div><span style="font-weight: bold;">Estado/Entidad</span>: {{ $customer['state'] }}
            </div>
            <div><span style="font-weight: bold;">Teléfono</span>: {{ $customer['phone'] }}</div>
        </div>

        <div class="middle-row right">
            <div class="bg-blue">PRESTA EL SERVICIO</div>
            <div>{{ $branch['name'] }}</div>
            <div>{{ $branch['address'] }}</div>
            <div style="font-weight: bold;">Licencia Sanitaria ROESB con nº
                {{ $branch['no_license'] }}</div>
        </div>
    </div>

    <!-- SERVICIOS con evidencias -->
    <div class="row">
        <div class="bg-blue">SERVICIOS</div>
        @foreach ($services as $service)
            <div style="margin-top: 10px;">
                <span class="square"></span>
                <span class="square-title">{{ $service['name'] }}</span>
            </div>
            <div class="render-html">{!! $service['text'] !!}</div>

            <!-- Evidencias del área "servicio" para este servicio específico -->
            @if (isset($photo_evidences['servicio']) && count($photo_evidences['servicio']) > 0)
                <div class="photo-evidence-section">
                    <div style="font-weight: bold; margin-top: 10px; color: #0d6efd;">
                        EVIDENCIAS FOTOGRÁFICAS - SERVICIO
                    </div>
                    <div class="photo-evidence-grid">
                        @foreach ($photo_evidences['servicio'] as $evidence)
                            <div class="photo-evidence-item">
                                <img src="{{ $evidence['image'] }}" class="photo-evidence-image" alt="Evidencia">
                                <div class="photo-evidence-description">
                                    {{ $evidence['description'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="row">
        <div class="bg-blue">PRODUCTOS</div>
        @if (count($products['data']) > 0)
            <table class="product-table">
                <thead>
                    <tr>
                        @foreach ($products['headers'] as $row)
                            <th>{{ $row }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products['data'] as $data)
                        <tr>
                            <td>{{ $data['name'] }}</td>
                            <td>{{ $data['active_ingredient'] }}</td>
                            <td>{{ $data['no_register'] }}</td>
                            <td>{{ $data['safety_period'] }}</td>
                            <td>{{ $data['application_method'] }}</td>
                            <td>{{ $data['dosage'] }}</td>
                            <td>{{ $data['amount'] }} {{ $data['metric'] }}</td>
                            <td>{{ $data['lot'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div>Sin productos</div>
        @endif
    </div>

    <div class="row">
        <div class="bg-blue">REVISIONES DE DISPOSITIVOS DE CONTROL</div>
        @forelse ($reviews as $index => $review)
            @if ($index == 0)
                <div style="margin-bottom: 10px;">
                    <div>SEDE DEL CLIENTE: {{ $review['sede'] }}</div>
                </div>
            @endif

            @foreach ($review['control_points'] as $control_point)
                <div style="font-weight: bold;">PLANO: {{ $review['floorplan'] }}</div>
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: bold;">DISPOSITIVO: {{ $control_point['name'] }}</div>
                    <table class="product-table">
                        <thead>
                            <tr>
                                @foreach ($control_point['headers'] as $row)
                                    <th>{{ $row }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($control_point['devices'] as $data)
                                <tr>
                                    <td>{{ $data['zone'] }}</td>
                                    <td>{{ $data['code'] }}</td>
                                    <td>{{ $data['intake'] }}</td>
                                    <td>{{ $data['pests'] }}</td>
                                    @foreach ($data['questions'] as $question)
                                        <td>{{ $question['answer'] }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td colspan="{{ count($control_point['headers']) }}">
                                        <span style="font-weight: bold; font-size: 9px;">Observaciones</span>:
                                        {{ $data['observations'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @empty
            <div>Sin revisiones de dispositivos</div>
        @endforelse
    </div>

    <!-- NOTAS DEL CLIENTE con evidencias -->
    <div class="row">
        <div class="bg-blue">NOTAS DEL CLIENTE</div>
        <div class="text-justify">
            {!! $notes !!}
        </div>

        <!-- Evidencias del área "notas" -->
        @if (isset($photo_evidences['notas']) && count($photo_evidences['notas']) > 0)
            <div class="photo-evidence-section">
                <div style="font-weight: bold; margin-top: 10px; color: #6c757d;">
                    EVIDENCIAS FOTOGRÁFICAS
                </div>
                <div class="photo-evidence-grid">
                    @foreach ($photo_evidences['notas'] as $evidence)
                        <div class="photo-evidence-item">
                            <img src="{{ $evidence['image'] }}" class="photo-evidence-image" alt="Evidencia">
                            <div class="photo-evidence-description">
                                {{ $evidence['description'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- RECOMENDACIONES con evidencias -->
    <div class="row">
        <div class="bg-blue">RECOMENDACIONES</div>
        <div class="text-justify">
            {!! $recommendations !!}
        </div>

        <!-- Evidencias del área "recomendaciones" -->
        @if (isset($photo_evidences['recomendaciones']) && count($photo_evidences['recomendaciones']) > 0)
            <div class="photo-evidence-section">
                <div style="font-weight: bold; margin-top: 10px; color: #198754;">
                    EVIDENCIAS FOTOGRÁFICAS - RECOMENDACIONES
                </div>
                <div class="photo-evidence-grid">
                    @foreach ($photo_evidences['recomendaciones'] as $evidence)
                        <div class="photo-evidence-item">
                            <img src="{{ $evidence['image'] }}" class="photo-evidence-image" alt="Evidencia">
                            <div class="photo-evidence-description">
                                {{ $evidence['description'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- EVIDENCIAS FOTOGRÁFICAS (área general) -->
    @if (isset($photo_evidences['evidencias']) && count($photo_evidences['evidencias']) > 0)
        <div class="row">
            <div class="bg-blue" style="background-color: #6f42c1;">EVIDENCIAS FOTOGRÁFICAS</div>
            <div class="photo-evidence-grid">
                @foreach ($photo_evidences['evidencias'] as $evidence)
                    <div class="photo-evidence-item">
                        <img src="{{ $evidence['image'] }}" class="photo-evidence-image" alt="Evidencia">
                        <div class="photo-evidence-description">
                            {{ $evidence['description'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Mensaje si no hay evidencias en ninguna área -->
    {{-- @if (empty($photo_evidences) || (empty($photo_evidences['servicio']) && empty($photo_evidences['notas']) && empty($photo_evidences['recomendaciones']) && empty($photo_evidences['evidencias'])))
        <div class="row">
            <div class="evidence-placeholder">
                No hay evidencias fotográficas para mostrar
            </div>
        </div>
    @endif --}}

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <!-- Firma Cliente -->
                <td class="signature-cell">
                    <div class="signature-container">
                        @if ($customer['signature_base64'])
                            <img src="{{ $customer['signature_base64'] }}" class="signature-image">
                        @endif
                        <div class="signature-title">Nombre y firma del cliente</div>
                        <div class="signature-name">{{ $customer['signed_by'] }}</div>
                        <div class="signature-name">{{ $customer['name'] }}</div>
                        <div class="signature-rfc">RFC: {{ $customer['rfc'] }}</div>
                    </div>
                </td>

                <!-- Firma Técnico -->
                <td class="signature-cell">
                    <div class="signature-container">
                        <img src="{{ $technician['signature_base64'] }}" class="signature-image" alt="Firma">
                        <div class="signature-title">Nombre y firma del técnico aplicador</div>
                        <div class="signature-name">{{ $technician['name'] }}</div>
                        <div class="signature-rfc">RFC: {{ $technician['rfc'] }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Clearfix -->
    <div style="clear: both;"></div>
</body>

</html>
