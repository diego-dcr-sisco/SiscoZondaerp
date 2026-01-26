<div class="modal fade" id="filesModalAdd"  tabindex="-1">
    <form class="modal-dialog" action="{{ route('user.file.uploadByName', ['userId' => $user->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="filesModalAddLabel">Archivos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="filename" class="form-label is-required" id="filename-label">Nombre  del archivo</label>
                    <input type="text" class="form-control" id="filename" name="filename" placeholder="Agrega un nombre para el archivo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de expiración</label>
                    <input type="date" class="form-control" id="expirated-at" name="expirated_at" value="" />
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Archivo</label>
                    <input class="form-control" accept=".pdf, .png, .jpg, .jpeg" type="file" id="file"
                        name="file" required>
                    <div class="form-text">Solo se permiten archivos con formato .PDF .JPG .JPEG .PNG</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    {{ __('buttons.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('buttons.store') }}
                </button>
            </div>
        </div>
    </form>
</div>
