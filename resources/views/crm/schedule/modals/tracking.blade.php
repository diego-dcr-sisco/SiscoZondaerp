<div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="tracking-form" method="POST"
            action="{{ route('tracking.handle', ['type' => $type]) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="trackingModalLabel">Seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <input type="hidden" id="customer-id" name="model_id" value="">
                    <input type="text" class="form-control" id="customer-name" name="customer_name" value=""
                        disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Servicio a programar</label>
                    <select class="form-select" id="service" name="service_id" required>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label is-required">Fecha de seguimiento</label>
                    <input type="date" class="form-control" id="date" name="tracking_date" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="aubmit" class="btn btn-primary">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setCustomer(element) {
        const data = JSON.parse(element.getAttribute("data-customer"));
        //const fetched_data = customer_services.find(item => item.customer_id == data.id);
        $('#tracking-form').find(
                'input[type="text"], input[type="number"], input[type="email"], input[type="date"], input[type="file"], select, textarea'
            )
            .val('');
        $('#service').empty();

        $('#customer-id').val(data.id);
        $('#customer-name').val(data.name);

        /*if (fetched_data) {
            fetched_data.services.forEach(item => {
                $('#service').append(new Option(item.name, item.id));
            });
        }*/

        services.forEach(item => {
            const type = item.service_type_id == 1 ? 'Domestico' : (item.service_type_id == 2 ? 'Comercial' :
                'Industrial');
            const name = `[${type}] ${item.name}`;
                $('#service').append(new Option(name, item.id));
        });
    }
</script>
