@extends('layouts.app')
@section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit();
        ?>
    @endif

    <style>
        .accordion-technicians .accordion-button {
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        .accordion-technicians .accordion-button:not(.collapsed) {
            color: var(--bs-primary);
        }

        .technicians-list {
            max-height: 350px;
            overflow-y: auto;
            padding: 10px;
        }

        .technician-item {
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .technician-item:hover {
            background-color: var(--bs-light);
        }

        .technician-item input[type="checkbox"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }

        .technician-item label {
            cursor: pointer;
            margin-bottom: 0;
            user-select: none;
            flex: 1;
        }

        .technician-item.checked {
            background-color: var(--bs-primary-bg-subtle);
            border-left: 3px solid var(--bs-primary);
        }

        .search-box {
            position: sticky;
            top: 0;
            background: var(--bs-white);
            z-index: 10;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--bs-border-color);
            margin-bottom: 10px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: var(--bs-warning-bg-subtle);
            color: var(--bs-warning-text-emphasis);
        }

        .technician-badge {
            display: inline-block;
            padding: 2px 6px;
            margin: 1px;
            background-color: var(--bs-primary-bg-subtle);
            border: 1px solid var(--bs-primary-border-subtle);
            border-radius: 3px;
            font-size: 0.75rem;
        }

        .folio-link {
            transition: all 0.2s ease;
        }

        .folio-link:hover {
            opacity: 0.7;
        }

        .table-scroll-container {
            max-height: 600px;
            overflow-y: auto;
            position: relative;
        }

        .table-scroll-container caption {
            position: sticky;
            top: 0;
            background-color: var(--bs-white);
            z-index: 11;
            padding: 10px;
            margin-bottom: 0;
        }

        .table-scroll-container thead th {
            position: sticky;
            top: 48px;
            background-color: var(--bs-white);
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }

        #accordionBranches .accordion-button {
            font-size: 0.9rem;
        }

        #accordionBranches .accordion-button:not(.collapsed) {
            background-color: var(--bs-primary);
            color: white;
        }

        #accordionBranches .accordion-body {
            background-color: var(--bs-white);
        }

        #accordionBranches table {
            font-size: 0.875rem;
        }

        #accordionBranches table th {
            background-color: var(--bs-light);
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        #accordionBranches table tbody tr:hover {
            background-color: var(--bs-light);
        }
    </style>

    <div class="container-fluid p-3">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold mb-0">
                Control de Operaciones
            </h2>

            <div class="text-end d-flex align-items-center gap-2">
                <span class="badge fs-5" style="background-color: #43A047; color: white;">
                    <i class="bi bi-calendar-fill"></i>
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </span>
                <span class="badge bg-danger fs-5">{{ $orders->total() }} Reportes Pendientes</span>
            </div>
        </div>

        @include('messages.alert')

        <!-- Filtros -->
        <div class="border rounded p-3 text-dark bg-light mb-3">
            <form method="GET" action="{{ route('operations.index') }}">
                <div class="row g-2">
                    <!-- Técnico(s) -->
                    <div class="col-lg-4">
                        <label class="form-label">
                            Técnico(s)
                            <span id="selected-count" class="badge bg-primary ms-2">0</span>
                            <span class="badge bg-secondary ms-1">{{ count($technicians) }} total</span>
                        </label>

                        <div class="accordion accordion-technicians" id="accordionTechnicians">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseTechnicians" aria-expanded="false">
                                        Seleccionar Técnicos
                                    </button>
                                </h2>
                                <div id="collapseTechnicians" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionTechnicians">
                                    <div class="accordion-body p-0">
                                        <!-- Búsqueda -->
                                        <div class="search-box p-3">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                <input type="text" class="form-control" id="search-technician"
                                                    placeholder="Buscar técnico por nombre..." autocomplete="off">
                                                <button class="btn btn-outline-danger" type="button"
                                                    onclick="clearSearch()">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Lista de Técnicos -->
                                        <div class="technicians-list" id="technicians-list">
                                            @foreach ($technicians as $technician)
                                                @php
                                                    $selectedTechnicians = request('technician_ids', []);
                                                    // Convertir a string para comparación consistente
                                                    $isSelected = in_array(
                                                        (string) $technician->id,
                                                        (array) $selectedTechnicians,
                                                    );
                                                @endphp
                                                <div class="technician-item d-flex align-items-center {{ $isSelected ? 'checked' : '' }}"
                                                    data-name="{{ strtolower($technician->name) }}">
                                                    <input type="checkbox" class="form-check-input technician-checkbox"
                                                        name="technician_ids[]" value="{{ $technician->id }}"
                                                        id="tech-{{ $technician->id }}" {{ $isSelected ? 'checked' : '' }}
                                                        onchange="toggleTechnicianItem(this)">
                                                    <label for="tech-{{ $technician->id }}">
                                                        {{ $technician->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Botones de Acción -->
                                        <div class="technician-actions p-3 border-top">
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                onclick="selectAllTechnicians()">
                                                Seleccionar Todos
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="clearAllTechnicians()">
                                                Deseleccionar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rango de Fechas -->
                    <div class="col-lg-4">
                        <label for="date_range" class="form-label">
                            Rango de Fechas
                        </label>
                        <input type="text" class="form-control form-control-sm" id="date_range" name="date_range"
                            value="{{ request('start_date') && request('end_date') ? request('start_date') . ' - ' . request('end_date') : '' }}"
                            placeholder="Seleccionar fechas" autocomplete="off">
                        <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>

                    <!-- Total por página -->
                    <div class="col-lg-1">
                        <label for="size" class="form-label">Total</label>
                        <select class="form-select form-select-sm" id="size" name="size">
                            <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('size', 50) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="col-lg-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="bi bi-funnel-fill"></i> Filtrar
                            </button>
                            <a href="{{ route('operations.index') }}" class="btn btn-secondary btn-sm flex-fill">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Accordion de plantas -->
        <div class="mb-3">
            <div class="row g-3">
                <!-- Distribución por Plantas -->
                <div class="col-lg-6">
                    <div class="accordion" id="accordionBranches">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-primary text-white py-2 px-3"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseBranches">
                                    <i class="bi bi-building-fill me-2"></i>
                                    <strong>Distribución por Plantas ({{ $ordersByBranch->sum() }} reportes)</strong>
                                </button>
                            </h2>
                            <div id="collapseBranches" class="accordion-collapse collapse"
                                data-bs-parent="#accordionBranches">
                                <div class="accordion-body p-2" style="max-height: 300px; overflow-y: auto;">
                                    @if ($ordersByBranch->isEmpty())
                                        <p class="text-muted mb-0">No se encontraron reportes para las plantas.</p>
                                    @else
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-start">Planta</th>
                                                    <th class="text-end">Reportes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ordersByBranch as $branchName => $count)
                                                    <tr>
                                                        <td>{{ $branchName }}</td>
                                                        <td class="text-danger fw-bold text-end">{{ $count }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribución por Clientes -->
                <div class="col-lg-6">
                    <div class="accordion" id="accordionCustomers">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-success text-white py-2 px-3"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseCustomers">
                                    <i class="bi bi-people-fill me-2"></i>
                                    <strong>Distribución por Clientes ({{ $ordersByCustomer->sum() }} reportes)</strong>
                                </button>
                            </h2>
                            <div id="collapseCustomers" class="accordion-collapse collapse"
                                data-bs-parent="#accordionCustomers">
                                <div class="accordion-body p-2" style="max-height: 300px; overflow-y: auto;">
                                    @if ($ordersByCustomer->isEmpty())
                                        <p class="text-muted mb-0">No se encontraron reportes para los clientes.</p>
                                    @else
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-start fw-bold">Cliente</th>
                                                    <th class="text-end fw-bold">Reportes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ordersByCustomer as $customerName => $count)
                                                    <tr>
                                                        <td>{{ $customerName }}</td>
                                                        <td class="text-danger fw-bold text-end">{{ $count }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="table-responsive table-scroll-container">
            <table class="table table-bordered table-striped table-sm">
                <caption class="caption-top bg-light border px-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Simbología de colores -->
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <strong class="me-2 text-dark">Estado de fechas:</strong>
                            <span class="badge" style="background-color: #C7170A;">
                                <i class="bi bi-exclamation-triangle-fill"></i> Vencido
                            </span>
                            <span class="badge" style="background-color: #761D86;">
                                <i class="bi bi-clock-fill"></i> Hoy
                            </span>
                            <span class="badge" style="background-color: #F57C00;">
                                <i class="bi bi-calendar-check"></i> Próximo
                            </span>
                        </div>

                        <!-- Botón exportar PDF -->
                        @if ($orders->count() > 0)
                            <div class="text-end">
                                <a href="{{ route('operations.export.pdf', request()->query()) }}"
                                    class="btn btn-dark btn-sm" target="_blank">
                                    <i class="bi bi-file-pdf-fill"></i> Exportar PDF
                                </a>
                            </div>
                        @endif
                    </div>
                </caption>
                <thead>
                    <tr>
                        <th scope="col"># (Folio)</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">ID</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Servicio(s)</th>
                        <th scope="col">Técnico(s)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $offset = ($orders->currentPage() - 1) * $orders->perPage();
                    @endphp
                    @forelse ($orders as $index => $order)
                        <tr>
                            <!-- # (Folio) -->
                            <td>
                                <a href="{{ route('order.edit', $order->id) }}" 
                                   class="text-decoration-none fw-bold text-primary folio-link">
                                    <span class="text-decoration-underline">({{ $order->folio }})</span>
                                </a>
                            </td>

                            <!-- Cliente -->
                            <td>
                                <span
                                    class="fw-bold text-decoration-underline">{{ $order->customer->name ?? 'Sin cliente' }}</span>
                                @if ($order->customer && $order->customer->type == 2)
                                    <br><small class="text-muted">Sede de:
                                        {{ $order->customer->matrix->name ?? '-' }}</small>
                                @endif
                            </td>

                            <!-- ID -->
                            <td class="fw-bold text-decoration-underline">{{ $order->id }}</td>

                            <!-- Hora -->
                            <td>
                                @if ($order->start_time)
                                    {{ \Carbon\Carbon::parse($order->start_time)->format('H:i') }}
                                    @if ($order->end_time)
                                        - {{ \Carbon\Carbon::parse($order->end_time)->format('H:i') }}
                                    @endif
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>

                            <!-- Fecha -->
                            <td
                                @if ($order->programmed_date) @php
                                        $programmedDate = \Carbon\Carbon::parse($order->programmed_date);
                                        $today = \Carbon\Carbon::today();
                                        
                                        // Determinar color del semáforo
                                        if ($programmedDate->isToday()) {
                                            $bgColor = '#761D86'; // Morado para hoy
                                        } elseif ($programmedDate->isFuture()) {
                                            $bgColor = '#F57C00'; // Naranja para futuro
                                        } else {
                                            $bgColor = '#C7170A'; // Rojo para vencido
                                        }
                                    @endphp
                                    style="background-color: {{ $bgColor }}; color: white; font-weight: 600;" @endif>
                                @if ($order->programmed_date)
                                    {{ \Carbon\Carbon::parse($order->programmed_date)->format('d/m/Y') }}
                                @else
                                    <small class="text-muted">Sin fecha</small>
                                @endif
                            </td>

                            <!-- Tipo -->
                            <td>{{ $order->contract_id > 0 ? 'MIP' : 'Seguimiento' }}</td>

                            <!-- Servicio(s) -->
                            <td>
                                @if ($order->services->count() > 0)
                                    @foreach ($order->services as $service)
                                        {{ $service->name }}<br>
                                    @endforeach
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>

                            <!-- Técnico(s) -->
                            <td>
                                @php
                                    $orderTechnicians = $order->getNameTechnicians();
                                @endphp
                                @if ($orderTechnicians->count() > 0)
                                    <ul class="mb-0 ps-3">
                                        @foreach ($orderTechnicians as $tech)
                                            <li>{{ $tech->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <small class="text-muted">Sin técnico asignado</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No se encontraron reportes pendientes</p>
                                    <small>Intenta ajustar los filtros de búsqueda</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($orders->hasPages())
            {{ $orders->links('pagination::bootstrap-5') }}
        @endif
    </div>

    <script>
        // Inicializar tooltips de Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })

            // Actualizar contador inicial
            updateSelectedCount();

            // Búsqueda de técnicos
            document.getElementById('search-technician').addEventListener('input', filterTechnicians);
        });

        // Función para actualizar el contador de técnicos seleccionados
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.technician-checkbox:checked');
            const count = checkboxes.length;
            const badge = document.getElementById('selected-count');

            badge.textContent = 'Técnicos seleccionados: ' + count;
            badge.className = count > 0 ? 'badge bg-primary ms-2' : 'badge bg-secondary ms-2';
        }

        // Función para alternar estilo del item al hacer check/uncheck
        function toggleTechnicianItem(checkbox) {
            const item = checkbox.closest('.technician-item');
            if (checkbox.checked) {
                item.classList.add('checked');
            } else {
                item.classList.remove('checked');
            }
            updateSelectedCount();
        }

        // Función para filtrar técnicos
        function filterTechnicians() {
            const searchValue = document.getElementById('search-technician').value.toLowerCase();
            const items = document.querySelectorAll('.technician-item');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.dataset.name;
                if (name.includes(searchValue)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Función para limpiar búsqueda
        function clearSearch() {
            document.getElementById('search-technician').value = '';
            filterTechnicians();
        }

        // Función para seleccionar todos los técnicos visibles
        function selectAllTechnicians() {
            const items = document.querySelectorAll('.technician-item');

            items.forEach(item => {
                if (item.style.display !== 'none') {
                    const checkbox = item.querySelector('.technician-checkbox');
                    checkbox.checked = true;
                    item.classList.add('checked');
                }
            });
            updateSelectedCount();
        }

        // Función para deseleccionar todos los técnicos
        function clearAllTechnicians() {
            const checkboxes = document.querySelectorAll('.technician-checkbox');
            const items = document.querySelectorAll('.technician-item');

            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            items.forEach(item => {
                item.classList.remove('checked');
            });

            updateSelectedCount();
        }

        // Inicializar daterangepicker
        $(function() {
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    weekLabel: 'S',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: [
                        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                @if (request('start_date') && request('end_date'))
                    startDate: "{{ request('start_date') }}",
                    endDate: "{{ request('end_date') }}"
                @endif
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
            });
        });
    </script>
@endsection
