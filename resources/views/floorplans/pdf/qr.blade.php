<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Reporte de Dispositivos</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            margin: 0;
        }

        .row {
            width: 100%;
            margin-bottom: 8px;
            display: block;
            clear: both;
        }

        .logo {
            margin-bottom: 4px;
        }

        .device-card {
            width: 43%;
            border: 1px solid #000;
            padding: 10px;
            box-sizing: border-box;
            display: inline-block;
            vertical-align: top;
            position: relative;

            background-image: url("file://{{ public_path('images/trans_watermark.png') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 50%;

        }

        .device-card:nth-child(odd) {
            margin-right: 4%;
        }

        .card-content {
            display: table;
            width: 100%;
        }

        .device-text {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
        }

        .device-qr {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: middle;
            border: 2px solid #F5F5F4;
            padding: 4px;
            box-sizing: border-box;
            background: #fff;
        }

        .device-header {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .device-text {
            font-size: 10px;
        }

        .device-txt-bold {
            font-size: 11px;
            font-weight: bold;
        }

        .device-code {
            font-size: 10px;
            font-weight: bold;
            font-style: italic;
        }

        .qr-image {
            width: 110px;
            height: 110px;
        }
    </style>
</head>

<body>
    @foreach ($devices as $index => $device)
        @if ($index % 2 == 0)
            <div class="row">
        @endif

        <div class="device-card">
            <div class="card-content">
                <div class="device-text">
                    <div class="logo">
                        <img src="file://{{ public_path('images/siscoplagas_logo.png') }}" style="width:70%; margin: 0;">
                    </div>
                    <div
                        style="font-size: 15px;
                                font-weight: bold;
                                margin-bottom: 0px;">
                        {{ $device['name'] }}</div>
                    <div style="font-size: 10px; margin-bottom: 8px;">Código: {{ $device['code'] }}
                    </div>
                    <div style="font-size: 12px; margin-bottom: 2px; font-weight: bold;">
                        {{ $device['customer'] }}</div>
                    <div style="font-size: 10px;">{{ $device['floorplan'] }}</div>
                </div>

                <div class="device-qr">
                    <img src="file://{{ $device['qr'] }}" style="width:90%; margin: 0;">
                </div>
            </div>
        </div>

        @if ($index % 2 == 1 || $loop->last)
            </div>
        @endif
    @endforeach
</body>

</html>
