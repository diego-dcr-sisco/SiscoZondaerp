/**
 * JavaScript para el modal de creación de almacenes
 * Maneja validación, interacciones y UX
 */

class WarehouseModal {
    constructor() {
        this.form = document.getElementById('warehouseForm');
        this.observationsTextarea = document.getElementById('observations_textarea');
        this.charCount = document.getElementById('charCount');
        this.submitBtn = document.getElementById('submitBtn');
        this.modal = document.getElementById('createModal');
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeCharCount();
        this.setupValidation();
    }
    
    setupEventListeners() {
        // Contador de caracteres
        this.observationsTextarea.addEventListener('input', () => this.updateCharCount());
        
        // Validación del formulario
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Limpiar al cerrar modal
        this.modal.addEventListener('hidden.bs.modal', () => this.resetForm());
        
        // Validación en tiempo real
        document.getElementById('warehouse_name').addEventListener('input', (e) => this.validateName(e.target));
        document.getElementById('branch_select').addEventListener('change', (e) => this.validateBranch(e.target));
    }
    
    initializeCharCount() {
        this.updateCharCount();
    }
    
    updateCharCount() {
        const length = this.observationsTextarea.value.length;
        this.charCount.textContent = length;
        
        // Remover clases anteriores
        this.charCount.classList.remove('text-warning', 'text-danger');
        
        // Aplicar clases según la longitud
        if (length > 950) {
            this.charCount.classList.add('text-danger');
        } else if (length > 900) {
            this.charCount.classList.add('text-warning');
        }
    }
    
    handleSubmit(e) {
        if (!this.form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        this.form.classList.add('was-validated');
        
        // Deshabilitar botón durante envío si es válido
        if (this.form.checkValidity()) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creando...';
        }
    }
    
    resetForm() {
        this.form.classList.remove('was-validated');
        this.form.reset();
        this.updateCharCount();
        
        // Resetear botón
        this.submitBtn.disabled = false;
        this.submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Crear Almacén';
        
        // Resetear estados
        const technicianSelect = document.getElementById('technician_select');
        const matrixCheckbox = document.getElementById('is_matrix_checkbox');
        
        technicianSelect.disabled = false;
        technicianSelect.classList.remove('bg-light');
        matrixCheckbox.value = '';
        
        // Limpiar validaciones visuales
        this.clearValidations();
    }
    
    validateName(input) {
        const name = input.value.trim();
        const isValid = name.length >= 3 && name.length <= 255;
        
        this.toggleValidation(input, isValid);
    }
    
    validateBranch(select) {
        const isValid = select.value !== '';
        this.toggleValidation(select, isValid);
    }
    
    toggleValidation(element, isValid) {
        element.classList.remove('is-valid', 'is-invalid');
        
        if (element.value.length > 0) {
            element.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }
    }
    
    clearValidations() {
        const inputs = this.form.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
    }
}

// Función global para manejar cambios en checkbox de matriz
function handleMatrixChange(element) {
    const technicianSelect = document.getElementById('technician_select');
    
    if (element.checked) {
        element.value = '1';
        technicianSelect.value = '';
        technicianSelect.disabled = true;
        technicianSelect.classList.add('bg-light');
        
        // Mostrar notificación
        showNotification('Almacén matriz seleccionado. El técnico será desasignado.', 'info');
    } else {
        element.value = '';
        technicianSelect.disabled = false;
        technicianSelect.classList.remove('bg-light');
    }
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Remover notificaciones existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Crear nueva notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification`;
    notification.innerHTML = `
        <i class="fas fa-info-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Función para validar formulario antes de enviar
function validateWarehouseForm() {
    const form = document.getElementById('warehouseForm');
    const name = form.querySelector('#warehouse_name').value.trim();
    const branch = form.querySelector('#branch_select').value;
    
    let isValid = true;
    let errors = [];
    
    // Validar nombre
    if (name.length < 3) {
        errors.push('El nombre debe tener al menos 3 caracteres');
        isValid = false;
    }
    
    if (name.length > 255) {
        errors.push('El nombre no puede exceder 255 caracteres');
        isValid = false;
    }
    
    // Validar sucursal
    if (!branch) {
        errors.push('Debe seleccionar una sucursal');
        isValid = false;
    }
    
    // Mostrar errores si los hay
    if (!isValid) {
        showNotification(errors.join('<br>'), 'danger');
    }
    
    return isValid;
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el modal existe antes de inicializar
    if (document.getElementById('createModal')) {
        new WarehouseModal();
    }
    
    // Agregar validación al formulario
    const form = document.getElementById('warehouseForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateWarehouseForm()) {
                e.preventDefault();
            }
        });
    }
});

// Funciones de utilidad
const WarehouseUtils = {
    // Formatear nombre de almacén
    formatWarehouseName: function(name) {
        return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
    },
    
    // Validar formato de nombre
    isValidWarehouseName: function(name) {
        const regex = /^[a-zA-Z0-9\s\-_\.]+$/;
        return regex.test(name) && name.length >= 3 && name.length <= 255;
    },
    
    // Generar slug para almacén
    generateSlug: function(name) {
        return name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
    },
    
    // Mostrar loading en botón
    showLoading: function(button, text = 'Procesando...') {
        button.disabled = true;
        button.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${text}`;
    },
    
    // Ocultar loading en botón
    hideLoading: function(button, originalText) {
        button.disabled = false;
        button.innerHTML = originalText;
    }
};

// Exportar para uso global si es necesario
if (typeof window !== 'undefined') {
    window.WarehouseModal = WarehouseModal;
    window.WarehouseUtils = WarehouseUtils;
} 