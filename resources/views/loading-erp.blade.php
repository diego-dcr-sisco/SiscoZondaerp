<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZONDA | Protección Inteligente</title>

    <style>
        :root {
            --primary: #00c2a8;
            --secondary: #1e2a38;
            --accent: #5eead4;
            --bg: #0b1320;
        }

        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            height: 100vh;
            background:
                radial-gradient(circle at center, #132238 0%, var(--bg) 70%);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: #fff;
            overflow: hidden;
        }

        /* CONTENEDOR */
        .loader {
            position: relative;
            width: 360px;
            padding: 40px 30px;
            background: rgba(30, 42, 56, .65);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            border: 1px solid rgba(94, 234, 212, .15);
            text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, .45);
            z-index: 5;
        }

        /* LOGO */
        .logo {
            width: 150px;
            margin-bottom: 25px;
            animation: fadeUp 1.2s ease forwards;
        }

        /* RADAR */
        .radar {
            position: absolute;
            inset: -80px;
            border-radius: 50%;
            background:
                radial-gradient(circle,
                    rgba(94, 234, 212, .12) 1px,
                    transparent 1px);
            background-size: 20px 20px;
            animation: rotate 18s linear infinite;
            z-index: -1;
        }

        .radar::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background:
                conic-gradient(from 0deg,
                    rgba(94, 234, 212, .25),
                    transparent 40%);
            animation: sweep 3s linear infinite;
        }

        /* ICONOS PLAGA */
        .pest {
            position: absolute;
            width: 28px;
            height: 28px;
            opacity: 0;
            animation: pestScan 3s infinite;
        }

        .pest svg {
            width: 100%;
            height: 100%;
            fill: #ff6b6b;
        }

        /* TEXTO */
        .title {
            font-size: 1.1rem;
            letter-spacing: 2px;
            color: var(--accent);
            margin-bottom: 8px;
            opacity: 0;
            animation: fadeUp .8s .3s forwards;
        }

        .subtitle {
            font-size: .9rem;
            color: rgba(255, 255, 255, .75);
            margin-bottom: 25px;
            opacity: 0;
            animation: fadeUp .8s .5s forwards;
        }

        /* BARRA */
        .progress {
            height: 6px;
            width: 100%;
            background: rgba(255, 255, 255, .1);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress span {
            display: block;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            animation: load 3s ease forwards;
        }

        /* ANIMACIONES */
        @keyframes rotate {
            to {
                transform: rotate(360deg)
            }
        }

        @keyframes sweep {
            from {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(360deg)
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes load {
            to {
                width: 100%
            }
        }

        @keyframes pestScan {
            0% {
                opacity: 0;
                transform: scale(.5)
            }

            40% {
                opacity: .8
            }

            60% {
                opacity: .8
            }

            100% {
                opacity: 0;
                transform: scale(1.6)
            }
        }
    </style>
</head>

<body>

    <div class="loader">
        <div class="radar"></div>

        <img src="{{ asset('images/zonda/isotype_logo.png') }}" class="logo" alt="ZONDA">

        <div class="title">CARGANDO SOFTWARE SMIP</div>
        <div class="subtitle">Monitoreo de Control de Plagas</div>

        <div class="progress">
            <span></span>
        </div>
    </div>

    <script>
        setTimeout(() => {
            @php
                $route = session('redirect_route');
                $params = session('route_params', []);
                $finalUrl = Route::has($route) ? route($route, $params) : route('dashboard');
            @endphp
            window.location.href = "{{ $finalUrl }}";
        }, 3000);
    </script>

</body>

</html>
