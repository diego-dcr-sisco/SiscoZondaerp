<!-- Modal -->
<div class="modal fade" id="datesModal" tabindex="-2" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Fechas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="preview">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal"
                    data-bs-target="#editServiceModal">{{ __('buttons.cancel') }}</button>
                <button type="button" class="btn btn-primary"
                    onclick="updateDates()">{{ __('buttons.store') }}</button>
            </div>
        </div>
    </div>
</div>
