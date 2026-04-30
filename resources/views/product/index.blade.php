@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'PRODUCTOS',
        'icon' => 'bi-box-seam',
        'actionRoute' => route('product.create'),
        'actionText' => 'Crear producto',
    ])
    <div class="container-fluid">

        <div class="overflow-auto w-100">
            <table class="table table-bordered table-striped table-sm">
                <caption class="border rounded-top p-2 text-dark bg-white caption-top">
                    <form action="{{ route('product.index') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-2">
                                <label for="name" class="form-label">Nombre</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                                    <input type="text" class="form-control form-control-sm" name="name"
                                        value="{{ request('name') }}" placeholder="Buscar por nombre..." />
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="active_ingredient" class="form-label">Ingrediente activo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-capsule"></i></span>
                                    <input type="text" class="form-control form-control-sm" name="active_ingredient"
                                        value="{{ request('active_ingredient') }}" placeholder="Ingrediente..." />
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="business_name" class="form-label">Fabricante/distribuidor</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <input type="text" class="form-control form-control-sm" name="business_name"
                                        value="{{ request('business_name') }}" placeholder="Empresa..." />
                                </div>
                            </div>

                            <div class="col-lg-2">
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

                            <div class="col-lg-2">
                                <label for="signature_status" class="form-label">Ordenar / Mostrar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="bi bi-arrow-down-up"></i></span>
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
                        <!-- Botones -->
                        <div class="row justify-content-end g-3 mb-0 mt-0">
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
                    </form>
                </caption>
                <thead class="table-light align-middle">
                    <tr>
                        <th scope="col" class="text-center">#</th>
                        <th scope="col" class="text-center">Imagen</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Presentación</th>
                        <th scope="col">Distribuidor</th>
                        <!-- <th scope="col">Línea de Negocio</th> -->
                        <th scope="col">No Registro</th>
                        <th scope="col">Ingrediente Activo</th>
                        <th scope="col">Dosificación</th>
                        <!-- <th scope="col">Métrica</th> -->
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    @forelse ($products as $index => $product)
                        <tr class="table-row-hover">
                            <td class="text-center fw-bold" scope="row">
                                {{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                            <td>
                                @if ($product->image_path)
                                    <img src="{{ route('image.show', ['path' => $product->image_path]) }}"
                                        class="rounded shadow-sm border"
                                        style="width: 48px; height: 48px; object-fit: cover;" alt="Imagen producto">
                                @else
                                    <span class="text-secondary-50">
                                        <i class="bi bi-image fs-3"></i>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $product->name }}</span>
                                @if (!empty($product->is_obsolete) && $product->is_obsolete)
                                    <span class="badge bg-danger ms-1">Obsoleto</span>
                                @endif
                            </td>
                            <td
                                class="d-flex flex-column text-primary fw-bold h-100 align-items-start justify-content-center">
                                {{ $product->presentation->name ?? '-' }}
                                <span class="text-muted"
                                    style="font-size: 11px;">{{ $product->metric->value ?? '-' }}</span>
                            </td>
                            <td>{{ $product->manufacturer ?? '-' }}</td>
                            <!-- <td>{{ $product->lineBusiness->name ?? '-' }}</td> -->
                            <td>{{ $product->register_number ?? '-' }}</td>
                            <td>{{ $product->active_ingredient ?? '-' }}</td>
                            <td>{{ $product->dosage ?? '-' }}</td>
                            <!-- <td>{{ $product->metric->value ?? '-' }}</td> -->
                            <td>
                                @can('write_product')
                                    <div class="d-flex justify-content-center g-2" role="group" aria-label="Acciones">
                                        <a href="{{ route('product.edit', ['id' => $product->id, 'section' => 1]) }}"
                                            class="btn btn-secondary btn-sm me-1" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Editar producto">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @if (auth()->user()->work_department_id == 1)
                                            <a href="{{ route('product.destroy', ['id' => $product->id]) }}"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-danger fw-bold text-center" colspan="11">Sin productos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
