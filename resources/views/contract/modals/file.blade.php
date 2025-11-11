<div class="modal fade" id="fileModal{{ $contract->id }}" tabindex="-1" aria-labelledby="fileModalLabel{{ $contract->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('contract.file', ['contractID' => $contract->id, 'type' => 1]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="fileModalLabel{{ $contract->id }}">Crear archivo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-12 bg-secondary-subtle rounded p-5" id="dropArea{{ $contract->id }}">
                    <div class="d-flex justify-content-center">
                        <input accept=".pdf,.jpg,.jpeg,.png" type="file" id="fileInput{{ $contract->id }}" name="file" style="display: none;">
                        <label for="fileInput{{ $contract->id }}" class="drop-area" id="input-label{{ $contract->id }}">Suelta aquí tu archivo o haz clic para seleccionarlo</label>
                    </div>
                    <ul class="list-group list-group-flush rounded" id="file-list{{ $contract->id }}"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="btnCrear{{ $contract->id }}">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        const $dropArea = $('#dropArea{{ $contract->id }}');
        const $fileInput = $('#fileInput{{ $contract->id }}');
        const $fileList = $('#file-list{{ $contract->id }}');

        // Evitar que el navegador abra los archivos al soltarlos
        $dropArea.on('dragenter dragover dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        // Cambiar estilos de la zona de soltar
        $dropArea.on('dragenter', function() {
            $dropArea.addClass('highlight');
        });

        $dropArea.on('dragleave drop', function() {
            $dropArea.removeClass('highlight');
        });

        // Procesar archivos soltados
        $dropArea.on('drop', function(e) {
            const files = e.originalEvent.dataTransfer.files;
            handleFile(files[0]);
        });

        // Procesar archivos seleccionados desde el input
        $fileInput.on('change', function() {
            const file = this.files[0];
            handleFile(file);
        });

        // Función para manejar el archivo
        function handleFile(file) {
            console.log('Archivo seleccionado:', file.name);

            // Validar el tipo de archivo
            if (!isValidFileType(file)) {
                console.log('Archivo no válido:', file.name);
                return;
            }

            // Validar el tamaño del archivo
            if (!isValidFileSize(file)) {
                console.log('Archivo demasiado grande:', file.name);
                return;
            }

            // Limpiar la lista de archivos y agregar el nuevo archivo
            $fileList.empty();
            addFileToList(file);
            setFile(file);
        }

        // Función para validar el tipo de archivo
        function isValidFileType(file) {
            const acceptedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            return acceptedTypes.includes(file.type);
        }

        // Función para validar el tamaño del archivo (menor a 5MB)
        function isValidFileSize(file) {
            const maxSizeInBytes = 5 * 1024 * 1024; // 5MB
            return file.size <= maxSizeInBytes;
        }

        // Funcion para truncar el nombre
        function truncateString(str) {
            return (str.length > 30) ? str.substring(0, 30) + "..." : str;
        }

        // Función para agregar archivo a la lista
        function addFileToList(file) {
            const $li = $('<li class="list-group-item"></li>');
            const $name = $('<span></span>').text(truncateString(file.name));
            const $button = $('<button class="btn btn-sm text-danger">X</button>');

            $button.on('click', function() {
                $li.remove();
                $fileInput.val('');
            });

            $li.append($name, $button);
            $fileList.append($li);
        }

        function setFile(file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            $fileInput[0].files = dataTransfer.files; // Asigna el archivo directamente al input
        }
    });
</script>
