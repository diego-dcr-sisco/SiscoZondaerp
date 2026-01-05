<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZONDA - Felices Reyes y Año Nuevo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0c1a2d 0%, #1a2d42 25%, #2c1b3d 75%, #1c0b1c 100%);
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
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        /* Logo con efecto festivo */
        .logo-animation {
            animation: fadeInScale 1.5s ease-out forwards, gentleFloat 4s ease-in-out infinite;
            max-width: 180px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 10px 20px rgba(255, 215, 0, 0.5));
            position: relative;
            z-index: 2;
        }

        /* Corona de estrellas */
        .star-wreath {
            position: absolute;
            width: 220px;
            height: 220px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            opacity: 0.8;
        }

        .star {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: gold;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            transform-origin: center;
            animation: starTwinkle 2s infinite alternate;
        }

        .gift {
            position: absolute;
            width: 16px;
            height: 16px;
            background: linear-gradient(45deg, #d32f2f, #ff9800);
            border-radius: 4px;
            animation: giftFloat 3s infinite ease-in-out;
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
            background-color: gold;
            border-radius: 50%;
            margin: 0 4px;
            opacity: 0;
            animation: dotPulse 1.5s infinite;
            box-shadow: 0 0 10px gold;
        }

        .dot:nth-child(1) {
            animation-delay: 0s;
            background-color: #ff9800;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
            background-color: gold;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
            background-color: #4caf50;
        }

        /* Barra de progreso festiva */
        .progress-bar {
            width: 250px;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin: 2rem auto;
            overflow: hidden;
            position: relative;
            z-index: 2;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.2);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff9800, gold, #4caf50);
            width: 0%;
            animation: progressFill 3s ease-in-out forwards;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 30px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: progressShine 2s infinite;
        }

        /* Mensaje festivo */
        .festive-message {
            font-size: 1.1rem;
            color: gold;
            margin-top: 1.5rem;
            opacity: 0;
            animation: fadeInUp 1s ease-out 1s forwards;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
            padding: 10px 20px;
            background: rgba(255, 215, 0, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 215, 0, 0.3);
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
                box-shadow: 0 0 15px currentColor;
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
                left: -30px;
            }

            100% {
                left: 100%;
            }
        }

        @keyframes starTwinkle {
            0% {
                opacity: 0.3;
                transform: scale(0.8);
            }

            100% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        @keyframes giftFloat {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(10deg);
            }
        }

        /* Estrellas fugaces */
        .shooting-stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shooting-star {
            position: absolute;
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, transparent, gold);
            border-radius: 50%;
            opacity: 0;
            filter: blur(1px);
            animation-name: shootingStar;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }

        @keyframes shootingStar {
            0% {
                opacity: 0;
                transform: translateX(0) translateY(0) rotate(45deg);
            }

            10% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translateX(100vw) translateY(100vh) rotate(45deg);
            }
        }

        /* Luces festivas */
        .festive-lights {
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
            width: 15px;
            height: 15px;
            border-radius: 50%;
            animation: lightTwinkle 1.5s infinite alternate;
            box-shadow: 0 0 15px currentColor;
            filter: drop-shadow(0 0 5px currentColor);
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

        /* Confeti */
        .confetti {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }

        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
            animation: confettiFall 5s linear infinite;
        }

        @keyframes confettiFall {
            0% {
                opacity: 1;
                transform: translateY(-100px) rotate(0deg);
            }

            100% {
                opacity: 0;
                transform: translateY(100vh) rotate(720deg);
            }
        }

        /* Efecto de brillo general */
        .glow-effect {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, rgba(255, 152, 0, 0.05) 50%, transparent 70%);
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

        /* Corona de reyes */
        .kings-crown {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 40px;
            z-index: 3;
            opacity: 0.8;
            animation: crownFloat 4s infinite ease-in-out;
        }

        @keyframes crownFloat {
            0%, 100% {
                transform: translateX(-50%) translateY(0) rotate(-5deg);
            }
            50% {
                transform: translateX(-50%) translateY(-10px) rotate(5deg);
            }
        }

        /* Año nuevo */
        .new-year {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 2rem;
            color: gold;
            font-weight: bold;
            opacity: 0.7;
            animation: yearGlow 2s infinite alternate;
            text-shadow: 0 0 10px gold;
            z-index: 2;
        }

        @keyframes yearGlow {
            0% {
                opacity: 0.4;
                text-shadow: 0 0 5px gold;
            }
            100% {
                opacity: 1;
                text-shadow: 0 0 20px gold, 0 0 30px #ff9800;
            }
        }
    </style>
</head>

