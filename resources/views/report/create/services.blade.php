@php
    $answer = null;
    $pests_data = [];
@endphp

<div class="accordion" id="servicesAccordion">
    @foreach ($order->services as $service)
        <div class="accordion-item mb-2">
            <h2 class="accordion-header" id="service-heading-{{ $service->id }}">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                    data-bs-toggle="collapse" data-bs-target="#service-collapse-{{ $service->id }}"
                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    aria-controls="service-collapse-{{ $service->id }}">
                    {{ $service->name }}
                </button>
            </h2>
            <div id="service-collapse-{{ $service->id }}"
                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                aria-labelledby="service-heading-{{ $service->id }}" data-bs-parent="#servicesAccordion">
                <div class="accordion-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input border-dark" type="checkbox" value="1"
                                id="service{{ $service->id }}-can-propagate" {{ $order->contract_id ? '' : 'disabled' }}>
                            <label class="form-check-label" for="service{{ $service->id }}-can-propagate">
                                Replicar a todas las órdenes incluidas en el contrato (si corresponde a MIP).
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div id="service{{ $service->id }}-text" class="smnote smnote-service"
                            data-autosave-type="service" data-service-id="{{ $service->id }}" style="height: 300px">
                            @if ($order->propagateByService($service->id) && $order->propagateByService($service->id)->text)
                                {!! cleanHtmlSimple($order->propagateByService($service->id)->text) !!}
                            @elseif ($order->setting && $order->setting->service_description)
                                {!! cleanHtmlSimple($order->setting->service_description) !!}
                            @elseif ($service->description)
                                {!! cleanHtmlSimple($service->description) !!}
                            @endif
                        </div>
                    </div>

                    <div class="section-action-bar">
                        <span id="autosave-status-service-{{ $service->id }}" class="autosave-status">Sin cambios</span>
                        <div class="section-action-buttons">
                            <button type="button" class="btn btn-primary btn-sm report-save-btn"
                                onclick="updateDescription({{ $service->id }})">
                                <i class="bi bi-save"></i> Guardar descripción
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
