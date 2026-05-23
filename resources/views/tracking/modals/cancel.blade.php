<div class="modal fade" id="cancelModal" tabindex="-2" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('tracking.set') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancelar seguimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="cancelTrackingForm">
                        <input type="hidden" id="cancel_tracking_id" name="tracking_id">
                        <input type="hidden" id="cancel_status" name="status" value="canceled">
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Razón de cancelación</label>
                            <textarea class="form-control" id="cancel_reason" name="reason" rows="3" required
                                placeholder="Explica por qué se cancela el seguimiento..."></textarea>
                            <div class="form-text">
                                Proporciona una razón clara para la cancelación de este seguimiento.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"
                        onclick="handleTracking('canceled')">Guardar</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function cancelModal(tracking_id) {
        $('#cancelModal').modal('show');
        $('#cancel_tracking_id').val(tracking_id);
        $('#cancelModal').addClass("modal-blur");
    }
</script>