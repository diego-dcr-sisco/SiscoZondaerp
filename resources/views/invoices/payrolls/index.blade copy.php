@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row m-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center border-bottom ps-4 p-2">
                <div>
                    <span class="text-black fw-bold fs-4">Listado de Nóminas</span>
                    <span class="badge bg-primary ms-2">{{ $payrolls->count() }} registros</span>
                </div>
                <a href="{{ route('payrolls.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i> Nueva Nómina
                </a>
            </div>

            <!-- Filtros -->
            <div class="border rounded shadow p-3 mb-3 mt-3">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label small fw-bold">Folio</label>
                        <input type="text" class="form-control form-control-sm" id="filterFolio" placeholder="Buscar folio...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label small fw-bold">Empleado</label>
                        <input type="text" class="form-control form-control-sm" id="filterEmployee" placeholder="Nombre del empleado...">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small fw-bold">Estado</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">Todos</option>
                            <option value="draft">Borrador</option>
                            <option value="stamped">Timbrada</option>
                            <option value="cancelled">Cancelada</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small fw-bold">Fecha Inicio</label>
                        <input type="date" class="form-control form-control-sm" id="filterStartDate">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small fw-bold">Fecha Fin</label>
                        <input type="date" class="form-control form-control-sm" id="filterEndDate">
                    </div>
                    <div class="col-12">
                        <button type="button" id="btnApplyFilters" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i> Aplicar Filtros
                        </button>
                        <button type="button" id="btnClearFilters" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensajes de sesión -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Tabla de nóminas -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="payrollsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th width="80">Folio</th>
                                    <th width="120">Fecha Pago</th>
                                    <th>Empleado</th>
                                    <th width="100">RFC</th>
                                    <th width="120">Tipo</th>
                                    <th width="120">Total</th>
                                    <th width="100">Estado</th>
                                    <th width="120">UUID</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payrolls as $payroll)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $payroll->folio }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $payroll->payment_date->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="fas fa-user text-white small"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <div class="fw-semibold">{{ Str::limit($payroll->receiver_name, 30) }}</div>
                                                    <small class="text-muted">{{ $payroll->employee_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="small">{{ $payroll->receiver_rfc }}</code>
                                        </td>
                                        <td>
                                            @if($payroll->payroll_type == 'O')
                                                <span class="badge bg-info">Ordinaria</span>
                                            @else
                                                <span class="badge bg-warning">Extraordinaria</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ $payroll->days_paid }} días</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">${{ number_format($payroll->total, 2) }}</span>
                                            <br>
                                            <small class="text-muted">
                                                P: ${{ number_format($payroll->total_perceptions, 2) }}<br>
                                                D: ${{ number_format($payroll->total_deductions, 2) }}
                                            </small>
                                        </td>
                                        <td>
                                            @switch($payroll->status)
                                                @case('draft')
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-edit me-1"></i> Borrador
                                                    </span>
                                                    @break
                                                @case('stamped')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i> Timbrada
                                                    </span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i> Cancelada
                                                    </span>
                                                    @break
                                                @case('error')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-exclamation me-1"></i> Error
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ $payroll->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($payroll->uuid)
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-hashtag text-muted me-1 small"></i>
                                                    <small class="text-truncate" style="max-width: 120px;" 
                                                           title="{{ $payroll->uuid }}">
                                                        {{ Str::limit($payroll->uuid, 12) }}
                                                    </small>
                                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1" 
                                                            onclick="copyToClipboard('{{ $payroll->uuid }}')"
                                                            title="Copiar UUID">
                                                        <i class="fas fa-copy small"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small">No timbrada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <!-- Ver detalles -->
                                                <a href="{{ route('payroll.show', $payroll->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Editar -->
                                                <a href="{{ route('payroll.edit', $payroll->id) }}" 
                                                   class="btn btn-sm btn-outline-warning {{ $payroll->status == 'stamped' ? 'disabled' : '' }}" 
                                                   title="Editar nómina"
                                                   @if($payroll->status == 'stamped') onclick="return false;" @endif>
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Timbrar -->
                                                @if($payroll->status == 'draft')
                                                    <form action="{{ route('payroll.stamp', $payroll->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                title="Timbrar nómina"
                                                                onclick="return confirm('¿Estás seguro de timbrar esta nómina?')">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Descargar PDF -->
                                                @if($payroll->status == 'stamped')
                                                    <a href="{{ route('payroll.download.pdf', $payroll->id) }}" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       title="Descargar PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                @endif

                                                <!-- Descargar XML -->
                                                @if($payroll->status == 'stamped')
                                                    <a href="{{ route('payroll.download.xml', $payroll->id) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Descargar XML">
                                                        <i class="fas fa-file-code"></i>
                                                    </a>
                                                @endif

                                                <!-- Cancelar -->
                                                @if($payroll->status == 'stamped')
                                                    <form action="{{ route('payroll.cancel', $payroll->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-dark" 
                                                                title="Cancelar nómina"
                                                                onclick="return confirm('¿Estás seguro de cancelar esta nómina?')">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Eliminar -->
                                                @if(in_array($payroll->status, ['draft', 'error']))
                                                    <form action="{{ route('payroll.destroy', $payroll->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Eliminar nómina"
                                                                onclick="return confirm('¿Estás seguro de eliminar esta nómina?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                                <h5>No se encontraron nóminas</h5>
                                                <p>Comienza creando tu primera nómina</p>
                                                <a href="{{ route('payrolls.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i> Crear Nómina
                                                </a>
                                            </div>
                                        </td>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       
                                    </tr>
                                @endforelse
                            </tbody>                                                                                                                                                                                                                                                                                                                                                
                        </table>
                    </div>

                    <!-- Paginación -->
                    {{--@if($payrolls->hasPages())
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Mostrando {{ $payrolls->firstItem() }} - {{ $payrolls->lastItem() }} de {{ $payrolls->total() }} registros
                                </div>
                                <div>
                                    {{ $payrolls->links() }}
                                </div>
                            </div>
                        </div>
                    @endif--}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para vista rápida del UUID -->
<div class="modal fade" id="uuidModal" tabindex="-1" aria-labelledby="uuidModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uuidModalLabel">UUID de la Nómina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <code id="uuidFullText" class="fs-6"></code>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="copyModalUuid()">
                    <i class="fas fa-copy me-2"></i> Copiar UUID
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Mostrar notificación de éxito
            showToast('UUID copiado al portapapeles', 'success');
        }, function(err) {
            //console.error('Error al copiar: ', err);
            showToast('Error al copiar el UUID', 'error');
        });
    }

    function showUuidModal(uuid) {
        $('#uuidFullText').text(uuid);
        $('#uuidModal').modal('show');
    }

    function copyModalUuid() {
        const uuid = $('#uuidFullText').text();
        copyToClipboard(uuid);
        $('#uuidModal').modal('hide');
    }

    function showToast(message, type = 'info') {
        // Implementar notificación toast simple
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Filtros en el cliente (puedes implementar filtros del lado del servidor también)
    $(document).ready(function() {
        $('#btnApplyFilters').on('click', function() {
            applyFilters();
        });

        $('#btnClearFilters').on('click', function() {
            $('#filterFolio').val('');
            $('#filterEmployee').val('');
            $('#filterStatus').val('');
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
            applyFilters();
        });

        function applyFilters() {
            const folio = $('#filterFolio').val().toLowerCase();
            const employee = $('#filterEmployee').val().toLowerCase();
            const status = $('#filterStatus').val();
            const startDate = $('#filterStartDate').val();
            const endDate = $('#filterEndDate').val();

            $('#payrollsTable tbody tr').each(function() {
                const $row = $(this);
                const rowFolio = $row.find('td:eq(0)').text().toLowerCase();
                const rowEmployee = $row.find('td:eq(2)').text().toLowerCase();
                const rowStatus = $row.find('td:eq(6) .badge').text().toLowerCase();
                const rowDate = $row.find('td:eq(1)').text();

                let show = true;

                if (folio && !rowFolio.includes(folio)) {
                    show = false;
                }
                if (employee && !rowEmployee.includes(employee)) {
                    show = false;
                }
                if (status) {
                    const statusMap = {
                        'draft': 'borrador',
                        'stamped': 'timbrada', 
                        'cancelled': 'cancelada',
                        'error': 'error'
                    };
                    if (statusMap[status] !== rowStatus) {
                        show = false;
                    }
                }
                if (startDate || endDate) {
                    // Implementar filtrado por fecha si es necesario
                }

                $row.toggle(show);
            });
        }

        // Hacer filas clickeables para ver detalles
        $('#payrollsTable tbody tr').on('click', function(e) {
            // Evitar que se active cuando se hace click en un botón
            if (!$(e.target).closest('button, a').length) {
                const payrollId = $(this).find('td:eq(0)').data('id'); // Necesitarías agregar data-id
                if (payrollId) {
                    window.location.href = `/payroll/${payrollId}`;
                }
            }
        });
    });
</script>

<style>
.table-hover tbody tr:hover {
    cursor: pointer;
    background-color: rgba(0, 123, 255, 0.075) !important;
}

.badge {
    font-size: 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

.table td {
    vertical-align: middle;
}

/* Estilos para estados */
.status-draft { background-color: #f8f9fa; }
.status-stamped { background-color: #d1edff; }
.status-cancelled { background-color: #ffe6e6; }
.status-error { background-color: #fff3cd; }
</style>
@endpush