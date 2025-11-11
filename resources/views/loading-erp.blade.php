<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZONDA - Cargando...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #182A41 0%, #304054 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .loading-erp-container {
            text-align: center;
            color: white;
        }

        .logo-animation {
            animation: fadeInScale 1.5s ease-out forwards;
            max-width: 200px;
            margin-bottom: 2rem;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
        }

        .loading-text {
            opacity: 0;
            animation: fadeInUp 1s ease-out 0.5s forwards;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 1rem;
        }

        .loading-dots {
            display: inline-flex;
            margin-top: 1rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            margin: 0 4px;
            opacity: 0;
            animation: dotPulse 1.5s infinite;
        }

        .dot:nth-child(1) {
            animation-delay: 0s;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes dotPulse {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(0.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .progress-bar {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin: 2rem auto;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ffc107, #fd7e14);
            width: 0%;
            animation: progressFill 3s ease-in-out forwards;
            border-radius: 2px;
        }

        @keyframes progressFill {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="loading-erp-container">
        <img src="{{ asset('images/header_logo.png') }}" alt="ZONDA Logo" class="logo-animation">

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <div class="loading-text">
            Cargando ZONDA
        </div>

        <div class="loading-dots">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                @if (session()->has('redirect_route'))
                    @php
                        // Verificar que la ruta existe
                        $route = session('redirect_route');
                        $params = session('route_params', []);

                        if (Route::has($route)) {
                            $finalUrl = route($route, $params);
                        } else {
                            $finalUrl = route('dashboard'); // Fallback
                        }
                    @endphp

                    window.location.href = "{{ $finalUrl }}";
                @else
                    window.location.href = "{{ route('dashboard') }}";
                @endif
            }, 3000);
        });
    </script>
</body>

</html>
