<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Producto</th>
            <th>Proveedor 1</th>
            <th>Costo 1</th>
            <th>Proveedor 2</th>
            <th>Costo 2</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->unit }}</td>
                <td>{{ $product->description }}</td>
                <td>{{ $product->supplier1->name }}</td>
                <td>{{ $product->supplier1_cost }}</td>
                <td>{{ $product->supplier2 ? $product->supplier2->name : 'sin proveedor' }}</td>
                <td>{{ $product->supplier2_cost }}</td>
            </tr>
        @endforeach
        @if (isset($total_1) && isset($total_2))
            <tr>
                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                <td>$ {{ $total_1 }}</td>
                <td></td>
                <td>$ {{ $total_2 }}</td>
            </tr>
        @endif
    </tbody>
</table>