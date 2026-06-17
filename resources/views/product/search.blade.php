<form action="{{ route('product.index') }}" method="GET">
    @csrf
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <h5 class="card-title fw-bold mb-0"><i class="bi bi-funnel-fill"></i> Busqueda Avanzada</h5>
                <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target=".multi-collapse" aria-expanded="true"
                    aria-controls="multiCollapseExample1 multiCollapseExample2">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
            </div>
        </div>
        <div class="card-body collapse show multi-collapse">
            <div class="row g-3 mb-3">
                <div class="col-lg-2 col-sm-6 col-12">
                    <label for="name" class="form-label">Nombre</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                        <input type="text" class="form-control form-control-sm" name="name"
                            value="{{ request('name') }}" placeholder="Buscar por nombre..." />
                    </div>
                </div>

                {{-- Usuario --}}
                <div class="col-lg-2 col-sm-6 col-12">
                    <label for="active_ingredient" class="form-label">Ingrediente activo</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-capsule"></i></span>
                        <input type="text" class="form-control form-control-sm" name="active_ingredient"
                            value="{{ request('active_ingredient') }}" placeholder="Ingrediente..." />
                    </div>
                </div>

                {{-- Correo --}}
                <div class="col-lg-2 col-sm-6 col-12">
                    <label for="business_name" class="form-label">Fabricante/distribuidor</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input type="text" class="form-control form-control-sm" name="business_name"
                            value="{{ request('business_name') }}" placeholder="Empresa..." />
                    </div>
                </div>

                {{-- Rol --}}
                <div class="col-lg-2 col-sm-6">
                    <label for="presentation_id" class="form-label">Presentación</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                    <select class="form-select form-select-sm" name="presentation_id">
                        <option value="">Todas las presentaciones</option>
                        @foreach ($presentations as $presentation)
                            <option value="{{ $presentation->id }}"
                                {{ request('presentation_id') == $presentation->id ? 'selected' : '' }}>
                                {{ $presentation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                </div>

                <div class="col-lg-2 col-sm-6">
                    <label for="signature_status" class="form-label">Ordenar / Mostrar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-arrow-down-up"></i></span>
                        <select class="form-select form-select-sm" id="direction" name="direction">
                            <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>
                                DESC
                            </option>
                            <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>
                                ASC
                            </option>
                        </select>
                        <span class="input-group-text" id="basic-addon1"><i class="bi bi-list-ol"></i></span>
                        <select class="form-select form-select-sm" id="size" name="size">
                            <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25
                            </option>
                            <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50
                            </option>
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
                    <a href="{{ route('product.index') }}" class="btn btn-secondary btn-sm w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>