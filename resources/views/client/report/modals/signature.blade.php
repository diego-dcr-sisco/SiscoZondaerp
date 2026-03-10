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

    // Función para convertir imagen a base64
    function convertImageToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }

    // Función para comprobar el tamaño del archivo (5MB máximo)
    function checkFileSize(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        return file.size <= maxSize;
    }

    async function store() {
        const name = $('#signature-name').val();
        const has_name = name && name != '';
        const signatureEmpty = signaturePad.isEmpty();
        const hasImage = $('#image').get(0).files.length > 0;

        if ((!signatureEmpty || hasImage) && has_name) {
            const orderId = $('#order').val();
            const csrfToken = $('meta[name="csrf-token"]').attr("content");
            
            let base64Signature = null;
            let base64Image = null;
            
            // Obtener la firma si no está vacía
            if (!signatureEmpty) {
                base64Signature = signaturePad.toDataURL('image/png');
            }

            // Convertir imagen a base64 si existe
            if (hasImage) {
                const imageFile = $('#image')[0].files[0];
                
                // Verificar tamaño del archivo
                if (!checkFileSize(imageFile)) {
                    alert("La imagen excede el tamaño máximo de 5MB");
                    return;
                }
                
                try {
                    base64Image = await convertImageToBase64(imageFile);
                } catch (error) {
                    alert("Error al procesar la imagen");
                    //console.error(error);
                    return;
                }
            }

            // Crear objeto JSON para enviar
            const data = {
                name: name,
                order: orderId,
                signature: base64Signature,
                image: base64Image
            };

            console.log('Datos a enviar:', data);

            $.ajax({
                url: "{{ route('client.report.signature.store') }}",
                type: "POST",
                data: JSON.stringify(data),
                contentType: "application/json",
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
                    //console.error(xhr);
                },
            });
        } else {
            alert("Por favor, firme y/o cargue una imagen, y agregue un nombre antes de guardar.");
        }
    }

    function openModal(id) {
        var confirmed = confirm("¿Estas seguro de firmar el reporte? (Si ya existe una firma, esta se actualizará)");

        if (confirmed) {
            $('#order').val(id);
            $('#signatureModal').modal('show');
            
            // Limpiar campos al abrir el modal
            $('#signature-name').val('');
            $('#image').val('');
            signaturePad.clear();
        }
    }
</script>