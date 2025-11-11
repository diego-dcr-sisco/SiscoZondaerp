@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="history.back(); return false;"
                class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                PLANO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $floorplan->filename }}</span>
            </span>
        </div>
        <div class="m-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm caption-top">
                    <caption class="border rounded-top p-2 text-dark bg-light">
                        <form action="{{ route('floorplan.search.qr', ['id' => $floorplan->id]) }}" method="POST">
                            @csrf
                            <div class="row g-3 mb-0">
                                <div class="col-lg-4 col-12">
                                    <label for="version" class="form-label">Version</label>
                                    <select class="form-select form-select-sm" id="version" name="version">
                                        @foreach ($floorplan->versions()->latest()->get() as $floorVersion)
                                            <option value="{{ $floorVersion->version }}" {{ request('version') == $floorVersion->version ? 'selected' : '' }}>
                                                v.{{ $floorVersion->version }} [{{ \Carbon\Carbon::parse($floorVersion->updated_at)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($floorVersion->updated_at)->format('H:i:s') }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="control-point" class="form-label">Punto de control</label>
                                    <select class="form-select form-select-sm" id="point" name="point">
                                        <option value="">Todos los puntos de control</option>
                                        @foreach ($control_points as $point)
                                            <option value="{{ $point->id }}" {{ request('point') == $point->id ? 'selected' : '' }}>
                                                {{ $point->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4 col-12">
                                    <label for="app_area" class="form-label">Área</label>
                                    <select class="form-select form-select-sm" id="app-area" name="app_area">
                                        <option value="">Todas las zonas</option>
                                        @foreach ($application_areas as $app_area)
                                            <option value="{{ $app_area->id }}" {{ request('app_area') == $app_area->id ? 'selected' : '' }}>{{ $app_area->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="signature_status" class="form-label">Dirección</label>
                                    <select class="form-select form-select-sm" id="direction" name="direction">
                                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                        </option>
                                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                        </option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label for="order_type" class="form-label">Total</label>
                                    <select class="form-select form-select-sm" id="size" name="size">
                                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="col-lg-12 d-flex justify-content-end m-0">
                                    <button type="submit" class="btn btn-primary btn-sm me-2">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">
                                <input class="form-check-input border-secondary" type="checkbox" id="select-all"
                                    onchange="selectAllDevices(this.checked)">
                            </th>
                            <th scope="col">#</th>
                            <th scope="col">Código</th>
                            <th scope="col">Color</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Zona</th>
                            <th scope="col">Version</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @foreach ($devices as $device)
                            <tr>
                                <td>
                                    <input class="form-check-input border-secondary" type="checkbox"
                                        value="{{ $device->id }}" onchange="selectDevice(this)">
                                </td>
                                <td>{{ $device->nplan }}</td>
                                <td class="text-primary fw-bold">{{ $device->code }}</td>
                                <td class="">
                                    <div class="rounded"
                                        style="width:25px; height: 25px; background-color: {{ $device->color }};">
                                    </div>
                                </td>
                                <td id="{{ $device->type_control_point_id }}">{{ $device->controlPoint->name }}
                                </td>
                                <td id="{{ $device->application_area_id }}">{{ $device->applicationArea->name }}
                                </td>
                                <td>{{ $device->version }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form class="p-0" method="POST" action="{{ route('floorplan.qr.print', ['id' => $floorplan->id]) }}" target="_blank">
                @csrf
                <input type="hidden" id="selected-devices" name="selected_devices" value="">
                <button type="submit" class="btn btn-primary my-3" onclick="setDevices()">Generar</button>
            </form>
        </div>
    </div>

    <script>
        var selected_devices = [];

        function selectAllDevices(isChecked) {
            if (isChecked) {
                selected_devices = $('#table-body input[type="checkbox"]')
                    .prop('checked', true)
                    .map(function() {
                        return this.value;
                    }).get();
            } else {
                $('#table-body input[type="checkbox"]').prop('checked', false);
                selected_devices = [];
            }
        }

        function selectDevice(element) {
            const value = parseInt(element.value);
            const isChecked = element.checked;
            if (isChecked) {
                if (!selected_devices.includes(value)) {
                    selected_devices.push(value);
                }
            } else {
                if (selected_devices.includes(value)) {
                    selected_devices = selected_devices.filter(item => item != value);
                }
            }
        }

        function setDevices() {
            $('#selected-devices').val(JSON.stringify(selected_devices));
        }

        function searchDevices() {
            const csrfToken = $('meta[name="csrf-token"]').attr("content");

            var formData = new FormData();
            var point = $('#point').val();
            var app_area = $('#app-area').val();
            var version = $('#version').val();
            var html = '';

            formData.append('point', point);
            formData.append('app_area', app_area);
            formData.append('version', version);

            $.ajax({
                url: "{{ route('floorplan.search.devices', ['id' => $floorplan->id]) }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                success: function(response) {
                    const devices = response.data;
                    console.log(response)
                    if (devices.length > 0) {
                        $('#table-body').html('');

                        devices.forEach(device => {
                            html += `
                                    <tr>
                                        <td>
                                            <input class="form-check-input border-secondary" type="checkbox"
                                                value="${device.device_id}" onchange="selectDevice(this)" />
                                        </td>
                                        <td>${device.nplan}</td>
                                        <td class="d-flex justify-content-center">
                                            <div class="rounded"
                                                style="width:25px; height: 25px; background-color: ${device.color};"></div>
                                        </td>
                                        <td>${device.type}
                                        </td>
                                        <td>${device.app_area}
                                        </td>
                                        <td>${device.version}</td>
                                    </tr>
                                `;
                        });
                    }

                    $('#table-body').html(html);
                },
                error: function(error) {
                    console.error(error);
                },
            });
        }
    </script>
@endsection
