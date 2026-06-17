@extends('layouts.app')

@section('content')
    @php
        $activeSection = (int) $section;
        $sectionTitle = [
            1 => 'Personal',
            2 => 'Documentos faltantes',
            3 => 'Vencimientos',
        ][$activeSection] ?? 'Personal';
        $statsCards = [
            [
                'label' => 'Personal activo',
                'value' => $stats['active'],
                'icon' => 'bi-person-check-fill',
                'tone' => 'success',
            ],
            [
                'label' => 'Usuarios pendientes',
                'value' => $stats['pending_users'],
                'icon' => 'bi-person-fill-exclamation',
                'tone' => 'warning',
            ],
            [
                'label' => 'Docs faltantes',
                'value' => $stats['missing_files'],
                'icon' => 'bi-file-earmark-x-fill',
                'tone' => 'danger',
            ],
            [
                'label' => 'Por vencer',
                'value' => $stats['expiring_files'],
                'icon' => 'bi-calendar2-week-fill',
                'tone' => 'primary',
            ],
        ];
    @endphp

    @include('components.page-header', [
        'title' => 'Recursos Humanos - ' . $sectionTitle,
        'icon' => 'bi-person-vcard-fill',
        'actionRoute' => route('user.create', ['type' => 1]),
        'actionText' => 'Crear usuario',
        'actionIcon' => 'bi-person-plus-fill',
    ])

    <div class="container-fluid py-3 rrhh-dashboard">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="text-muted">Control de expedientes, vencimientos y estado del personal.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people-fill"></i> Usuarios
                </a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            @foreach ($statsCards as $card)
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="card h-100 rrhh-stat-card border-{{ $card['tone'] }}">
                        <div class="card-body d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <div class="text-muted small fw-semibold">{{ $card['label'] }}</div>
                                <div class="fs-2 fw-bold lh-1">{{ number_format($card['value']) }}</div>
                            </div>
                            <span class="rrhh-stat-icon text-{{ $card['tone'] }}">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-8 col-12">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h2 class="h6 fw-bold mb-0">
                                <i class="bi bi-speedometer2"></i> Resumen operativo
                            </h2>
                            <span class="badge text-bg-light border">{{ number_format($stats['employees']) }} colaboradores</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4 col-12">
                                <div class="rrhh-mini-metric">
                                    <span class="text-muted">Expedientes sin alertas</span>
                                    <strong class="text-success">{{ number_format($stats['complete_users']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="rrhh-mini-metric">
                                    <span class="text-muted">Documentos vencidos</span>
                                    <strong class="text-danger">{{ number_format($stats['expired_files']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="rrhh-mini-metric">
                                    <span class="text-muted">Ventana de vencimiento</span>
                                    <strong>{{ request('days', 30) }} dias</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="text-muted small fw-semibold mb-2">Distribucion por departamento</div>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($departmentStats as $department)
                                    <span class="badge text-bg-light border rrhh-department-badge">
                                        {{ $department->name }} <strong>{{ $department->users_count }}</strong>
                                    </span>
                                @empty
                                    <span class="text-muted">Sin departamentos registrados.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-12">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h2 class="h6 fw-bold mb-0">
                            <i class="bi bi-alarm-fill"></i> Proximos vencimientos
                        </h2>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($upcomingFiles as $file)
                            <a href="{{ route('user.edit', ['id' => $file->user_id]) }}"
                                class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-semibold text-truncate">
                                        {{ $file->filename->name ?? $file->file_name ?? 'Documento' }}
                                    </span>
                                    <span class="badge text-bg-warning">
                                        {{ \Carbon\Carbon::parse($file->expirated_at)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="small text-muted text-truncate">{{ $file->user->name ?? 'Usuario no disponible' }}</div>
                            </a>
                        @empty
                            <div class="list-group-item text-muted">No hay vencimientos en la ventana actual.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white">
                <ul class="nav nav-pills rrhh-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeSection === 1 ? 'active' : '' }}"
                            href="{{ route('rrhh', array_merge(request()->except(['users_page', 'files_page']), ['section' => 1])) }}">
                            <i class="bi bi-person-lines-fill"></i> Personal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeSection === 2 ? 'active' : '' }}"
                            href="{{ route('rrhh', array_merge(request()->except(['users_page', 'files_page']), ['section' => 2])) }}">
                            <i class="bi bi-folder-x"></i> Faltantes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeSection === 3 ? 'active' : '' }}"
                            href="{{ route('rrhh', array_merge(request()->except(['users_page', 'files_page']), ['section' => 3])) }}">
                            <i class="bi bi-calendar2-x"></i> Vencimientos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <form action="{{ route('rrhh', ['section' => $activeSection]) }}" method="GET">
                    <div class="row g-2 align-items-end">
                        <div class="col-xl-3 col-md-6 col-12">
                            <label for="search" class="form-label">Buscar</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="search" class="form-control" id="search" name="search"
                                    value="{{ request('search') }}" placeholder="Nombre, correo o usuario">
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-6 col-12">
                            <label for="status_id" class="form-label">Estado</label>
                            <select class="form-select form-select-sm" id="status_id" name="status_id">
                                <option value="">Todos</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-6 col-12">
                            <label for="work_department_id" class="form-label">Departamento</label>
                            <select class="form-select form-select-sm" id="work_department_id" name="work_department_id">
                                <option value="">Todos</option>
                                @foreach ($workDepartments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ request('work_department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-6 col-12">
                            <label for="document_state" class="form-label">Expediente</label>
                            <select class="form-select form-select-sm" id="document_state" name="document_state">
                                <option value="">Todos</option>
                                <option value="missing" {{ request('document_state') === 'missing' ? 'selected' : '' }}>Faltantes</option>
                                <option value="expired" {{ request('document_state') === 'expired' ? 'selected' : '' }}>Vencidos</option>
                                <option value="expiring" {{ request('document_state') === 'expiring' ? 'selected' : '' }}>Por vencer</option>
                                <option value="complete" {{ request('document_state') === 'complete' ? 'selected' : '' }}>Sin alertas</option>
                            </select>
                        </div>
                        <div class="col-xl-1 col-md-6 col-12">
                            <label for="days" class="form-label">Dias</label>
                            <input type="number" class="form-control form-control-sm" id="days" name="days"
                                value="{{ request('days', 30) }}" min="1" max="365">
                        </div>
                        <div class="col-xl-2 col-md-6 col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm flex-fill">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                            <a href="{{ route('rrhh', ['section' => $activeSection]) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0">{{ $sectionTitle }}</h2>
            </div>
            <div class="card-body p-0">
                @if ($activeSection === 1)
                    @include('dashboard.rrhh.tables.waiting')
                @else
                    @include('dashboard.rrhh.tables.files')
                @endif
            </div>
        </div>
    </div>

    <style>
        .rrhh-dashboard .card {
            border-radius: .45rem;
        }

        .rrhh-stat-card {
            border-left-width: .35rem;
        }

        .rrhh-stat-icon {
            font-size: 2rem;
            line-height: 1;
        }

        .rrhh-mini-metric {
            border: 1px solid #e9ecef;
            border-radius: .35rem;
            padding: .75rem;
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            min-height: 3.25rem;
        }

        .rrhh-department-badge {
            font-size: .82rem;
            font-weight: 500;
            padding: .45rem .6rem;
        }

        .rrhh-tabs {
            gap: .35rem;
        }

        .rrhh-tabs .nav-link {
            font-size: .9rem;
            font-weight: 700;
        }

        @media (max-width: 575.98px) {
            .rrhh-mini-metric {
                flex-direction: column;
                gap: .25rem;
            }
        }
    </style>
@endsection
