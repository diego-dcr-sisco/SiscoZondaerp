<!-- Modal -->
<div class="modal fade" id="referenceModal" tabindex="-1" aria-labelledby="referenceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="reference-form"
            action="{{ route('customer.reference.store', ['customerId' => $customer->id]) }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="referenceModalLabel">Referencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="name" class="form-label is-required">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" required/>
                </div>
                <div class="mb-3">
                    <label for="reference-type" class="form-label is-required">Tipo de referencia</label>
                    <select class="form-select" id="reference-type" name="reference_type_id" required>
                        @foreach ($reference_types as $reference_type)
                            <option value="{{ $reference_type->id }}">{{ $reference_type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="phone" class="col-form-label is-required">Télefono</label>
                    <input type="number" class="form-control" id="phone" name="phone" min="0" required />
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label is-required">Correo:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label is-required">Dirección:</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __('buttons.store') }}</button>
                <button type="button" class="btn btn-danger"
                    data-bs-dismiss="modal">{{ __('buttons.cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setData(element) {
        const reference = JSON.parse(element.getAttribute("data-reference"));
        const url = "{{ route('customer.reference.update', ['id' => ':id']) }}";
        const actionUrl = url.replace(':id', reference.id);

        $('#reference-form').attr('action', actionUrl);
        $('#name').val(reference.name);
        $('#reference-type').val(reference.reference_type_id);
        $('#phone').val(reference.phone);
        $('#email').val(reference.email);
        $('#address').val(reference.address);
    }

    function resetForm() {
        $('#reference-form').find(
                'input[type="text"], input[type="number"], input[type="email"], input[type="date"], input[type="file"], select, textarea'
                )
            .val('');
        $('#reference-type').val(1);
        $('#reference-form').attr('action', "{{ route('customer.reference.store', ['customerId' => $customer->id]) }}");
    }
</script>
