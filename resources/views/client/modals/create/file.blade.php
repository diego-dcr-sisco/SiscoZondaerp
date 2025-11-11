<style>
    .drop-area.highlight {
        border-color: #009688;
    }
</style>

<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('client.file.store') }}"enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="fileModalLabel">Subir archivo(s)</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="w-100 border rounded bg-secondary-subtle" style="height: 200px;" id="dropArea">
                        <div class="drop-area w-100 h-100">
                            <input accept=".pdf,.jpg,.jpeg,.png" type="file" id="fileInput" name="files[]" multiple
                                style="display: none;">
                            <label for="fileInput"
                                class="w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                <span class="fs-1"><i class="bi bi-file-earmark-arrow-up"></i></span>
                                <span class="fw-bold"> Selecciona o arrastra tus archivos </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="card">
                        <div class="card-header">
                            Archivos agregados
                        </div>
                        <ul class="list-group list-group-flush" id="file-list">
                            <li class="list-group-item">
                                <span class="text-danger">No hay archivos agregados</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <input type="hidden" name="path" value="{{ $data['root_path'] }}">

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="btnCrear">{{ __('buttons.upload') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    const dropArea = $('#dropArea');
    const fileInput = $('#fileInput');
    const fileList = $('#file-list');
    let currentFiles = []; // Mantener un registro de los archivos actuales

    // Evitar el comportamiento por defecto en eventos de arrastre
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.on(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    // Resaltar el área de drop cuando se arrastra sobre ella
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.on(eventName, function() {
            dropArea.addClass('border-primary bg-primary bg-opacity-10');
        });
    });

    // Quitar el resaltado cuando se sale o se suelta
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.on(eventName, function() {
            dropArea.removeClass('border-primary bg-primary bg-opacity-10');
        });
    });

    // Manejar el drop de archivos
    dropArea.on('drop', function(e) {
        const newFiles = e.originalEvent.dataTransfer.files;
        addFiles(newFiles);
    });

    // Manejar la selección de archivos por click
    fileInput.on('change', function() {
        const newFiles = this.files;
        addFiles(newFiles);
        //$(this).val(''); // Limpiar el input para permitir seleccionar el mismo archivo otra vez
    });

    // Función para agregar archivos manteniendo los existentes
    function addFiles(newFiles) {
        const filesToAdd = Array.from(newFiles);
        
        filesToAdd.forEach(newFile => {
            // Verificar si el archivo ya existe
            const exists = currentFiles.some(existingFile => 
                existingFile.name == newFile.name && 
                existingFile.size == newFile.size &&
                existingFile.type == newFile.type
            );
            
            if (!exists) {
                currentFiles.push(newFile);
            }
        });
        
        updateFileInput();
        updateFileList();
    }

    // Actualizar el input file con los archivos actuales
    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        currentFiles.forEach(file => dataTransfer.items.add(file));
        fileInput[0].files = dataTransfer.files;
    }

    // Función para actualizar la lista visual de archivos
    function updateFileList() {
        fileList.empty();

        if (currentFiles.length == 0) {
            fileList.append(
                '<li class="list-group-item"><span class="text-danger">No hay archivos agregados</span></li>'
            );
            return;
        }

        currentFiles.forEach((file, index) => {
            fileList.append(
                `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span style="width:70%;">${file.name}</span>
                    <span class="badge bg-secondary rounded-pill">${formatFileSize(file.size)}</span>
                    <button type="button" class="btn-close" aria-label="Close" data-index="${index}"></button>
                </li>`
            );
        });

        // Asignar evento de eliminación a los botones
        fileList.find('.btn-close').on('click', function() {
            const index = $(this).data('index');
            removeFile(index);
        });
    }

    // Función para formatear el tamaño del archivo
    function formatFileSize(bytes) {
        if (bytes == 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Función para eliminar un archivo de la lista
    function removeFile(index) {
        currentFiles.splice(index, 1);
        updateFileInput();
        updateFileList();
    }
});
</script>
