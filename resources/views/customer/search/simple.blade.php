<form action="{{ route('customer.search') }}" method="GET">
    @csrf
    <input type="hidden" id="customer-type" name="customer_type" value="1">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <h5 class="card-title fw-bold mb-0"><i class="bi bi-funnel-fill"></i> Busqueda Avanzada</h5>
                <button class="btn btn-outline-dark btn-sm p-collapse-button" type="button" data-bs-toggle="collapse"
                    data-bs-target=".multi-collapse" aria-expanded="true"
                    aria-controls="multiCollapseExample1 multiCollapseExample2">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
            </div>
        </div>
        <div class="card-body collapse show multi-collapse">
            <div class="row g-3 mb-3">
                {{-- Nombre --}}
                <div class="col-lg-3 col-sm-6">
                    <label for="name" class="form-label">Nombre</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ request('name') }}" placeholder="Buscar nombre">
                    </div>
                </div>
                {{-- Código --}}
                <div class="col-lg-3 col-sm-6">
                    <label for="code" class="form-label">Código</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-hash"></i></span>
                        <input type="text" class="form-control" id="code" name="code"
                            value="{{ request('code') }}" placeholder="Buscar código">
                    </div>
                </div>

                {{-- Tipo --}}
                <div class="col-lg-3 col-sm-6">
                    <label for="service_type" class="form-label">Tipo</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                        <select class="form-select" id="service_type" name="service_type">
                            <option value="">Todos</option>
                            @foreach ($service_types as $service_type)
                                <option value="{{ $service_type->id }}"
                                    {{ request('service_type') == $service_type->id ? 'selected' : '' }}>
                                    {{ $service_type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Recurrente --}}
                <div class="col-lg-3 col-sm-6">
                    <label for="recurrent" class="form-label">Recurrente</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-arrow-repeat"></i></span>
                        <select class="form-select" id="recurrent" name="recurrent">
                            <option value="">Todos</option>
                            <option value="1" {{ request('recurrent') === '1' ? 'selected' : '' }}>Si</option>
                            <option value="0" {{ request('recurrent') === '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>

                {{-- Categoría --}}
                <div class="col-lg-3 col-sm-6">
                    <label for="category" class="form-label">Categoría</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-grid-fill"></i></span>
                        <select class="form-select" id="category" name="category">
                            @foreach ($categories as $key => $category)
                                <option value="{{ $key }}"
                                    {{ request('category') == $key || $key == 1 ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <label for="signature_status" class="form-label">Ordenar / Mostrar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-arrow-down-up"></i></span>
                        <select class="form-select form-select-sm" id="direction" name="direction">
                            <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                DESC
                            </option>
                            <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                            </option>
                        </select>
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-list-ol"></i></span>
                        <select class="form-select form-select-sm" id="size" name="size">
                            <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100
                            </option>
                            <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200
                            </option>
                            <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer collapse show multi-collapse">
            <div class="row justify-content-end">
                <div class="col-lg-1 col-6">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                </div>
                <div class="col-lg-1 col-6">
                    <a href="{{ route('user.search') }}" class="btn btn-secondary btn-sm w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