<body>
    <!-- Efecto de brillo general -->
    <div class="glow-effect"></div>

    <!-- Estrellas fugaces -->
    <div class="shooting-stars" id="shooting-stars"></div>

    <!-- Luces festivas -->
    <div class="festive-lights" id="festive-lights"></div>

    <!-- Confeti -->
    <div class="confetti" id="confetti"></div>

    <!-- Contenedor principal -->
    <div class="loading-erp-container">
        <!-- Corona de reyes -->
        <div class="kings-crown">
            <svg viewBox="0 0 60 40" fill="gold">
                <path d="M5 35 L55 35 L55 15 L50 10 L45 15 L40 10 L35 15 L30 10 L25 15 L20 10 L15 15 L10 10 L5 15 Z" 
                      fill="gold" stroke="#ff9800" stroke-width="1"/>
                <circle cx="15" cy="12" r="3" fill="#ff9800"/>
                <circle cx="30" cy="8" r="4" fill="#ff9800"/>
                <circle cx="45" cy="12" r="3" fill="#ff9800"/>
            </svg>
        </div>

        <!-- Corona de estrellas -->
        <div class="star-wreath" id="star-wreath"></div>

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

        <!-- Mensaje festivo -->
        <div class="festive-message">
            ¡Feliz Día de Reyes y Próspero Año Nuevo! 🎉👑
        </div>
    </div>

    <!-- Año nuevo -->
    <div class="new-year" id="new-year">2026</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Crear estrellas fugaces
            const shootingStarsContainer = document.getElementById('shooting-stars');
            for (let i = 0; i < 5; i++) {
                const star = document.createElement('div');
                star.classList.add('shooting-star');
                
                // Posición inicial aleatoria
                star.style.left = `${Math.random() * 50}vw`;
                star.style.top = `${Math.random() * 50}vh`;
                
                // Duración y retraso aleatorio
                const duration = Math.random() * 3 + 2;
                const delay = Math.random() * 10;
                star.style.animationDuration = `${duration}s`;
                star.style.animationDelay = `${delay}s`;
                
                shootingStarsContainer.appendChild(star);
            }

            // Crear luces festivas
            const lightsContainer = document.getElementById('festive-lights');
            const colors = ['gold', '#ff9800', '#4caf50', '#2196f3', '#9c27b0', '#ffeb3b'];
            
            for (let i = 0; i < 40; i++) {
                const light = document.createElement('div');
                light.classList.add('light');
                
                // Color aleatorio
                const color = colors[Math.floor(Math.random() * colors.length)];
                light.style.backgroundColor = color;
                
                // Posición aleatoria
                light.style.left = `${Math.random() * 100}vw`;
                light.style.top = `${Math.random() * 100}vh`;
                
                // Tamaño aleatorio
                const size = Math.random() * 8 + 8;
                light.style.width = `${size}px`;
                light.style.height = `${size}px`;
                
                // Retraso de animación aleatorio
                light.style.animationDelay = `${Math.random() * 2}s`;
                
                lightsContainer.appendChild(light);
            }

            // Crear confeti
            const confettiContainer = document.getElementById('confetti');
            const confettiColors = ['gold', '#ff9800', '#4caf50', '#2196f3', '#9c27b0', '#ffeb3b'];
            
            for (let i = 0; i < 100; i++) {
                const piece = document.createElement('div');
                piece.classList.add('confetti-piece');
                
                // Color aleatorio
                const color = confettiColors[Math.floor(Math.random() * confettiColors.length)];
                piece.style.backgroundColor = color;
                
                // Posición horizontal aleatoria
                piece.style.left = `${Math.random() * 100}vw`;
                
                // Retraso aleatorio
                piece.style.animationDelay = `${Math.random() * 5}s`;
                
                // Duración aleatoria
                const duration = Math.random() * 3 + 3;
                piece.style.animationDuration = `${duration}s`;
                
                // Tamaño aleatorio
                const size = Math.random() * 8 + 4;
                piece.style.width = `${size}px`;
                piece.style.height = `${size}px`;
                
                // Forma aleatoria (redonda o rectangular)
                if (Math.random() > 0.5) {
                    piece.style.borderRadius = '50%';
                } else {
                    piece.style.borderRadius = '0';
                }
                
                confettiContainer.appendChild(piece);
            }

            // Crear corona de estrellas y regalos
            const wreathContainer = document.getElementById('star-wreath');
            const starCount = 12;
            const giftCount = 6;
            
            // Crear estrellas
            for (let i = 0; i < starCount; i++) {
                const angle = (i / starCount) * 2 * Math.PI;
                const radius = 100;
                
                const star = document.createElement('div');
                star.classList.add('star');
                
                const x = radius * Math.cos(angle);
                const y = radius * Math.sin(angle);
                
                star.style.left = `calc(50% + ${x}px)`;
                star.style.top = `calc(50% + ${y}px)`;
                
                const rotation = (angle * 180 / Math.PI);
                star.style.transform = `translate(-50%, -50%) rotate(${rotation}deg)`;
                
                // Tamaño aleatorio
                const size = Math.random() * 10 + 15;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                // Retraso de animación aleatorio
                star.style.animationDelay = `${Math.random() * 2}s`;
                
                wreathContainer.appendChild(star);
            }
            
            // Crear regalos
            for (let i = 0; i < giftCount; i++) {
                const angle = (i / giftCount) * 2 * Math.PI;
                const radius = 85;
                
                const gift = document.createElement('div');
                gift.classList.add('gift');
                
                const x = radius * Math.cos(angle);
                const y = radius * Math.sin(angle);
                
                gift.style.left = `calc(50% + ${x}px)`;
                gift.style.top = `calc(50% + ${y}px)`;
                
                // Retraso de animación aleatorio
                gift.style.animationDelay = `${Math.random() * 3}s`;
                
                wreathContainer.appendChild(gift);
            }

            // Animación del año nuevo
            const newYearElement = document.getElementById('new-year');
            let year = 2025;
            let pulseCount = 0;
            
            const yearPulse = setInterval(() => {
                newYearElement.style.transform = `scale(${1 + Math.sin(pulseCount * 0.5) * 0.2})`;
                pulseCount++;
                
                if (pulseCount > 20) {
                    clearInterval(yearPulse);
                }
            }, 200);

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