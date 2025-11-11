<div class="row">
    @foreach ($order->services as $service)
        <h5 class="border-bottom p-0 pb-1 mb-3 fw-bold">Servicio - {{ $service->name }} </h5>
        <div class="col-12 mb-3">
            @if ($service->prefix != 2)
                <ul class="list-group list-group-flush">
                    @foreach ($recommendations as $recommendation)
                        <li class="list-group-item">
                            <div class="form-check">
                                <input class="form-check-input border-secondary recommendations" type="checkbox"
                                    value="{{ $recommendation->id }}"
                                    {{ $order->hasRecommendation($recommendation->id) ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ $recommendation->description }}
                                </label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div id="summary-recs{{ $service->id }}" class="smnote" style="height: 300px">
                    <p><strong>ANTES DE LA APLICACIÓN QUÍMICA</strong></p>
                    <ol>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Identificar la plaga
                            a controlar.</li>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>No debe encontrarse
                            personal en el área.</li>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>No debe de haber
                            materia prima expuesta.</li>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Asegurar que la
                            aplicación no afecte el proceso, producción o a terceros.</li>
                    </ol>
                    <p><br></p>
                    <p><strong>DURANTE DE LA APLICACIÓN QUÍMICA</strong></p>
                    <ol>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>En el área solo debe
                            de encontrarse el técnico aplicador</li>
                    </ol>
                    <p><br></p>
                    <p><strong>DESPUÉS DE LA APLICACIÓN QUÍMICA</strong></p>
                    <ol>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Respetar el tiempo de
                            reentrada conforme a la etiqueta del producto a utilizar.</li>
                        <li data-list="bullet"><span class="ql-ui" contenteditable="false"></span>Realizar recolección
                            de plaga o limpieza necesaria al tipo de área.</li>
                    </ol>
                </div>
            @endif
        </div>
    @endforeach

    <input type="hidden" id="recommendations" name="recommendations" />
</div>
