<div class="modal fade" id="clipboardModal" tabindex="-1" aria-labelledby="clipboardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="clipboardModalLabel">Portapapeles</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Ruta seleccionada:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="currentPathDisplay" value="client_system/"
                            readonly>
                        <button class="btn btn-outline-secondary" id="btnHome" title="Volver a raíz">
                            <i class="bi bi-house-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="directory-tree-container border rounded p-2 mb-3">
                    <ul id="directoryTree" class="directory-tree list-unstyled"></ul>
                </div>
                <input type="hidden" name="path" id="selectedPath" value="client_system/">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="copyButton"
                    onclick="copyDirectories()">Copiar</button>
                <button type="button" class="btn btn-warning" id="moveButton"
                    onclick="moveDirectories()">Mover</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        var selected_dirs = [];

        function clipboardMode() {
            selected_dirs = getSelectedDirectories();
            if (selected_dirs.length === 0) {
                alert('No hay directorios seleccionados');
                return;
            }
            $('#clipboardModal').modal('show')
        }

        function getSelectedDirectories() {
            const selected = [];
            $('.dir-checkbox:checked').each(function() {
                selected.push($(this).val());
            });
            return selected;
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
                    console.error(error);
                }
            });
        }

        function copyDirectories() {
            var path = $('#selectedPath').val();
            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            if (path === '') {
                alert('Por favor, ingrese una ruta de destino');
                return;
            }
            if (selected_dirs.length === 0) {
                alert('No hay directorios seleccionados');
                return;
            }

            formData.append('path', path);
            formData.append('directories', JSON.stringify(selected_dirs));



            console.log("Directories a copiar:", selected_dirs);
            console.log("JSON stringificado:", JSON.stringify(selected_dirs));

            $.ajax({
                url: "{{ route('client.directory.copy') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    console.log(response);
                    alert('Directorios copiados correctamente');
                    $('#clipboardModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    console.error(error);
                }
            });
        }

        function moveDirectories() {
            var path = $('#selectedPath').val();
            var formData = new FormData();
            var csrfToken = $('meta[name="csrf-token"]').attr("content");

            formData.append('path', path);
            formData.append('directories', JSON.stringify(selected_dirs));

            console.log('Path: ', path);
            console.log('Directories: ', selected_dirs);

            $.ajax({
                url: "{{ route('client.directory.move') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    console.log(response);
                    alert('Directorios movidos correctamente');
                    $('#clipboardModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    console.error(error);
                }
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


            items.forEach(item => {
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
        }

        .directory-tree .directory-item>i.bi-folder-fill {
            margin-right: 8px;
            color: #ffc107;
            font-size: 1.1em;
        }

        .directory-tree .directory-item:hover {
            background-color: rgba(151, 219, 244, 0.68);
        }

        .directory-tree .directory-item.selected {
            background-color: #e3f2fd;
            font-weight: bold;
        }

        .back-arrow {
            color: rgb(74, 107, 223);
            fill: rgb(74, 107, 223);
        }
    </style>
