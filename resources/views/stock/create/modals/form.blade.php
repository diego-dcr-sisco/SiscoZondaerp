<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form class="modal-content border-0 shadow-sm needs-validation" action="{{ route('stock.store') }}" method="POST"
            id="warehouseForm" novalidate>
            @csrf

            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title text-primary fw-bold mb-1" id="createModalLabel">
                        <i class="bi bi-building-add me-2"></i>
                        Crear almacén
                    </h5>
                    <div class="small text-muted">Registra la información operativa del nuevo almacén.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-info-circle me-2"></i>
                            Información básica
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" for="warehouse_name">
                                    <i class="bi bi-building me-1"></i>
                                    Nombre del almacén <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="warehouse_name" name="name" value="{{ old('name') }}" required minlength="3"
                                    maxlength="255" placeholder="Ej: Almacén Central Norte" autocomplete="off">
                                <div class="invalid-feedback">
                                    Ingrese un nombre válido para el almacén.
                                </div>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" for="branch_select">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    Sucursal/Delegación <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_select"
                                    name="branch_id" required>
                                    <option value="" selected disabled>Seleccione una sucursal</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Seleccione una sucursal.
                                </div>
                                @error('branch_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" for="technician_select">
                                    <i class="bi bi-person-gear me-1"></i>
                                    Técnico asociado
                                </label>
                                <select class="form-select @error('technician_id') is-invalid @enderror"
                                    id="technician_select" name="technician_id">
                                    <option value="" selected>Sin técnico asignado</option>
                                    @foreach ($technicians as $technician)
                                        <option value="{{ $technician->id }}"
                                            {{ old('technician_id') == $technician->id ? 'selected' : '' }}>
                                            {{ $technician->user->name ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Opcional. Técnico responsable del almacén.
                                </div>
                                @error('technician_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" for="observations_textarea">
                                    <i class="bi bi-chat-text me-1"></i>
                                    Observaciones
                                </label>
                                <textarea class="form-control @error('observations') is-invalid @enderror" id="observations_textarea"
                                    name="observations" rows="4" maxlength="1000"
                                    placeholder="Ubicación, uso principal o notas del almacén...">{{ old('observations') }}</textarea>
                                <div class="form-text">
                                    <span id="charCount">{{ strlen(old('observations', '')) }}</span>/1000 caracteres
                                </div>
                                @error('observations')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-gear me-2"></i>
                            Configuración del almacén
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="h-100 bg-light rounded p-3 text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input type="hidden" name="allow_material_receipts" value="0">
                                        <input class="form-check-input form-check-input-lg" type="checkbox"
                                            role="switch" id="allow_receipts_checkbox"
                                            name="allow_material_receipts" value="1"
                                            {{ old('allow_material_receipts', true) ? 'checked' : '' }}>
                                    </div>
                                    <label class="form-check-label fw-semibold mt-2" for="allow_receipts_checkbox">
                                        <i class="bi bi-box-arrow-in-down text-success me-1"></i>
                                        Permite recibos de material
                                    </label>
                                    <p class="small text-muted mt-1 mb-0">
                                        Habilita entradas y recepción de productos en este almacén.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="h-100 bg-light rounded p-3 text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input type="hidden" name="is_matrix" value="0">
                                        <input class="form-check-input form-check-input-lg" type="checkbox"
                                            role="switch" id="is_matrix_checkbox" name="is_matrix" value="1"
                                            {{ old('is_matrix') ? 'checked' : '' }}>
                                    </div>
                                    <label class="form-check-label fw-semibold mt-2" for="is_matrix_checkbox">
                                        <i class="bi bi-diagram-3 text-primary me-1"></i>
                                        Es almacén matriz
                                    </label>
                                    <p class="small text-muted mt-1 mb-0">
                                        Si se marca, el técnico asociado se limpiará automáticamente.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Los campos con <span class="text-danger">*</span> son obligatorios.
                </small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                        <i class="bi bi-check-lg me-1" id="submitIcon"></i>
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .form-check-input-lg {
        width: 2.5rem;
        height: 1.25rem;
    }

    #createModal .modal-content {
        border-radius: 0.5rem;
    }

    #createModal .card {
        border-radius: 0.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('warehouseForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitSpinner = document.getElementById('submitSpinner');
        const submitIcon = document.getElementById('submitIcon');
        const observationsTextarea = document.getElementById('observations_textarea');
        const charCount = document.getElementById('charCount');
        const matrixCheckbox = document.getElementById('is_matrix_checkbox');
        const technicianSelect = document.getElementById('technician_select');

        if (!form) {
            return;
        }

        function updateCharCount() {
            if (!observationsTextarea || !charCount) {
                return;
            }

            const length = observationsTextarea.value.length;
            charCount.textContent = length;
            charCount.className = length > 950 ? 'text-danger fw-bold' : length > 900 ? 'text-warning fw-bold' :
                'text-muted';
        }

        function syncMatrixTechnician() {
            if (!matrixCheckbox || !technicianSelect) {
                return;
            }

            if (matrixCheckbox.checked) {
                technicianSelect.value = '';
                technicianSelect.disabled = true;
                technicianSelect.classList.add('bg-light');
            } else {
                technicianSelect.disabled = false;
                technicianSelect.classList.remove('bg-light');
            }
        }

        updateCharCount();
        syncMatrixTechnician();

        observationsTextarea?.addEventListener('input', updateCharCount);
        matrixCheckbox?.addEventListener('change', syncMatrixTechnician);

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            submitBtn.disabled = true;
            submitSpinner?.classList.remove('d-none');
            submitIcon?.classList.add('d-none');
        });
    });
</script>
