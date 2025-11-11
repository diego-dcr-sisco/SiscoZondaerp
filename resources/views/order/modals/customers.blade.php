<div class="modal fade" id="customerModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">Seleccionar Clientes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-funnel-fill"></i></span>
                    <input type="text" class="form-control form-control-sm w-25" id="customerSearch"
                        placeholder="Filtrar cliente...">
                </div>
                <div id="customer-list"></div>
            </div>
            <div class="modal-footer">
                <div class="me-auto">
                    <span id="selected-count" class="badge bg-primary">0 cliente seleccionado(s)</span>
                </div>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn  btn-primary" onclick="confirmSelection()">Confirmar</button>
            </div>
        </div>
    </div>
</div>
