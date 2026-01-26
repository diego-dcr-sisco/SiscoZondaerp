<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" action="{{ route('warehouse.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Crear almacen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label is-required" for="name">Nombre </label>
                    <input type="text" class="form-control" id="name" name="name" value=""
                        placeholder="Example" required />
                </div>
                <div class="mb-3">
                    <label class="form-label is-required" for="name">Sucursal/Delegación </label>
                    <select class="form-select" id="branch" name="branch_id" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}"> {{ $branch->name }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="floatingTextarea">Observaciones</label>
                    <textarea class="form-control" placeholder="Example of observations" id="observations" name="observations"
                        rows="5"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name">Ligar a técnico</label>
                    <select class="form-select" id="technician" name="technician_id">
                        <option value="" selected> Sin técnico </option>
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}"> {{ $technician->user->name }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="allow-material-receipts"
                            name="allow_material_receipts" onchange="$(this).val(this.checked ? 1 : null);" checked>
                        <label class="form-check-label" for="allow-material-receipts">
                            Permite recibos de material
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="is-matrix"
                            name="is_matrix" onchange="$(this).val(this.checked ? 1 : null);" />
                        <label class="form-check-label" for="is-matrix">
                            ¿Es matriz?
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>