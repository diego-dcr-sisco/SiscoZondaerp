<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Producto</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->unit }}</td>
                <td>{{ $product->description }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('purchase-requisition.edit', $requisition->id) }}" class="btn btn-primary">
    <i class="bi bi-pencil"></i> {{ __('buttons.edit') }}
</a>