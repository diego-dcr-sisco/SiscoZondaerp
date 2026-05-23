<div class="modal fade" id="areaCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="area-form" action="{{ route('area.store', ['customerId' => $customer->id]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="areaModalLabel">Agregar área</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label is-required">Nombre de zona</label>
                    <input type="text" class="form-control" id="area-name" name="name"
                        placeholder="Agrega una nueva zona/área" required>
                </div>
                <div class="mb-3">
                    <label for="exampleFormControlInput2" class="form-label">Tipo de zona</label>
                    <select class="form-select " id="area-zone-type" name="zone_type_id">
                        <option value="">No Aplica (N/A)</option>
                        @foreach ($zone_types as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="m2" class="form-label is-required">Metros cuadrados (m²)</label>
                    <input type="number" class="form-control" id="area-m2" name="m2" min="0" value="0" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-primary"> {{ __('buttons.store') }} </button>
            </div>
        </form>
    </div>
</div>
