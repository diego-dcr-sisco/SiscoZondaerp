<div class="modal fade" id="createFloorplanModal" tabindex="-1" aria-labelledby="createFloorplanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST"
            action="{{ route('floorplan.store', ['customerId' => $customer->id]) }}" enctype="multipart/form-data"
            id="createFloorplanForm">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="createFloorplanModalLabel">
                    Agregar plano de planta
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="filename" class="form-label is-required">Nombre:
                    </label>
                    <input type="text" class="form-control" id="filename" name="filename" placeholder="Plano Edificio A"
                        pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_]+" 
                        title="Solo se permiten letras, números, espacios, guiones y guiones bajos"
                        maxlength="100"
                        required />
                </div>
                <div class="mb-3">
                    <label for="filename" class="form-label">Servicio:
                    </label>
                    <select class="form-select" name="service_id">
                        <option value="">Sin servicio</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="formFile" class="form-label is-required">Layout (Imagen)</label>
                    <input class="form-control" accept=".png, .jpg, .jpeg" type="file" name="file" required>
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}" />
                    <div class="form-text">Solo se permiten archivos con formato .JPG .JPEG .PNG (máx. 5MB)</div>
                    <div id="fileError" class="text-danger mt-1" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    {{ __('buttons.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary" id="submitFloorplanBtn">
                    {{ __('buttons.store') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createFloorplanForm');
    const filenameInput = document.getElementById('filename');
    const fileInput = document.querySelector('input[name="file"]');
    const fileError = document.getElementById('fileError');
    const submitBtn = document.getElementById('submitFloorplanBtn');

    // Validar nombre del plano en tiempo real
    filenameInput.addEventListener('input', function() {
        // Remover caracteres no permitidos
        const invalidChars = /[<>:"/\\|?*\x00-\x1f]/g;
        if (invalidChars.test(this.value)) {
            this.value = this.value.replace(invalidChars, '');
            showError(filenameInput, 'Se han removido caracteres no permitidos');
        } else {
            clearError(filenameInput);
        }
    });

    // Validar archivo seleccionado
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        fileError.style.display = 'none';
        
        if (!file) return;

        // Validar tamaño (5MB máximo)
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if (file.size > maxSize) {
            fileError.textContent = 'El archivo excede el tamaño máximo de 5MB';
            fileError.style.display = 'block';
            this.value = '';
            return;
        }

        // Validar extensión
        const allowedExtensions = ['jpg', 'jpeg', 'png'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension)) {
            fileError.textContent = 'Solo se permiten archivos .JPG, .JPEG o .PNG';
            fileError.style.display = 'block';
            this.value = '';
            return;
        }

        // Validar nombre del archivo
        const invalidFileNameChars = /[<>:"/\\|?*\x00-\x1f]/;
        if (invalidFileNameChars.test(file.name)) {
            fileError.textContent = 'El nombre del archivo contiene caracteres no permitidos';
            fileError.style.display = 'block';
            this.value = '';
            return;
        }
    });

    // Validación final antes de enviar
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validar nombre del plano
        const filename = filenameInput.value.trim();
        if (filename.length < 3) {
            e.preventDefault();
            showError(filenameInput, 'El nombre debe tener al menos 3 caracteres');
            isValid = false;
        }

        if (filename.length > 100) {
            e.preventDefault();
            showError(filenameInput, 'El nombre no debe exceder 100 caracteres');
            isValid = false;
        }

        // Verificar que se haya seleccionado un archivo
        if (!fileInput.files.length) {
            e.preventDefault();
            fileError.textContent = 'Debes seleccionar un archivo';
            fileError.style.display = 'block';
            isValid = false;
        }

        // Deshabilitar botón para evitar doble envío
        if (isValid) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        }
    });

    function showError(input, message) {
        input.classList.add('is-invalid');
        let feedback = input.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.insertBefore(feedback, input.nextSibling);
        }
        feedback.textContent = message;
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.remove();
        }
    }
});
</script>
