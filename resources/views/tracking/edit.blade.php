@extends('layouts.app')
@section('content')
    @php
        $range = json_decode($tracking->range);
    @endphp
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{-- route('customer.quote', ['id' => $quote->model_id, 'class' => $class]) --}} javascript:history.back();" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                EDITAR SEGUIMIENTO
            </span>
        </div>
        <form class="p-3" action="{{ route('crm.tracking.update', ['id' => $tracking->id]) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-6">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="row">
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="customer" class="form-label">Cliente</label>
                                <input type="text" class="form-control" value="{{ $tracking->trackable->name }}"
                                    disabled>
                                <input type="hidden" name="trackable_id" value="{{ $tracking->trackable->id }}">
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="service" class="form-label">Servicio</label>
                                <input type="text" class="form-control" value="{{ $tracking->service->name }}" disabled>
                                <input type="hidden" name="service_id" value="{{ $tracking->service_id }}">
                            </div>
                            @php
                                $range = json_decode($tracking->range);
                                $r_type = $range->frequency_type ?? null;
                            @endphp
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="frequency" class="form-label">Frecuencia</label>
                                <input type="text" class="form-control" value="{{ $r_type }}" disabled>
                            </div>

                            <div class="col-lg-6 col-3 mb-3">
                                <label for="next-date" class="form-label is-required">Fecha del seguimiento</label>
                                <input type="date" class="form-control" id="next-date" name="next_date"
                                    value="{{ $tracking->next_date }}" required>
                            </div>
                            <div class="col-lg-6 col-12 mb-3">
                                <label for="status" class="form-label is-required">Estado</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" {{ $tracking->status == 'active' ? 'selected' : '' }}>Activo
                                    </option>
                                    <option value="completed" {{ $tracking->status == 'completed' ? 'selected' : '' }}>
                                        Completado
                                    </option>
                                    <option value="canceled" {{ $tracking->status == 'canceled' ? 'selected' : '' }}>
                                        Cancelado
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded shadow p-3 mb-3">
                        <div class="mb-3">
                            <label for="title" class="form-label">Título</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="{{ $tracking->title }}" placeholder="Título del seguimiento">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="5"
                                placeholder="Descripción del seguimiento">{{ $tracking->description }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-3">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    </div>

    <script>
        var last_date = "{{ $tracking->next_date }}";

        function generateDate() {
            var frequency = parseInt($('#frequency').val());
            var frequency_type = $('#frequency-type').val();
            var next_date = last_date != '' ? new Date(last_date) : new Date();

            if (frequency_type == '' && last_date != '') {
                $('#next-date').val(next_date.toISOString().split('T')[0]);
                return;
            }

            if (frequency && frequency_type) {
                if (frequency_type === 'days') {
                    next_date.setDate(next_date.getDate() + frequency);
                } else if (frequency_type === 'weeks') {
                    next_date.setDate(next_date.getDate() + (frequency * 7));
                } else if (frequency_type === 'months') {
                    next_date.setMonth(next_date.getMonth() + frequency);
                }
                $('#next-date').val(next_date.toISOString().split('T')[0]);
            }
        }
    </script>
@endsection
