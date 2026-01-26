<div class="table-responsive">
    <table class="table table-hover table-bordered table-striped align-middle mb-4">
        <thead class="table-light sticky-top">
            <tr>
                <th scope="col" class="text-center" style="width: 60px;">Foto</th>
                <th scope="col" class="text-start">Nombre</th>
                <th scope="col" class="text-center">Estado</th>
                <th scope="col" class="text-center">Línea</th>
                <th scope="col" class="text-center">Obsoleto</th>
                <th scope="col" class="text-center">Básico</th>
                <th scope="col" class="text-center" style="width: 120px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @if(count($products) > 0)
                @foreach($products as $i => $product)
                    <tr class="product-row">
                        <td>
                            @if($product->photo)
                                <img src="{{ asset($product->photo) }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;" alt="Miniatura de imagen">
                            @else
                                <span class="text-muted"><i class="bi bi-image fs-3"></i></span>
                            @endif
                        </td>
                        <td class="text-start">
                            <div class="fw-semibold text-dark">{{ $product->name }}</div>
                        </td>
                        <td>
                            @if($product->status == 1)
                                <span class="badge bg-success"><i class="bi bi-check2"></i> Activo</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-x"></i> Inactivo</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $lineName = '-';
                                if(isset($lineBs)) {
                                    foreach($lineBs as $lineb) {
                                        if($product->linebuss_id == $lineb->id) {
                                            $lineName = $lineb->name;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <span class="fw-semibold">{{ $lineName }}</span>
                        </td>
                        <td>
                            @if($product->obsolete == 1)
                                <span class="badge bg-danger"><i class="bi bi-x"></i> Sí</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check2"></i> No</span>
                            @endif
                        </td>
                        <td>
                            @if($product->basic == 1)
                                <span class="badge bg-success"><i class="bi bi-check2"></i> Sí</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-x"></i> No</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('pesticide.show', $product->id) }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="Ver producto">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('pesticide.edit', $product->id) }}" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Editar producto">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-box-seam text-muted fs-1 mb-3"></i>
                            <h5 class="text-muted">No hay productos para mostrar</h5>
                            <p class="text-muted mb-0">No se encontraron productos en el sistema.</p>
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- CSS inline -->
<style>
.table-responsive {
    overflow: visible !important;
}
.table {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    position: relative;
    z-index: 0;
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
    animation: fadeInUp 0.3s ease forwards;
    position: relative;
    z-index: 0;
}
.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.empty-state {
    padding: 3rem 1rem;
}
.empty-state i {
    display: block;
    margin: 0 auto;
}
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
        position: relative;
        z-index: 0;
    }
    .badge {
        font-size: 0.7rem !important;
        padding: 0.375rem 0.5rem !important;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
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
</style>

<script>
    // Animación de entrada para las filas y tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.product-row');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.05}s`;
        });
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>