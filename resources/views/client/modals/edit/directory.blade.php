<div class="modal fade" id="editDirectoryModal" tabindex="-1" aria-labelledby="editDirectoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST"
            action="{{ route('client.directory.update') }}"enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="directoryModalLabel">Editar carpeta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="name" class="form-label is-required">Nombre: </label>
                <input type="text" class="form-control" id="edit-name"
                    name="name" value="" maxlength="50" required>
                <input type="hidden" id="path" name="path" value="" />
                <input type="hidden" id="root-path" name="root_path" value="{{ $data['root_path']}}" />
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __('buttons.update')}}</button>
                <button type="button" class="btn btn-danger"
                     data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setRoot(name, path) {
        $('#edit-name').val(name);
        $('#path').val(path);
    }
</script>

