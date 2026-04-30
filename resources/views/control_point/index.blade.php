@extends('layouts.app')
@section('content')
    @include('components.page-header', [
        'title' => 'PUNTOS DE CONTROL',
        'icon' => 'bi-journal-check',
        'actionRoute' => route('point.create'),
        'actionText' => 'Crear punto de control',
    ])

    @php
        $offset = ($points->currentPage() - 1) * $points->perPage();
    @endphp
    <div class="container-fluid">
        <div class="overflow-auto w-100">
            <table class="table table-bordered table-striped table-sm caption-top">
                <caption class="border rounded-top p-2 text-dark bg-white">
                    <form action="{{ route('point.search') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-lg-3">
                                <label for="name" class="form-label">Nombre</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name"
                                        value="{{ request('name') }}" placeholder="Buscar nombre" />
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <label for="code" class="form-label">Código</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-upc"></i></span>
                                    <input type="text" class="form-control form-control-sm" id="code" name="code"
                                        value="{{ request('code') }}" placeholder="Buscar código" />
                                </div>
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
                                <a href="{{ route('point.index') }}" class="btn btn-secondary btn-sm w-100">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th class="fw-bold" scope="col">#</th>
                        <th class="fw-bold" scope="col">{{ __('product.product-data.color') }}</th>
                        <th class="fw-bold" scope="col">{{ __('product.product-data.name') }}</th>
                        <th class="fw-bold" scope="col">Código</th>
                        <th class="fw-bold" scope="col">{{ __('product.product-data.line_b') }}</th>
                        <th class="fw-bold" scope="col">{{ __('product.product-data.porp') }}</th>
                        <th class="fw-bold" scope="col">Preguntas asociadas</th>
                        <th class="fw-bold" scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($points as $index => $point)
                        <tr>
                            <th scope="row">{{ $offset + $index + 1 }}</th>
                            <td>
                                <div class="rounded"
                                    style="width:25px; height: 25px; background-color: {{ htmlspecialchars($point->color) }};">
                                </div>
                            </td>
                            <td>{{ $point->name }}</td>
                            <td class="fw-bold text-primary">{{ $point->code }}</td>
                            <td>{{ $point->product && $point->product->lineBusiness ? $point->product->lineBusiness->name : '-' }}
                            </td>
                            <td>{{ $point->product->purpose->type ?? '-' }}</td>
                            <td>{{ count($point->questions) }}</td>
                            <td>
                                <a href="{{ route('point.edit', ['id' => $point->id]) }}" class="btn btn-secondary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Editar punto de control">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="{{ route('point.destroy', ['id' => $point->id]) }}" class="btn btn-danger btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar punto de control"
                                    onclick="return confirm('{{ __('messages.are_you_sure_delete') }}')"><i
                                        class="bi bi-trash-fill"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        {{ $points->links('pagination::bootstrap-5') }}
    </div>

    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endsection
