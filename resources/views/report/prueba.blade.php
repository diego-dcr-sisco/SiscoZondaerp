<!DOCTYPE html>
<html lang="en">
<head>
    @php
    // Generar la URL de la imagen
    $imageUrl = route('image.show', ['filename' => $Fplan->path]);
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" >
    <style>
     body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        #logo img {
            width: 100px;
        }

        #image-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* Aspect ratio 16:9 */
            background-image: url('{{ $imageUrl }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .map-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        svg {
            width: 100%;
            height: 100%;
        }

        .point {
            fill: red;
            stroke: black;
            stroke-width: 2px;
        }
    </style>
</head>

<body>
    <header>
        <p id="logo"><img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo"></p>
        <p>{{ $branch->fiscal_name }}</p>
        <p>{{ $branch->address . ', ' . $branch->colony . ' #' . $branch->zip_code . ', ' . $branch->state . ', ' . $branch->country }}</p>
        <p>{{ 'Licencia Sanitaria nº : ' . $branch->license_number }}</p>
        <p>{{ $branch->name . ' Tel. ' . $branch->phone }}</p>
        <p>{{ $branch->email }}</p>
    </header>
    <main>
        <div id="image-container" >
            <div class="map-container">
                <svg>
                    @foreach ($devices as $device)
                    <circle cx="{{ $device['map_x'] }}" cy="{{ $device['map_y'] }}" r="6" class="point"></circle>
                    @endforeach
                </svg>
            </div>

        </div>

    </main>
    <footer></footer>
</body>

</html>