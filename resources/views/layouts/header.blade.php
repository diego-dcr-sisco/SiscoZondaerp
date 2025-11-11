<style>
    .navbar-brand img {
        max-width: 80px;
        height: auto;
    }

    /* Ajustes para dispositivos móviles */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background-color: #343a40;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .dropdown-menu {
            margin-left: 15px;
            width: calc(100% - 30px);
        }
    }

    /* Mejoras visuales para el botón de toggle */
    .navbar-toggler {
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .bg-gradiant-header {
        /*background: #182A41;
        background: linear-gradient(90deg, rgba(24, 42, 65, 1) 0%, rgba(48, 64, 84, 1) 100%);*/
        background: #182A41;
background: linear-gradient(90deg, rgba(24, 42, 65, 1) 0%, rgba(25, 42, 89, 1) 60%, rgba(74, 46, 132, 1) 100%);
    }

    .bg-gradiant-navbar {
        /*background: #18181B;*/
        background: #182A41;
/*background: linear-gradient(180deg, rgba(24, 24, 27, 1) 30%, rgba(63, 41, 109, 1) 80%, rgba(100, 57, 112, 1) 100%);*/
    }

    /* Estilos para la notificación de la campana */
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .notification-item {
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
        background-color: #ffffff;
    }

    .notification-item:hover {
        border-left-color: #ffc107;
        background-color: #ebebeb;
    }

    .notification-priority-high {
        border-left-color: #dc3545;
    }

    .notification-priority-medium {
        border-left-color: #fd7e14;
    }

    .notification-priority-low {
        border-left-color: #198754;
    }

    .header-auth {
        color: #FF8904;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-gradiant-header px-3 mb-0 p-1">
    <div class="container-fluid">
        <!-- Logo del menú -->
        <a href="{{ !auth()->check() ? '/' : (!auth()->user()->hasRole('Cliente') ? route('loading-erp') : route('client.index', ['section' => 1])) }}"
            class="navbar-brand">
            <img src="{{ asset('images/zonda/isotype_logo.png') }}" alt="Logo" class="img-fluid">
        </a>

        <!-- Botón toggle para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenido colapsable -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
            <ul class="navbar-nav">
                @auth
                    <!-- Menú de Administración (solo para usuarios tipo 1) -->
                    @if (auth()->user()->type_id == 1)
                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold text-light" data-bs-toggle="dropdown" href="#" role="button"
                                aria-expanded="false">
                                Menu
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-lg-end">
                                <li><a class="dropdown-item text-light" href="{{ route('crm.agenda') }}"><i
                                            class="bi bi-people-fill"></i> CRM</a>
                                </li>
                                <li><a class="dropdown-item text-light" href="{{ route('planning.schedule') }}"><i
                                            class="bi bi-calendar-fill"></i>
                                        Planificación</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('quality.customers') }}"><i
                                            class="bi bi-gear-fill"></i>
                                        Calidad</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('stock.index') }}"><i
                                            class="bi bi-box-fill"></i>
                                        Almacen</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('rrhh', ['section' => 1]) }}"><i
                                            class="bi bi-file-person-fill"></i>
                                        RRHH</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('invoices.index') }}"><i
                                            class="bi bi-stack"></i>
                                        Facturación</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('client.index') }}"><i
                                            class="bi bi-person-workspace"></i>
                                        Sistema de clientes</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold text-light" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Administración
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-lg-end">
                                <li><a class="dropdown-item text-light" href="{{ route('user.index', ['type' => 1]) }}"><i
                                            class="bi bi-person-fill"></i>
                                        Usuarios</a></li>
                                <li><a class="dropdown-item text-light"
                                        href="{{ route('customer.index', ['type' => 1, 'page' => 1]) }}"><i
                                            class="bi bi-people-fill"></i>
                                        Clientes</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('branch.index') }}"><i
                                            class="bi bi-globe-americas"></i>
                                        Sucursales</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('comercial-zones.index') }}"><i
                                            class="bi bi-geo-alt-fill"></i>
                                        Zonas
                                        comerciales</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-light" href="{{ route('service.index') }}"><i
                                            class="bi bi-gear-fill"></i>
                                        Servicios</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('product.index') }}"><i
                                            class="bi bi-box-fill"></i>
                                        Productos</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('pest.index') }}"><i
                                            class="bi bi-bug-fill"></i>
                                        Plagas</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-light" href="{{ route('order.index') }}"><i
                                            class="bi bi-nut-fill"></i>
                                        Ordenes de servicio</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('contract.index') }}"><i
                                            class="bi bi-calendar-fill"></i>
                                        Contratos</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('point.index') }}"><i
                                            class="bi bi-hand-index-fill"></i>
                                        Puntos de control</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold text-white fw-bold position-relative" data-bs-toggle="dropdown"
                                href="#" role="button" aria-expanded="false">
                                <i class="bi bi-bell-fill"></i>
                                @php
                                    $count_trackings = session('count_trackings', 0);
                                    $trackings_data = session('trackings_data', []);
                                    $statusMap = [
                                        'active' => ['color' => 'success', 'text' => 'Activo'],
                                        'completed' => ['color' => 'primary', 'text' => 'Completado'],
                                        'canceled' => ['color' => 'danger', 'text' => 'Cancelado'],
                                    ];
                                @endphp
                                @if ($count_trackings > 0)
                                    <span class="notification-badge">{{ $count_trackings }}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-lg-end"
                                style="width: 400px; max-height: 500px; overflow-y: auto;">
                                <li>
                                    <div class="dropdown-header bg-warning text-dark">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        <strong>CRM Seguimientos Pendientes</strong>
                                    </div>
                                </li>
                                <li>
                                    <div class="px-3 py-2">
                                        <!-- Alerta de seguimientos pendientes -->
                                        <div class="alert alert-warning border-0 shadow-sm mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                                <div>
                                                    <h6 class="mb-0 text-dark">{{ $count_trackings }} Pendientes</h6>
                                                    <small class="text-dark">Seguimientos que requieren atención</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lista de seguimientos -->
                                        @if (count($trackings_data) > 0)
                                            @foreach ($trackings_data as $tracking)
                                                <div
                                                    class="notification-item notification-priority-medium p-2 mb-2 rounded">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 text-dark">
                                                                {{ $tracking['customer'] ?? 'Cliente' }}</h6>
                                                            <p class="mb-1 small text-muted">
                                                                {{ $tracking['title'] ?? 'Seguimiento' }}</p>
                                                            <small class="text-primary">
                                                                <i class="bi bi-calendar-event me-1"></i>
                                                                {{ $tracking['next_date'] ?? 'Sin fecha' }}
                                                            </small>
                                                        </div>
                                                        <span
                                                            class="badge bg-{{ $statusMap[$tracking['status']]['color'] ?? 'secondary' }} ms-2">
                                                            {{ $statusMap[$tracking['status']]['text'] ?? 'Pendiente' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center bg-light rounded py-3">
                                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                                <p class="text-muted mt-2 mb-0">No hay seguimientos pendientes</p>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <div class="d-grid gap-2 px-3 pb-2">
                                        <a href="{{ route('crm.agenda') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-calendar-week me-2"></i>
                                            Ir a la Agenda
                                        </a>
                                        <a href="{{ route('crm.tracking') }}" class="btn btn-success btn-sm">
                                            <i class="bi bi-list-check me-2"></i>
                                            Ver todos los pendientes
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold text-white fw-bold" data-bs-toggle="dropdown" href="#"
                                role="button" aria-expanded="false">
                                <i class="bi bi-gear-fill"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-lg-end">
                                <li><a class="dropdown-item text-light" href="{{ route('config.appearance') }}">
                                        <i class="bi bi-palette2"></i>
                                        Configurar reporte</a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link fw-bold header-auth fw-bold" data-bs-toggle="dropdown" href="#"
                            role="button" aria-expanded="false">
                            <i class="bi bi-person-fill"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-lg-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i>
                                        Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-light" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
