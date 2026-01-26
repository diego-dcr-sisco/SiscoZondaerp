<h2>Nuevos productos agregados recientemente</h2>
<p style="color: red; font-size:small;">* Productos pedidos recientemente en solicitudes de compra </p>
<div class="table-responsive mb-3">
    <table class="table table-bordered  rounded shadow-sm table-striped">
        <thead>
            <tr>
                <th>Nombre del Producto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($newProducts as $product)
                <tr>
                    <td>{{ $product->description }}</td>
                    <td>
                        <!-- Botón para abrir el modal -->
                        <button type="button" class="btn btn-sm btn-success"
                            onclick="openStoreModal({{ $product }})">
                            <i class="bi bi-journal-plus"></i> Agregar a almacén
                            {{-- <i class="bi bi-file-plus"></i> Agregar a almacén --}}
                        </button>

                        <form action="{{ route('stock.indirect.destroy', $product->id) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash-fill"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


<!-- Modal -->
<div class="modal fade" id="storeProductModal" tabindex="-1" role="dialog" aria-labelledby="storeProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="storeProductModalLabel">Detalles del Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="storeProductForm" method="POST">
                    @csrf
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
                <button type="submit" class="btn btn-success btn-block w-100" form="storeProductForm">Crear producto en
                    almacén</button>
            </div>
        </div>
    </div>
</div>


<script>
    function openStoreModal(product) {
        document.getElementById('productId').value = product.id
        document.getElementById('description').value = product.description;
        document.getElementById('code').value = product.code;
        document.getElementById('base-stock').value = product.base_stock || '';

        const form = document.getElementById('storeProductForm');
        form.action = "{{ route('stock.indirect.store', '') }}/" + product.id;

        $('#storeProductModal').modal('show');
    }
</script>
