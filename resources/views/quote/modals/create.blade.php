<!-- Button trigger modal -->


<!-- Modal -->
<div class="modal fade" id="quoteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="quoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <form action="{{ route('customer.quote.store') }}" method="POST" id="quote-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="quoteModalLabel">Cotización</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="service" class="form-label">Servicio</label>
                            <select class="form-select" id="service" name="service_id">
                                <option value="">Sin servicio</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-4 mb-3">
                            <label for="start-date" class="form-label is-required">Fecha de inicio</label>
                            <input type="date" class="form-control" name="start_date" required/>
                        </div>

                        <div class="col-4 mb-3">
                            <label for="end-date" class="form-label is-required">Fecha estimada de fin</label>
                            <input type="date" class="form-control" name="end_date" required/>
                        </div>

                        <div class="col-4 mb-3">
                            <label for="valid-until" class="form-label is-required">Valido hasta</label>
                            <input type="date" class="form-control" name="valid_until" required/>
                        </div>

                        <div class="col-4 mb-3">
                            <label for="value" class="form-label is-required">Valor de la cotización</label>
                            <input type="number" class="form-control" name="value" min="0" step="0.01"
                                value="0.00" placeholder="0.00" required/>
                        </div>

                        <div class="col-4 mb-3">
                            <label for="end-date" class="form-label is-required">Prioridad</label>
                            <select class="form-select" id="priority" name="priority" required>
                                @foreach ($quote_priority as $priority)
                                    <option value="{{ $priority->value }}">
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-4 mb-3">
                            <label for="end-date" class="form-label is-required">Estado</label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach ($quote_status as $status)
                                    <option value="{{ $status->value }}">
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="value" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="value" class="form-label">Archivo</label>
                            <input type="file" class="form-control"  id="file" name="file" accept=".pdf">
                        </div>
                    </div>

                    <input type="hidden" id="model-id" name="model_id" value="{{ $customer['id'] }}" />
                    <input type="hidden" id="model-type" name="model_type" value="{{ $customer['type'] }}" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="add-quote-btn" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
