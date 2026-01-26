@extends('layouts.app')
@section('login')
    <style>
        .bg-zonda {
            background: #182A41;
            background: linear-gradient(90deg, rgba(24, 42, 65, 1) 0%, rgba(48, 64, 84, 1) 15%, rgba(195, 82, 62, 1) 50%, rgba(52, 66, 144, 1) 85%, rgba(29, 45, 131, 1) 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Animación de gradiente en movimiento */
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .bg-animated {
            background: linear-gradient(-45deg, #182A41, #304054, #C3523E, #344290, #1D2D83);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
        }
        
        /* Partículas animadas en el fondo */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Efecto de brillo sutil */
        .glow-effect {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(195, 82, 62, 0.2) 0%, rgba(195, 82, 62, 0) 70%);
            filter: blur(40px);
            animation: glowMove 8s ease-in-out infinite alternate;
            z-index: 1;
        }
        
        @keyframes glowMove {
            0% {
                transform: translate(-100px, -100px);
            }
            100% {
                transform: translate(100px, 100px);
            }
        }
        
        /* Contenedor del formulario con z-index superior */
        .form-container {
            position: relative;
            z-index: 2;
        }
        
        /* Animación de entrada para el card */
        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .card-animated {
            animation: cardEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        /* Efecto hover en el botón */
        .btn-login {
            background: linear-gradient(45deg, #C3523E, #344290);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(195, 82, 62, 0.3);
        }
        
        .btn-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::after {
            left: 100%;
        }
        
        /* Efecto en los inputs */
        .input-group:focus-within {
            transform: translateY(-1px);
            transition: transform 0.2s ease;
        }
        
        .input-group:focus-within .input-group-text {
            background-color: rgba(195, 82, 62, 0.1);
            border-color: #C3523E;
        }
        
        .form-control:focus {
            border-color: #C3523E;
            box-shadow: 0 0 0 0.25rem rgba(195, 82, 62, 0.25);
        }
    </style>

    <div class="container-fluid vh-100 bg-zonda bg-animated p-0 position-relative">
        <!-- Partículas animadas -->
        <div class="particles" id="particles"></div>
        
        <!-- Efectos de brillo -->
        <div class="glow-effect" style="top: 10%; left: 10%;"></div>
        <div class="glow-effect" style="top: 60%; right: 10%; animation-delay: -4s; background: radial-gradient(circle, rgba(52, 66, 144, 0.2) 0%, rgba(52, 66, 144, 0) 70%);"></div>
        
        <div class="row g-0 h-100 justify-content-center align-items-center">
            <!-- Contenedor único centrado -->
            <div class="col-lg-4 col-10 form-container">
                <div class="card shadow-lg border-0 card-animated" style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95);">
                    <!-- Logo en la parte superior del card -->
                    <div class="card-header bg-transparent border-0 p-5 pb-0">
                        <div class="text-center">
                            <img src="{{ asset('images/zonda/landscape_logo.png') }}" class="img-fluid" alt="Logo"
                                style="max-height: 120px; filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));">
                        </div>
                    </div>

                    <div class="card-body p-4 pt-5">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                @foreach ($errors->all() as $error)
                                    <span>{{ $error }}</span>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <!-- Campo de email -->
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">Correo/Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-dark">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input type="text" class="form-control border-dark" id="email" name="email"
                                        placeholder="Correo/Usuario" maxlength="50" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text border-dark">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control border-dark" id="password" name="password"
                                        placeholder="*********" maxlength="50" required>
                                    <button class="btn btn-outline-dark border-dark" type="button"
                                        onclick="togglePassword()" id="togglePasswordBtn">
                                        <i id="eye-icon-pass" class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="my-3">
                                <button type="submit" class="btn btn-login text-white w-100 mt-3 py-3 fw-bold">
                                    <span class="position-relative">{{ __('auth.login') }}</span>
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
            var toggleBtn = $('#togglePasswordBtn');

            if (passwordInput.attr('type') == 'text') {
                passwordInput.attr('type', 'password');
                eyeIcon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
                toggleBtn.removeClass('btn-dark').addClass('btn-outline-dark');
            } else {
                passwordInput.attr('type', 'text');
                eyeIcon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
                toggleBtn.removeClass('btn-outline-dark').addClass('btn-dark');
            }
        }

        // Crear partículas animadas
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Tamaño aleatorio
                const size = Math.random() * 60 + 20;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Posición inicial aleatoria
                particle.style.left = `${Math.random() * 100}%`;
                
                // Retraso de animación aleatorio
                particle.style.animationDelay = `${Math.random() * 20}s`;
                
                // Duración de animación aleatoria
                const duration = 15 + Math.random() * 25;
                particle.style.animationDuration = `${duration}s`;
                
                // Opacidad aleatoria
                const opacity = 0.1 + Math.random() * 0.2;
                particle.style.opacity = opacity;
                
                // Color aleatorio basado en la paleta
                const colors = [
                    'rgba(24, 42, 65, 0.1)',
                    'rgba(195, 82, 62, 0.1)',
                    'rgba(52, 66, 144, 0.1)',
                    'rgba(29, 45, 131, 0.1)'
                ];
                particle.style.background = colors[Math.floor(Math.random() * colors.length)];
                
                particlesContainer.appendChild(particle);
            }
        }

        // Efecto de entrada para inputs
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Efecto de enfoque en inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('input-focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('input-focused');
                });
            });
            
            // Efecto al enviar el formulario
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';
                submitBtn.disabled = true;
            });
        });
    </script>
@endsection