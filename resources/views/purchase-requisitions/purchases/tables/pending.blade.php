<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Producto</th>
            <th>Cantidad en almacen</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->unit }}</td>
                <td>{{ $product->description }}</td>
                <td style="color: {{ $amounts[$product->description] < 0 ? 'red' : 'green' }};">
                    {{ $amounts[$product->description] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>