<form id="order-form" class="m-3" method="POST" action="{{ route('order.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="url_customer" id="url-customer" value="{{ route('order.search.customer') }}">
    <input type="hidden" name="previous_url" id="previous-url" value="{{ $prevUrl }}">
    <input type="hidden" name="url_service_filter" id="url-service-filter"
        value="{{ route('order.search.service', ['type' => 0]) }}">
    <input type="hidden" name="url_service_input" id="url-service-input"
        value="{{ route('order.search.service', ['type' => 1]) }}">

    <div class="border rounded shadow p-3 mb-3">
        <div class="row">
            <div class="fw-bold mb-2 fs-5">Datos del servicio</div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="start_time" class="form-label is-required">{{ __('order.data.start_time') }}</label>
                <input type="time" class="form-control" id="start-time" name="start_time" placeholder="00:00"
                    required>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="end_time" class="form-label">{{ __('order.data.end_time') }}</label>
                <input type="time" class="form-control" id="end-time" name="end_time" placeholder="00:00">
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <label for="request_date" class="form-label is-required">{{ __('order.data.programmed_date') }}</label>
                <input type="date" class="form-control" id="programmed-date" name="programmed_date" required>
            </div>

            <div class="col-lg-2 col-6 mb-3">
                <label for="cost" class="form-label">{{ __('order.data.cost') }}</label>
                <div class="input-group mb-0">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="cost" name="cost" min="0"
                        placeholder="0" readonly disabled />
                </div>
            </div>
            <div class="col-lg-2 col-6 mb-3">
                <label for="price" class="form-label is-required">Precio de venta</label>
                <div class="input-group border border-success rounded mb-0">
                    <span class="input-group-text bg-success border-success text-white">$</span>
                    <input type="number" class="form-control" id="price" name="price" min="0"
                        placeholder="0" />
                </div>
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3 mb-3">
        <div class="fw-bold mb-0 fs-5">Cliente</div>
        <p class="text-danger mb-2">* Si no se encuentra, será necesario <a
                href="{{ route('customer.create') }}">registrarlo</a></p>
        <div class="input-group mb-3">
            <input type="text" class="form-control" name="customer_name" id="customer-name"
                placeholder="Buscar por nombre de la sede">
            <input type="text" class="form-control" name="customer_phone" id="customer-phone"
                placeholder="Buscar por telefono: 444-887-4810">
            <input type="text" class="form-control" name="customer_address" id="customer-address"
                placeholder="Buscar por direccion: Example #00, Col. Example">
            <button class="btn btn-primary btn-sm" type="button" onclick="searchCustomer()">
                {{ __('buttons.search') }}</button>
        </div>
        @include('order.modals.customers')
        <div id="selected-customer-container" class="row mt-4">
            <div class="col-12">
                <div class="alert alert-danger">
                    No hay cliente seleccionado
                </div>
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

    {{-- <div class="row" id="select-contract">
        <h5 class="border-bottom pb-1 fw-bold">
            {{ __('contract.title.associate_contract') }}:
        </h5>
        <div class="form-text text-danger m-0 mb-1" id="basic-addon4">
            * Si deseas vincular las órdenes a un contrato que ya ha sido creado, selecciónalo despues de buscar al
            cliente.
        </div>
        <div class="col-3 mb-3">
            <select class="form-select" id="contract" name="contract_id">
                <option value="" selected>Sin contrato</option>
            </select>
        </div>
    </div> --}}


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
                                        onchange="setTechnician(this)" />
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
                <label class="mb-2">{{ __('order.data.execution') }}</label>
                <textarea class="form-control" id="execution" name="execution"
                    placeholder="Describe cómo se llevó a cabo el servicio o procedimiento." style="height: 200px"></textarea>
            </div>

            <div class="col-4 mb-3">
                <label class="mb-2">{{ __('order.data.areas') }}</label>
                <textarea class="form-control" id="areas" name="areas"
                    placeholder="Indica las áreas o zonas donde se realizó el trabajo." style="height: 200px"></textarea>
            </div>

            <div class="col-4 mb-3">
                <label class="mb-2">{{ __('order.data.comments') }}</label>
                <textarea class="form-control" id="additional_comments" name="additional_comments"
                    placeholder="Agrega observaciones relevantes o detalles adicionales." style="height: 200px"></textarea>
            </div>
        </div>

    </div>

    <input type="hidden" id="customer-id" name="customer_id" value="">
    <input type="hidden" id="services" name="services" value="">
    <input type="hidden" name="technicians" id="technicians" value="">

    <button type="button" class="btn btn-primary my-3" onclick="generateOrder()">{{ __('buttons.store') }}
    </button>
</form>

<script>
    var contracts = @json($contracts);
    const new_client_account = false;

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
