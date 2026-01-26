<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ZONDA') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- STYLES -->
    <link rel="stylesheet" href="{{ asset('styles/app.min.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <!-- CDN -->
    @include('links.cdn')
</head>

<body class="m-0 d-flex flex-column" style="height: 100vh;">
    @auth
        @include('layouts.header')
        <div class="d-flex flex-column flex-md-row flex-grow-1" style="overflow: hidden;">
            <!-- Navbar primero en móvil, luego a la izquierda en desktop -->
            @unless (request()->is('dashboard', 'dashboard/*'))
                <div class="order-md-1 col-sm-auto col-lg-1 p-0 shadow bg-gradiant-navbar">
                    @include('layouts.navbar')
                </div>
            @endunless
            <main class="order-md-2 col-md p-0" style="overflow-y: auto;">
                @include('layouts.alert')
                @yield('content')
            </main>
        </div>
    @else
        <main class="flex-grow-1">
            @yield('login')
        </main>
    @endauth

    <script src="{{ asset('js/login.min.js') }}"></script>
</body>

</html>
