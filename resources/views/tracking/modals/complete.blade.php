<div class="modal fade" id="completedModal" tabindex="-2" aria-labelledby="completedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tracking.set') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completedModalLabel">Completar seguimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="complete_tracking_id" name="tracking_id">
                    <input type="hidden" id="completed_status" name="status" value="completed">
                    <div class="mb-3">
                        <label for="complete_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="complete_description" name="reason" rows="3" required
                            placeholder="Describe cómo se completó el seguimiento..."></textarea>
                        <div class="form-text">
                            Detalla los resultados o acciones tomadas para completar este seguimiento.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"
                        onclick="handleTracking('completed')">Guardar</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function completedModal(tracking_id) {
        $('#completedModal').modal('show');
        $('#complete_tracking_id').val(tracking_id);
        $('#completedModal').addClass("modal-blur");
    }
</script>