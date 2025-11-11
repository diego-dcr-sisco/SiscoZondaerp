<div class="modal fade" id="rejectPurchaseModal" tabindex="-1" role="dialog" aria-labelledby="rejectPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="rejectPurchaseForm" class="form" method="POST" action="{{ route('purchase-requisition.reject', $requisition->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectPurchaseModalLabel">Rechazar Requisición de Compra</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectionReason">Motivo del Rechazo</label>
                        <textarea class="form-control" id="rejectionReason" name="rejectionReason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" data-dismiss="modal">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>