@extends('layouts.app')
@section('content')
    <div>
        <ul class="nav fs-4 border-bottom mb-3">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="javascript:history.back()"><i
                        class="bi bi-arrow-left"></i></a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled text-black fw-bold" aria-current="page" href="#"> CREAR SEGUIMIENTO</a>
            </li>
        </ul>
        <div class="container-fluid">
            <form class="px-3" action="{{ route('crm.tracking.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-auto mb-3">
                        <label for="customer" class="form-label">Cliente</label>
                        @if (isset($customer))
                            <input type="text" class="form-control" value="{{ $customer->name }}" disabled>
                            <input type="hidden" id="trackable-input-id" name="trackable_id" value="{{ $customer->id }}">
                            <input type="hidden" id="trackable-input-type" name="trackable_type" value="customer">
                        @else
                            <div class="input-group mb-3">
                                <select class="input-group-text" id="trackable-type" name="trackable_type" onchange="handleTrackable(this.value)">
                                    <option value="customer" selected>Cliente</option>
                                    <option value="lead">Lead (Potencial)</option>
                                </select>
                                <select class="form-select" id="select-customer" name="trackable_id">
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="col-4">
                        <label for="service" class="form-label">Servicio</label>
                        <select class="form-select" id="service" name="service_id" onchange="changeService(this.value)">
                            <option value="" selected>Selecciona un servicio</option>
                            @foreach ($services as $s)
                                <option value="{{ $s->id }}"
                                    {{ isset($service) && $s->id == $service->id ? 'selected' : '' }}>
                                    {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto mb-3">
                        <label for="technician" class="form-label">Ultimo servicio realizado</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="last-date"
                                value="{{ !isset($customer) ? now()->format('Y-m-d') : '' }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 mb-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="title" name="title"
                            placeholder="Título del seguimiento">
                    </div>
                    <div class="col-8 mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            placeholder="Descripción del seguimiento"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4 mb-3">
                        <label for="time" class="form-label">Frecuencia</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="frequency" name="frequency"
                                placeholder="Frecuencia" min="1" oninput="generateDate()">
                            <select class="form-select" id="frequency-type" name="frequency_type" onchange="generateDate()">
                                <option value="" selected>Sin opción</option>
                                <option value="days">Días</option>
                                <option value="weeks">Semanas</option>
                                <option value="months">Meses</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-3 mb-3">
                        <label for="next-date" class="form-label">Proxima fecha</label>
                        <input type="date" class="form-control" id="next-date" name="next_date">
                    </div>

                    <div class="col-3 mb-3">
                        <label for="tracking-type" class="form-label is-required">Tipo de seguimiento </label>
                        <select class="form-select" id="tracking-type" name="tracking_type" required>
                            <option value="">Selecciona una opción</option>
                            <option value="one">Seguimiento individual</option>
                            <option value="year">Seguimiento anual</option>
                        </select>
                    </div>
                </div>

                <div class="my-3">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const orders_data = @json($orders_data ?? []);
        var service = @json($service ?? null);
        var order = @json($order ?? null);
        var customers = @json($customers);
        var leads = @json($leads);

        $(document).ready(function() {
            if (service) {
                var service_id = service.id;
                if (orders_data[service_id] && orders_data[service_id].length > 0) {
                    var last_order = orders_data[service_id][0];
                    $('#last-date').val(new Date(last_order.date).toISOString().split('T')[0]);
                    $('#next-date').val(new Date(last_order.date).toISOString().split('T')[0]);
                }
            }

            if (order) {
                $('#last-date').val(new Date(order.programmed_date).toISOString().split('T')[0]);
                $('#next-date').val(new Date(order.programmed_date).toISOString().split('T')[0]);
            }

            if ($('#last-date').val() == '') {
                $('#last-date').val(new Date().toISOString().split('T')[0]);
            }
        });

        function changeService(new_service_id) {
            if (orders_data[new_service_id] && orders_data[new_service_id].length > 0) {
                var last_order = orders_data[new_service_id][0];
                var last_date = new Date(last_order.date).toISOString().split('T')[0];
                $('#last-date').val(last_date);
                $('#next-date').val(last_date);
            } else {
                $('#last-date').val(new Date().toISOString().split('T')[0]);
                $('#next-date').val(new Date().toISOString().split('T')[0]);
            }

            $('#frequency-type').val('');
            $('#frequency').val('');
        }

        function generateDate() {
            var frequency = parseInt($('#frequency').val());
            var frequency_type = $('#frequency-type').val();
            var last_date = $('#last-date').val();
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

        function handleTrackable(key) {
            let select = $('#select-customer');
            select.empty();

            if (key === 'customer') {
                customers.forEach(function(c) {
                    select.append($('<option>', {
                        value: c.id,
                        text: c.name
                    }));
                });
                select.attr('name', 'trackable_id');
            } else if (key === 'lead') {
                leads.forEach(function(l) {
                    select.append($('<option>', {
                        value: l.id,
                        text: l.name
                    }));
                });
                select.attr('name', 'trackable_id');
            }
        }
    </script>
@endsection
