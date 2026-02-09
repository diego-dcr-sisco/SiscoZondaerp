<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZONDA | Protección Inteligente</title>

    <style>
        :root {
            /* Colores extraídos del gradiente del login */
            --color1: #182A41;      /* Azul oscuro */
            --color2: #304054;      /* Azul grisáceo */
            --color3: #C3523E;      /* Rojo terracota */
            --color4: #344290;      /* Azul intermedio */
            --color5: #1D2D83;      /* Azul púrpura */
            --color-accent: #5eead4; /* Verde turquesa para acentos */
            --bg-dark: #0b1320;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            height: 100vh;
            background: linear-gradient(135deg, var(--color1) 0%, var(--color5) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        /* Fondo animado con los colores del login */
        .gradient-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, 
                var(--color1) 0%, 
                var(--color2) 25%, 
                var(--color3) 50%, 
                var(--color4) 75%, 
                var(--color5) 100%);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
            opacity: 0.9;
        }

        /* Partículas que representan los colores del gradiente */
        .particles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .color-particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0;
            filter: blur(20px);
            animation: particleFloat 8s infinite ease-in-out;
        }

        .particle-1 { background: var(--color1); }
        .particle-2 { background: var(--color2); }
        .particle-3 { background: var(--color3); }
        .particle-4 { background: var(--color4); }
        .particle-5 { background: var(--color5); }

        /* CONTENEDOR PRINCIPAL */
        .loader {
            position: relative;
            width: 400px;
            max-width: 90%;
            padding: 50px 40px;
            background: rgba(30, 42, 56, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(195, 82, 62, 0.3);
            text-align: center;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.5),
                0 0 60px rgba(195, 82, 62, 0.2),
                inset 0 0 0 1px rgba(255, 255, 255, 0.1);
            z-index: 10;
            overflow: hidden;
        }

        /* Efecto de borde animado */
        .loader::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                var(--color1), 
                var(--color3), 
                var(--color4), 
                var(--color5));
            background-size: 400% 400%;
            border-radius: 26px;
            z-index: -1;
            animation: borderGlow 3s linear infinite;
            opacity: 0.7;
        }

        /* LOGO */
        .logo {
            width: 160px;
            margin-bottom: 30px;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.3));
            animation: logoPulse 4s ease-in-out infinite;
        }

        /* CÍRCULO DE ESCANEADO CON COLORES DEL GRADIENTE */
        .gradient-scan {
            position: absolute;
            width: 300px;
            height: 300px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0.6;
        }

        .gradient-scan::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                var(--color1) 0%,
                var(--color2) 20%,
                var(--color3) 40%,
                var(--color4) 60%,
                var(--color5) 80%,
                var(--color1) 100%
            );
            animation: rotate 12s linear infinite;
            opacity: 0.4;
        }

        .gradient-scan::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(
                from 180deg,
                transparent 0%,
                rgba(195, 82, 62, 0.3) 10%,
                rgba(52, 66, 144, 0.3) 30%,
                transparent 50%
            );
            animation: scanSweep 2.5s ease-in-out infinite;
        }

        /* ELEMENTOS DE DATOS FLOTANTES */
        .data-node {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            opacity: 0;
            animation: dataFlow 3s infinite;
        }

        /* TEXTO */
        .title {
            font-size: 1.3rem;
            letter-spacing: 3px;
            color: var(--color-accent);
            margin-bottom: 12px;
            opacity: 0;
            animation: fadeUp 0.8s 0.4s forwards;
            text-shadow: 0 2px 10px rgba(94, 234, 212, 0.5);
            font-weight: 600;
        }

        .subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 35px;
            opacity: 0;
            animation: fadeUp 0.8s 0.6s forwards;
            line-height: 1.5;
        }

        /* BARRA DE PROGRESO EN BLANCO */
        .progress-container {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.15); /* Fondo más claro para contraste */
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            margin-top: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1); /* Borde sutil */
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0.9) 0%, 
                rgba(255, 255, 255, 1) 50%, 
                rgba(255, 255, 255, 0.9) 100%);
            border-radius: 4px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3); /* Brillo suave */
        }

        /* Efecto de brillo animado en la barra blanca */
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 255, 255, 0.8), 
                transparent);
            animation: shine 1.5s ease-in-out infinite;
        }

        /* TEXTO DE PORCENTAJE */
        .percentage {
            position: absolute;
            right: 0;
            top: -25px;
            font-size: 0.9rem;
            color: var(--white);
            font-weight: bold;
            opacity: 0;
            animation: fadeIn 0.5s 0.5s forwards;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        /* ANIMACIONES */
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes borderGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes rotate {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @keyframes scanSweep {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes load {
            to { width: 100%; }
        }

        @keyframes shine {
            0% { transform: translateX(0); }
            100% { transform: translateX(200%); }
        }

        @keyframes particleFloat {
            0% { 
                transform: translateY(100vh) scale(0.5); 
                opacity: 0; 
            }
            20% { opacity: 0.3; }
            80% { opacity: 0.3; }
            100% { 
                transform: translateY(-100px) scale(1.2); 
                opacity: 0; 
            }
        }

        @keyframes dataFlow {
            0% { 
                opacity: 0;
                transform: translate(0, 0) scale(0);
            }
            30% { opacity: 0.8; }
            70% { opacity: 0.8; }
            100% { 
                opacity: 0;
                transform: translate(var(--tx), var(--ty)) scale(1.5);
            }
        }

        /* ANIMACIÓN DE TEXTO "INICIALIZANDO" */
        .status-text {
            margin-top: 25px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.85); /* Texto más claro */
            height: 20px;
            font-weight: 500;
        }

        .dot-animation {
            display: inline-block;
            animation: dots 1.5s infinite;
            color: var(--white);
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
    </style>
</head>

<body>
    <!-- Fondo con gradiente animado -->
    <div class="gradient-bg"></div>
    
    <!-- Partículas de colores -->
    <div class="particles-container" id="particles"></div>

    <!-- Contenedor principal -->
    <div class="loader">
        <!-- Círculo de escaneo con gradiente -->
        <div class="gradient-scan"></div>

        <!-- Logo -->
        <img src="{{ asset('images/zonda/isotype_logo.png') }}" class="logo" alt="ZONDA">

        <!-- Título y subtítulo -->
        <div class="title">INICIALIZANDO SISTEMA SMIP</div>
        <div class="subtitle">Sistema de Monitoreo Inteligente de Plagas</div>

        <!-- Barra de progreso -->
        <div class="progress-container">
            <div class="percentage" id="percentage">0%</div>
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <!-- Texto de estado animado -->
        <div class="status-text">
            Cargando módulos<span class="dot-animation"></span>
        </div>
    </div>

    <script>
        // Crear partículas de colores
        function createColorParticles() {
            const container = document.getElementById('particles');
            const colors = ['particle-1', 'particle-2', 'particle-3', 'particle-4', 'particle-5'];
            
            for (let i = 0; i < 25; i++) {
                const particle = document.createElement('div');
                const colorClass = colors[Math.floor(Math.random() * colors.length)];
                
                particle.className = `color-particle ${colorClass}`;
                
                // Posición y tamaño aleatorios
                const size = Math.random() * 80 + 40;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${Math.random() * 100}%`;
                
                // Retraso y duración aleatorios
                const delay = Math.random() * 5;
                const duration = 6 + Math.random() * 6;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;
                
                container.appendChild(particle);
            }
        }

        // Crear nodos de datos flotantes
        function createDataNodes() {
            const loader = document.querySelector('.loader');
            
            for (let i = 0; i < 15; i++) {
                const node = document.createElement('div');
                node.className = 'data-node';
                
                // Color aleatorio del gradiente
                const colors = [
                    '#182A41', '#304054', '#C3523E', '#344290', '#1D2D83'
                ];
                node.style.background = colors[Math.floor(Math.random() * colors.length)];
                
                // Posición aleatoria alrededor del círculo
                const angle = Math.random() * Math.PI * 2;
                const radius = 140 + Math.random() * 30;
                const x = Math.cos(angle) * radius;
                const y = Math.sin(angle) * radius;
                
                // Propiedades CSS personalizadas para la animación
                node.style.setProperty('--tx', `${x}px`);
                node.style.setProperty('--ty', `${y}px`);
                
                // Posición inicial
                node.style.left = `calc(50% + ${x}px)`;
                node.style.top = `calc(50% + ${y}px)`;
                
                // Retraso y duración
                const delay = Math.random() * 3;
                const duration = 2 + Math.random() * 2;
                node.style.animationDelay = `${delay}s`;
                node.style.animationDuration = `${duration}s`;
                
                loader.appendChild(node);
            }
        }

        // Actualizar porcentaje de carga
        function updateProgress() {
            const progressBar = document.getElementById('progressBar');
            const percentage = document.getElementById('percentage');
            let progress = 0;
            
            const interval = setInterval(() => {
                progress += Math.random() * 10 + 10; // Incremento variable
                if (progress > 100) progress = 100;
                
                progressBar.style.width = `${progress}%`;
                percentage.textContent = `${Math.round(progress)}%`;
                
                // Añadir un poco de brillo extra cuando avanza
                if (progress % 20 < 5) {
                    progressBar.style.boxShadow = '0 0 20px rgba(255, 255, 255, 0.4)';
                    setTimeout(() => {
                        progressBar.style.boxShadow = '0 0 15px rgba(255, 255, 255, 0.3)';
                    }, 200);
                }
                
                if (progress >= 100) {
                    clearInterval(interval);
                    
                    // Efecto final de completado
                    progressBar.style.boxShadow = '0 0 25px rgba(255, 255, 255, 0.6)';
                    
                    // Pequeña pausa antes de redirigir
                    setTimeout(() => {
                        @php
                            $route = session('redirect_route');
                            $params = session('route_params', []);
                            $finalUrl = Route::has($route) ? route($route, $params) : route('dashboard');
                        @endphp
                        window.location.href = "{{ $finalUrl }}";
                    }, 500);
                }
            }, 150); // Actualizar cada 150ms
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            createColorParticles();
            createDataNodes();
            updateProgress();
        });
    </script>
</body>
</html>
