<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signatureModalLabel">Firma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label is-required">Firmado por</label>
                    <input type="text" class="form-control" id="signature-name" name="signature_name"
                        placeholder="Example " required>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="form-label is-required">Firma</label>
                        <button type="button" class="btn btn-danger btn-sm" id="clear"
                            onclick="clean()">{{ __('buttons.clear') }}</button>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <canvas class="border rounded" id="signature-pad" width="450" height="200"></canvas>
                    </div>
                    <input type="hidden" id="signature" name="signature" value="" />
                    <input type="hidden" id="order" name="order" value="" />
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
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="store()">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    const canvas = $('#signature-pad')[0];
    const container = $('#signature-container');
    let signaturePad = '';

    $(document).ready(function() {
        // Configurar el SignaturePad con tinta azul
        signaturePad = new SignaturePad(canvas, {
            penColor: '#076B9F' // Cambia el color del trazo a azul
        });
    });

    function clean() {
        signaturePad.clear();
    }

    function store() {
    const name = $('#signature-name').val();
    const has_name = name && name != '';
    const signatureEmpty = signaturePad.isEmpty();
    const hasImage = $('#image').get(0).files.length > 0;

    if ((!signatureEmpty || hasImage) && has_name) {
        var formData = new FormData();
        const orderId = $('#order').val();
        const csrfToken = $('meta[name="csrf-token"]').attr("content");

        // Agregar la firma si no está vacía
        if (!signatureEmpty) {
            const base64 = signaturePad.toDataURL('image/png');
            formData.append('signature', base64);
        }

        // Agregar la imagen si existe
        if (hasImage) {
            const imageFile = $('#image')[0].files[0];
            formData.append('image', imageFile);
        }

        // Agregar los demás datos
        formData.append('name', name);
        formData.append('order', orderId);

        $.ajax({
            url: "{{ route('client.report.signature.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function(response) {
                if (response.success) {
                    // Recargar o redirigir según la respuesta
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        location.reload();
                    }
                } else {
                    alert(response.message || "Error al guardar");
                }
            },
            error: function(xhr) {
                const errorMessage = xhr.responseJSON?.message || "Error en el servidor";
                alert(errorMessage);
                console.error(xhr);
            },
        });
    } else {
        alert("Por favor, firme y/o cargue una imagen, y agregue un nombre antes de guardar.");
    }
}

    function openModal(id) {
        var confirmed = confirm("¿Estas seguro de firmar el reporte? (Si ya existe una firma, esta se actualizará)");

        if (confirm) {
            $('#order').val(id);
            $('#signatureModal').modal('show')
        }
    }
</script>
