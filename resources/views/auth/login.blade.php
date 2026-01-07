{{-- @extends('layouts.app')
@section('login')
    <style>
        .bg-zonda {
            background: #182A41;
            background: linear-gradient(90deg, rgba(24, 42, 65, 1) 0%, rgba(48, 64, 84, 1) 15%, rgba(195, 82, 62, 1) 50%, rgba(52, 66, 144, 1) 85%, rgba(29, 45, 131, 1) 100%);
        }
        
    </style>
    <div class="container-fluid vh-100 bg-zonda p-0">
        <div class="row g-0 h-100 justify-content-center align-items-center">
            <!-- Contenedor único centrado -->
            <div class="col-lg-4 col-10">
                <div class="card shadow-lg border-0 animate__animated animate__fadeIn">
                    <!-- Logo en la parte superior del card -->
                    <div class="card-header bg-transparent border-0 p-5 pb-0">
                        <div class="text-center">
                            <img src="{{ asset('images/zonda/landscape_logo.png') }}" class="img-fluid" alt="Logo"
                                style="max-height: 120px;">
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    <span>{{ $error }}</span>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Campo de email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo/Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-dark">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="text" class="form-control border-dark" id="email" name="email"
                                        placeholder="Correo/Usuario" maxlength="50" required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text border-dark">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-dark" id="password" name="password"
                                        placeholder="*********" maxlength="50" required>
                                    <button class="btn btn-outline-dark  border-dark" type="button"
                                        onclick="togglePassword()">
                                        <i id="eye-icon-pass" class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="my-3">
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    {{ __('auth.login') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = $('#password');
            var eyeIcon = $('#eye-icon-pass');

            if (passwordInput.attr('type') == 'text') {
                passwordInput.attr('type', 'password');
                eyeIcon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
            } else {
                passwordInput.attr('type', 'text');
                eyeIcon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
            }
        }
    </script>
@endsection
--}}

@extends('layouts.app')
@section('login')
    <style>
        .bg-zonda {
            background: #182A41;
            background: linear-gradient(90deg, rgba(24, 42, 65, 1) 0%, rgba(48, 64, 84, 1) 15%, rgba(195, 82, 62, 1) 50%, rgba(52, 66, 144, 1) 85%, rgba(29, 45, 131, 1) 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Contenedor de animaciones de fondo */
        .bg-animation-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }
        
        /* Regalos animados */
        .gift {
            position: absolute;
            width: 40px;
            height: 40px;
            animation: float 6s ease-in-out infinite;
        }
        
        .gift-box {
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: #e74c3c;
            border-radius: 5px;
        }
        
        .gift-lid {
            position: absolute;
            width: 110%;
            height: 20px;
            background-color: #c0392b;
            top: -10px;
            left: -5%;
            border-radius: 3px;
        }
        
        .gift-bow {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: #2ecc71;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 50%;
        }
        
        .gift-bow::before,
        .gift-bow::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 10px;
            background-color: #2ecc71;
            top: 5px;
        }
        
        .gift-bow::before {
            left: -25px;
            transform: rotate(45deg);
        }
        
        .gift-bow::after {
            right: -25px;
            transform: rotate(-45deg);
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }
        
        /* Luces brillantes */
        .light {
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #fff;
            border-radius: 50%;
            box-shadow: 
                0 0 10px 2px rgba(255, 255, 255, 0.8),
                0 0 20px 4px rgba(255, 255, 255, 0.6);
            animation: twinkle 3s infinite alternate;
        }
        
        @keyframes twinkle {
            0% {
                opacity: 0.3;
                transform: scale(0.8);
            }
            100% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
        
        /* Estrellas suaves */
        .soft-star {
            position: absolute;
            width: 3px;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: soft-glow 4s infinite alternate;
        }
        
        @keyframes soft-glow {
            0% {
                opacity: 0.2;
                box-shadow: 0 0 5px 1px rgba(255, 255, 255, 0.3);
            }
            100% {
                opacity: 0.8;
                box-shadow: 0 0 10px 2px rgba(255, 255, 255, 0.6);
            }
        }
        
        /* Movimiento suave de burbujas */
        .bubble {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: bubble-float 15s linear infinite;
        }
        
        @keyframes bubble-float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100px) translateX(100px);
                opacity: 0;
            }
        }
        
        /* Card centrado sobre animaciones */
        .card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(3px);
        }
        
        /* Efecto sutil en el botón */
        .btn-primary {
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            animation: btn-shine 3s infinite;
        }
        
        @keyframes btn-shine {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .gift {
                width: 30px;
                height: 30px;
            }
            
            .light {
                width: 6px;
                height: 6px;
            }
        }
    </style>
    
    <div class="container-fluid vh-100 bg-zonda p-0">
        <!-- Animaciones de fondo -->
        <div class="bg-animation-container" id="bgAnimation">
            <!-- Se generarán con JavaScript -->
        </div>
        
        <div class="row g-0 h-100 justify-content-center align-items-center">
            <!-- Contenedor único centrado -->
            <div class="col-lg-4 col-10">
                <div class="card shadow-lg border-0 animate__animated animate__fadeIn">
                    <!-- Logo en la parte superior del card -->
                    <div class="card-header bg-transparent border-0 p-5 pb-0">
                        <div class="text-center">
                            <img src="{{ asset('images/zonda/landscape_logo.png') }}" class="img-fluid" alt="Logo"
                                style="max-height: 120px;">
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach ($errors->all() as $error)
                                    <span>{{ $error }}</span>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Campo de email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo/Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-dark">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="text" class="form-control border-dark" id="email" name="email"
                                        placeholder="Correo/Usuario" maxlength="50" required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text border-dark">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-dark" id="password" name="password"
                                        placeholder="*********" maxlength="50" required>
                                    <button class="btn btn-outline-dark  border-dark" type="button"
                                        onclick="togglePassword()">
                                        <i id="eye-icon-pass" class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="my-3">
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    {{ __('auth.login') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordInput = $('#password');
            var eyeIcon = $('#eye-icon-pass');

            if (passwordInput.attr('type') == 'text') {
                passwordInput.attr('type', 'password');
                eyeIcon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
            } else {
                passwordInput.attr('type', 'text');
                eyeIcon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
            }
        }
        
        // Generar regalos animados
        function createGifts() {
            const container = document.getElementById('bgAnimation');
            const giftColors = [
                { box: '#e74c3c', lid: '#c0392b', bow: '#2ecc71' }, // Rojo con verde
                { box: '#3498db', lid: '#2980b9', bow: '#9b59b6' }, // Azul con morado
                { box: '#2ecc71', lid: '#27ae60', bow: '#e74c3c' }, // Verde con rojo
                { box: '#f1c40f', lid: '#f39c12', bow: '#3498db' }, // Amarillo con azul
                { box: '#9b59b6', lid: '#8e44ad', bow: '#f1c40f' }  // Morado con amarillo
            ];
            
            for (let i = 0; i < 12; i++) {
                const gift = document.createElement('div');
                gift.className = 'gift';
                
                // Posición aleatoria
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                const delay = Math.random() * 6;
                const duration = 4 + Math.random() * 4;
                const scale = 0.7 + Math.random() * 0.6;
                const color = giftColors[Math.floor(Math.random() * giftColors.length)];
                
                // Estilos del contenedor
                gift.style.left = `${left}%`;
                gift.style.top = `${top}%`;
                gift.style.animationDelay = `${delay}s`;
                gift.style.animationDuration = `${duration}s`;
                gift.style.transform = `scale(${scale})`;
                
                // Crear partes del regalo
                const giftBox = document.createElement('div');
                giftBox.className = 'gift-box';
                giftBox.style.backgroundColor = color.box;
                
                const giftLid = document.createElement('div');
                giftLid.className = 'gift-lid';
                giftLid.style.backgroundColor = color.lid;
                
                const giftBow = document.createElement('div');
                giftBow.className = 'gift-bow';
                giftBow.style.backgroundColor = color.bow;
                
                giftBow.style.setProperty('--bow-color', color.bow);
                
                // Ensamblar regalo
                gift.appendChild(giftBox);
                gift.appendChild(giftLid);
                gift.appendChild(giftBow);
                
                container.appendChild(gift);
            }
        }
        
        // Generar luces brillantes
        function createLights() {
            const container = document.getElementById('bgAnimation');
            
            for (let i = 0; i < 25; i++) {
                const light = document.createElement('div');
                light.className = 'light';
                
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                const delay = Math.random() * 3;
                const duration = 1.5 + Math.random() * 3;
                const size = 4 + Math.random() * 8;
                
                light.style.left = `${left}%`;
                light.style.top = `${top}%`;
                light.style.animationDelay = `${delay}s`;
                light.style.animationDuration = `${duration}s`;
                light.style.width = `${size}px`;
                light.style.height = `${size}px`;
                
                container.appendChild(light);
            }
        }
        
        // Generar estrellas suaves
        function createSoftStars() {
            const container = document.getElementById('bgAnimation');
            
            for (let i = 0; i < 40; i++) {
                const star = document.createElement('div');
                star.className = 'soft-star';
                
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                const delay = Math.random() * 4;
                const duration = 2 + Math.random() * 3;
                
                star.style.left = `${left}%`;
                star.style.top = `${top}%`;
                star.style.animationDelay = `${delay}s`;
                star.style.animationDuration = `${duration}s`;
                
                container.appendChild(star);
            }
        }
        
        // Generar burbujas flotantes
        function createBubbles() {
            const container = document.getElementById('bgAnimation');
            
            for (let i = 0; i < 15; i++) {
                const bubble = document.createElement('div');
                bubble.className = 'bubble';
                
                const left = Math.random() * 100;
                const delay = Math.random() * 15;
                const duration = 10 + Math.random() * 10;
                const size = 15 + Math.random() * 25;
                
                bubble.style.left = `${left}%`;
                bubble.style.animationDelay = `${delay}s`;
                bubble.style.animationDuration = `${duration}s`;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                bubble.style.borderColor = `rgba(255, 255, 255, ${0.1 + Math.random() * 0.2})`;
                
                container.appendChild(bubble);
            }
        }
        
        // Inicializar todas las animaciones cuando cargue la página
        document.addEventListener('DOMContentLoaded', function() {
            createGifts();
            createLights();
            createSoftStars();
            createBubbles();
        });
    </script>
@endsection