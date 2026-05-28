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

    <style>
        body {
            background-color: #f4f6f9;
        }

        .border.rounded.shadow,
        .border.rounded.shadow-sm {
            background-color: #fff;
        }

        *,
        *::before,
        *::after {
            scroll-behavior: auto !important;
            transition: none !important;
        }

        *:not(.spinner-border):not(.spinner-grow),
        *:not(.spinner-border):not(.spinner-grow)::before,
        *:not(.spinner-border):not(.spinner-grow)::after {
            animation: none !important;
        }

        /* ── Desktop: sidebar siempre visible ── */
        @media (min-width: 768px) {
            #sidebar {
                display: flex !important;
                flex-direction: column;
                height: 100%;
                overflow-y: auto;
            }
        }

        /* ── Móvil: sidebar como drawer oculto ── */
        @media (max-width: 767.98px) {
            #sidebar {
                position: fixed !important;
                top: 0;
                left: -240px;
                /* oculto fuera de pantalla */
                width: 120px !important;
                min-width: 120px !important;
                height: 100vh !important;
                z-index: 1050;
                overflow-y: auto;
            }

            #sidebar.active {
                left: 0;
                /* visible al abrir */
            }
        }

        /* ── Overlay oscuro detrás del drawer ── */
        #sidebarOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        #sidebarOverlay.active {
            display: block;
        }

        .p-collapse-button {
            padding: 0px 5px 0px 5px; 
        }
    </style>
</head>

<body class="m-0 d-flex flex-column" style="height: 100vh;">
    @auth
        @include('layouts.header')

        <div class="d-flex flex-column flex-md-row flex-grow-1" style="overflow: hidden;">

            @unless (request()->is('dashboard', 'dashboard/*'))
                <!-- ✅ SIDEBAR -->
                <div class="order-md-1 p-0 shadow bg-gradiant-navbar" id="sidebar"
                    style="width: 120px; min-width: 120px; flex-shrink: 0;">
                    @include('layouts.navbar')
                </div>

                <!-- ✅ BOTÓN HAMBURGUESA solo en móvil -->
                <button class="btn btn-dark d-md-none position-fixed" id="toggleSidebar"
                    style="bottom: 20px; right: 20px; z-index: 1060; border-radius: 50%; width:50px; height:50px;">
                    ☰
                </button>
            @endunless

            <!-- ✅ CONTENIDO PRINCIPAL -->
            <main class="order-md-2 flex-grow-1 p-0" style="overflow-y: auto; min-width: 0;">
                @include('layouts.alert')
                @yield('content')
            </main>

        </div>
    @else
        <main class="flex-grow-1">
            @yield('login')
        </main>
    @endauth

    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');

            if (!sidebar || !toggleBtn) return;

            // Crear overlay dinámicamente
            const overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            document.body.appendChild(overlay);

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        })();

        (function() {
            function isAdvancedSearchCard(card) {
                const title = card.querySelector('.card-title');

                return title && title.textContent
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .includes('busqueda avanzada');
            }

            function openAdvancedSearchCollapses() {
                document.querySelectorAll('.card').forEach((card) => {
                    if (!isAdvancedSearchCard(card)) return;

                    card.querySelectorAll('.collapse').forEach((collapseElement) => {
                        window.bootstrap?.Collapse.getOrCreateInstance(collapseElement, {
                            toggle: false
                        });

                        collapseElement.classList.remove('collapsing');
                        collapseElement.classList.add('collapse');
                        collapseElement.classList.add('show');
                        collapseElement.style.height = '';
                    });

                    card.querySelectorAll('[data-bs-toggle="collapse"]').forEach((button) => {
                        button.classList.remove('collapsed');
                        button.setAttribute('aria-expanded', 'true');
                    });
                });
            }

            openAdvancedSearchCollapses();
            document.addEventListener('DOMContentLoaded', openAdvancedSearchCollapses);
            window.addEventListener('pageshow', openAdvancedSearchCollapses);
        })();
    </script>
</body>

</html>
