<div class="table-responsive">
    <table class="table table-hover table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Acciones</th>
            <th>Cliente</th>
            <th>Producto</th>
            <th>Cantidad</th>
            
        </tr>
    </thead>
    <tbody>
        @forelse($consumptions as $orderIndex => $order)
        @foreach($order->products as $productIndex => $orderProduct)
            <tr>
                <td>{{ $orderIndex + 1 }}</td>

                <td >
                    <a  class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
                <td>{{ $order->customer->name }}</td>
                <td>{{ $orderProduct->product->name }}</td>
                <td>{{ $orderProduct->amount}}</td>
                
            </tr>
        @endforeach
    @empty
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No hay datos para mostrar
                </div>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>