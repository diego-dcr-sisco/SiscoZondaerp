<div class="modal fade" id="technicianModal" tabindex="-1" aria-labelledby="technicianModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="technicianModalLabel">Asignar técnicos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded shadow p-3 mb-3">
                    <div class="fw-bold mb-3">Datos de busqueda</div>
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Rango de Fechas</label>
                        <select class="form-select form-select-sm" id="customer-range" name="customer_range">
                            @foreach ($customer_ranges as $item)
                                <option value="{{ $item->id }}"
                                    {{ request('customer_range') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Rango de fechas</label>
                        <input type="text" class="form-control form-control-sm date-range-picker"
                            id="date-range-technician" name="date_range_technician" value="{{ request('date_range') }}"
                            placeholder="Selecciona un rango">
                    </div>

                    <button type="button" class="btn btn-primary btn-sm" id="search-technicians"
                        onclick="searchTechnicianByInterval()">
                        Buscar
                    </button>
                </div>
                <div class="border rounded shadow p-3 mb-3">
                    <div class="fw-bold mb-3">Técnicos asignados</div>
                    <div class="mb-3">
                        <ul class="list-group" id="technician-list">
                            <li class="list-group-item text-danger">Sin técnicos</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="assignTechnicians()">Asignar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function searchTechnicianByInterval() {
        var form_data = new FormData();
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var date_range = $('#date-range-technician').val();
        var customer_range = $('#customer-range').val();
        form_data.append('date', date_range);
        form_data.append('customer_id', customer_range);

        $.ajax({
            url: "{{ route('order.search.technician') }}",
            type: 'POST',
            data: form_data,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function(response) {
                const technicianList = $('#technician-list');
                technicianList.empty(); // Limpiar la lista antes de agregar nuevos técnicos
                if (response.technicians.length > 0) {
                    response.technicians.forEach(function(technician) {
                        technicianList.append(
                            `<li class="list-group-item">
                                    <div class="form-check">
                                        <input class="form-check-input range-technicians" type="checkbox" value="${technician.id}" id="technician-${technician.id}" ${technician.is_assigned ? 'checked' : ''}>
                                        ${technician.name}
                                    </div>
                                </li>`
                        );
                    });

                    order_ids = response.orders;
                } else {
                    technicianList.append('<li class="list-group-item text-danger">Sin técnicos</li>');
                }
            },
            error: function(xhr, status, error) {
                //console.error('Error al obtener técnicos:', error);
            }
        });
    }

    function assignTechnicians() {
        var form_data = new FormData();
        var assigned_technicians = [];

        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        // Obtener los técnicos seleccionados
        $('.range-technicians:checked').each(function() {
            assigned_technicians.push(parseInt($(this).val()));
        });

        form_data.append('technicians', JSON.stringify(assigned_technicians));
        form_data.append('orders', JSON.stringify(order_ids));

        showSpinner();

        $.ajax({
            url: "{{ route('order.assign.technicians') }}",
            type: 'POST',
            data: form_data,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            success: function(response) {
                if (response.success) {
                    alert('Técnicos asignados correctamente.');
                    $('#technicianModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error al asignar técnicos.');
                }
            },
            error: function(xhr, status, error) {
                //console.error('Error al asignar técnicos:', error);
            },
            complete: function() {
                hideSpinner();
            },
        });
    }
</script>
