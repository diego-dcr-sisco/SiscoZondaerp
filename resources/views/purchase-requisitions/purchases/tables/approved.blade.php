<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Producto</th>
            <th>Proveedor </th>
            <th>Costo unitario</th>
            <th>Costo total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->quantity }}</td>
                <td>{{ $product->unit }}</td>
                <td>{{ $product->description }}</td>
                <td>{{ $product->approvedSupplier->name }}</td>
                @if ($product->approved_supplier_id == $product->supplier1_id)
                    <td>$ {{ $product->supplier1_cost }}</td>
                @else
                    <td>$ {{ $product->supplier2_cost }}</td>
                @endif
                <td></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5" class="text-right"><strong>Total:</strong></td>
            <td>$ 00.00</td>
        </tr>
    </tbody>
</table>
@if ($requisition->status == 'Aprobada')
    <div class="text-right mt-3">
        <form action="{{ route('purchase-requisition.complete', $requisition->id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success">Finalizar Solicitud</button>
        </form>
    </div>
@endif

@if ($requisition->status == 'Finalizada')
    <div class="text-right mt-3">
        <form action="{{ route('purchase-requisition.destroy', $requisition->id) }}" method="POST"
            style="display:inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"
                onclick="return confirm('¿Estás seguro de que deseas eliminar esta solicitud de compra?')">
                <i class="bi bi-trash-fill"></i> Eliminar
            </button>
        </form>
        <a href="{{ route('purchase-requisition.pdf', $requisition->id) }}" class="btn btn-dark btn-sm">
            <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF de orden de compra
        </a>
    </div>
@endif

<script>
    function calculateProductCost() {
        let total = 0;
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            if (index < rows.length - 1) {
                const quantity = parseFloat(row.cells[0].textContent) || 0;
                const cost = parseFloat(row.cells[4].textContent.replace('$', '')) || 0;
                const productTotal = quantity * cost;
                total += productTotal;
                row.cells[5].textContent = `$ ${productTotal.toFixed(2)}`;
            }
        });
    }

    function calculateTotalCost() {
        let total = 0;
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            if (index < rows.length - 1) {
                const quantity = parseFloat(row.cells[0].textContent) || 0;
                const cost = parseFloat(row.cells[4].textContent.replace('$', '')) || 0;
                total += quantity * cost;
            }
        });
        const totalRow = rows[rows.length - 1];
        totalRow.cells[totalRow.cells.length - 1].textContent = `$ ${total.toFixed(2)}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        calculateProductCost();
        calculateTotalCost();
    });
</script>
