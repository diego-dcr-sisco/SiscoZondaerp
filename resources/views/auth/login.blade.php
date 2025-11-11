@extends('layouts.app')
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
                            <img src="{{ asset('images/login_logo.png') }}" class="img-fluid" alt="Logo"
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
