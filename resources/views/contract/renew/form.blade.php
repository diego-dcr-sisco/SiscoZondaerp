<style>
    .modal-blur {
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.3);
    }
</style>

<form id="contract-form" class="form m-3" method="POST" action="{{ route('contract.store') }}"
    enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="url_customer" id="url-customer" value="{{ route('order.search.customer') }}" />
    <input type="hidden" name="url_service_filter" id="url-service-filter"
        value="{{ route('order.search.service', ['type' => 0]) }}" />
    <input type="hidden" name="url_service_input" id="url-service-input"
        value="{{ route('order.search.service', ['type' => 1]) }}" />
    <input type="hidden" name="url_selected_service" id="url-selected-service"
        value="{{ route('ajax.contract.service') }}" />

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
                            <td class="text-primary fw-bold">{{ $contract->customer->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Código:</td>
                            <td>
                                {{ $contract->customer->code }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">ID:</td>
                            <td>
                                {{ $contract->customer->id }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Dirección:</td>
                            <td class="text-muted">{{ $contract->customer->address }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tipo:</td>
                            <td>{{ $contract->customer->serviceType->name }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="border rounded shadow p-3 mb-3">
        <div class="row" id="duration">
            <div class="fw-bold mb-0 fs-5">Duración</div>
            <div class="col-3 mb-3">
                <label for="client" class="form-label is-required">
                    {{ __('contract.data.start_date') }}
                </label>
                <input type="date" class="form-control" name="startdate" id="startdate" value="{{ $new_dates[0] }}" oninput="set_endDate()"
                    required />
            </div>

            <div class="col-3 mb-3">
                <label for="client" class="form-label">
                    {{ __('contract.data.end_date') }}
                </label>
                <input type="date" class="form-control" name="enddate" id="enddate" value="{{ $new_dates[1] }}"/>
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
            <div class="fw-bold mb-0 fs-5">Tecnico(s)</div>
            <p class="text-danger mb-2">* Si no lo encuentras, puedes <a href="{{ route('user.create') }}">añadirlo</a>
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
                                        onchange="setTechnician(this)"  {{ $contract->hasTechnician($technician->id) ? 'checked' : '' }}/>
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

    <button type="button" class="btn btn-primary my-3" onclick="submitContract()">
        {{ __('buttons.update') }}
    </button>
    <input type="hidden" id="customer-id" name="customer_id" value="{{ $contract->customer_id }}" />
    <input type="hidden" id="contract-configurations" name="configurations" value="{{ json_encode($configurations) }}" />
    <input type="hidden" name="technicians" id="technicians" value="{{ json_encode($contract->technicians->pluck('id')) }}" />
</form>
