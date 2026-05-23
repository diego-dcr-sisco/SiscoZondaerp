<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
    <form class="modal-dialog" action="{{ route('customer.file.upload', ['customerId' => $customer->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="filesModalLabel">Archivos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="filename" class="form-label is-required" id="filename-label">Tipo de archivo</label>
                    <select class="form-select" id="filename" name="filename_id" required>
                        @foreach ($filenames as $filename)
                            <option value="{{ $filename->id }}">{{ $filename->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de expiración</label>
                    <input type="date" class="form-control" id="expirated-at" name="expirated_at" value="" />
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Archivo</label>
                    <input class="form-control" accept=".pdf, .xlsx, .png, .jpg, .jpeg" type="file" id="file"
                        name="file" required>
                    <div class="form-text">Solo se permiten archivos .PDF .XLSX .JPG .JPEG .PNG y no deben superar 5 MB.</div>
                    <div class="invalid-feedback d-block" id="file-size-error" style="display: none !important;"></div>
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

<script>
    function setFileId(file_id) {
        $('#file-id').val(file_id);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file');
        const fileSizeError = document.getElementById('file-size-error');
        const maxSizeBytes = 5 * 1024 * 1024;

        if (!fileInput || !fileSizeError) {
            return;
        }

        fileInput.addEventListener('change', function() {
            const selectedFile = this.files && this.files[0] ? this.files[0] : null;

            if (!selectedFile) {
                fileSizeError.textContent = '';
                fileSizeError.style.setProperty('display', 'none', 'important');
                return;
            }

            if (selectedFile.size > maxSizeBytes) {
                this.value = '';
                fileSizeError.textContent = 'El archivo no debe ser mayor a 5 MB.';
                fileSizeError.style.setProperty('display', 'block', 'important');
                return;
            }

            fileSizeError.textContent = '';
            fileSizeError.style.setProperty('display', 'none', 'important');
        });
    });
</script>
