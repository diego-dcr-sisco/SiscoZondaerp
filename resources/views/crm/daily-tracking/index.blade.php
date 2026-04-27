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

                            @include('components.page-header', [
                                'title' => 'ACTIVIDADES DIARIAS',
                                'icon' => 'bi-clock-history',
                            ])
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
                                                    <i class="bi bi-cloud-upload"></i> Importar CSV
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
                                        $daily = $importResult['daily_tracking'] ?? ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => [], 'errors' => []];
                                        $prospects = $importResult['commercial_prospects'] ?? ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => [], 'errors' => []];
                                        $dailySkippedRows = $daily['skipped_rows'] ?? [];
                                        $prospectsSkippedRows = $prospects['skipped_rows'] ?? [];
                                        $totalSkippedRows = count($dailySkippedRows) + count($prospectsSkippedRows);
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

                                        @if ($totalSkippedRows > 0)
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#skippedRowsImportModal">
                                                    <i class="bi bi-list-ul"></i> Ver filas no insertadas ({{ $totalSkippedRows }})
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($totalSkippedRows > 0)
                                        <div class="modal fade" id="skippedRowsImportModal" tabindex="-1"
                                            aria-labelledby="skippedRowsImportModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="skippedRowsImportModalLabel">Filas no insertadas en importación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if (!empty($dailySkippedRows))
                                                            <h6 class="mb-2">Registro_Diario_CRM ({{ count($dailySkippedRows) }})</h6>
                                                            @foreach ($dailySkippedRows as $row)
                                                                <div class="border rounded p-2 mb-2 bg-light">
                                                                    <div class="small mb-1"><strong>Fila:</strong> {{ $row['row_number'] ?? '-' }}</div>
                                                                    <div class="small mb-2"><strong>Motivo:</strong> {{ $row['reason'] ?? 'Sin detalle' }}</div>
                                                                    <details>
                                                                        <summary class="small">Ver datos de la fila</summary>
                                                                        <pre class="small bg-white border rounded p-2 mt-2 mb-0">{{ json_encode($row['row_data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                    </details>
                                                                </div>
                                                            @endforeach
                                                        @endif

                                                        @if (!empty($prospectsSkippedRows))
                                                            <h6 class="mt-3 mb-2">PROSPECTOS COMERCIALES ({{ count($prospectsSkippedRows) }})</h6>
                                                            @foreach ($prospectsSkippedRows as $row)
                                                                <div class="border rounded p-2 mb-2 bg-light">
                                                                    <div class="small mb-1"><strong>Fila:</strong> {{ $row['row_number'] ?? '-' }}</div>
                                                                    <div class="small mb-2"><strong>Motivo:</strong> {{ $row['reason'] ?? 'Sin detalle' }}</div>
                                                                    <details>
                                                                        <summary class="small">Ver datos de la fila</summary>
                                                                        <pre class="small bg-white border rounded p-2 mt-2 mb-0">{{ json_encode($row['row_data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                    </details>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
                                                <th>Recurrente</th>
                                                <th>Cotizado</th>
                                                <th>Cerrado</th>
                                                <th>Monto</th>
                                                <th>Facturado</th>
                                                <th>Monto Facturado</th>
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
                                                    $recurrentBadgeClass = $item->is_recurrent ? 'text-bg-info' : 'text-bg-light text-dark';
                                                    $recurrentLabel = $item->is_recurrent ? 'Si' : 'No';
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
                                                        <td><span class="badge {{ $recurrentBadgeClass }}">{{ $recurrentLabel }}</span></td>
                                                    <td>{{ $quotedLabel }}</td>
                                                    <td>{{ $closedLabel }}</td>
                                                    <td>{{ $amount ? '$' . number_format((float) $amount, 2) : '-' }}</td>
                                                    <td>
                                                        @php
                                                            $invoiceValue = $item->invoice?->value ?? $item->invoice;
                                                            $invoiceBadgeClass = $invoiceValue === 'yes' ? 'text-bg-success' : ($invoiceValue === 'no' ? 'text-bg-danger' : 'text-bg-secondary');
                                                            $invoiceLabel = $item->invoice?->label() ?? $invoiceValue ?? '-';
                                                        @endphp
                                                        <span class="badge {{ $invoiceBadgeClass }}">{{ $invoiceLabel }}</span>
                                                    </td>
                                                    <td>{{ $item->billed_amount ? '$' . number_format((float) $item->billed_amount, 2) : '-' }}</td>
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
                                                            <button type="button"
                                                                class="btn btn-warning btn-sm px-2 btn-create-customer"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-title="Crear cliente"
                                                                data-daily-tracking-id="{{ $item->id }}"
                                                                data-customer-name="{{ $item->customer_name }}"
                                                                data-phone="{{ $item->phone }}"
                                                                data-state="{{ $item->state }}"
                                                                data-city="{{ $item->city }}"
                                                                data-address="{{ $item->address }}"
                                                                data-customer-type="{{ $item->customer_type?->value ?? $item->customer_type }}">
                                                                <i class="bi bi-person-plus-fill"></i>
                                                            </button>
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
                                                    <td colspan="11" class="text-center py-4 text-muted">
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

                                <div class="modal fade" id="createCustomerFromTrackingModal" data-bs-backdrop="static"
                                    data-bs-keyboard="false" tabindex="-1"
                                    aria-labelledby="createCustomerFromTrackingModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning-subtle">
                                                <h5 class="modal-title" id="createCustomerFromTrackingModalLabel">
                                                    <i class="bi bi-person-plus-fill"></i> Crear cliente desde actividad diaria
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form id="createCustomerFromTrackingForm" method="POST" action="#">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-info py-2">
                                                        Se tomaran los datos del registro diario seleccionado y podras ajustarlos antes de guardar.
                                                    </div>

                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Nombre *</label>
                                                            <input type="text" class="form-control form-control-sm" name="name" id="ct-name" required>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label">Telefono</label>
                                                            <input type="text" class="form-control form-control-sm" name="phone" id="ct-phone">
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Tipo de servicio *</label>
                                                            <select class="form-select form-select-sm" name="service_type_id" id="ct-service-type" required>
                                                                <option value="">Seleccionar</option>
                                                                @foreach ($customerServiceTypes as $serviceType)
                                                                    <option value="{{ $serviceType->id }}">{{ $serviceType->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Sucursal *</label>
                                                            <select class="form-select form-select-sm" name="branch_id" id="ct-branch" required>
                                                                <option value="">Seleccionar</option>
                                                                @foreach ($customerBranches as $branch)
                                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label class="form-label">Medio de contacto *</label>
                                                            <select class="form-select form-select-sm" name="contact_medium" id="ct-contact-medium" required>
                                                                @foreach ($customerContactMediumOptions as $key => $label)
                                                                    <option value="{{ $key }}" @selected($key === 'call')>{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label">Estado</label>
                                                            <select class="form-select form-select-sm" name="state" id="ct-state">
                                                                <option value="">Seleccionar</option>
                                                                @foreach ($states as $state)
                                                                    <option value="{{ $state['name'] ?? '' }}" data-key="{{ $state['key'] ?? '' }}">{{ $state['name'] ?? '' }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label">Ciudad</label>
                                                            <select class="form-select form-select-sm" name="city" id="ct-city">
                                                                <option value="">Seleccionar</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-12">
                                                            <label class="form-label">Direccion</label>
                                                            <textarea class="form-control form-control-sm" name="address" id="ct-address" rows="2"></textarea>
                                                        </div>

                                                        <div class="col-12">
                                                            <label class="form-label">Email (opcional)</label>
                                                            <input type="email" class="form-control form-control-sm" name="email" id="ct-email"
                                                                placeholder="cliente@correo.com">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-warning btn-sm">
                                                        <i class="bi bi-check-lg"></i> Crear cliente
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="exportDailyTrackingModal" data-bs-backdrop="static"
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
                                                    <i class="bi bi-cloud-upload"></i> Importar CSV
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
                                                            <li>Archivo CSV con los campos requeridos para importación</li>
                                                            <li>Formato: .csv (máximo 5MB)</li>
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
                                                            <p class="small text-muted mb-0">Soporta: CSV (.csv)</p>
                                                            <input type="file" name="excel_file" id="excelFileInput"
                                                                class="d-none" accept=".csv,text/csv" required>
                                                        </div>
                                                        <div class="mt-2" id="fileName" class="alert alert-success d-none">
                                                            <i class="bi bi-check-circle"></i> <span id="fileNameText"></span>
                                                        </div>
                                                        <div class="text-danger mt-2 d-none" id="excelFileError">
                                                            Por favor, selecciona un archivo CSV antes de importar.
                                                        </div>
                                                    </div>

                                                    <div id="importLoadingState" class="d-none border rounded p-3 bg-light">
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                                                            <span class="fw-semibold text-primary">Importando archivo...</span>
                                                        </div>
                                                        <div class="progress" role="progressbar" aria-label="Import progress" aria-valuemin="0" aria-valuemax="100">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                                                        </div>
                                                        <small class="text-muted d-block mt-2">Esto puede tardar unos segundos dependiendo del tamano del archivo.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal" id="importCancelBtn">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary" id="importBtn">
                                                        <i class="bi bi-upload"></i> Importar datos
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            {{-- Modal: Crear Cliente desde registro diario --}}
                            <div class="modal fade" id="createCustomerFromTrackingModal" data-bs-backdrop="static"
                                data-bs-keyboard="false" tabindex="-1"
                                aria-labelledby="createCustomerFromTrackingModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning">
                                            <h5 class="modal-title text-dark" id="createCustomerFromTrackingModalLabel">
                                                <i class="bi bi-person-plus-fill me-1"></i> Crear cliente desde registro diario
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form id="createCustomerFromTrackingForm" method="POST" action="">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-info small mb-3">
                                                    <i class="bi bi-info-circle"></i>
                                                    Los datos se precargaron del registro seleccionado. Revisa y completa los campos requeridos antes de guardar.
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Nombre del cliente *</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                                            <input type="text" name="name" id="ctf_name"
                                                                class="form-control form-control-sm" required maxlength="255">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Telefono</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                                            <input type="text" name="phone" id="ctf_phone"
                                                                class="form-control form-control-sm" maxlength="50">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Correo electronico</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                                            <input type="email" name="email" id="ctf_email"
                                                                class="form-control form-control-sm" maxlength="255">
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="service_type_id" id="ctf_service_type_id">
                                                    <span id="ctf_service_type_map" class="d-none"
                                                        data-map="{{ htmlspecialchars(json_encode($customerServiceTypes->map(fn($st) => ['id' => $st->id, 'name' => strtolower($st->name)])->values()->all()), ENT_QUOTES) }}"></span>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Sucursal asignada *</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                                                            <select name="branch_id" id="ctf_branch_id"
                                                                class="form-select form-select-sm" required>
                                                                <option value="">— Seleccionar —</option>
                                                                @foreach ($customerBranches as $branch)
                                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Medio de contacto *</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
                                                            <select name="contact_medium" id="ctf_contact_medium"
                                                                class="form-select form-select-sm" required>
                                                                <option value="">— Seleccionar —</option>
                                                                @foreach ($customerContactMediumOptions as $key => $label)
                                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Estado</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-map-fill"></i></span>
                                                            <select name="state" id="ctf_state" class="form-select form-select-sm">
                                                                <option value="">— Seleccionar estado —</option>
                                                                @foreach ($states as $st)
                                                                    <option value="{{ $st['name'] }}">{{ $st['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold mb-1">Ciudad</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                                            <select name="city" id="ctf_city" class="form-select form-select-sm">
                                                                <option value="">— Selecciona primero el estado —</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold mb-1">Direccion</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text"><i class="bi bi-pin-map-fill"></i></span>
                                                            <input type="text" name="address" id="ctf_address"
                                                                class="form-control form-control-sm" maxlength="500">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-warning btn-sm text-dark fw-semibold">
                                                    <i class="bi bi-person-check-fill"></i> Crear cliente
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
                                const importForm = document.getElementById('importExcelForm')
                                const importLoadingState = document.getElementById('importLoadingState')
                                const importCancelBtn = document.getElementById('importCancelBtn')
                                const excelFileError = document.getElementById('excelFileError')

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
                                            if (excelFileError) {
                                                excelFileError.classList.add('d-none')
                                            }
                                            importBtn.disabled = false
                                        }
                                    }
                                }

                                if (importForm) {
                                    importForm.addEventListener('submit', function(e) {
                                        if (!excelFileInput || !excelFileInput.files || excelFileInput.files.length === 0) {
                                            e.preventDefault()
                                            if (excelFileError) {
                                                excelFileError.classList.remove('d-none')
                                            }
                                            if (importBtn) {
                                                importBtn.disabled = true
                                            }
                                            return
                                        }

                                        if (importLoadingState) {
                                            importLoadingState.classList.remove('d-none')
                                        }

                                        if (uploadZone) {
                                            uploadZone.classList.add('opacity-50')
                                            uploadZone.style.pointerEvents = 'none'
                                        }

                                        if (importBtn) {
                                            importBtn.disabled = true
                                            importBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...'
                                        }

                                        if (importCancelBtn) {
                                            importCancelBtn.disabled = true
                                        }
                                    })
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

                                    const hasCoverage = form.querySelector('[name="has_not_coverage"][type="checkbox"]')
                                    if (hasCoverage) {
                                        hasCoverage.checked = true
                                        hasCoverage.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }))
                                    }

                                    const isRecurrent = form.querySelector('[name="is_recurrent"][type="checkbox"]')
                                    if (isRecurrent) {
                                        isRecurrent.checked = false
                                        isRecurrent.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }))
                                    }
                                }

                                @if ($errors->any())
                                    const createModal = new bootstrap.Modal(document.getElementById('createDailyTrackingModal'))
                                    createModal.show()
                                @endif

                                // --- Crear Cliente desde registro diario ---
                                ;(function () {
                                    const ctfCitiesData = @json($cities);
                                    const ctfStatesData = @json($states);

                                    const stateSelect = document.getElementById('ctf_state');
                                    const citySelect  = document.getElementById('ctf_city');

                                    function loadCtfCities(stateName, selectedCity) {
                                        citySelect.innerHTML = '<option value="">— Seleccionar ciudad —</option>';

                                        // Busca la key del estado por nombre
                                        const stateObj = ctfStatesData.find(s => s.name === stateName);
                                        if (!stateObj) return;

                                        const citiesList = ctfCitiesData[stateObj.key] || [];
                                        citiesList.forEach(function (city) {
                                            const opt = document.createElement('option');
                                            opt.value = typeof city === 'object' ? city.name : city;
                                            opt.textContent = typeof city === 'object' ? city.name : city;
                                            if (opt.value === selectedCity) opt.selected = true;
                                            citySelect.appendChild(opt);
                                        });
                                    }

                                    stateSelect.addEventListener('change', function () {
                                        loadCtfCities(this.value, '');
                                    });

                                    // Mapa customer_type (enum) -> service_type_id
                                    const ctfServiceTypeMapEl = document.getElementById('ctf_service_type_map');
                                    const ctfServiceTypeMap = ctfServiceTypeMapEl
                                        ? JSON.parse(ctfServiceTypeMapEl.getAttribute('data-map') || '[]')
                                        : [];

                                    function resolveServiceTypeId(customerTypeValue) {
                                        const val = (customerTypeValue || '').toLowerCase();
                                        const found = ctfServiceTypeMap.find(function (st) {
                                            return st.name === val || st.name.startsWith(val);
                                        });
                                        return found ? found.id : '';
                                    }

                                    document.querySelectorAll('.btn-create-customer').forEach(function (btn) {
                                        btn.addEventListener('click', function () {
                                            const trackingId   = this.dataset.dailyTrackingId;
                                            const name         = this.dataset.customerName || '';
                                            const phone        = this.dataset.phone || '';
                                            const state        = this.dataset.state || '';
                                            const city         = this.dataset.city || '';
                                            const address      = this.dataset.address || '';
                                            const customerType = this.dataset.customerType || '';

                                            // Set action URL
                                            const form = document.getElementById('createCustomerFromTrackingForm');
                                            form.action = '/crm/daily-tracking/' + trackingId + '/store-customer';

                                            // Prefill basics
                                            document.getElementById('ctf_name').value    = name;
                                            document.getElementById('ctf_phone').value   = phone;
                                            document.getElementById('ctf_address').value = address;
                                            document.getElementById('ctf_email').value   = '';

                                            // Deriva service_type_id del tipo de cliente
                                            document.getElementById('ctf_service_type_id').value =
                                                resolveServiceTypeId(customerType);

                                            // Select state
                                            stateSelect.value = state;
                                            loadCtfCities(state, city);

                                            // Show modal
                                            const modal = new bootstrap.Modal(document.getElementById('createCustomerFromTrackingModal'));
                                            modal.show();
                                        });
                                    });
                                })();
                            </script>
                        @endsection
