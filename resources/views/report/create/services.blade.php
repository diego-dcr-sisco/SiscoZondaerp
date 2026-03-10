@php
    $answer = null;
    $pests_data = [];
@endphp

@foreach ($order->services as $service)
    <div class="row">
        <div class="col-12">
            <div class="border border-bottom-0 rounded-top-1 p-2 bg-secondary-subtle">
                <span class="fw-bold">Servicio - {{ $service->name }} </span>
            </div>
        </div>
        <div class="col-12">
            <div class="p-2 border border-bottom-0 border-top-0">
                <div class="form-check">
                    <input class="form-check-input border-dark" type="checkbox" value="1"
                        id="service{{ $service->id }}-can-propagate" {{ $order->contract_id ? '' : 'disabled' }}>
                    <label class="form-check-label" for="flexCheckDefault">
                        Replicar a todas las órdenes incluidas en el contrato (si corresponde a MIP).
                    </label>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="p-2 border border-top-0 rounded-bottom-1">
                <div id="service{{ $service->id }}-text" class="smnote" style="height: 300px">
                    @if ($order->propagateByService($service->id) && $order->propagateByService($service->id)->text)
                        {!! cleanHtmlSimple($order->propagateByService($service->id)->text) !!}
                    @elseif ($order->setting && $order->setting->service_description)
                        {!! cleanHtmlSimple($order->setting->service_description) !!}
                    @elseif ($service->description)
                        {!! cleanHtmlSimple($service->description) !!}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary btn-sm" onclick="updateDescription({{ $service->id }})">
        Actualizar descripción
    </button>
@endforeach
