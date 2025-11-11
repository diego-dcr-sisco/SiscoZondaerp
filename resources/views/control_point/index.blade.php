@extends('layouts.app')
@section('content')
    @php
        $offset = ($points->currentPage() - 1) * $points->perPage();
    @endphp
    <div class="container-fluid">
        <div class="py-3">
            @can('write_customer')
                <a class="btn btn-primary btn-sm" href="{{ route('point.create') }}">
                    <i class="bi bi-plus-lg fw-bold"></i> Crear punto de control
                </a>
            @endcan
        </div>

        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <caption class="border rounded-top p-2 text-dark bg-light caption-top">
                    <form action="{{ route('point.search') }}" method="GET">
                        @csrf
                        <div class="row g-3 mb-0">
                            <div class="col-lg-4 col-12">
                                <label for="version" class="form-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm" name="name"
                                    value="{{ request('name') }}" />
                            </div>

                            <div class="col-lg-2 col-12">
                                <label for="version" class="form-label">Código</label>
                                <input type="text" class="form-control form-control-sm" name="code"
                                    value="{{ request('code') }}" />
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
                                <a href="{{ route('point.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </caption>
                <thead>
                    <tr>
                        <th scope="col-1">#</th>
                        <th scope="col-1">{{ __('product.product-data.color') }} </th>
                        <th scope="col-2">{{ __('product.product-data.name') }} </th>
                        <th scope="col">Código</th>
                        <th scope="col-2">{{ __('product.product-data.line_b') }} </th>
                        <th scope="col-2">{{ __('product.product-data.porp') }}</th>
                        <th scope="col-1"> Preguntas asociadas </th>
                        <th scope="col-2"></th>
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
                            <td class="text-center">
                                <a href="{{ route('point.edit', ['id' => $point->id]) }}"
                                    class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Editar punto de control">
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
