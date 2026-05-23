
<div class="table-responsive border-top pt-3 mt-3">
    <h2>Productos en almacén</h2>
    <table class="table table-responsive shadow-sm table-bordered table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre del Producto</th>
                <th>Stock general</th>
                <th>Cantidad en almacén</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->description }}</td>
                    <td>{{ $product->base_stock }}</td>
                    <td style="{{ $product->quantity > 0 ? 'color: green' : 'color: red' }}">{{ $product->quantity }}
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-secondary"
                            onclick="openUpdateModal({{ $product }})">
                            <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                        </button>
                        {{-- <a href="{{ route('stock.indirect.edit', $product->id) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-pencil-square"></i> {{ __('buttons.edit') }}
                        </a> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $products->links('pagination::bootstrap-5') }}
</div>


<!-- Modal -->
<div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProductModalLabel">Detalles del Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="updateProductForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="productId" name="id">
                    <div class="form-group">
                        <label for="description" class="is-required">Nombre</label>
                        <input type="text" class="form-control" id="description" name="description">
                    </div>
                    <div class="form-group mt-2">
                        <label for="code">Código</label>
                        <input type="text" class="form-control" id="code" name="code">
                    </div>
                    <div class="form-group mt-2">
                        <label for="base-stock">Stock Base</label>
                        <input type="number" class="form-control" id="base-stock" name="base_stock" min="0"
                            default="0" placeholder="Cantidad de producto que tiene que haber en almacén">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success btn-block" form="updateProductForm">Actualizar producto
                    en almacén</button>
                <form action="{{ route('stock.indirect.destroy', $product->id) }}" method="POST"
                    style="display:inline;"
                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-block btn-outline-danger">Eliminar producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openUpdateModal(product) {
        document.getElementById('productId').value = product.id
        document.getElementById('description').value = product.description;
        document.getElementById('code').value = product.code;
        document.getElementById('base-stock').value = product.base_stock || '';

        const form = document.getElementById('updateProductForm');
        form.action = "{{ route('stock.indirect.update', '') }}/" + product.id;

        $('#updateProductModal').modal('show');
    }
</script>
