<!-- Modal de Servicios -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="serviceModalLabel">
                    <i class="fas fa-concierge-bell me-2"></i>Seleccionar Servicios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Barra de búsqueda -->
                <div class="row mb-3">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-funnel-fill"></i></span>
                        <input type="text" class="form-control form-control-sm w-25" id="serviceSearch"
                            placeholder="Filtrar servicio...">
                    </div>

                    {{-- <div class="col-md-6 text-end">
                        <button class="btn btn-outline-secondary btn-sm" onclick="toggleSelectAllServices(true)">
                            <i class="fas fa-check-square me-1"></i>Seleccionar Todos
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-2" onclick="toggleSelectAllServices(false)">
                            <i class="fas fa-times-circle me-1"></i>Deseleccionar Todos
                        </button>
                    </div> --}}
                </div>

                <!-- Lista de servicios -->
                <div id="services-list"></div>
            </div>
            <div class="modal-footer">
                <div class="me-auto">
                    <span id="selected-services-count" class="badge bg-primary">0 servicio(s) seleccionado(s)</span>
                    <span id="total-cost" class="badge bg-success ms-2">Total: $0.00</span>
                </div>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmServicesSelection()">
                    <i class="fas fa-check me-1"></i>Confirmar Selección
                </button>
            </div>
        </div>
    </div>
</div>
