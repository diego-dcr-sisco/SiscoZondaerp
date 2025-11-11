<div class="modal" id="addPestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar plaga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="new-pest-input" class="form-label">Plaga</label>
                    <input class="form-control" list="pestOptions" id="new-pest-input"
                        placeholder="Escribe para buscar...">
                    <datalist id="pestOptions">
                        @foreach ($pests as $pest)
                            <option value="{{ $pest->name }}" data-id="{{ $pest->id }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="mb-3">
                    <label for="new-pest-total" class="form-label">Cantidad</label>
                    <input type="number" class="form-control" id="new-pest-total" placeholder="0" min="1"
                        value="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addNewPest()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function addNewPest() {
        var pestName = $('#new-pest-input').val();
        var $opt = $('#pestOptions option').filter(function() {
            return this.value === pestName;
        }).first();
        var pest_id = $opt.data('id');

        if (device_in_review <= 0) {
            return alert('No hay un dispositivo seleccionado');
        }
        if (!pest_id) {
            return alert('Selecciona una plaga válida');
        }
        var total = $('#new-pest-total').val();
        if (!total) {
            return alert('Ingresa una cantidad');
        }

        var index = devices.findIndex(d => d.id == device_in_review);
        if (index === -1) {
            return alert('Dispositivo no encontrado');
        }

        var pest_index = devices[index].pests.findIndex(p => p.id == pest_id);
        if (pest_index !== -1) {
            devices[index].pests[pest_index].total += parseInt(total);
        } else {
            devices[index].pests.push({
                id: pest_id,
                name: pestName,
                total: parseInt(total)
            });
        }
        showPests();
        $('#addPestModal').modal('hide');
        $('#new-pest-input').val('');
        $('#new-pest-total').val(1);
    }
</script>
