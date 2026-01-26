<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurchaseModalLabel">Crear Nueva Solicitud de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('purchase-requisition.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label for="company" class="form-label is-required">Empresa Destino</label>
                            <input type="text" class="form-control" id="company" name="company" required>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="final_date" class="form-label">Fecha a Requerir</label>
                            <input type="date" class="form-control" id="final_date" name="final_date" required>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="company_address" class="form-label is-required">Dirección Empresa Destino</label>
                            <input type="text" class="form-control" id="company_address" name="company_address" required>
                        </div>
                        
                    </div>
                    <div class="mb-3">
                        <label for="product_name" class="form-label is-required">Nombre del Producto</label>
                        <input type="text" class="form-control" id="product_name" name="product_name[]" required
                        placeholder="Descripcion del material, marca, gramaje, etc.">
                    </div>
                    <div class="row">
                        <!-- unidad -->
                        <div class="col-lg-6 mb-3">
                            <label for="product_unit" class="form-label" is-required>Unidad</label>
                            <select class="form-select" id="product_unit" name="product_unit[]" required>
                                <option value="pza">Pieza</option>
                                <option value="mg">Miligramo</option>
                                <option value="g">Gramo</option>
                                <option value="kg">Kilogramo</option>
                                <option value="m">Metro</option>
                                <option value="ml">Mililitro</option>
                                <option value="l">Litro</option>
                                <option value="paq">Paquete</option>
                                <option value="doc">Docena</option>
                                <option value="bulto">Bulto</option>
                                <option value="rollo">Rollo</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <div class="col-lg-6 mb-3">
                            <label for="product_quantity" class="form-label is-required">Cantidad</label>
                            <input type="number" class="form-control" id="product_quantity" name="product_quantity[]" min="0.5" step="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="product_price" class="form-label is-required">Precio</label>
                        <input type="number" class="form-control" id="product_price" name="product_price[]" min="0.5" step="1" required>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addProductButton">Añadir Producto</button>

                    <script>
                        document.getElementById('addProductButton').addEventListener('click', function() {
                            var productFields = `
                                <div class="mb-3">
                                    <label for="product_name" class="form-label is-required">Nombre del Producto</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name[]" required>
                                </div>
                                <div class="mb-3">
                                    <label for="product_quantity" class="form-label is-required">Cantidad</label>
                                    <input type="number" class="form-control" id="product_quantity" name="product_quantity[]" required>
                                </div>
                                <div class="mb-3">
                                    <label for="product_price" class="form-label is-required">Precio</label>
                                    <input type="number" class="form-control" id="product_price" name="product_price[]" required>
                                </div>
                            `;
                            var form = document.querySelector('form');
                            form.insertAdjacentHTML('beforeend', productFields);
                        });
                    </script>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>