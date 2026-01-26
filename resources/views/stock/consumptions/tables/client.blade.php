<div class="table-responsive">
    <table class="table table-hover" id="client-consumption-table">
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
                    <td class="text-start ">
                        <a href="{{ route('consumption.product.detail', [
                            'id' => $consumption['product']->id,
                            'start_date' => $start ?? now()->subMonth()->format('Y-m-d'),
                            'end_date' => $end ?? now()->format('Y-m-d'),
                            'customer_id' => $customerId,
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
                            No se encontraron consumos para este cliente en el período seleccionado
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $consumptions->links('pagination::bootstrap-5') }}

</div>

<script>
    $(document).ready(function() {
        $('#client-consumption-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            },
            order: [
                [2, 'desc']
            ], // Ordenar por cantidad total descendente
            pageLength: 10,
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="bi bi-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Consumos por Cliente - ' + moment().format('DD/MM/YYYY'),
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="bi bi-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Consumos por Cliente - ' + moment().format('DD/MM/YYYY'),
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Imprimir',
                    className: 'btn btn-secondary btn-sm',
                    title: 'Consumos por Cliente - ' + moment().format('DD/MM/YYYY'),
                    exportOptions: {
                        columns: [0, 1, 2, 3]
                    }
                }
            ]
        });
    });
</script>

<style>
    .dataTables_wrapper .dt-buttons {
        float: right;
        margin-bottom: 1rem;
        gap: 0.5rem;
        display: flex;
    }

    #client-consumption-table img {
        transition: transform 0.2s;
    }

    #client-consumption-table img:hover {
        transform: scale(2);
    }
</style>
