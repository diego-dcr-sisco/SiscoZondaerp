<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="signatureModalLabel">Firma de la orden</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="signatureModalBody">
                <div class="mb-3">
                    <label for="modal-signed_by" class="form-label is-required">
                        Autorizó:
                    </label>
                    <input type="text" class="form-control border-secondary border-opacity-25" id="modal-signed_by"
                        placeholder="Nombre del responsable de fimar los reportes" />
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Imagen
                    </label>
                    <input type="file" class="form-control" id="signature" name="signature"
                        accept=".png, .jpg, .jpeg" />
                    <div class="form-text">
                        Selecciona la imagen de la firma. (Formato: .png, .jpg, .jpeg) Tamaño maximo: 5MB
                    </div>
                </div>

                <input type="hidden" id="order-id" name="order_id" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary" id="convertBtn">{{ __('buttons.update') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(element) {
        var confirmed = confirm("¿Estas seguro de firmar el reporte? (Si ya existe una firma, esta se actualizará)");
        const data = JSON.parse(element.getAttribute("data-order"));

        if (confirm) {
            $('#signatureModalBody #order-id').val(data.id);
            $('#modal-signed_by').val(data.signature_name);
            $('#signatureModal').modal('show');
        }
    }

    // Botón para convertir a Base64
    $('#convertBtn').click(function() {
        const fileInput = $('#signature')[0];
        const file = fileInput.files[0];

        if (!file) {
            alert('Debe seleccionar una imagen primero');
            return;
        }

        // Validar tamaño del archivo (5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('La imagen es demasiado grande. El tamaño máximo es 5MB.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                
                // Redimensionar si es muy grande (máximo 800px de ancho)
                const maxWidth = 800;
                let width = img.width;
                let height = img.height;
                
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }
                
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Convertir a PNG con compresión
                const base64Image = canvas.toDataURL('image/png', 0.8);
                
                // Verificar tamaño del base64 (aproximadamente 2MB cuando se codifica)
                const base64Size = base64Image.length * 0.75; // Aproximado en bytes
                if (base64Size > 2 * 1024 * 1024) {
                    alert('La imagen procesada es muy grande. Por favor, usa una imagen más pequeña.');
                    return;
                }

                $('#signed-by').val($('#modal-signed_by').val());
                $('#signature-base64').val(base64Image);
                $('#signature-preview').attr('src', base64Image);
                $('#signature-preview').show();
                $('#signature-changed').val('1'); // Marcar que la firma ha cambiado
                $('#signatureModal').modal('hide');
                alert('Firma actualizada correctamente. Recuerda hacer clic en "Guardar" para almacenar los cambios.');
            };

            img.onerror = function() {
                alert('Error al cargar la imagen. Verifica que sea un archivo válido.');
            };

            img.src = e.target.result;
        };

        reader.onerror = function() {
            alert('Error al leer el archivo. Intenta con otra imagen.');
        };

        reader.readAsDataURL(file);
    });


    function updateSignature() {
        console.log($('#signature').val());
    }
</script>
