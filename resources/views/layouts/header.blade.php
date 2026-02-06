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

    /* Contenedor del dropdown con altura fija y scroll */
    .notifications-dropdown {
        width: 400px;
        max-height: 500px;
        overflow-y: auto;
        position: relative;
    }

    /* Contenedor para los botones fijos */
    .dropdown-footer {
        position: sticky;
        bottom: 0;
        background-color: #212529;
        z-index: 10;
        padding: 0.5rem;
        border-top: 1px solid #495057;
        margin-top: auto;
    }

    /* Asegurar que el contenido sea scrollable */
    .dropdown-content {
        max-height: 350px;
        overflow-y: auto;
        padding: 0.5rem;
    }

    /* Estilos para los botones */
    .sticky-buttons {
        display: grid;
        gap: 0.5rem;
        padding-top: 0.5rem;
    }

    /* Agregar cursor pointer a las notificaciones */
    .notification-item {
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
        background-color: #ffffff;
        cursor: pointer;
    }

    .notification-item:hover {
        border-left-color: #ffc107;
        background-color: #ebebeb;
        transform: translateY(-1px);
    }

    /* Estilos para el modal */
    .notification-modal .modal-content {
        border-radius: 10px;
    }

    .notification-modal .modal-header {
        background: linear-gradient(90deg, rgba(24, 42, 65, 1) 0%, rgba(25, 42, 89, 1) 60%, rgba(74, 46, 132, 1) 100%);
        color: white;
        border-bottom: none;
    }

    .notification-modal .modal-body {
        max-height: 60vh;
        overflow-y: auto;
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
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-lg-end notifications-dropdown">
                                <li>
                                    <div class="dropdown-header bg-warning text-dark">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        <strong>CRM Seguimientos Pendientes</strong>
                                    </div>
                                </li>
                                <li>
                                    <div class="dropdown-content">
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
                                            <!-- Dentro del foreach de trackings_data -->
                                            @foreach ($trackings_data as $tracking)
                                                <div class="notification-item p-2 mb-2 rounded"
                                                    data-notification-id="{{ $tracking['id'] }}"
                                                    data-tracking-id="{{ $tracking['tracking_id'] ?? $tracking['id'] }}"
                                                    data-customer-name="{{ $tracking['customer_name'] ?? 'Cliente' }}"
                                                    data-title="{{ $tracking['title'] ?? '' }}"
                                                    data-description="{{ $tracking['description'] ?? '' }}"
                                                    data-next-date="{{ $tracking['next_date'] ?? 'Sin fecha' }}"
                                                    data-customer-phone="{{ $tracking['customer_phone'] ?? '' }}"
                                                    data-status="{{ $tracking['status'] ?? 'active' }}">
                                                    <!-- El resto del contenido permanece igual -->
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <!-- ... contenido existente ... -->
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center bg-light rounded p-2">
                                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                                <p class="text-muted mt-2 mb-0">No hay seguimientos pendientes</p>
                                            </div>
                                        @endif
                                    </div>
                                </li>

                                <!-- Sección fija con botones -->
                                <li class="d-flex justify-content-between m-2">
                                    <a href="{{ route('crm.agenda') }}" class="btn btn-primary btn-sm m-0">
                                        <i class="bi bi-calendar-week me-2"></i>
                                        Ir a la Agenda
                                    </a>
                                    <a href="{{ route('crm.tracking') }}" class="btn btn-success btn-sm m-0">
                                        <i class="bi bi-list-check me-2"></i>
                                        Ver todos los pendientes
                                    </a>
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


<!-- Modal para mostrar detalles de la notificación -->
<div class="modal fade notification-modal" id="notificationDetailModal" tabindex="-1"
    aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationDetailModalLabel">
                    <i class="bi bi-info-circle me-2"></i>
                    Detalles del Seguimiento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Contenido dinámico se cargará aquí -->
                <div id="notificationDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted">Cargando información...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cerrar
                </button>
                <a href="#" id="goToTrackingBtn" class="btn btn-primary">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Ir al Seguimiento
                </a>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript para manejar el modal -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del modal
        const notificationDetailModal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
        const notificationDetailContent = document.getElementById('notificationDetailContent');
        const goToTrackingBtn = document.getElementById('goToTrackingBtn');
        const modalTitle = document.getElementById('notificationDetailModalLabel');

        // Mapeo de estados para mostrar en el modal
        const statusMap = {
            'active': {
                color: 'success',
                text: 'Activo',
                icon: 'bi-check-circle'
            },
            'completed': {
                color: 'primary',
                text: 'Completado',
                icon: 'bi-check-circle-fill'
            },
            'canceled': {
                color: 'danger',
                text: 'Cancelado',
                icon: 'bi-x-circle-fill'
            }
        };

        // Agregar evento click a todas las notificaciones
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Prevenir que se cierre el dropdown si está abierto
                if (!e.target.closest('a') && !e.target.closest('button')) {
                    // Obtener datos de la notificación desde atributos data
                    const notificationId = this.getAttribute('data-notification-id');
                    const customerName = this.getAttribute('data-customer-name');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const nextDate = this.getAttribute('data-next-date');
                    const customerPhone = this.getAttribute('data-customer-phone');
                    const status = this.getAttribute('data-status');
                    const trackingId = this.getAttribute('data-tracking-id');

                    // Actualizar botón de "Ir al Seguimiento"
                    goToTrackingBtn.href = `/crm/tracking/${trackingId || notificationId}`;

                    // Crear contenido del modal
                    const statusInfo = statusMap[status] || {
                        color: 'secondary',
                        text: 'Pendiente',
                        icon: 'bi-clock'
                    };

                    const content = `
                    <div class="notification-detail">
                        <!-- Encabezado con nombre e ID -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="mb-1">${customerName || 'Cliente'}</h4>
                                <small class="text-muted">ID: #${notificationId}</small>
                            </div>
                            <span class="badge bg-${statusInfo.color} fs-6">
                                <i class="bi ${statusInfo.icon} me-1"></i>
                                ${statusInfo.text}
                            </span>
                        </div>
                        
                        <!-- Título -->
                        <div class="mb-4">
                            <h5 class="text-primary">
                                <i class="bi bi-card-heading me-2"></i>
                                Título
                            </h5>
                            <p class="fs-5">${title || 'Sin título'}</p>
                        </div>
                        
                        <!-- Descripción completa -->
                        <div class="mb-4">
                            <h5 class="text-primary">
                                <i class="bi bi-text-paragraph me-2"></i>
                                Descripción
                            </h5>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">${description || 'Sin descripción'}</p>
                            </div>
                        </div>
                        
                        <!-- Información de contacto y fechas -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5 class="text-primary">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    Próxima Fecha
                                </h5>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-check fs-4 text-primary me-3"></i>
                                    <div>
                                        <p class="fs-5 mb-0">${nextDate || 'Sin fecha programada'}</p>
                                        <small class="text-muted">Fecha de seguimiento</small>
                                    </div>
                                </div>
                            </div>
                            
                            ${customerPhone ? `
                            <div class="col-md-6 mb-3">
                                <h5 class="text-primary">
                                    <i class="bi bi-telephone me-2"></i>
                                    Contacto
                                </h5>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-telephone-fill fs-4 text-success me-3"></i>
                                    <div>
                                        <a href="tel:${customerPhone}" class="fs-5 mb-0 text-decoration-none">
                                            ${customerPhone}
                                        </a>
                                        <br>
                                        <small class="text-muted">Teléfono del cliente</small>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        
                        <!-- Información adicional si la hay -->
                        <div class="alert alert-info mt-4">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                <div>
                                    <strong>Información:</strong>
                                    <p class="mb-0 mt-1">Este seguimiento requiere tu atención. Puedes marcarlo como completado desde la agenda de CRM.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                    // Actualizar contenido del modal
                    notificationDetailContent.innerHTML = content;

                    // Mostrar el modal
                    notificationDetailModal.show();

                    // Cerrar el dropdown de notificaciones si está abierto
                    const dropdown = document.querySelector('.notifications-dropdown');
                    const dropdownInstance = bootstrap.Dropdown.getInstance(document
                        .querySelector('[data-bs-toggle="dropdown"]'));
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    }
                }
            });
        });
    });
</script>
