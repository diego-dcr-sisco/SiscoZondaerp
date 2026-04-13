<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del sistema — SISCOPLAGAS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --deep-space-blue:  #012640;
            --deep-navy:        #02265A;
            --true-cobalt:      #0A2986;
            --indigo-velvet:    #512A87;
            --velvet-purple:    #793775;
            --dusty-mauve:      #B74453;
            --fiery-terracotta: #DD513A;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Fondo animado – igual que el login ─────────── */
        body {
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(-45deg,
                var(--deep-space-blue),
                var(--deep-navy),
                var(--fiery-terracotta),
                var(--true-cobalt),
                var(--indigo-velvet));
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        @keyframes gradientMove {
            0%   { background-position: 0%   50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0%   50%; }
        }

        /* ── Partículas ─────────────────────────────────── */
        #particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: floatUp linear infinite;
        }

        @keyframes floatUp {
            0%   { transform: translateY(100vh) translateX(0)    rotate(0deg);   opacity: 0; }
            10%  { opacity: .2; }
            90%  { opacity: .2; }
            100% { transform: translateY(-120px) translateX(80px) rotate(360deg); opacity: 0; }
        }

        /* ── Glows ──────────────────────────────────────── */
        .glow {
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            z-index: 1;
        }

        .glow-left {
            top: 8%; left: 8%;
            background: radial-gradient(circle, rgba(221,81,58,.22) 0%, transparent 70%);
            animation: glowPulse 9s ease-in-out infinite alternate;
        }

        .glow-right {
            bottom: 8%; right: 8%;
            background: radial-gradient(circle, rgba(81,42,135,.22) 0%, transparent 70%);
            animation: glowPulse 9s ease-in-out infinite alternate-reverse;
        }

        @keyframes glowPulse {
            from { transform: translate(-50px, -50px); }
            to   { transform: translate( 50px,  50px); }
        }

        /* ── Card wrapper ───────────────────────────────── */
        .card-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            animation: cardIn .8s cubic-bezier(.34,1.56,.64,1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(28px) scale(.95); }
            to   { opacity: 1; transform: translateY(0)    scale(1);   }
        }

        /* ── Card blanco – igual al login ───────────────── */
        .card {
            background: rgba(255, 255, 255, 0.97);
            border: none;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(1, 38, 64, .55);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        /* ── Logo ───────────────────────────────────────── */
        .card-logo {
            padding: 30px 32px 0;
            text-align: center;
        }

        .card-logo img {
            max-height: 88px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,.1));
        }

        /* ── Banner de alerta ───────────────────────────── */
        .alert-banner {
            margin: 22px 26px 0;
            background: linear-gradient(90deg, var(--dusty-mauve), var(--fiery-terracotta));
            border-radius: 10px;
            padding: 13px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-banner i {
            font-size: 1.35rem;
            color: #fff;
            flex-shrink: 0;
        }

        .alert-banner span {
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.3;
        }

        /* ── Cuerpo ─────────────────────────────────────── */
        .card-body {
            padding: 20px 26px 26px;
        }

        .subtitle {
            color: #4b5563;
            font-size: .92rem;
            line-height: 1.65;
            margin-bottom: 18px;
            margin-top: 4px;
        }

        /* ── Bloque de contacto ─────────────────────────── */
        .contact-block {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .contact-block-header {
            background: linear-gradient(90deg, var(--deep-navy), var(--true-cobalt));
            color: rgba(255,255,255,.75);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            padding: 7px 16px;
        }

        .contact-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-top: 1px solid #f1f1f1;
            color: #1f2937;
            font-size: .9rem;
        }

        .contact-row:first-of-type { border-top: none; }

        .c-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .c-dept  { background: rgba(10,41,134,.1);  color: var(--true-cobalt);   }
        .c-mail  { background: rgba(183,68,83,.1);  color: var(--dusty-mauve);  }
        .c-super { background: rgba(81,42,135,.1);  color: var(--indigo-velvet);}

        .contact-row a {
            color: var(--dusty-mauve);
            font-weight: 600;
            text-decoration: none;
        }
        .contact-row a:hover { text-decoration: underline; }

        /* ── Detalle técnico ────────────────────────────── */
        .error-detail {
            background: #f9fafb;
            border-left: 3px solid var(--fiery-terracotta);
            border-radius: 6px;
            padding: 10px 14px;
            font-family: Consolas, Monaco, "Courier New", monospace;
            font-size: .78rem;
            color: #6b7280;
            white-space: pre-wrap;
            word-break: break-word;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* ── Botón volver – estilo login ────────────────── */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(45deg, var(--dusty-mauve), var(--true-cobalt));
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            border: none;
            border-radius: 8px;
            padding: 11px 22px;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-back::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
            transition: left .45s;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(183,68,83,.3);
            color: #fff;
            text-decoration: none;
        }

        .btn-back:hover::after { left: 100%; }

        /* ── Footer ─────────────────────────────────────── */
        .card-footer-bar {
            border-top: 1px solid #f1f1f1;
            background: #fafafa;
            text-align: center;
            padding: 11px 26px;
            font-size: .78rem;
            color: #9ca3af;
        }
    </style>
</head>

<body>

    <!-- Partículas flotantes -->
    <div id="particles"></div>

    <!-- Efectos de brillo -->
    <div class="glow glow-left"></div>
    <div class="glow glow-right"></div>

    <div class="card-wrap">
        <div class="card">

            <!-- Logo -->
            <div class="card-logo">
                <img src="{{ asset('images/zonda/landscape_logo.png') }}" alt="SISCOPLAGAS">
            </div>

            <!-- Banner de error -->
            <div class="alert-banner">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Ocurrió un error en el sistema</span>
            </div>

            <div class="card-body">

                <p class="subtitle">
                    No fue posible completar la operación solicitada. Por favor comunícate con el área
                    de soporte para que puedan ayudarte a resolver este inconveniente.
                </p>

                <!-- Bloque de contacto -->
                <div class="contact-block">
                    <div class="contact-block-header">Contacto de soporte</div>

                    <div class="contact-row">
                        <span class="c-icon c-dept">
                            <i class="bi bi-pc-display"></i>
                        </span>
                        Departamento de Sistemas &mdash; <strong>&nbsp;SISCOPLAGAS</strong>
                    </div>

                    <div class="contact-row">
                        <span class="c-icon c-mail">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <a href="mailto:dev.siscoplagas@gmail.com">dev.siscoplagas@gmail.com</a>
                    </div>

                    <div class="contact-row">
                        <span class="c-icon c-super">
                            <i class="bi bi-person-badge-fill"></i>
                        </span>
                        Tu <strong>&nbsp;Supervisor de Calidad</strong>
                    </div>
                </div>

                @if (!empty($error_message))
                    <div class="error-detail">{{ $error_message }}</div>
                @endif

                <a class="btn-back" href="{{ url()->previous() }}">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

            </div>

            <div class="card-footer-bar">
                SISCOPLAGAS &copy; {{ date('Y') }} &mdash; Sistema ERP
            </div>

        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('particles');
            const colors = [
                'rgba(1,38,64,.12)',
                'rgba(221,81,58,.12)',
                'rgba(10,41,134,.12)',
                'rgba(81,42,135,.12)',
            ];
            for (let i = 0; i < 22; i++) {
                const el = document.createElement('div');
                el.className = 'particle';
                const size = 20 + Math.random() * 58;
                el.style.cssText = [
                    `width:${size}px`,
                    `height:${size}px`,
                    `left:${Math.random() * 100}%`,
                    `background:${colors[Math.floor(Math.random() * colors.length)]}`,
                    `animation-duration:${15 + Math.random() * 20}s`,
                    `animation-delay:-${Math.random() * 20}s`,
                ].join(';');
                container.appendChild(el);
            }
        })();
    </script>

</body>

</html>
