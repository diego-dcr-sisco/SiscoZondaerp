<div class="container-fluid">
    <form method="POST" class="needs-validation" action="{{ route('stock.update', $warehouse->id) }}" 
          enctype="multipart/form-data" novalidate id="warehouseForm">
        @csrf
        @method('PUT')

        <!-- Información básica -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-info-circle me-2"></i>
                            Información Básica
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-semibold">
                                    <i class="bi bi-building me-1"></i>
                                    Nombre del almacén <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="name" 
                                       name="name"
                                       value="{{ $warehouse->name }}" 
                                       required 
                                       minlength="3"
                                       maxlength="100"
                                       placeholder="Ej: Almacén Central Norte">
                                <div class="invalid-feedback">
                                    Por favor ingrese un nombre válido para el almacén (mínimo 3 caracteres).
                                </div>
                                <div class="valid-feedback">
                                    ¡Perfecto!
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="branch" class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ __('customer.data.branch') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" name="branch_id" id="branch" required>
                                    <option value="" disabled>Seleccione una sucursal</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                                {{ $branch->id == $warehouse->branch_id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Por favor seleccione una sucursal.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="technician" class="form-label fw-semibold">
                                    <i class="bi bi-person-gear me-1"></i>
                                    Técnico asociado
                                </label>
                                <select class="form-select form-select-lg" name="technician_id" id="technician">
                                    <option value="" {{ $warehouse->technician_id ? '' : 'selected' }}>
                                        Sin técnico asignado
                                    </option>
                                    @foreach ($technicians as $technician)
                                        <option value="{{ $technician->id }}"
                                                {{ $technician->id == $warehouse->technician_id ? 'selected' : '' }}>
                                            {{ $technician->user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Técnico responsable del almacén (opcional)
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-chat-text me-1"></i>
                                    Observaciones
                                </label>
                                <textarea class="form-control" 
                                          id="observations" 
                                          name="observations" 
                                          rows="4" 
                                          maxlength="500"
                                          placeholder="Ingrese detalles adicionales sobre el estado, ubicación o cualquier incidencia del inventario...">{{ $warehouse->observations }}</textarea>
                                <div class="form-text">
                                    <span id="charCount">{{ strlen($warehouse->observations ?? '') }}</span>/500 caracteres
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración del almacén -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-primary">
                            <i class="bi bi-gear me-2"></i>
                            Configuración del Almacén
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input form-check-input-lg" 
                                                   type="checkbox" 
                                                   role="switch"
                                                   id="allow-material-receipts"
                                                   name="allow_material_receipts" 
                                                   {{ $warehouse->allow_material_receipts ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label fw-semibold mt-2" for="allow-material-receipts">
                                            <i class="bi bi-box-arrow-in-down text-success me-1"></i>
                                            Permite recibos de material
                                        </label>
                                        <p class="small text-muted mt-1 mb-0">
                                            Habilita la recepción de materiales en este almacén
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input form-check-input-lg" 
                                                   type="checkbox" 
                                                   role="switch"
                                                   id="is-active" 
                                                   name="is_active"
                                                   {{ $warehouse->is_active ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label fw-semibold mt-2" for="is-active">
                                            <i class="bi bi-toggle-on text-success me-1"></i>
                                            Almacén activo
                                        </label>
                                        <p class="small text-muted mt-1 mb-0">
                                            Determina si el almacén está operativo
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input form-check-input-lg" 
                                                   type="checkbox" 
                                                   role="switch"
                                                   id="is-matrix" 
                                                   name="is_matrix"
                                                   {{ $warehouse->is_matrix ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label fw-semibold mt-2" for="is-matrix">
                                            <i class="bi bi-diagram-3 text-primary me-1"></i>
                                            Es almacén matriz
                                        </label>
                                        <p class="small text-muted mt-1 mb-0">
                                            Marca si es el almacén principal
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Los campos marcados con <span class="text-danger">*</span> son obligatorios
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary ">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary " id="submitBtn">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" id="submitSpinner"></span>
                                    <i class="bi bi-check-lg me-2" id="submitIcon"></i>
                                    {{ __('buttons.update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Estilos personalizados -->
<style>
    .form-check-input-lg {
        width: 2.5rem;
        height: 1.25rem;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-1px);
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .is-valid {
        border-color: #198754;
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(45deg, #0d6efd, #6610f2);
    }
    
    @media (max-width: 768px) {
        .btn-lg {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
    }
</style>

<!-- JavaScript mejorado -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('warehouseForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const submitIcon = document.getElementById('submitIcon');
    const observationsTextarea = document.getElementById('observations');
    const charCount = document.getElementById('charCount');
    
    // Contador de caracteres para observaciones
    if (observationsTextarea && charCount) {
        observationsTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            if (currentLength > 450) {
                charCount.className = 'text-warning fw-bold';
            } else if (currentLength > 480) {
                charCount.className = 'text-danger fw-bold';
            } else {
                charCount.className = 'text-muted';
            }
        });
    }
    
    // Validación en tiempo real
    const inputs = form.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', validateField);
    });
    
    function validateField(e) {
        const field = e.target;
        const isValid = field.checkValidity();
        
        field.classList.remove('is-valid', 'is-invalid');
        
        if (field.value.trim() !== '') {
            if (isValid) {
                field.classList.add('is-valid');
            } else {
                field.classList.add('is-invalid');
            }
        }
    }
    
    // Manejo del envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar formulario
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            
            // Mostrar mensaje de error
            showAlert('Por favor complete todos los campos requeridos correctamente.', 'danger');
            
            // Scroll al primer campo inválido
            const firstInvalid = form.querySelector('.is-invalid, :invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
            return;
        }
        
        // Mostrar estado de carga
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');
        submitIcon.classList.add('d-none');
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Actualizando...
        `;
        
        // Configurar valores de checkboxes
        setCheckboxValues();
        
        // Enviar formulario
        form.submit();
    });
    
    function setCheckboxValues() {
        const checkboxes = ['allow_material_receipts', 'is_active', 'is_matrix'];
        checkboxes.forEach(name => {
            const checkbox = form.querySelector(`[name="${name}"]`);
            if (checkbox) {
                // Crear input hidden para el valor
                let hiddenInput = form.querySelector(`input[name="${name}_hidden"]`);
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = name;
                    form.appendChild(hiddenInput);
                }
                hiddenInput.value = checkbox.checked ? '1' : '0';
                checkbox.disabled = true; // Deshabilitar para que no se envíe
            }
        });
    }
    
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Animaciones para las tarjetas de configuración
    const configCards = document.querySelectorAll('.card.bg-light');
    configCards.forEach(card => {
        const switchInput = card.querySelector('.form-check-input');
        if (switchInput) {
            switchInput.addEventListener('change', function() {
                card.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    card.style.transform = 'scale(1)';
                }, 150);
            });
        }
    });
});

// Función legacy para compatibilidad (si es necesaria)
function load_city() {
    // Mantener función si es utilizada en otro lugar
    console.warn('load_city function is deprecated in this form');
}

function convertToUppercase(id) {
    const element = document.getElementById(id);
    if (element) {
        element.value = element.value.toUpperCase();
    }
}
</script>
