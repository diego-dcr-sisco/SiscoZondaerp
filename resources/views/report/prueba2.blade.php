<!DOCTYPE html>
<html lang="es">
<head>
    @php
    // Generar la URL de la imagen
    $imageUrl = route('image.show', ['filename' => $Fplan->path]);
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVG con Imagen de Fondo y Puntos</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        svg {
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <svg width="1155" height="930" xmlns="http://www.w3.org/2000/svg">
        <!-- Imagen de fondo -->
        <image href="{{$imageUrl}}" width="1155" height="930" />
        <svg>
            @foreach ($devices as $device)
                <circle cx="{{ $device['map_x'] }}" cy="{{ $device['map_y'] }}" r="6" class="point"></circle>
            @endforeach
        </svg>
    </svg>
</body>
</html>