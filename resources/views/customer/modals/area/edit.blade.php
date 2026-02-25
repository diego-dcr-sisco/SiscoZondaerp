<div class="modal fade" id="areaEditModal" tabindex="-1" aria-labelledby="areaEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="area-form" action="{{ route('area.update') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="areaEditModalLabel">Editar área</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="name" class="form-label is-required">Nombre de zona</label>
                    <input type="text" class="form-control" id="edit-area-name" name="name"
                        placeholder="Agrega una nueva zona/área">
                </div>
                <div class="mb-3">
                    <label for="area-zone_type_id" class="form-label">Tipo de zona</label>
                    <select class="form-select " id="edit-area-zone" name="zone_type_id">
                        <option value="">No Aplica (N/A)</option>
                        @foreach ($zone_types as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="m2" class="form-label is-required">Metros cuadrados (m²)</label>
                    <input type="number" class="form-control" id="edit-area-m2" name="m2" min="0" step="0.01"
                        value="0" required>
                </div>
                <input type="hidden" id="edit-area-id" name="id" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary"> {{ __('buttons.update') }} </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setInputs(element) {
        const jsonData = element.getAttribute('data-area');
        try {
            const data = JSON.parse(jsonData);
            $('#edit-area-name').val(data.name);
            $('#edit-area-zone').val(data.zone_type_id);
            $('#edit-area-m2').val(data.m2);
            $('#edit-area-id').val(data.id);
        } catch (error) {
            //console.error('Error parsing JSON data:', error);
        }
    }
</script>
