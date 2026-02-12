@php
    function extractParenthesesContent($cadena)
    {
        // Buscar el contenido entre paréntesis
        preg_match('/\((.*?)\)/', $cadena, $coincidencias);
        return isset($coincidencias[1]) ? $coincidencias[1] : '';
    }
@endphp

@foreach ($order->services as $service)
    <div class="mb-3">
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#newDeviceModal">
            <i class="bi bi-plus-lg"></i> Agregar revision especial
        </button>

        @if (!empty($devices))
            <button class="btn btn-warning btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#autoreviewModal">
                <i class="bi bi-tools"></i> Configurar autorevision
            </button>
        @endif
    </div>

    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th scope="col"># (Número)</th>
                <th scope="col">Código</th>
                <th scope="col">Nombre</th>
                <th scope="col">Plano</th>
                <th scope="col">Zona</th>
                <th scope="col">Plaga(s)</th>
                <th scope="col">Producto(s)</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody id="table-body-devices">
            @if (!empty($devices))
                @php
                    $reviewedDevices = collect($devices)->filter(function($device) {
                        return $device['states']['is_checked'];
                    })->sortBy('nplan');
                    $notReviewedDevices = collect($devices)->filter(function($device) {
                        return !$device['states']['is_checked'];
                    })->sortBy('nplan');
                @endphp

                {{-- Dispositivos Revisados --}}
                @if ($reviewedDevices->count() > 0)
                    <tr class="table-success">
                        <td colspan="9" class="fw-bold text-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            DISPOSITIVOS REVISADOS ({{ $reviewedDevices->count() }})
                        </td>
                    </tr>
                    @foreach ($reviewedDevices as $device)
                        <tr>
                            <th scope="row">{{ $device['nplan'] }}</th>
                            <td class="fw-bold text-primary">{{ $device['code'] }}</td>
                            <td>
                                {{ $device['control_point']['name'] ?? '' }}
                            </td>
                            <td>{{ $device['floorplan']['name'] }}</td>
                            <td>{{ $device['application_area']['name'] }}</td>
                            <td id="device{{ $device['id'] }}-review-pests">
                                <ul id="table-row-pest{{ $device['id'] }}-list">
                                    @foreach ($device['pests'] as $pest)
                                        <li class="product-item">
                                            <span class="fw-bold">{{ $pest['name'] }}</span>
                                            (<span class="product-quantity">{{ $pest['quantity'] }})
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td id="device{{ $device['id'] }}-review-products">
                                <ul id="table-row-product{{ $device['id'] }}-list">
                                    @foreach ($device['products'] as $product)
                                        <li class="product-item">
                                            <span class="fw-bold">{{ $product['name'] }}</span>
                                            (<span class="product-quantity">{{ $product['quantity'] }}
                                                {{ extractParenthesesContent($product['metric']) }}</span>)
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                <span id="device{{ $device['id'] }}-is_checked"
                                    class="{{ $device['states']['is_checked'] ? 'text-success' : 'text-danger' }} m-1"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                    data-bs-title="{{ $device['states']['is_checked'] ? 'Revisado' : 'No revisado' }}">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>

                                <span id="device{{ $device['id'] }}-is_scanned"
                                    class="{{ $device['states']['is_scanned'] ? 'text-success' : 'text-danger' }} m-1"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                    data-bs-title="{{ $device['states']['is_scanned'] ? 'Escaneado' : 'No escaneado' }}">
                                    <i class="bi bi-qr-code"></i>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-review-device{{ $device['id'] }}"
                                    data-device="{{ json_encode($device) }}"
                                    onclick="openReviewModal(this, {{ $service->id }})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif

                {{-- Dispositivos NO Revisados --}}
                @if ($notReviewedDevices->count() > 0)
                    <tr class="table-warning">
                        <td colspan="9" class="fw-bold text-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            DISPOSITIVOS PENDIENTES DE REVISIÓN ({{ $notReviewedDevices->count() }})
                        </td>
                    </tr>
                    @foreach ($notReviewedDevices as $device)
                        <tr>
                            <th scope="row">{{ $device['nplan'] }}</th>
                            <td class="fw-bold text-primary">{{ $device['code'] }}</td>
                            <td>
                                {{ $device['control_point']['name'] ?? '' }}
                            </td>
                            <td>{{ $device['floorplan']['name'] }}</td>
                            <td>{{ $device['application_area']['name'] }}</td>
                            <td id="device{{ $device['id'] }}-review-pests">
                                <ul id="table-row-pest{{ $device['id'] }}-list">
                                    @foreach ($device['pests'] as $pest)
                                        <li class="product-item">
                                            <span class="fw-bold">{{ $pest['name'] }}</span>
                                            (<span class="product-quantity">{{ $pest['quantity'] }})
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td id="device{{ $device['id'] }}-review-products">
                                <ul id="table-row-product{{ $device['id'] }}-list">
                                    @foreach ($device['products'] as $product)
                                        <li class="product-item">
                                            <span class="fw-bold">{{ $product['name'] }}</span>
                                            (<span class="product-quantity">{{ $product['quantity'] }}
                                                {{ extractParenthesesContent($product['metric']) }}</span>)
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                <span id="device{{ $device['id'] }}-is_checked"
                                    class="{{ $device['states']['is_checked'] ? 'text-success' : 'text-danger' }} m-1"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                    data-bs-title="{{ $device['states']['is_checked'] ? 'Revisado' : 'No revisado' }}">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>

                                <span id="device{{ $device['id'] }}-is_scanned"
                                    class="{{ $device['states']['is_scanned'] ? 'text-success' : 'text-danger' }} m-1"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                    data-bs-title="{{ $device['states']['is_scanned'] ? 'Escaneado' : 'No escaneado' }}">
                                    <i class="bi bi-qr-code"></i>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-review-device{{ $device['id'] }}"
                                    data-device="{{ json_encode($device) }}"
                                    onclick="openReviewModal(this, {{ $service->id }})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @else
                <tr id="table-row-empty">
                    <td class="text-danger" colspan="9">
                        No se encuentran dispositivos asociados a este servicio.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
@endforeach


<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
</script>

<script>
    var copy_devices = @json($devices);
    console.log('Dispositivos cargados:', copy_devices);
</script>
