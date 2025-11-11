<div class="modal fade" id="editFileModal" tabindex="-1" aria-labelledby="editFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST"
            action="{{ route('client.file.update') }}"enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="directoryModalLabel">Editar archivo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="name" class="form-label is-required">Nombre: </label>
                <input type="text" class="form-control" id="filename" name="name" value="" maxlength="50"
                    required>
                <input type="hidden" id="extension" name="extension" value="" />
                <input type="hidden" id="filepath" name="path" value="" />
                <input type="hidden" id="root-path" name="root_path" value="{{ $data['root_path'] }}" />
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __('buttons.update') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function splitFilename(filename) {
        const parts = filename.split('.');
        const extension = parts.length > 1 ? parts.pop() : '';
        const filenameWithoutExt = parts.join('.');

        return {
            filename: filenameWithoutExt,
            extension: extension
        };
    }

    function setFilename(name, path) {
        let file_data = splitFilename(name)
        $('#filename').val(file_data['filename']);
        $('#extension').val(file_data['extension']);
        $('#filepath').val(path);
    }
</script>
