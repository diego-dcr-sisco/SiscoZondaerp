<form action="{{ route('pest.search') }}" method="GET">
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
                        <span class="input-group-text"><i class="bi bi-bug-fill"></i></span>
                        <input type="text" class="form-control form-control-sm" name="name"
                            value="{{ request('name') }}" placeholder="Buscar por nombre..." />
                    </div>
                </div>

                {{-- Usuario --}}
                <div class="col-lg-2 col-sm-6 col-12">
                    <label for="code" class="form-label">Código</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-upc"></i></span>
                        <input type="text" class="form-control form-control-sm" name="code"
                            value="{{ request('code') }}" placeholder="Código..." />
                    </div>
                </div>

                {{-- Correo --}}
                <div class="col-lg-2 col-sm-6 col-12">
                    <label for="category_id" class="form-label">Categoría</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-collection-fill"></i></span>
                        <select class="form-select form-select-sm" name="category_id">
                            <option value="">Todos</option>
                            @foreach ($pest_categories as $pc)
                                <option value="{{ $pc->id }}"
                                    {{ request('category_id') == $pc->id ? 'selected' : '' }}>
                                    {{ $pc->category }}
                                </option>
                            @endforeach
                        </select>
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



<caption class="border rounded-top p-2 text-dark bg-white caption-top">
    <form action="{{ route('pest.search') }}" method="GET">
        @csrf
        <div class="row g-3 mb-0">
            <div class="col-lg-2">

            </div>

            <div class="col-lg-2">

            </div>

            <div class="col-lg-3">

            </div>

            <div class="col-lg-2">
                <label class="form-label">Ordenar / Mostrar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                    <select class="form-select form-select-sm" id="direction" name="direction">
                        <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                        </option>
                        <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                        </option>
                    </select>
                    <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                    <select class="form-select form-select-sm" id="size" name="size">
                        <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row justify-content-end g-3 mb-0 mt-0">
            <div class="col-lg-1 col-6">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel-fill"></i> Filtrar
                </button>
            </div>
            <div class="col-lg-1 col-6">
                <a href="{{ route('pest.index') }}" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</caption>
