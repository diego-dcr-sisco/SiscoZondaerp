<div class="modal fade" id="editTrackingModal" tabindex="-1" aria-labelledby="editTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTrackingModalLabel">Seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="service" class="form-label">Servicio</label>
                    <select class="form-select" id="tracking-service">
                        @foreach ($services as $s)
                            <option value="{{ $s->id }}">
                                {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="start-date" class="form-label">Fecha</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="tracking-date" value="">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="service" class="form-label">Estado</label>
                    <select class="form-select" id="tracking-status">
                        <option value="active">Activo</option>
                        <option value="completed">Completado</option>
                        <option value="canceled">Cancelado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Título</label>
                    <input type="text" class="form-control" id="tracking-title" placeholder="Título del seguimiento">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="tracking-description" rows="5"
                        placeholder="Ingrese los detalles del seguimiento: acciones tomadas, estado actual del servicio, observaciones relevantes y próximos pasos"></textarea>
                </div>
                <input type="hidden" id="tracking-id" name="tracking_id">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="updateTracking()">Actualizar</button>
            </div>
        </div>
    </div>
</div>