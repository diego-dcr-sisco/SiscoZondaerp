<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" class="form" method="POST"
            action="{{ route('product.store.file', ['id' => $product->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="filesModalLabel">Archivo</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="filename" class="form-label is-required">Tipo de archivo</label>
                    <select class="form-select" id="filename" name="filename_id" required>
                        @foreach ($filenames as $filename)
                            <option value="{{ $filename->id }}">{{ $filename->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="" class="form-label">Fecha de expiración</label>
                    <input type="date" class="form-control" id="expirated-at" name="expirated_at">
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label is-required">Archivo</label>
                    <input type="file" class="form-control rounded" accept=".pdf" name="file"
                        id="file" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
            </div>
        </form>
    </div>
</div>
