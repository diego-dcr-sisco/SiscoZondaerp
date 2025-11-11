@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center border-bottom p-2">
            <span class="text-black fw-bold fs-4">
                PRODUCTOS
            </span>
        </div>
        <div class="py-3">
            @can('write_product')
                <a class="btn btn-primary btn-sm" href="{{ route('product.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear producto 
                </a>
            @endcan
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <caption class="border rounded-top p-2 text-dark bg-light caption-top">
                    <form action="{{ route('product.index') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-3 col-12">
                                <label for="version" class="form-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm" name="name" value="{{ request('name') }}"/>
                            </div>

                            <div class="col-lg-2 col-12">
                                <label for="version" class="form-label">Ingrediente activo</label>
                                <input type="text" class="form-control form-control-sm" name="active_ingredient" value="{{ request('active_ingredient') }}"/>
                            </div>

                            <div class="col-lg-2 col-12">
                                <label for="control-point" class="form-label">Fabricante/distribuidor</label>
                                <input type="text" class="form-control form-control-sm" name="business_name" value="{{ request('business_name') }}"/>
                            </div>

                            <div class="col-auto">
                                <label for="app_area" class="form-label">Presentación</label>
                                <select class="form-select form-select-sm" name="presentation_id">
                                    <option value="">Todas las presentaciones</option>
                                    @foreach ($presentations as $presentation)
                                        <option value="{{ $presentation->id }}" {{ request('presentation_id') == $presentation->id ? 'selected' : '' }}>{{ $presentation->name }}</option>
                                    @endforeach                                    
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="signature_status" class="form-label">Dirección</label>
                                <select class="form-select form-select-sm" id="direction" name="direction">
                                    <option value="ASC" {{ request('direction') == 'ASC' ? 'selected' : '' }}>ASC
                                    </option>
                                    <option value="DESC" {{ request('direction') == 'DESC' ? 'selected' : '' }}>DESC
                                    </option>
                                </select>
                            </div>

                            <div class="col-auto">
                                <label for="order_type" class="form-label">Total</label>
                                <select class="form-select form-select-sm" id="size" name="size">
                                    <option value="25" {{ request('size') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('size') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('size') == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ request('size') == 200 ? 'selected' : '' }}>200</option>
                                    <option value="500" {{ request('size') == 500 ? 'selected' : '' }}>500</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="col-lg-12 d-flex justify-content-end m-0">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-funnel-fill"></i> Filtrar
                                </button>
                                <a href="{{ route('product.index') }}" class="btn btn-secondary btn-sm">
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
                            <td class="text-center fw-bold" scope="row">{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                            <td class="text-center">
                                @if ($product->image_path)
                                    <img src="{{ route('image.show', ['path' => $product->image_path]) }}"
                                         class="rounded shadow-sm border"
                                         style="width: 48px; height: 48px; object-fit: cover;"
                                         alt="Imagen producto">
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
                            <td class="d-flex flex-column text-primary fw-bold h-100 align-items-start justify-content-center">
                                {{ $product->presentation->name ?? '-' }}
                                <span class="text-muted" style="font-size: 11px;">{{ $product->metric->value ?? '-' }}</span>
                            </td>
                            <td>{{ $product->manufacturer ?? '-' }}</td>
                            <!-- <td>{{ $product->lineBusiness->name ?? '-' }}</td> -->
                            <td>{{ $product->register_number ?? '-' }}</td>
                            <td>{{ $product->active_ingredient ?? '-' }}</td>
                            <td>{{ $product->dosage ?? '-' }}</td>
                            <!-- <td>{{ $product->metric->value ?? '-' }}</td> -->
                            <td class="text-center">
                                @can('write_product')
                                    <div class="d-flex justify-content-center g-2" role="group" aria-label="Acciones">
                                        <a href="{{ route('product.edit', ['id' => $product->id, 'section' => 1]) }}"
                                            class="btn btn-secondary btn-sm me-1"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Editar producto">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @if (auth()->user()->work_department_id == 1)
                                            <a href="{{ route('product.destroy', ['id' => $product->id]) }}"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="Eliminar producto">
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
