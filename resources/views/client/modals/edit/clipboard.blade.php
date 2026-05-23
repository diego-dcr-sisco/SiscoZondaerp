<div class="modal fade" id="clipboardModal" tabindex="-1" aria-labelledby="clipboardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="clipboardModalLabel">Portapapeles</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Elementos seleccionados:</label>
                    <textarea class="form-control" id="selectedItemsDisplay" rows="3" readonly 
                        style="resize: none; background-color: #f8f9fa; font-size: 0.9rem;"></textarea>
                    <small class="text-muted">Carpetas y archivos que se copiarán o moverán</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ruta de destino:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="currentPathDisplay" value="client_system/"
                            readonly>
                        <button class="btn btn-outline-secondary" id="btnHome" title="Volver a raíz">
                            <i class="bi bi-house-fill"></i>
                        </button>
                    </div>
                    <small class="text-muted">Selecciona la carpeta de destino navegando en el árbol</small>
                </div>
                <div class="directory-tree-container border rounded p-2 mb-3">
                    <ul id="directoryTree" class="directory-tree list-unstyled"></ul>
                </div>
                <input type="hidden" name="path" id="selectedPath" value="client_system/">
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <div>
                    <button type="button" class="btn btn-success me-2" id="copyButton"
                        onclick="copyDirectories()">
                        <i class="bi bi-clipboard2-check-fill"></i> <span id="copyButtonText">Copiar</span>
                        <span class="spinner-border spinner-border-sm d-none" id="copySpinner" role="status" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-primary" id="moveButton"
                        onclick="moveDirectories()">
                        <i class="bi bi-arrows-move"></i> <span id="moveButtonText">Mover</span>
                        <span class="spinner-border spinner-border-sm d-none" id="moveSpinner" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para el clipboard
        window.selected_dirs = [];
        window.selected_files = [];

        // Definir funciones en el scope global
        window.clipboardMode = function() {
            window.selected_dirs = getSelectedDirectories();
            window.selected_files = getSelectedFiles();
            
            if (window.selected_dirs.length === 0 && window.selected_files.length === 0) {
                alert('No hay carpetas ni archivos seleccionados');
                return;
            }
            
            // Actualizar el título del modal con la cantidad de elementos seleccionados
            const totalItems = window.selected_dirs.length + window.selected_files.length;
            const dirText = window.selected_dirs.length === 1 ? 'carpeta' : 'carpetas';
            const fileText = window.selected_files.length === 1 ? 'archivo' : 'archivos';
            
            let selectionText = '';
            if (window.selected_dirs.length > 0 && window.selected_files.length > 0) {
                selectionText = `${window.selected_dirs.length} ${dirText} y ${window.selected_files.length} ${fileText} seleccionados`;
            } else if (window.selected_dirs.length > 0) {
                selectionText = `${window.selected_dirs.length} ${dirText} seleccionados`;
            } else {
                selectionText = `${window.selected_files.length} ${fileText} seleccionados`;
            }
            
            $('#clipboardModalLabel').text(`Portapapeles - ${selectionText}`);
            
            // Mostrar los nombres de los elementos seleccionados
            displaySelectedItems();
            
            $('#clipboardModal').modal('show')
        }

        function getSelectedDirectories() {
            const selected = [];
            $('.dir-checkbox:checked').each(function() {
                selected.push($(this).val());
            });
            return selected;
        }
        
        function getSelectedFiles() {
            const selected = [];
            $('.file-checkbox:checked').each(function() {
                selected.push($(this).val());
            });
            return selected;
        }

        /**
         * Muestra los nombres de carpetas y archivos seleccionados en el textarea
         */
        function displaySelectedItems() {
            let displayText = '';
            
            // Agregar carpetas
            if (window.selected_dirs.length > 0) {
                displayText += '📁 CARPETAS:\n';
                window.selected_dirs.forEach((dirPath, index) => {
                    const dirName = dirPath.split('/').pop() || dirPath;
                    displayText += `  ${index + 1}. ${dirName}\n`;
                });
                displayText += '\n';
            }
            
            // Agregar archivos
            if (window.selected_files.length > 0) {
                displayText += '📄 ARCHIVOS:\n';
                window.selected_files.forEach((filePath, index) => {
                    const fileName = filePath.split('/').pop() || filePath;
                    displayText += `  ${index + 1}. ${fileName}\n`;
                });
            }
            
            $('#selectedItemsDisplay').val(displayText.trim());
        }

        function searchDirectories() {
            var path = $('#pathDataList').val();
            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            if (path === '') {
                alert('Por favor, ingrese una ruta de destino');
                return;
            }

            formData.append('path', path);

            $.ajax({
                url: "{{ route('client.directory.search') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    var response_data = response;
                    console.log(response_data);
                    $('#pathlistOptions').empty(); // Clear previous options
                    response_data.forEach(function(response_data) {
                        $('#pathlistOptions').append(
                            `<option value="${response_data.path}"> ${response_data.path} </option>`
                        );
                    });
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    //console.error(error);
                }
            });
        }

        function copyDirectories() {
            var path = $('#selectedPath').val();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            if (path === '') {
                alert('Por favor, seleccione una ruta de destino');
                return;
            }
            if (window.selected_dirs.length === 0 && window.selected_files.length === 0) {
                alert('No hay elementos seleccionados');
                return;
            }

            // Mostrar animación de carga
            setLoadingState('copy', true);

            // Preparar requests para carpetas y archivos
            const requests = [];
            
            // Request para copiar carpetas
            if (window.selected_dirs.length > 0) {
                var formDataDirs = new FormData();
                formDataDirs.append('path', path);
                formDataDirs.append('directories', JSON.stringify(window.selected_dirs));
                
                requests.push(
                    $.ajax({
                        url: "{{ route('client.directory.copy') }}",
                        type: 'POST',
                        data: formDataDirs,
                        processData: false,
                        contentType: false,
                        headers: {"X-CSRF-TOKEN": csrfToken}
                    })
                );
            }
            
            // Request para copiar archivos
            if (window.selected_files.length > 0) {
                var formDataFiles = new FormData();
                formDataFiles.append('path', path);
                formDataFiles.append('file_paths', JSON.stringify(window.selected_files));
                
                requests.push(
                    $.ajax({
                        url: "{{ route('client.file.copy') }}",
                        type: 'POST',
                        data: formDataFiles,
                        processData: false,
                        contentType: false,
                        headers: {"X-CSRF-TOKEN": csrfToken}
                    })
                );
            }

            // Ejecutar todas las peticiones
            $.when.apply($, requests)
                .done(function() {
                    console.log('Elementos copiados correctamente');
                    setLoadingState('copy', false);
                    
                    // Mostrar mensaje de éxito con animación
                    showSuccessMessage('¡Elementos copiados correctamente!');
                    
                    // Cerrar modal y recargar página después de 1 segundo
                    setTimeout(function() {
                        $('#clipboardModal').modal('hide');
                        location.reload();
                    }, 1500);
                })
                .fail(function(xhr) {
                    console.error('Error al copiar:', xhr);
                    setLoadingState('copy', false);
                    const message = xhr.responseJSON?.message || 'Error al copiar los elementos';
                    showErrorMessage(message);
                });
        }

        function moveDirectories() {
            var path = $('#selectedPath').val();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            if (path === '') {
                alert('Por favor, seleccione una ruta de destino');
                return;
            }
            if (window.selected_dirs.length === 0 && window.selected_files.length === 0) {
                alert('No hay elementos seleccionados');
                return;
            }

            if (!confirm('¿Está seguro de mover los elementos seleccionados? Esta acción no se puede deshacer.')) {
                return;
            }

            // Mostrar animación de carga
            setLoadingState('move', true);

            // Preparar requests para carpetas y archivos
            const requests = [];
            
            // Request para mover carpetas
            if (window.selected_dirs.length > 0) {
                var formDataDirs = new FormData();
                formDataDirs.append('path', path);
                formDataDirs.append('directories', JSON.stringify(window.selected_dirs));
                
                requests.push(
                    $.ajax({
                        url: "{{ route('client.directory.move') }}",
                        type: 'POST',
                        data: formDataDirs,
                        processData: false,
                        contentType: false,
                        headers: {"X-CSRF-TOKEN": csrfToken}
                    })
                );
            }
            
            // Request para mover archivos
            if (window.selected_files.length > 0) {
                var formDataFiles = new FormData();
                formDataFiles.append('path', path);
                formDataFiles.append('file_paths', JSON.stringify(window.selected_files));
                
                requests.push(
                    $.ajax({
                        url: "{{ route('client.file.move') }}",
                        type: 'POST',
                        data: formDataFiles,
                        processData: false,
                        contentType: false,
                        headers: {"X-CSRF-TOKEN": csrfToken}
                    })
                );
            }

            // Ejecutar todas las peticiones
            $.when.apply($, requests)
                .done(function() {
                    console.log('Elementos movidos correctamente');
                    setLoadingState('move', false);
                    
                    // Mostrar mensaje de éxito con animación
                    showSuccessMessage('¡Elementos movidos correctamente!');
                    
                    // Cerrar modal y recargar página después de 1 segundo
                    setTimeout(function() {
                        $('#clipboardModal').modal('hide');
                        location.reload();
                    }, 1500);
                })
                .fail(function(xhr) {
                    console.error('Error al mover:', xhr);
                    setLoadingState('move', false);
                    const message = xhr.responseJSON?.message || 'Error al mover los elementos';
                    showErrorMessage(message);
                });
        }
    </script>

    <script>
        let selectedPath = '';

        function loadDirectoryTree(path = '') {
            // Spinner de carga
            $('#directoryTree').html(
                '<div class="text-center py-2"><div class="spinner-border spinner-border-sm"></div></div>');
            $.ajax({
                url: "{{ route('client.directory.list') }}",
                data: {
                    path
                },
                success: function(items) {
                    renderTree(items, path);
                },
                error: function(xhr) {
                    $('#directoryTree').html('<li class="text-danger">Error al cargar directorios</li>');
                }
            });
        }

        function renderTree(items, basePath) {
            const $tree = $('#directoryTree').empty();

            // Botón “Atrás” si no estamos en la raíz
            if (basePath) {
                const parent = basePath.split('/').slice(0, -1).join('/');
                $tree.append(`
        <li class="directory-item back" data-path="${parent}">
          <i class="bi bi-arrow-left-circle-fill back-arrow"></i> Atrás
        </li>
      `);
            }


            // Ordenar carpetas alfabéticamente por nombre (insensible a mayúsculas/minúsculas)
            const sortedItems = items.sort((a, b) => {
                return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
            });

            sortedItems.forEach(item => {
                $tree.append(`
        <li class="directory-item" data-path="${item.path}">
          <i class="bi bi-folder-fill"></i> ${item.name}
        </li>
      `);
            });


            $('.directory-item').on('click', function(e) {
                const path = $(this).data('path');
                if ($(this).hasClass('back')) {
                    loadDirectoryTree(path);
                } else if (e.target.tagName === 'LI' || $(this).hasClass('directory-item')) {
                    loadDirectoryTree(path);
                }
            });

            $('.directory-item').on('dblclick', function() {
                selectedPath = $(this).data('path');
                updateSelectedPath();
                $('.directory-item.selected').removeClass('selected');
                $(this).addClass('selected');
            });

            updateSelectedPath(basePath);
        }

        function updateSelectedPath(overridePath = null) {
            if (overridePath !== null) {
                selectedPath = overridePath;
            }
            const full = 'client_system/' + (selectedPath ? selectedPath + '/' : '');
            $('#currentPathDisplay').val(full);
            $('#selectedPath').val(full);
        }

        // Botón Home 
        $(document).ready(function() {
            $('#btnHome').on('click', () => loadDirectoryTree(''));
            loadDirectoryTree();
        });

        /**
         * Controla el estado de carga de los botones
         */
        function setLoadingState(action, isLoading) {
            const button = action === 'copy' ? '#copyButton' : '#moveButton';
            const spinner = action === 'copy' ? '#copySpinner' : '#moveSpinner';
            const text = action === 'copy' ? '#copyButtonText' : '#moveButtonText';
            
            if (isLoading) {
                $(button).prop('disabled', true);
                // Deshabilitar el otro botón también
                const otherButton = action === 'copy' ? '#moveButton' : '#copyButton';
                $(otherButton).prop('disabled', true);
                
                $(spinner).removeClass('d-none');
                $(text).text(action === 'copy' ? 'Copiando...' : 'Moviendo...');
            } else {
                $(button).prop('disabled', false);
                const otherButton = action === 'copy' ? '#moveButton' : '#copyButton';
                $(otherButton).prop('disabled', false);
                
                $(spinner).addClass('d-none');
                $(text).text(action === 'copy' ? 'Copiar' : 'Mover');
            }
        }

        /**
         * Muestra un mensaje de éxito con animación
         */
        function showSuccessMessage(message) {
            // Crear elemento de alerta
            const alert = $(`
                <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                     role="alert" style="z-index: 9999; min-width: 300px;">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>${message}</strong>
                </div>
            `);
            
            $('body').append(alert);
            
            // Remover después de 3 segundos
            setTimeout(function() {
                alert.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }

        /**
         * Muestra un mensaje de error con animación
         */
        function showErrorMessage(message) {
            // Crear elemento de alerta
            const alert = $(`
                <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                     role="alert" style="z-index: 9999; min-width: 300px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            
            $('body').append(alert);
            
            // Remover después de 5 segundos
            setTimeout(function() {
                alert.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    </script>

    <style>
        .directory-tree-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }

        .directory-tree {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .directory-tree .directory-item {
            cursor: pointer;
            padding: 5px 8px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border-radius: 4px;
        }

        .directory-tree .directory-item>i.bi-folder-fill {
            margin-right: 8px;
            color: #ffc107;
            font-size: 1.1em;
        }

        .directory-tree .directory-item:hover {
            background-color: rgba(151, 219, 244, 0.68);
            transform: translateX(5px);
        }

        .directory-tree .directory-item.selected {
            background-color: #e3f2fd;
            font-weight: bold;
            border-left: 3px solid #0d6efd;
        }

        .back-arrow {
            color: rgb(74, 107, 223);
            fill: rgb(74, 107, 223);
        }

        /* Estilos mejorados para los botones */
        #copyButton, #moveButton {
            min-width: 140px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #copyButton:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3);
        }

        #moveButton:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }

        #copyButton:disabled, #moveButton:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Animación para el spinner */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner-border {
            animation: spin 0.75s linear infinite;
        }

        /* Animación para las alertas */
        @keyframes slideDown {
            from {
                transform: translate(-50%, -100%);
                opacity: 0;
            }
            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        .alert.position-fixed {
            animation: slideDown 0.3s ease-out;
        }

        /* Efecto de pulso en los botones cuando se hace clic */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
            }
        }

        #copyButton:active:not(:disabled) {
            animation: pulse 0.6s;
        }

        #moveButton:active:not(:disabled) {
            animation: pulse 0.6s;
        }

        /* Estilo para el textarea de elementos seleccionados */
        #selectedItemsDisplay {
            font-family: 'Courier New', monospace;
            color: #495057;
            border: 2px solid #dee2e6;
            line-height: 1.6;
        }

        #selectedItemsDisplay:focus {
            outline: none;
            box-shadow: none;
        }
    </style>
