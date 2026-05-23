<table class="table table-bordered table-striped text-center align-middle">
    <thead>
        <tr>
            <th>#</th>
            <th>Producto</th>
            <th>Lote</th>
            <th>Orden aplicado: Cliente</th>
            <th>Orden aplicado: Servicios</th>
            <th>Orden aplicado: Fecha</th>
            <th>Técnico/Administrativo</th>
            <th>Cantidad</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movements as $index => $movement)
            <tr>
                <th>{{ ++$index }}</th>
                <td>{{ $movement->product->name ?? '-' }}</td>
                <td>{{ $movement->lot->registration_number ?? '-' }}</td>
                <td>{{ $movement->order->customer->name ?? '-' }}</td>
                <td>{{ implode(', ', $movement->order->services->pluck('name')->toArray()) }}</td>
                <td>{{ date('d/m/Y', strtotime($movement->order->programmed_date)) }}</td>
                <td>{{ $movement->technician ? $movement->technician->user->name : $movement->order->customer->administrative->name ?? '-' }}
                </td>
                <td class="text-primary fw-bold">
                    {{ $movement->amount ? $movement->amount . ' ' . $movement->product->metric->value : '-' }}</td>
                <td>
                    {{-- <a href="{{ route('warehouse.pdf', ['id' => $movement->id]) }}"
                            class="btn btn-dark btn-sm"><i class="bi bi-file-pdf-fill"></i> Imprimir</a> --}}
                    @if(auth()->user()->work_department_id == 1)
                        <a href="{{ route('stock.destroy.movement', ['id' => $movement->id, 'type' => 2]) }}" class="btn btn-danger btn-sm"><i
                            class="bi bi-trash-fill"></i> {{ __('buttons.delete') }}</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td class="text-danger" colspan="10">No hay movimientos en este almacén.</td>
            </tr>
        @endforelse
    </tbody>
</table>
{{ $movements->links('pagination::bootstrap-5') }}

