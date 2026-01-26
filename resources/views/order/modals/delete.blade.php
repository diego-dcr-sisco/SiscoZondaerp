<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <form class="modal-dialog modal-dialog-centered" method="GET" action="{{ route('order.destroy') }}"
        enctype="multipart/form-data">
        <input type="hidden" id="delete-id" name="delete_id" value="">
        <div class="modal-content">
            <div class="modal-body">
                <span class="text-warning-emphasis fw-bold">Atención: </span> ¿Estás seguro de que deseas cancelar la
                orden de servicio?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm">Aceptar</button>
            </div>
        </div>
    </form>
</div>

<script>
    function set_delete_id(value) {
        $('#delete-id').val(value);
        $('#deleteModal').modal('show');
    }
</script>
