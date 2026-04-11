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

<div class="modal fade" id="bulkSignatureModal" tabindex="-1" aria-labelledby="bulkSignatureModalLabel"
    aria-hidden="true">
    <form class="modal-dialog" action="{{ route('order.signature.bulk.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="bulkSignatureModalLabel">Firma masiva de reportes</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bulkSignatureModalBody">
                <div class="alert alert-info py-2">
                    Se firmarán <span id="selected-orders-count" class="fw-bold">0</span> reportes seleccionados.
                </div>

                <div class="mb-3">
                    <label for="bulk-signature-name" class="form-label is-required">
                        Autorizó:
                    </label>
                    <input type="text" class="form-control border-secondary border-opacity-25" name="signature_name"
                        id="bulk-signature-name" placeholder="Nombre del responsable de firmar los reportes" required />
                </div>

                <div class="mb-3">
                    <label class="form-label" for="bulk-image">
                        Imagen de firma
                    </label>
                    <input type="file" class="form-control" id="bulk-image" name="image" accept=".png, .jpg, .jpeg" />
                    <div class="form-text">
                        La imagen seleccionada se aplicará a todos los reportes marcados. Formato: .png, .jpg, .jpeg.
                        Tamaño máximo: 5MB.
                    </div>
                </div>

                <input type="hidden" id="selected-orders" name="selected_orders" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary">Firmar seleccionados</button>
            </div>
        </div>
    </form>
</div>

<script>
    function openModal(element) {
        var confirmed = confirm("¿Estas seguro de firmar el reporte? (Si ya existe una firma, esta se actualizará)");
        const data = JSON.parse(element.getAttribute("data-order"));

        if (confirmed) {
            $('#signatureModalBody #order-id').val(data.id);
            $('#signature-name').val(data.signature_name)
            $('#signatureModal').modal('show')
        }
    }

    function openBulkSignatureModal() {
        const selectedOrders = [];

        $('.checkbox-order:checked').each(function() {
            selectedOrders.push(parseInt($(this).val()));
        });

        if (selectedOrders.length === 0) {
            alert('Por favor, selecciona al menos una orden.');
            return;
        }

        $('#selected-orders').val(JSON.stringify(selectedOrders));
        $('#selected-orders-count').text(selectedOrders.length);
        $('#bulkSignatureModal').modal('show');
    }

    function validateSignatureImage(imageInput, event) {
        const maxSize = 5 * 1024 * 1024;

        if (imageInput.files.length > 0) {
            const selectedFile = imageInput.files[0];

            if (selectedFile.size > maxSize) {
                event.preventDefault();
                alert('La imagen excede el tamaño máximo permitido de 5MB');
                return false;
            }

            const allowedTypes = ['image/jpeg', 'image/png'];
            if (!allowedTypes.includes(selectedFile.type)) {
                event.preventDefault();
                alert('Solo se permiten imágenes en formato JPG o PNG');
                return false;
            }
        }

        return true;
    }

    document.querySelector('#signatureModal form').addEventListener('submit', function(e) {
        const imageInput = document.getElementById('image');
        return validateSignatureImage(imageInput, e);
    });

    document.querySelector('#bulkSignatureModal form').addEventListener('submit', function(e) {
        const imageInput = document.getElementById('bulk-image');
        return validateSignatureImage(imageInput, e);
    });
</script>
