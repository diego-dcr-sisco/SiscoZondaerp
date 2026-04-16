                        @extends('layouts.app')

                        @section('content')
                            <style>
                                .font-small {
                                    font-size: 14px;
                                }

                                /* Estilos mejorados para nav-tabs CRM */
                                .nav-tabs {
                                    border: none !important;
                                    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
                                    border-radius: 12px;
                                    padding: 4px;
                                    gap: 6px;
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                                    margin-bottom: 1.5rem !important;
                                    border: 1px solid rgba(0, 0, 0, 0.1) !important;
                                }

                                .nav-tabs .nav-link {
                                    border: none !important;
                                    color: #495057 !important;
                                    font-weight: 500;
                                    padding: 0.5rem 1rem;
                                    border-radius: 8px;
                                    transition: all 0.3s ease;
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                    position: relative;
                                    background: transparent;
                                }

                                .nav-tabs .nav-link:hover {
                                    background-color: rgba(0, 123, 255, 0.1);
                                    color: #0056b3 !important;
                                    transform: translateY(-2px);
                                }

                                .nav-tabs .nav-link.active {
                                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                                    color: white !important;
                                    border-radius: 8px;
                                    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
                                }

                                .nav-tabs .nav-link i {
                                    font-size: 1.1em;
                                }
                            </style>

                            <div class="container-fluid font-small p-3">

                                {{-- Tabs CRM --}}
                                <ul class="nav nav-tabs mb-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ $nav == 'c' ? 'active' : '' }}"
                                            href="{{ route('crm.agenda') }}">
                                            <i class="bi bi-calendar-week"></i>
                                            <span>Calendario</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $nav == 't' ? 'active' : '' }}"
                                            href="{{ route('crm.tracking') }}">
                                            <i class="bi bi-arrow-repeat"></i>
                                            <span>Seguimientos</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $nav == 'q' ? 'active' : '' }}"
                                            href="{{ route('crm.quotation') }}">
                                            <i class="bi bi-receipt"></i>
                                            <span>Cotizaciones</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $nav == 'd' ? 'active' : '' }}"
                                            href="{{ route('crm.daily-tracking.index') }}">
                                            <i class="bi bi-clock-history"></i>
                                            <span>Actividades diarias</span>
                                        </a>
                                    </li>
                                </ul>

                                <div class="d-flex justify-content-start gap-2 mb-3">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#createDailyTrackingModal">
                                        <i class="bi bi-plus-circle"></i> Nuevo registro
                                    </button>

                                    <div class="dropdown">
                                        <button class="btn btn-success btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-stars"></i> Funciones especiales
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#importDailyTrackingModal">
                                                    <i class="bi bi-cloud-upload"></i> Importar Excel
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#exportDailyTrackingModal">
                                                    <i class="bi bi-cloud-download"></i> Exportar Excel
                                                </button>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('crm.daily-tracking.charts', request()->query()) }}">
                                                    <i class="bi bi-bar-chart-line"></i> Gráficas
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                @if (session('import_result'))
                                    @php
                                        $importResult = session('import_result');
                                        $daily = $importResult['daily_tracking'] ?? ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
                                        $prospects = $importResult['commercial_prospects'] ?? ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
                                    @endphp

                                    <div class="alert alert-info border mb-3">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                            <div>
                                                <strong>Resumen de importación</strong><br>
                                                <small class="text-muted">Los prospectos se guardan en la tabla de prospectos comerciales y no aparecen en esta tabla de actividades diarias.</small>
                                            </div>
                                            <div class="small text-muted">
                                                Tiempo: {{ session('import_time', 0) }}s
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="p-2 rounded bg-light border h-100">
                                                    <strong>Registro_Diario_CRM</strong>
                                                    <div class="small mt-1">
                                                        Insertados: <strong>{{ $daily['inserted'] ?? 0 }}</strong> |
                                                        Actualizados: <strong>{{ $daily['updated'] ?? 0 }}</strong> |
                                                        Omitidos: <strong>{{ $daily['skipped'] ?? 0 }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 rounded bg-light border h-100">
                                                    <strong>PROSPECTOS COMERCIALES</strong>
                                                    <div class="small mt-1">
                                                        Insertados: <strong>{{ $prospects['inserted'] ?? 0 }}</strong> |
                                                        Actualizados: <strong>{{ $prospects['updated'] ?? 0 }}</strong> |
                                                        Omitidos: <strong>{{ $prospects['skipped'] ?? 0 }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $dailyErrors = array_slice($daily['errors'] ?? [], 0, 5);
                                            $prospectErrors = array_slice($prospects['errors'] ?? [], 0, 5);
                                        @endphp

                                        @if (!empty($dailyErrors) || !empty($prospectErrors))
                                            <div class="mt-3">
                                                <strong>Primeros errores detectados:</strong>
                                                <ul class="mb-0 mt-1 small">
                                                    @foreach ($dailyErrors as $error)
                                                        <li>[Registro] {{ $error }}</li>
                                                    @endforeach
                                                    @foreach ($prospectErrors as $error)
                                                        <li>[Prospectos] {{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                {{-- Filtros --}}
                                <div class="border p-2 text-dark rounded mb-3 bg-light">
                                    <form method="GET" action="{{ route('crm.daily-tracking.index') }}">
                                        <div class="row g-2 mb-0">

                                            <div class="col-lg-3">
                                                <label class="form-label">Cliente</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i
                                                            class="bi bi-person-circle"></i></span>
                                                    <input type="text" name="customer"
                                                        class="form-control form-control-sm"
                                                        value="{{ request('customer') }}"
                                                        placeholder="Buscar por nombre..." />
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <label class="form-label">Estatus</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i class="bi bi-flag-fill"></i></span>
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="">Todos</option>
                                                        @foreach ($statusOptions as $option)
                                                            <option value="{{ $option->value }}"
                                                                @selected(request('status') === $option->value)>
                                                                {{ $option->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <label class="form-label">Tipo de servicio</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i class="bi bi-gear-fill"></i></span>
                                                    <select name="service_type" class="form-select form-select-sm">
                                                        <option value="">Todos</option>
                                                        @foreach ($serviceTypeOptions as $option)
                                                            <option value="{{ $option->value }}"
                                                                @selected(request('service_type') === $option->value)>
                                                                {{ $option->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-2">
                                                <label class="form-label">Rango de fechas</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i
                                                            class="bi bi-calendar-fill"></i></span>
                                                    <input type="text"
                                                        class="form-control form-control-sm date-range-picker"
                                                        id="date-range" name="date_range"
                                                        value="{{ request('date_range') }}"
                                                        placeholder="Selecciona un rango" autocomplete="off" />
                                                </div>
                                            </div>

                                            <div class="col-lg-1">
                                                <label class="form-label">Ordenar</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i
                                                            class="bi bi-sort-alpha-down"></i></span>
                                                    <select name="sort" class="form-select form-select-sm">
                                                        <option value="created_at" @selected(request('sort', 'created_at') === 'created_at')>Creacion
                                                        </option>
                                                        <option value="service_date" @selected(request('sort') === 'service_date')>Fecha
                                                        </option>
                                                        <option value="customer_name" @selected(request('sort') === 'customer_name')>Cliente
                                                        </option>
                                                        <option value="status" @selected(request('sort') === 'status')>Estatus</option>
                                                        <option value="service_type" @selected(request('sort') === 'service_type')>Tipo
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-1">
                                                <label class="form-label">Dirección</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i
                                                            class="bi bi-arrow-down-up"></i></span>
                                                    <select name="direction" class="form-select form-select-sm">
                                                        <option value="DESC" @selected(strtoupper(request('direction', 'DESC')) === 'DESC')>DESC</option>
                                                        <option value="ASC" @selected(strtoupper(request('direction')) === 'ASC')>ASC</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-1">
                                                <label class="form-label">Total</label>
                                                <div class="input-group input-group-sm mb-3">
                                                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                                    <select name="per_page" class="form-select form-select-sm">
                                                        <option value="15" @selected((int) request('per_page', 15) === 15)>15</option>
                                                        <option value="25" @selected((int) request('per_page') === 25)>25</option>
                                                        <option value="50" @selected((int) request('per_page') === 50)>50</option>
                                                        <option value="100" @selected((int) request('per_page') === 100)>100</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-12 d-flex justify-content-end gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-funnel-fill"></i> Buscar
                                                </button>
                                                <a href="{{ route('crm.daily-tracking.index') }}"
                                                    class="btn btn-secondary btn-sm">
                                                    <i class="bi bi-x-circle"></i> Limpiar
                                                </a>
                                            </div>

                                        </div>
                                    </form>
                                </div>



                                {{-- Tabla --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Servicio</th>
                                                <th>Cliente</th>
                                                <th>Estatus</th>
                                                <th>Tipo de servicio</th>
                                                <th>Cotizado</th>
                                                <th>Cerrado</th>
                                                <th>Monto</th>
                                                <th>Fecha servicio</th>
                                                <th>Creado</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($dailyTrackings as $item)
                                                @php
                                                    $statusValue = $item->status?->value ?? $item->status;
                                                    $statusLabel = $item->status?->label() ?? $statusValue;
                                                    $statusClass = match ($statusValue) {
                                                        'closed' => 'text-bg-success',
                                                        'survey' => 'text-bg-warning',
                                                        'no_requiere' => 'text-bg-secondary',
                                                        default => 'text-bg-secondary',
                                                    };
                                                    $quotedLabel = $item->quoted?->label() ?? $item->quoted;
                                                    $closedLabel = $item->closed?->label() ?? $item->closed;
                                                    $stypeLabel = $item->service_type?->label() ?? $item->service_type;
                                                    $amount = $item->billed_amount ?? $item->quoted_amount;
                                                @endphp
                                                <tr>
                                                    <td>{{ $item->id }}</td>
                                                    <td>{{ $item->service->name ?? '-' }}</td>
                                                    <td>
                                                        <span class="fw-semibold">{{ $item->customer_name }}</span>
                                                        @if ($item->phone)
                                                            <br><small class="text-muted"><i class="bi bi-telephone"></i>
                                                                {{ $item->phone }}</small>
                                                        @endif
                                                    </td>
                                                    <td><span
                                                            class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                                    </td>
                                                    <td>{{ $stypeLabel }}</td>
                                                    <td>{{ $quotedLabel }}</td>
                                                    <td>{{ $closedLabel }}</td>
                                                    <td>{{ $amount ? '$' . number_format((float) $amount, 2) : '-' }}</td>
                                                    <td>{{ optional($item->service_date)->format('d/m/Y') ?? '-' }}</td>
                                                    <td>{{ optional($item->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                                    <td class="py-2 px-2">
                                                        <div
                                                            class="d-flex gap-1 align-items-center justify-content-center flex-wrap">
                                                            <a href="{{ route('crm.daily-tracking.show', $item) }}"
                                                                class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                                                                data-bs-title="Ver detalle">
                                                                <i class="bi bi-eye-fill"></i>
                                                            </a>
                                                            <a href="{{ route('crm.daily-tracking.edit', $item) }}"
                                                                class="btn btn-secondary btn-sm px-2"
                                                                data-bs-toggle="tooltip" data-bs-title="Editar">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                            <form
                                                                action="{{ route('crm.daily-tracking.destroy', $item) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('¿Eliminar este registro de actividad diaria?')"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm px-2"
                                                                    data-bs-toggle="tooltip" data-bs-title="Eliminar">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center py-4 text-muted">
                                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                        No hay registros para mostrar.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{ $dailyTrackings->links('pagination::bootstrap-5') }}

                                <div class="modal fade" id="createDailyTrackingModal" data-bs-backdrop="static"
                                    data-bs-keyboard="false" tabindex="-1"
                                    aria-labelledby="createDailyTrackingModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createDailyTrackingModalLabel">Nuevo registro
                                                    de actividad diaria</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form id="createDailyTrackingForm"
                                                action="{{ route('crm.daily-tracking.store') }}" method="POST">
                                                @csrf
                                                <div class="modal-body"
                                                    style="max-height: calc(100vh - 210px); overflow-y: auto;">
                                                    @if ($errors->any())
                                                        <div class="alert alert-danger mb-3">
                                                            <strong>Corrige los siguientes errores:</strong>
                                                            <ul class="mb-0 mt-2">
                                                                @foreach ($errors->all() as $error)
                                                                    <li>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif

                                                    @include('crm.daily-tracking._form')
                                                </div>
                                                <div class="modal-footer d-flex justify-content-between">
                                                    <button type="button" class="btn btn-info"
                                                        onclick="autoFillDailyTrackingForm()">
                                                        <i class="bi bi-magic"></i> Autocompletado
                                                    </button>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-check-lg"></i> Guardar registro
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="exportDailyTrackingModal" da                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ta-bs-backdrop="static"
                                    data-bs-keyboard="false" tabindex="-1"
                                    aria-labelledby="exportDailyTrackingModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exportDailyTrackingModalLabel">Exportar
                                                    reporte Excel</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form method="GET" action="{{ route('crm.daily-tracking.export') }}">
                                                @php
                                                    $selectedExportMethods = request()->has('contact_methods')
                                                        ? (array) request('contact_methods', [])
                                                        : collect($contactMethodOptions)->map(fn($option) => $option->value)->all();
                                                @endphp
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rango de fechas</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i
                                                                    class="bi bi-calendar-range"></i></span>
                                                            <input type="text"
                                                                class="form-control form-control-sm date-range-picker"
                                                                id="export-date-range" name="date_range"
                                                                value="{{ request('date_range') }}"
                                                                placeholder="Selecciona un rango" autocomplete="off"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Agrupar por</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i
                                                                    class="bi bi-diagram-3"></i></span>
                                                            <select name="group_by" class="form-select form-select-sm"
                                                                required>
                                                                <option value="day">Dia</option>
                                                                <option value="week">Semana</option>
                                                                <option value="month" selected>Mes</option>
                                                                <option value="year">Ano</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="mb-1">
                                                        <label class="form-label">Metodos de contacto a listar</label>
                                                        <div class="border rounded p-2">
                                                            <div class="row g-2">
                                                                @foreach ($contactMethodOptions as $option)
                                                                    <div class="col-md-6">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox"
                                                                                name="contact_methods[]"
                                                                                id="export-contact-{{ $option->value }}"
                                                                                value="{{ $option->value }}"
                                                                                @checked(in_array($option->value, $selectedExportMethods, true))>
                                                                            <label class="form-check-label"
                                                                                for="export-contact-{{ $option->value }}">
                                                                                {{ $option->label() }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="customer"
                                                        value="{{ request('customer') }}">
                                                    <input type="hidden" name="status"
                                                        value="{{ request('status') }}">
                                                    <input type="hidden" name="service_type"
                                                        value="{{ request('service_type') }}">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-file-earmark-excel"></i> Exportar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Import Modal --}}
                                <div class="modal fade" id="importDailyTrackingModal" data-bs-backdrop="static"
                                    data-bs-keyboard="false" tabindex="-1"
                                    aria-labelledby="importDailyTrackingModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="importDailyTrackingModalLabel">
                                                    <i class="bi bi-cloud-upload"></i> Importar Excel
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="{{ route('crm.daily-tracking.import-excel') }}"
                                                enctype="multipart/form-data" id="importExcelForm">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-info mb-3">
                                                        <i class="bi bi-info-circle"></i>
                                                        <strong>Instrucciones:</strong>
                                                        <ul class="mb-0">
                                                            <li>Archivo Excel con 2 hojas: "Registro_Diario_CRM" y "PROSPECTOS COMERCIALES"</li>
                                                            <li>Formato: .xlsx o .csv (máximo 5MB)</li>
                                                            <li>Los registros duplicados se actualizarán automáticamente</li>
                                                        </ul>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Selecciona archivo</label>
                                                        <div class="upload-zone border-2 border-dashed border-primary rounded p-4 text-center"
                                                            id="uploadZone" style="cursor: pointer; background-color: #f0f7ff; transition: all 0.3s;">
                                                            <div class="mb-2">
                                                                <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.5rem;"></i>
                                                            </div>
                                                            <p class="mb-1 fw-semibold text-primary">Arrastra tu archivo aquí o haz clic</p>
                                                            <p class="small text-muted mb-0">Soporta: Excel (.xlsx, .xls) y CSV</p>
                                                            <input type="file" name="excel_file" id="excelFileInput"
                                                                class="d-none" accept=".xlsx,.xls,.csv">
                                                        </div>
                                                        <div class="mt-2" id="fileName" class="alert alert-success d-none">
                                                            <i class="bi bi-check-circle"></i> <span id="fileNameText"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary" id="importBtn">
                                                        <i class="bi bi-upload"></i> Importar datos
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <script>
                                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
                                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
                                // Drag and drop para modal de importación
                                const uploadZone = document.getElementById('uploadZone')
                                const excelFileInput = document.getElementById('excelFileInput')
                                const fileNameDisplay = document.getElementById('fileName')
                                const fileNameText = document.getElementById('fileNameText')
                                const importBtn = document.getElementById('importBtn')

                                if (uploadZone) {
                                    uploadZone.addEventListener('click', () => excelFileInput.click())

                                    uploadZone.addEventListener('dragover', (e) => {
                                        e.preventDefault()
                                        uploadZone.style.borderColor = '#0056b3'
                                        uploadZone.style.backgroundColor = '#e7f1ff'
                                    })

                                    uploadZone.addEventListener('dragleave', () => {
                                        uploadZone.style.borderColor = '#0056cc'
                                        uploadZone.style.backgroundColor = '#f0f7ff'
                                    })

                                    uploadZone.addEventListener('drop', (e) => {
                                        e.preventDefault()
                                        uploadZone.style.borderColor = '#0056cc'
                                        uploadZone.style.backgroundColor = '#f0f7ff'
                                        const files = e.dataTransfer.files
                                        if (files.length > 0) {
                                            excelFileInput.files = files
                                            handleFileSelected()
                                        }
                                    })

                                    excelFileInput.addEventListener('change', handleFileSelected)

                                    function handleFileSelected() {
                                        if (excelFileInput.files.length > 0) {
                                            const file = excelFileInput.files[0]
                                            fileNameText.textContent = '✓ ' + file.name
                                            fileNameDisplay.classList.remove('d-none')
                                            importBtn.disabled = false
                                        }
                                    }
                                }
                                $(document).ready(function() {
                                    $('input[name="date_range"]').daterangepicker({
                                        locale: {
                                            format: 'DD/MM/YYYY',
                                            applyLabel: 'Aplicar',
                                            cancelLabel: 'Cancelar',
                                            fromLabel: 'Desde',
                                            toLabel: 'Hasta',
                                            customRangeLabel: 'Personalizado',
                                        },
                                        ranges: {
                                            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                                            'Hoy': [moment(), moment()],
                                            'Esta semana': [moment().startOf('week'), moment().endOf('week')],
                                            'Ultimos 7 dias': [moment().subtract(6, 'days'), moment()],
                                            'Este mes': [moment().startOf('month'), moment().endOf('month')],
                                            'Ultimos 30 dias': [moment().subtract(29, 'days'), moment()],
                                            'Este ano': [moment().startOf('year'), moment().endOf('year')],
                                        },
                                        showDropdowns: true,
                                        alwaysShowCalendars: true,
                                        opens: 'left',
                                        autoUpdateInput: false
                                    });

                                    $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                                        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                                            'DD/MM/YYYY'));
                                    });

                                    $('input[name="date_range"]').on('cancel.daterangepicker', function() {
                                        $(this).val('');
                                    });
                                });

                                function autoFillDailyTrackingForm() {
                                    const form = document.getElementById('createDailyTrackingForm')
                                    if (!form) return

                                    const setFieldValue = (name, value) => {
                                        const field = form.querySelector(`[name="${name}"]`)
                                        if (!field) return
                                        field.value = value
                                        field.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }))
                                        field.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }))
                                    }

                                    const serviceSelect = form.querySelector('[name="service_id"]')
                                    if (serviceSelect && serviceSelect.options.length > 1) {
                                        serviceSelect.selectedIndex = 1
                                        serviceSelect.dispatchEvent(new Event('input', {
                                            bubbles: true
                                        }))
                                        serviceSelect.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }))
                                    }

                                    const today = new Date()
                                    const y = today.getFullYear()
                                    const m = String(today.getMonth() + 1).padStart(2, '0')
                                    const d = String(today.getDate()).padStart(2, '0')
                                    const todayISO = `${y}-${m}-${d}`

                                    setFieldValue('customer_name', 'Cliente Prueba Autocompletado')
                                    setFieldValue('phone', '5551234567')
                                    setFieldValue('customer_type', 'comercial')
                                    setFieldValue('state', 'CDMX')
                                    setFieldValue('city', 'Ciudad de Mexico')
                                    setFieldValue('address', 'Calle Demo 123, Colonia Centro')
                                    setFieldValue('contact_method', 'llamada')
                                    setFieldValue('status', 'survey')
                                    setFieldValue('service_type', 'comercial')
                                    setFieldValue('quoted', 'yes')
                                    setFieldValue('closed', 'pending')
                                    setFieldValue('quoted_amount', '2500.00')
                                    setFieldValue('billed_amount', '')
                                    setFieldValue('payment_method', '')
                                    setFieldValue('invoice', 'not_applicable')
                                    setFieldValue('service_date', todayISO)
                                    setFieldValue('quote_sent_date', todayISO)
                                    setFieldValue('close_date', '')
                                    setFieldValue('payment_date', '')
                                    setFieldValue('follow_up_date', todayISO)
                                    setFieldValue('service_time', '10:30')
                                    setFieldValue('notes', 'Registro generado con el boton de autocompletado para pruebas.')

                                    const responded = form.querySelector('[name="responded"][type="checkbox"]')
                                    if (responded) {
                                        responded.checked = true
                                        responded.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }))
                                    }

                                    const hasCoverage = form.querySelector('[name="has_coverage"][type="checkbox"]')
                                    if (hasCoverage) {
                                        hasCoverage.checked = true
                                        hasCoverage.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }))
                                    }
                                }

                                @if ($errors->any())
                                    const createModal = new bootstrap.Modal(document.getElementById('createDailyTrackingModal'))
                                    createModal.show()
                                @endif
                            </script>
                        @endsection
