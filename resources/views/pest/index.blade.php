@extends('layouts.app')
@section('content')
    @php
        $offset = ($pests->currentPage() - 1) * $pests->perPage();
    @endphp

    <div class="container-fluid">
        <div class="py-3">
            @can('write_product')
                <a class="btn btn-primary btn-sm" href="{{ route('pest.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear plaga
                </a>
            @endcan
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <caption class="border rounded-top p-2 text-dark bg-light caption-top">
                    <form action="{{ route('pest.search') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-4 col-12">
                                <label for="version" class="form-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm" name="name"
                                    value="{{ request('name') }}" />
                            </div>

                            <div class="col-auto">
                                <label for="control-point" class="form-label">Código</label>
                                <input type="text" class="form-control form-control-sm" name="code"
                                    value="{{ request('code') }}" />
                            </div>

                            <div class="col-auto">
                                <label for="app_area" class="form-label">Categoría</label>
                                <select class="form-select form-select-sm" name="category_id">
                                    <option value="">Todos</option>
                                    @foreach ($pest_categories as $pc)
                                        <option value="{{ $pc->id }}"
                                            {{ request('category_id') == $pc->id ? 'selected' : '' }}>{{ $pc->category }}</option>
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
                                <a href="{{ route('pest.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">{{ __('pest.data.name') }} </th>
                        <th scope="col">{{ __('pest.data.code') }} </th>
                        <th scope="col">{{ __('pest.data.category') }} </th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pests as $index => $pest)
                        <tr>
                            <th scope="row">{{ $offset + $index + 1 }}</th>
                            @if ($pest->image)
                                <td><img src="{{ route('image.show', ['path' => $pest->image]) }}"
                                        style="width: 60px; height: 60px;" alt="min-img"></td>
                            @else
                                <td><i class="bi bi-image"></i></td>
                            @endif
                            <td>{{ $pest->name ?? '-' }}</td>
                            <td>{{ $pest->pest_code ?? '-' }}</td>
                            <td>{{ $pest->pestCategory->category ?? '-' }}</td>
                            <td>
                                <a href="{{ route('pest.edit', ['id' => $pest->id]) }}" class="btn btn-secondary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar plaga"><i
                                        class="bi bi-pencil-square"></i></a>
                                <a href="{{ route('pest.destroy', ['id' => $pest->id]) }}" class="btn btn-danger btn-sm"
                                    onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar plaga">
                                    <i class="bi bi-trash-fill"></i> </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-danger fw-bold text-center" colspan="6">Sin plagas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pests->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
