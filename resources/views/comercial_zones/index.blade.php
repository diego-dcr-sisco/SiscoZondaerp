@extends('layouts.app')
@section('content')
    <style>
        .commercial-zone-customer-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .commercial-zone-customer-list li {
            display: flex;
            align-items: center;
            gap: .35rem;
            padding: .18rem 0;
            line-height: 1.25;
        }

        .commercial-zone-customer-list li + li {
            border-top: 1px solid #f1f3f5;
        }

        .commercial-zone-customer-list i {
            font-size: .8rem;
        }

        .commercial-zone-description {
            max-width: 18rem;
        }
    </style>

    @can('write_user')
        @include('components.page-header', [
            'title' => 'ZONAS COMERCIALES',
            'icon' => 'bi-geo-alt-fill',
            'iconColor' => 'text-primary',
            'actionRoute' => route('comercial-zones.create'),
            'actionText' => 'Crear zona comercial',
            'actionIcon' => 'bi-plus-lg',
        ])
    @else
        @include('components.page-header', [
            'title' => 'ZONAS COMERCIALES',
            'icon' => 'bi-geo-alt-fill',
            'iconColor' => 'text-primary',
        ])
    @endcan

    <div class="container-fluid">
                <div class="row g-3 mb-3">
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="bg-white border rounded p-3 h-100">
                            <div class="text-muted small">Zonas registradas</div>
                            <div class="fs-4 fw-bold">{{ number_format($zonesSummary['total'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="bg-white border rounded p-3 h-100">
                            <div class="text-muted small">Resultados filtrados</div>
                            <div class="fs-4 fw-bold">{{ number_format($zonesSummary['filtered'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="bg-white border rounded p-3 h-100">
                            <div class="text-muted small">Clientes asociados</div>
                            <div class="fs-4 fw-bold">{{ number_format($zonesSummary['unique_customers'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-12">
                        <div class="bg-white border rounded p-3 h-100">
                            <div class="text-muted small">Zonas sin clientes</div>
                            <div class="fs-4 fw-bold {{ ($zonesSummary['without_customers'] ?? 0) > 0 ? 'text-warning' : '' }}">
                                {{ number_format($zonesSummary['without_customers'] ?? 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('comercial-zones.index') }}" method="GET" class="mb-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h5 class="card-title fw-bold mb-0">
                                    <i class="bi bi-funnel-fill"></i> Busqueda Avanzada
                                </h5>
                                <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target=".commercial-zone-search-collapse" aria-expanded="true"
                                    aria-controls="commercialZoneSearchFilters commercialZoneSearchFooter">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body collapse show commercial-zone-search-collapse"
                            id="commercialZoneSearchFilters">
                            <div class="row g-3">
                                <div class="col-lg-8 col-12">
                                    <label for="search" class="form-label">Buscar</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="search" name="search"
                                            value="{{ $search }}" placeholder="Zona, codigo o cliente asociado">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <label for="size" class="form-label">Mostrar</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                                        <select class="form-select" id="size" name="size">
                                            @foreach ([25, 50, 100, 200] as $optionSize)
                                                <option value="{{ $optionSize }}" {{ (int) $size === $optionSize ? 'selected' : '' }}>
                                                    {{ $optionSize }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer collapse show commercial-zone-search-collapse"
                            id="commercialZoneSearchFooter">
                            <div class="row justify-content-end g-2">
                                <div class="col-lg-1 col-6">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-funnel-fill"></i> Filtrar
                                    </button>
                                </div>
                                <div class="col-lg-1 col-6">
                                    <a href="{{ route('comercial-zones.index') }}" class="btn btn-secondary btn-sm w-100">
                                        <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="overflow-auto w-100">
                    <table class="table table-hover table-bordered table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 54px;">#</th>
                                <th scope="col">Zona comercial</th>
                                <th scope="col">Codigo</th>
                                <th scope="col" class="text-center">Clientes</th>
                                <th scope="col">Clientes asociados</th>
                                <th scope="col">Descripcion</th>
                                <th scope="col" class="text-center" style="width: 116px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($comercial_zones as $index => $cz)
                                <tr>
                                    <td class="text-center fw-bold text-muted">
                                        {{ $loop->iteration + ($comercial_zones->currentPage() - 1) * $comercial_zones->perPage() }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width:32px;height:32px;">
                                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                            </span>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $cz->name }}</div>
                                                <small class="text-muted">ID {{ $cz->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">{{ $cz->code ?? '-' }}</span>
                                    </td>
                                    <td class="text-center fw-bold">
                                        {{ number_format($cz->customers_count ?? $cz->customers->count()) }}
                                    </td>
                                    <td>
                                        @if ($cz->customers->isNotEmpty())
                                            @php
                                                $visibleCustomers = $cz->customers->take(3);
                                                $hiddenCustomers = $cz->customers->slice(3);
                                                $customersCollapseId = 'commercialZoneCustomers' . $cz->id;
                                            @endphp
                                            <ul class="commercial-zone-customer-list">
                                                @foreach ($visibleCustomers as $customer)
                                                    <li>
                                                        <i class="bi bi-person-check text-success"></i>
                                                        <span>{{ $customer->name }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if ($hiddenCustomers->isNotEmpty())
                                                <div class="collapse mt-1" id="{{ $customersCollapseId }}">
                                                    <ul class="commercial-zone-customer-list">
                                                        @foreach ($hiddenCustomers as $customer)
                                                            <li>
                                                                <i class="bi bi-person-check text-success"></i>
                                                                <span>{{ $customer->name }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <button class="btn btn-link btn-sm p-0 text-decoration-none text-start commercial-zone-customers-toggle"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $customersCollapseId }}" aria-expanded="false"
                                                    aria-controls="{{ $customersCollapseId }}">
                                                    <span class="show-more-label">
                                                        Ver {{ $hiddenCustomers->count() }} mas
                                                    </span>
                                                    <span class="show-less-label d-none">Ver menos</span>
                                                    <i class="bi bi-chevron-down ms-1"></i>
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-muted">Sin clientes asociados</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="commercial-zone-description text-truncate" title="{{ $cz->description ?: '-' }}">
                                            {{ $cz->description ?: '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1" role="group" aria-label="Acciones">
                                            <a href="{{ route('comercial-zones.edit', ['id' => $cz->id]) }}"
                                                class="btn btn-secondary btn-sm" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Editar zona">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @can('write_user')
                                                <form action="{{ route('comercial-zones.destroy', ['id' => $cz->id]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Desea eliminar esta zona comercial?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Eliminar zona">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-geo-alt text-muted fs-1 d-block mb-2"></i>
                                        No hay zonas comerciales con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Mostrando {{ $comercial_zones->firstItem() ?? 0 }} a {{ $comercial_zones->lastItem() ?? 0 }}
                        de {{ $comercial_zones->total() }} zonas
                    </div>
                    <div>
                        {{ $comercial_zones->links('pagination::bootstrap-5') }}
                    </div>
        </div>
    </div>

    <script>
        $(function() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
                new bootstrap.Tooltip(element);
            });

            document.querySelectorAll('.commercial-zone-customers-toggle').forEach(function(button) {
                var icon = button.querySelector('i');
                var showMoreLabel = button.querySelector('.show-more-label');
                var showLessLabel = button.querySelector('.show-less-label');
                var collapse = document.querySelector(button.dataset.bsTarget);

                if (!collapse) {
                    return;
                }

                collapse.addEventListener('shown.bs.collapse', function() {
                    icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
                    showMoreLabel.classList.add('d-none');
                    showLessLabel.classList.remove('d-none');
                });

                collapse.addEventListener('hidden.bs.collapse', function() {
                    icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
                    showLessLabel.classList.add('d-none');
                    showMoreLabel.classList.remove('d-none');
                });
            });
        });
    </script>
@endsection
