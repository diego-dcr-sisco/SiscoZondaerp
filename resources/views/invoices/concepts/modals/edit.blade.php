    <div class="modal fade" id="editConceptModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('invoices.concept.update') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="edit_staticBackdropLabel">Nuevo concepto para Facturacion</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 col-12 mb-3">
                                <label for="product_key" class="form-label is-required">Clave de
                                    servicio/producto</label>
                                <input type="text" name="product_key" id="edit_product_key" class="form-control"
                                    placeholder="Clave de servicio" required>
                            </div>
                            <div class="col-md-8 col-12 mb-3">
                                <label for="name" class="form-label is-required">Objeto de impuesto</label>
                                <select class="form-select" id="edit_tax_object" name="tax_object" required>
                                    <option value="" disabled>Seleccione objeto de impuesto</option>
                                    @forelse($taxObjects as $index => $taxObject)
                                        <option value="{{ $index }}" {{ $index == '02' ? 'selected' : '' }}>
                                            {{ $index }} - {{ $taxObject }}</option>
                                    @empty
                                        <option value="">Sin objeto de impuesto</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <label for="identification_number" class="form-label is-required">Codigo
                                    Identificacion</label>
                                <input type="text" name="identification_number" id="edit_identification_number"
                                    class="form-control" placeholder="Codigo de identificacion de concepto" required>
                            </div>
                            <div class="col-md-8 col-12 mb-3">
                                <label for="name" class="form-label is-required">Nombre</label>
                                <input type="text" name="name" id="edit_name" class="form-control"
                                    placeholder="Nombre del concepto" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label is-required">Descripción</label>
                                <input type="text" name="description" id="edit_description" class="form-control"
                                    placeholder="Descripción de las actividades o desglose   del concepto" required>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <label for="amount" class="form-label is-required">Monto</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" id="edit_amount" class="form-control"
                                        min="0" step="0.01" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <label for="tax_rate" class="form-label is-required">Tasa de impuesto</label>
                                <div class="input-group">
                                    <input type="number" name="tax_rate" id="edit_tax_rate" class="form-control"
                                        value="16" step="0.01" min="0" max="100" required>
                                    <span class="input-group-text" id="edit_basic-addon2">%</span>
                                </div>
                                <div class="form-text"> El impuesto se debe colocar en valor porcentual. Ejemplo: 16% =
                                    0.16
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <label for="unit_code" class="form-label is-required">Clave de unidad</label>
                                <select name="unit_code" id="edit_unit_code" class="form-select" required>
                                    <option value="" disabled>Seleccione unidad</option>
                                    @forelse ($unitCodes as $index => $unit)
                                        <option value="{{ $index }}">{{ $unit }}</option>
                                    @empty
                                        <option value="">Sin unidad</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <input type="hidden" id="concept_id" name="concept_id" value="" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editConcept(element) {
            const conceptData = element.getAttribute('data-concept');
            const parsed = JSON.parse(conceptData);

            $('#edit_product_key').val(parsed.product_key);
            $('#edit_tax_object').val(parsed.tax_object);
            $('#edit_identification_number').val(parsed.identification_number);
            $('#edit_name').val(parsed.name);
            $('#edit_description').val(parsed.description);
            $('#edit_amount').val(parsed.amount);
            $('#edit_tax_rate').val(parsed.tax_rate * 100);
            $('#edit_unit_code').val(parsed.unit_code);
            $('#concept_id').val(parsed.id)

            $('#editConceptModal').modal('show');
        }
    </script>
