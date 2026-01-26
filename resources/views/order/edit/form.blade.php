@php
    $folio_split = $order->folio ? explode('-', $order->folio) : [0, 0];
@endphp



<form id="order-form" class="form m-3" method="POST" action="{{ route('order.update', ['id' => $order->id]) }}"
    enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="url_customer" id="url-customer" value="{{ route('order.search.customer') }}">
    <input type="hidden" name="url_service_filter" id="url-service-filter"
        value="{{ route('order.search.service', ['type' => 0]) }}">
    <input type="hidden" name="url_service_input" id="url-service-input"
        value="{{ route('order.search.service', ['type' => 1]) }}">

    <div class="border rounded shadow p-3 mb-3">
        <div class="row">
            <div class="fw-bold mb-2 fs-5">Datos del servicio</div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="start_time" class="form-label is-required">{{ __('order.data.start_time') }}:</label>
                <input type="time" class="form-control" id="start_time" name="start_time"
                    value="{{ $order->start_time }}" onchange="validate_time()" required>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="end_time" class="form-label">{{ __('order.data.end_time') }}:</label>
                <input type="time" class="form-control" id="end_time" name="end_time"
                    value="{{ $order->end_time }}">
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="request_date" class="form-label is-required">{{ __('order.data.programmed_date') }}:</label>
                <input type="date" class="form-control" id="programmed_date" name="programmed_date"
                    value="{{ $order->programmed_date }}" required>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="request_date" class="form-label">Fecha de finalización:</label>
                <input type="date" class="form-control" id="completed_date" name="completed_date"
                    value="{{ $order->completed_date }}">
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="request_date" class="form-label is-required">{{ __('order.data.status') }}:</label>
                <select class="form-select " id="status" name="status_id">
                    @foreach ($order_status as $status)
                        <option value="{{ $status->id }}" {{ $status->id == $order->status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-6 mb-3">
                <label for="folio" class="form-label">Folio</label>
                <input type="text" class="form-control" id="folio" name="folio_number"
                    value="# {{ $order->folio }}" disabled />
            </div>

            <div class="col-lg-2 col-6 mb-3">
                <label for="cost" class="form-label">{{ __('order.data.cost') }}:</label>
                <div class="input-group mb-0">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="cost" name="cost" min="0"
                        placeholder="0" value="{{ $cost }}" readonly />
                </div>
            </div>
            <div class="col-lg-2 col-6 mb-3">
                <label for="price" class="form-label is-required"> {{ __('order.data.price') }}: </label>
                <div class="input-group border border-success rounded mb-0">
                    <span class="input-group-text bg-success border-success text-white">$</span>
                    <input type="number" class="form-control" id="price" name="price" min="0"
                        placeholder="0.0" value="{{ $order->price }}" />
                </div>
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3 mb-3">
        <div class="fw-bold mb-0 fs-5">Cliente</div>
        <div id="selected-customer-container" class="row mt-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-success">
                        <tr>
                            <th colspan="2" class="text-center">
                                Cliente Seleccionado
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold" style="width: 30%;">Nombre:</td>
                            <td class="text-primary fw-bold">{{ $order->customer->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Código:</td>
                            <td>
                                {{ $order->customer->code }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">ID:</td>
                            <td>
                                {{ $order->customer->id }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Dirección:</td>
                            <td class="text-muted">{{ $order->customer->address }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tipo:</td>
                            <td>{{ $order->customer->serviceType->name }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

<div class="border rounded shadow p-3 mb-3">
        <div class="row">
            <div class="fw-bold mb-0 fs-5">Servicios</div>
            <div class="form-text text-danger m-0" id="basic-addon4">
                * Selecciona al menos 1 servicio.
            </div>
            <div class="form-text text-danger m-0" id="basic-addon4">
                * En caso de que no aparezca deberas crearlo.
            </div>
            <div class="form-text text-danger m-0" id="basic-addon4">
                * Para añadir días, solo se permite la primera letra en
                mayúscula: (L) Lunes, (M) Martes, (Mi) Miércoles, (J) Jueves,
                (V) Viernes, (S) Sábado, (D) Domingo.
            </div>
            <div class="col-12 p-0 m-0 mb-1">
                <a href="{{ route('service.create') }}" id="form_service_button" class="btn btn-link" target="_blank">
                    {{ __('service.button.create') }}
                </a>
            </div>

            <div class="col-12">
                <h6 class="pb-1 mb-1 fw-bold">
                    {{ __('contract.title.find_service') }}
                </h6>
                <div class="input-group mb-3">
                    <input type="search" class="form-control" id="search-service-input" name="search_service_input"
                        placeholder="Nombre del servicio" />
                    <button class="btn btn-primary btn-sm" type="button" id="btn-search-service"
                        onclick="searchService()">
                        <i class="bi bi-search"></i> {{ __('buttons.search') }}
                    </button>
                </div>
            </div>

            <!-- Contenedor para servicios seleccionados -->
            <div id="selected-services-container" class="mt-3">
                <div class="alert alert-danger">No hay servicios seleccionados</div>
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3 mb-3">
        <div class="row">
            <div class="fw-bold mb-0 fs-5">Técnico(s)</div>
            <p class="text-danger mb-2">* Si no lo encuentras, puedes <a
                    href="{{ route('user.create') }}">añadirlo</a>
            </p>
            <div class="col-12 mb-3">
                <div class="form-check mb-3">
                    <input class="form-check-input me-1" type="checkbox" id="technician-0"
                        onchange="setAllTechnicians(this)">
                    <label class="form-check-label fw-bold" for="technician-0">
                        Todos los técnicos
                    </label>
                </div>

                <!-- Grid de Bootstrap con alineación corregida -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                    @foreach ($technicians as $technician)
                        <div class="col">
                            <div class="border rounded p-2 h-100 d-flex align-items-center">
                                <div class="form-check mb-0 w-100"> <!-- w-100 para que ocupe todo el ancho -->
                                    <input class="form-check-input me-2 technician" type="checkbox"
                                        value="{{ $technician->id }}" id="technician-{{ $technician->id }}"
                                        onchange="setTechnician(this)"
                                        {{ $order->closed_by == $technician->user_id || $order->hasTechnician($technician->id) ? 'checked' : '' }} />
                                    <label class="form-check-label d-block" for="technician-{{ $technician->id }}">
                                        {{ $technician->user->name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3">
        <div class="row">
            <div class="fw-bold mb-0 fs-5">Información para la ejecución</div>
            <div class="col-4 mb-3">
                <label class="mb-2">{{ __('order.data.execution') }} </label>
                <div class="border border-end-0 p-2 bg-secondary-subtle">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="replicate-execution"
                            name="replicate_execution">
                        <label class="form-check-label" for="replicate-execution">
                            Replicar <b>ejecución</b> a las ordenes del cliente con servicio(s) similares.
                        </label>
                    </div>
                </div>
                <textarea class="form-control border-top-0 rounded-0" id="execution" name="execution"
                    placeholder="Describe cómo se llevó a cabo el servicio o procedimiento." style="height: 250px;">{{ $order->execution }}</textarea>

            </div>
            <div class="col-4 mb-3">
                <label class="mb-2">{{ __('order.data.areas') }} </label>
                <div class="border border-end-0 p-2 bg-secondary-subtle">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="replicate-areas"
                            name="replicate_areas">
                        <label class="form-check-label" for="replicate-areas">
                            Replicar <b>áreas</b> a las ordenes del cliente con servicio(s) similares.
                        </label>
                    </div>
                </div>
                <textarea class="form-control border-top-0 rounded-0" id="areas" name="areas"
                    placeholder="Indica las áreas o zonas donde se realizó el trabajo." style="height: 250px;">{{ $order->areas ?? implode(', ', $order->customer->applicationAreas->pluck('name')->toArray()) }}</textarea>
            </div>
            <div class="col-4">
                <label class="mb-2">{{ __('order.data.comments') }} </label>
                <div class="border border-end-0 p-2 bg-secondary-subtle">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="replicate-comments"
                            name="replicate_comments">
                        <label class="form-check-label" for="replicate-comments">
                            Replicar <b>comentarios</b> a las ordenes del cliente con servicio(s) similares.
                        </label>
                    </div>
                </div>
                <textarea class="form-control border-top-0 rounded-0" id="additional_comments" name="additional_comments"
                    placeholder="Agrega observaciones relevantes o detalles adicionales." style="height: 250px;">{{ $order->additional_comments }}</textarea>
            </div>
        </div>
    </div>

    <input type="hidden" name="technicians" id="technicians"
        value="{{ json_encode($order->technicians->pluck('id')->toArray()) ?? [] }}">
    <input type="hidden" id="services" name="services" value="">
    <input type="hidden" id="customer-id" name="customer_id" value="{{ $order->customer_id }}">


    <button type="button" class="btn btn-primary my-3 me-3"
        onclick="generateOrder()">{{ __('buttons.update') }}</button>
</form>
