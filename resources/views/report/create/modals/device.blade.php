<div class="modal fade" id="deviceModal" tabindex="-1" aria-labelledby="deviceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceModalLabel">Agregar revision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="floorplan" class="form-label">Plano</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="floorplan" name="floorplan">
                            <option value="" selected>Seleccionar plano</option>
                            @foreach ($order->customer->floorplans as $floorplan)
                                <option value="{{ $floorplan->id }}">{{ $floorplan->filename }}</option>
                            @endforeach
                        </select>
                        <input type="number" class="form-control" id="version" name="version" value="1"
                            min="1">
                        <button class="btn btn-outline-success" type="button" id="button-search"
                            onclick="getDevices()">Buscar</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="service" class="form-label">Servicio</label>
                    <input type="text" class="form-control" id="service_device" name="service_device" value=""
                        readonly>
                </div>

                <div class="mb-3">
                    <label for="revision" class="form-label">Dispositivos</label>
                    <select class="form-select" id="device" name="device">
                        <option selected>Seleccionar dispositivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function getDevices() {
        let floorplan = $('#floorplan').val();
        let version = $('#version').val();

        const csrfToken = $('meta[name="csrf-token"]').attr("content")

        var formData = new FormData();
        formData.append('floorplan_id', floorplan);
        formData.append('version', version ?? 1);
        formData.append('order_id', {{ $order->id }});

        if (floorplan == null || floorplan == '') {
            alert('Selecciona un plano');
            return;
        }

        $.ajax({
            url: "{{ route('report.device') }}",
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            processData: false,
            contentType: false,
            success: function(response) {
                var data = response.data;

                $('#service_device').val(data.service_name);
                $('#device').empty();

                if (data.devices.length == 0) {
                    $('#device').append('<option selected>Seleccionar dispositivo</option>');
                    alert('No existe la version en el plano o no hay dispositivos en el plano');
                    return;
                }

                $.each(data.devices, function(key, value) {
                    $('#device').append(`<option value="${value.id}">${value.name}</option>`);
                });
            },
            error: function(data) {
                console.log('Error:', data);
            }
        });

    }
</script>
