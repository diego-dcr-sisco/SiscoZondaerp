<div class="table-responsive">
    <table class="table table-hover" id="consumption-table">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Ver</th>
                <th>Producto</th>
                <th>Cantidad Total</th>
                <th>Unidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consumptions as $index => $consumption)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-start">
                        <a href="{{ route('consumption.product.detail', [
                            'id' => $consumption['product']->id,
                            'start_date' => $start ?? now()->subMonth()->format('Y-m-d'),
                            'end_date' => $end ?? now()->format('Y-m-d')
                        ]) }}" 
                            class="btn btn-sm btn-outline-primary" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if ($consumption['product']->image_path)
                                <img src="{{ Storage::url($consumption['product']->image_path) }}" alt="Producto"
                                    class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            @endif
                            {{ $consumption['product']->name }}
                        </div>
                    </td>
                    <td>{{ number_format($consumption['amount'], 2) }}</td>
                    <td>{{ $consumption['product']->metric->value }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No se encontraron consumos en el período seleccionado
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>