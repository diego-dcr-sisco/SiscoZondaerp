@extends('layouts.app')
@section('content')
    @php
         if (!function_exists('stockLevel')) {
            function stockLevel($total, $current)
            {
                // Evitar división por cero y manejar $total o $current igual a 0
                if ($total == 0) {
                    return 'text-secondary'; // Sin stock o sin información
                }
                if ($current == 0) {
                    return 'text-danger'; // Sin stock (rojo)
                }
                $perc = ($current / $total) * 100;
                if ($perc <= 20) {
                    return 'text-danger'; // Critical (red)
                } elseif ($perc <= 50) {
                    return 'text-warning'; // Warning (yellow/orange)
                } elseif ($perc <= 100) {
                    return 'text-success'; // Normal (blue)
                } else {
                    return 'text-primary'; // Good (green)
                }
            }
        }
    @endphp

    <div class="container-fluid">
        <div class="d-flex align-items-center border-bottom p-2">
            <span class="text-black fw-bold fs-4">
                LOTES DE PRODUCTOS
            </span>
        </div>
        <div class="py-3">
            @if(auth()->user()->work_department_id == 1 || auth()->user()->work_department_id == 5)
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createLotModal">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear lote
                </button>
            @endif
        </div>
        

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm align-middle caption-top">
                <caption class="border rounded-top p-2 text-dark bg-light">
                    <form action="{{ route('lot.index') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-4 col-12">
                                <label for="customer" class="form-label">No de lote</label>
                                <input type="text" class="form-control form-control-sm" id="registration_number"
                                    name="registration_number" value="{{ request('registration_number') }}"
                                    placeholder="Buscar por número de registro del lote">
                            </div>

                            <div class="col-lg-4 col-12">
                                <label for="customer" class="form-label">Almacén</label>
                                <select class="form-select form-select-sm" id="warehouse" name="warehouse">
                                    <option value="">Todos los almacenes</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-4 col-12">
                                <label for="customer" class="form-label">Producto</label>
                                <input type="text" class="form-control form-control-sm" id="product" name="product"
                                    value="{{ request('product') }}" placeholder="Buscar por el nombre del producto">
                            </div>

                            <div class="col-auto">
                                <label for="signature_status" class="form-label">Dirección</label>
                                <select class="form-select form-select-sm" id="direction" name="direction">
                                    <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                    </option>
                                    <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="order_type" class="form-label">Total</label>
                                <select class="form-select form-select-sm" id="size" name="size">
                                    <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                    <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="col-12 d-flex justify-content-end m-0">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>

                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Producto</th>
                        <th scope="col">N° Lote</th>
                        <th scope="col">Almacén</th>
                        <th scope="col">Cantidad registro</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Caducidad</th>
                        <th scope="col">Período</th>
                        @if (auth()->user()->work_department_id == 1)
                            <th scope="col"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lots as $index => $lot)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ ++$index }}</td>
                            <!-- Producto -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-icon me-2">
                                        <i class="bi bi-box-seam text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $lot->product->name ?? 'Sin producto' }}
                                        </div>
                                        <small
                                            class="text-muted">{{ $lot->product->presentation->name ?? 'Sin presentación' }}</small>
                                    </div>
                                </div>
                            </td>

                            <!-- Número de Lote -->
                            <td>
                                <span class="text-primary fs-6 px-3 py-2 fw-bold">
                                    {{ $lot->registration_number }}
                                </span>
                            </td>

                            <!-- Almacén -->
                            <td>
                                <div class="warehouse-info">
                                    <i class="bi bi-building text-muted me-1"></i>
                                    <span class="fw-semibold">{{ $lot->warehouse->name ?? 'Sin almacén' }}</span>
                                </div>
                            </td>

                            <!-- Cantidad de Ingreso -->
                            <td>
                                <div class="stock-info">
                                    <div class="fw-bold fs-6">
                                        {{ number_format($lot->amount, 2) }}
                                    </div>
                                    <small class="text-muted">{{ $lot->product->metric->value ?? '' }}</small>
                                </div>
                            </td>

                            <!-- Cantidad Restante -->
                            <td>
                                <div class="{{ stockLevel($lot->amount, $lot->countProducts()) }}">
                                    <div class="fw-bold fs-6">
                                        {{ number_format($lot->countProducts(), 2) }}
                                    </div>
                                    <small class="text-muted">{{ $lot->product->metric->value ?? '' }}</small>
                                </div>
                            </td>

                            <!-- Fecha de Caducidad -->
                            <td>
                                @if ($lot->expiration_date)
                                    <div class="expiration-info">
                                        <div class="fw-semibold {{ $lot->isExpired() ? 'text-danger' : 'text-success' }}">
                                            {{ date('d/m/Y', strtotime($lot->expiration_date)) }}
                                        </div>
                                        @if ($lot->isExpired())
                                            <small class="text-danger fw-bold">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>CADUCADO
                                            </small>
                                        @else
                                            <small class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>VIGENTE
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Sin fecha</span>
                                @endif
                            </td>

                            <!-- Período -->
                            <td>
                                <div class="period-info">
                                    <div class="fw-semibold text-dark">
                                        {{ $lot->start_date && $lot->end_date
                                            ? date('d/m/Y', strtotime($lot->start_date)) . ' - ' . date('d/m/Y', strtotime($lot->end_date))
                                            : ($lot->start_date
                                                ? 'Desde: ' . date('d/m/Y', strtotime($lot->start_date))
                                                : ($lot->end_date
                                                    ? 'Hasta: ' . date('d/m/Y', strtotime($lot->end_date))
                                                    : '-')) }}
                                    </div>
                                </div>
                            </td>

                            <!-- Acciones -->
                            <td class="text-center">
                                <a class="btn btn-info btn-sm"
                                    href="{{ route('lot.traceability', $lot->id) }}"
                                    title="Ver">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('lot.edit', $lot->id) }}" class="btn btn-secondary btn-sm"
                                    title="Editar lote">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="confirmDelete({{ $lot->id }}, '{{ $lot->registration_number }}')"
                                    title="Eliminar lote">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->work_department_id == 1 ? 10 : 9 }}"
                                class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-box-seam text-muted fs-1 mb-3"></i>
                                    <h5 class="text-muted">No hay lotes para mostrar</h5>
                                    <p class="text-muted mb-0">No se encontraron lotes en el sistema.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if ($lots->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 px-3">
            <div class="text-muted">
                Mostrando {{ $lots->firstItem() ?? 0 }} a {{ $lots->lastItem() ?? 0 }} de {{ $lots->total() }} lotes
            </div>
            <div>
                {{ $lots->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif

    <!-- Mensaje de búsqueda sin resultados -->
    @if ($lots->isEmpty() && request()->has('search'))
        <div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
            <i class="bi bi-search me-2"></i>
            <div>
                <strong>No se encontraron resultados</strong><br>
                No hay lotes que coincidan con: "<strong>{{ request('search') }}</strong>"
            </div>
        </div>
    @endif

    @include('lot.create.modals.create')

    <!-- Estilos CSS -->
    <style>
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Estados de lotes */
        .lot-status-expired {
            background-color: rgba(220, 53, 69, 0.05) !important;
        }

        .lot-status-empty {
            background-color: rgba(108, 117, 125, 0.05) !important;
        }

        .lot-status-expiring {
            background-color: rgba(255, 193, 7, 0.05) !important;
        }

        .lot-status-low {
            background-color: rgba(23, 162, 184, 0.05) !important;
        }

        .lot-status-normal {
            background-color: rgba(40, 167, 69, 0.02) !important;
        }

        /* Iconos y badges */
        .product-icon {
            width: 32px;
            height: 32px;
            background-color: rgba(0, 123, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.75rem !important;
        }

        /* Progress bar */
        .progress {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 2px;
        }

        /* Empty state */
        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state i {
            display: block;
            margin: 0 auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .badge {
                font-size: 0.7rem !important;
                padding: 0.375rem 0.5rem !important;
            }

            .btn-group .btn {
                padding: 0.25rem 0.5rem;
            }
        }

        /* Animaciones */
        .table tbody tr {
            animation: fadeInUp 0.3s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hover effects */
        .warehouse-info:hover,
        .stock-info:hover,
        .remaining-stock:hover,
        .expiration-info:hover,
        .period-info:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
    </style>

    <!-- JavaScript -->
    <script>
        function confirmDelete(lotId, lotNumber) {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Confirmar Eliminación
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="mb-3">¿Estás seguro de que deseas eliminar el lote?</p>
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                            <strong>Lote:</strong> ${lotNumber}<br>
                            <small>Esta acción no se puede deshacer.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancelar
                    </button>
                    <a href="{{ route('lot.destroy', ['id' => 'LOT_ID']) }}" 
                       class="btn btn-danger"
                       onclick="this.innerHTML='<i class=\'bi bi-hourglass-split me-1\'></i>Eliminando...'; this.disabled=true;">
                        <i class="bi bi-trash-fill me-1"></i>Eliminar
                    </a>
                </div>
            </div>
        </div>
    `;

            // Reemplazar LOT_ID con el ID real
            modal.innerHTML = modal.innerHTML.replace(/LOT_ID/g, lotId);

            document.body.appendChild(modal);
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();

            // Limpiar el modal después de cerrarlo
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        }

        // Tooltips para botones
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Animación de entrada para las filas
            const rows = document.querySelectorAll('.lot-row');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });

            // Contador de lotes por estado
            updateStatusCount();
        });

        function updateStatusCount() {
            const statuses = ['expired', 'empty', 'expiring', 'low', 'normal'];
            const counts = {};

            statuses.forEach(status => {
                counts[status] = document.querySelectorAll(`.lot-status-${status}`).length;
            });

            // Aquí puedes agregar lógica para mostrar contadores si es necesario
            console.log('Estado de lotes:', counts);
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Cerrar modales abiertos
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    bootstrap.Modal.getInstance(modal).hide();
                });
            }
        });
    </script>
@endsection