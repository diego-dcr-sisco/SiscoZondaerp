<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <form class="modal-dialog" action="{{ route('order.signature.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="signatureModalLabel">Firma de la orden</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="signatureModalBody">
                <div class="mb-3">
                    <label for="signature_name" class="form-label is-required">
                        Autorizó:
                    </label>
                    <input type="text" class="form-control border-secondary border-opacity-25" name="signature_name"
                        id="signature-name" placeholder="Nombre del responsable de fimar los reportes" required/>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Imagen
                    </label>
                    <input type="file" class="form-control" id="image" name="image"
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
                <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
            </div>
        </div>
    </form>
</div>

<script>
    function openModal(element) {
        var confirmed = confirm("¿Estas seguro de firmar el reporte? (Si ya existe una firma, esta se actualizará)");
        const data = JSON.parse(element.getAttribute("data-order"));

        if (confirm) {
            $('#signatureModalBody #order-id').val(data.id);
            $('#signature-name').val(data.signature_name)
            $('#signatureModal').modal('show')
        }
    }

    document.querySelector('#signatureModal form').addEventListener('submit', function(e) {
        const imageInput = document.getElementById('image');
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes

        if (imageInput.files.length > 0) {
            const selectedFile = imageInput.files[0];

            // Validar tamaño
            if (selectedFile.size > maxSize) {
                e.preventDefault(); // Detener el envío
                alert('La imagen excede el tamaño máximo permitido de 5MB');
                return false;
            }

            // Validar tipo (opcional, ya lo haces con accept)
            const allowedTypes = ['image/jpeg', 'image/png'];
            if (!allowedTypes.includes(selectedFile.type)) {
                e.preventDefault();
                alert('Solo se permiten imágenes en formato JPG o PNG');
                return false;
            }
        }

        return true;
    });
</script>
