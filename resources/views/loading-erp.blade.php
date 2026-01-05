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
            background: linear-gradient(135deg, #0d1c2e 0%, #1a2d42 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Contenedor principal */
        .loading-erp-container {
            text-align: center;
            color: white;
            position: relative;
            z-index: 10;
        }

        /* Logo con efecto navideño */
        .logo-animation {
            animation: fadeInScale 1.5s ease-out forwards, gentleFloat 4s ease-in-out infinite;
            max-width: 180px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.4));
            position: relative;
            z-index: 2;
        }

        /* Corona navideña alrededor del logo */
        .holly-wreath {
            position: absolute;
            width: 220px;
            height: 220px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            opacity: 0.7;
        }

        .holly-leaf {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: #2e7d32;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            transform-origin: center;
        }

        .holly-berry {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #d32f2f;
            border-radius: 50%;
        }

        /* Texto de carga */
        .loading-text {
            opacity: 0;
            animation: fadeInUp 1s ease-out 0.5s forwards;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 1rem;
            font-weight: 300;
            letter-spacing: 1px;
        }

        /* Puntos de carga */
        .loading-dots {
            display: inline-flex;
            margin-top: 1rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            margin: 0 4px;
            opacity: 0;
            animation: dotPulse 1.5s infinite;
        }

        .dot:nth-child(1) {
            animation-delay: 0s;
            background-color: #4caf50;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
            background-color: #ff9800;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
            background-color: #f44336;
        }

        /* Barra de progreso navideña */
        .progress-bar {
            width: 250px;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            margin: 2rem auto;
            overflow: hidden;
            position: relative;
            z-index: 2;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #ff9800, #f44336);
            width: 0%;
            animation: progressFill 3s ease-in-out forwards;
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 20px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: progressShine 2s infinite;
        }

        /* Mensaje navideño */
        .christmas-message {
            font-size: 0.9rem;
            color: #ffeb3b;
            margin-top: 1.5rem;
            opacity: 0;
            animation: fadeInUp 1s ease-out 1s forwards;
            font-weight: 300;
            letter-spacing: 1.5px;
            text-shadow: 0 0 5px rgba(255, 235, 59, 0.5);
        }

        /* Animaciones */
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

        @keyframes gentleFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
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
            0%, 100% {
                opacity: 0.3;
                transform: scale(0.8);
            }

            50% {
                opacity: 1;
                transform: scale(1.2);
                box-shadow: 0 0 10px currentColor;
            }
        }

        @keyframes progressFill {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }

        @keyframes progressShine {
            0% {
                left: -20px;
            }

            100% {
                left: 100%;
            }
        }

        /* Copos de nieve */
        .snowflakes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .snowflake {
            position: absolute;
            top: -10px;
            background-color: white;
            border-radius: 50%;
            opacity: 0.8;
            filter: blur(0.5px);
            animation-name: snowfall;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }

        @keyframes snowfall {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.8;
            }

            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Luces navideñas */
        .christmas-lights {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 0;
        }

        .light {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: lightTwinkle 1.5s infinite alternate;
            box-shadow: 0 0 10px currentColor;
        }

        @keyframes lightTwinkle {
            0% {
                opacity: 0.3;
                transform: scale(0.8);
            }

            100% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        /* Estrellas brillantes en el fondo */
        .stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .star {
            position: absolute;
            background-color: white;
            border-radius: 50%;
            animation: starTwinkle 3s infinite alternate;
        }

        @keyframes starTwinkle {
            0%, 100% {
                opacity: 0.1;
            }

            50% {
                opacity: 0.8;
            }
        }

        /* Efecto de brillo general */
        .glow-effect {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(76, 175, 80, 0.1) 0%, rgba(33, 150, 243, 0.05) 50%, transparent 70%);
            transform: translate(-50%, -50%);
            z-index: 0;
            animation: gentlePulse 4s infinite alternate;
        }

        @keyframes gentlePulse {
            0% {
                opacity: 0.3;
                transform: translate(-50%, -50%) scale(0.9);
            }

            100% {
                opacity: 0.7;
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        /* Árbol de navidad pequeño */
        .mini-tree {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            z-index: 2;
            opacity: 0.7;
            animation: treeSway 5s infinite alternate;
        }

        @keyframes treeSway {
            0%, 100% {
                transform: rotate(-2deg);
            }
            50% {
                transform: rotate(2deg);
            }
        }
    </style>
</head>

<body>
    <!-- Efecto de brillo general -->
    <div class="glow-effect"></div>

    <!-- Copos de nieve -->
    <div class="snowflakes" id="snowflakes"></div>

    <!-- Luces navideñas -->
    <div class="christmas-lights" id="christmas-lights"></div>

    <!-- Estrellas de fondo -->
    <div class="stars" id="stars"></div>

    <!-- Contenedor principal -->
    <div class="loading-erp-container">
        <!-- Corona navideña -->
        <div class="holly-wreath" id="holly-wreath"></div>

        <!-- Logo -->
        <img src="{{ asset('images/zonda/isotype_logo.png') }}" alt="ZONDA Logo" class="logo-animation">

        <!-- Barra de progreso -->
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <!-- Texto de carga -->
        <div class="loading-text">
            Cargando ZONDA
        </div>

        <!-- Puntos de carga -->
        <div class="loading-dots">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>

        <!-- Mensaje navideño -->
        <div class="christmas-message">
            ¡Felices Fiestas! 🎄
        </div>
    </div>

    <!-- Árbol de navidad pequeño -->
    <div class="mini-tree">
        <svg viewBox="0 0 40 40" fill="#2e7d32">
            <path d="M20 5 L25 15 L15 15 Z" fill="#4caf50"/>
            <path d="M20 12 L27 25 L13 25 Z" fill="#388e3c"/>
            <path d="M20 22 L30 35 L10 35 Z" fill="#2e7d32"/>
            <rect x="18" y="35" width="4" height="5" fill="#8d6e63"/>
        </svg>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Crear copos de nieve
            const snowflakesContainer = document.getElementById('snowflakes');
            for (let i = 0; i < 60; i++) {
                const snowflake = document.createElement('div');
                snowflake.classList.add('snowflake');
                
                // Tamaño aleatorio entre 3px y 8px
                const size = Math.random() * 5 + 3;
                snowflake.style.width = `${size}px`;
                snowflake.style.height = `${size}px`;
                
                // Posición horizontal aleatoria
                snowflake.style.left = `${Math.random() * 100}vw`;
                
                // Opacidad aleatoria
                snowflake.style.opacity = Math.random() * 0.7 + 0.3;
                
                // Duración de animación aleatoria entre 5 y 15 segundos
                const duration = Math.random() * 10 + 5;
                snowflake.style.animationDuration = `${duration}s`;
                
                // Retraso inicial aleatorio
                snowflake.style.animationDelay = `${Math.random() * 5}s`;
                
                snowflakesContainer.appendChild(snowflake);
            }

            // Crear luces navideñas
            const lightsContainer = document.getElementById('christmas-lights');
            const colors = ['#f44336', '#4caf50', '#2196f3', '#ff9800', '#9c27b0', '#ffeb3b'];
            
            for (let i = 0; i < 30; i++) {
                const light = document.createElement('div');
                light.classList.add('light');
                
                // Color aleatorio
                const color = colors[Math.floor(Math.random() * colors.length)];
                light.style.backgroundColor = color;
                
                // Posición aleatoria
                light.style.left = `${Math.random() * 100}vw`;
                light.style.top = `${Math.random() * 100}vh`;
                
                // Tamaño aleatorio
                const size = Math.random() * 6 + 6;
                light.style.width = `${size}px`;
                light.style.height = `${size}px`;
                
                // Retraso de animación aleatorio
                light.style.animationDelay = `${Math.random() * 2}s`;
                
                lightsContainer.appendChild(light);
            }

            // Crear estrellas de fondo
            const starsContainer = document.getElementById('stars');
            for (let i = 0; i < 40; i++) {
                const star = document.createElement('div');
                star.classList.add('star');
                
                // Tamaño muy pequeño
                const size = Math.random() * 2 + 1;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                // Posición aleatoria
                star.style.left = `${Math.random() * 100}vw`;
                star.style.top = `${Math.random() * 100}vh`;
                
                // Retraso de animación aleatorio
                star.style.animationDelay = `${Math.random() * 3}s`;
                
                starsContainer.appendChild(star);
            }

            // Crear corona de acebo alrededor del logo
            const wreathContainer = document.getElementById('holly-wreath');
            const leafCount = 12;
            const berryCount = 6;
            
            // Crear hojas
            for (let i = 0; i < leafCount; i++) {
                const angle = (i / leafCount) * 2 * Math.PI;
                const radius = 100; // Radio de la corona
                
                const leaf = document.createElement('div');
                leaf.classList.add('holly-leaf');
                
                // Posicionar en círculo
                const x = radius * Math.cos(angle);
                const y = radius * Math.sin(angle);
                
                leaf.style.left = `calc(50% + ${x}px)`;
                leaf.style.top = `calc(50% + ${y}px)`;
                
                // Rotar hacia afuera
                const rotation = (angle * 180 / Math.PI) + 90;
                leaf.style.transform = `translate(-50%, -50%) rotate(${rotation}deg)`;
                
                // Color aleatorio de verde
                const greenShade = Math.floor(Math.random() * 30 + 40);
                leaf.style.backgroundColor = `rgb(${greenShade}, ${greenShade + 50}, ${greenShade + 30})`;
                
                wreathContainer.appendChild(leaf);
            }
            
            // Crear bayas
            for (let i = 0; i < berryCount; i++) {
                const angle = (i / berryCount) * 2 * Math.PI;
                const radius = 90; // Radio ligeramente más pequeño
                
                const berry = document.createElement('div');
                berry.classList.add('holly-berry');
                
                // Posicionar en círculo
                const x = radius * Math.cos(angle);
                const y = radius * Math.sin(angle);
                
                berry.style.left = `calc(50% + ${x}px)`;
                berry.style.top = `calc(50% + ${y}px)`;
                
                // Tamaño aleatorio
                const size = Math.random() * 4 + 6;
                berry.style.width = `${size}px`;
                berry.style.height = `${size}px`;
                
                wreathContainer.appendChild(berry);
            }

            // Redirección después de 3 segundos
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