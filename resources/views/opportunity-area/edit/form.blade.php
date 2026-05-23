<style>
    .card-img-top {
        height: 12rem;
        /* Fija la altura de la imagen */
        object-fit: cover;
        /* Asegura que la imagen cubra el espacio sin distorsionarse */
    }

    .card {
        height: 20rem;
        /* Fija la altura de la card */
    }

    .card-body {
        flex-grow: 1;
        /* Hace que el cuerpo de la tarjeta ocupe el espacio restante */
    }
</style>

<form method="POST" action="{{ route('opportunity-area.update', ['id' => $opportunity_area->id]) }}"
    enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-4 mb-3">
            <label class="form-label is-required">Responsable </label>
            <input type="text" class="form-control bg-secondary-subtle" id="customer" name="customer_name"
                value="{{ $opportunity_area->customer->name }}" readonly />
            <input type="hidden" name="customer_id" value="{{ $opportunity_area->customer->id }}" required />
        </div>
        <div class="col-3 mb-3">
            <label class="form-label is-required">Área </label>
            <select class="form-select" id="application-area" name="application_area_id">
                @foreach ($opportunity_area->customer->applicationAreas as $area)
                    <option value="{{ $area->id }}"
                        {{ $opportunity_area->application_area_id == $area->id ? 'selected' : '' }}>{{ $area->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto mb-3">
            <label class="form-label is-required">Estado </label>
            <select class="form-select" id="status" name="status"  required>
                @foreach ($status_options as $key => $value)
                    <option value="{{ $key }}" {{ $opportunity_area->status == $key ? 'selected' : ''}}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto mb-3">
            <label class="form-label is-required">Seguimiento </label>
            <select class="form-select" id="tracing" name="tracing" required>
                @foreach ($tracing_options as $key => $value)
                    <option value="{{ $key }}" {{ $opportunity_area->tracing == $key ? 'selected' : ''}}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-auto mb-3">
            <label class="form-label is-required">Fecha </label>
            <input type="date" class="form-control" id="date" name="date"
                value="{{ $opportunity_area->date }}" required />
        </div>
        <div class="col-auto mb-3">
            <label class="form-label is-required">Fecha estimada </label>
            <input type="date" class="form-control" id="estimated-date" name="estimated_date"
                value="{{ $opportunity_area->estimated_date }}" required />
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label is-required">Áreas de oportunidad </label>
            <textarea class="form-control" id="opportunity-area" name="opportunity" rows="5" required>{{ $opportunity_area->opportunity }}</textarea>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label is-required">Recomendación </label>
            <textarea class="form-control" id="recommendation" name="recommendation" rows="5" required>{{ $opportunity_area->recommendation }}</textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <label class="form-label is-required" for="img_incidente">Incidencia</label>
            <div class="card">
                <img src="{{ route('opportunity-area.image.show', ['id' => $opportunity_area->id, 'type' => 1]) }}" class="card-img-top" alt="Imagen Incidencia">
                <div class="card-body">
                    <span>Modificar la imagen:</span>
                    <input type="file" class="form-control" id="img_incidente" name="img_incidente">
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <label class="form-label" for="img_conclusion">Evidencia de conclusión</label>
            <div class="card">
                <img src="{{ route('opportunity-area.image.show', ['id' => $opportunity_area->id, 'type' => 2]) }}"
                    class="card-img-top" alt="Imagen Conclusión">
                <div class="card-body">
                    <span>Modificar la imagen:</span>
                    <input type="file" class="form-control" id="img_conclusion" name="img_conclusion">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary my-3">{{ __('buttons.update') }}</button>
</form>
