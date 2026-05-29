@extends('layouts.app')

@section('content')
    @php
        $items = collect($products_data);
        $totalItems = $items->count();
        $totalProducts = $items->pluck('product')->unique()->count();
        $availableItems = $items->filter(fn($item) => (float) ($item['amount'] ?? 0) > 0)->count();
        $expiredItems = $items->filter(function ($item) {
            if (empty($item['expiration_date']) || $item['expiration_date'] === '-') {
                return false;
            }

            try {
                return \Carbon\Carbon::parse($item['expiration_date'])->isPast();
            } catch (\Exception $exception) {
                return false;
            }
        })->count();
    @endphp

    @include('components.page-header', [
        'title' => 'STOCK DE PRODUCTOS - ' . $warehouse->name,
        'icon' => 'bi-boxes',
        'backRoute' => route('stock.index'),
        'actionRoute' => route('stock.exportStock', ['id' => $warehouse->id]),
        'actionText' => 'Exportar',
        'actionIcon' => 'bi-file-earmark-excel-fill',
    ])

    <div class="container-fluid py-3 stock-products-page">
        <div class="stock-context-bar mb-3">
            <span class="badge {{ $warehouse->is_active ? 'bg-success' : 'bg-danger' }}">
                {{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}
            </span>
            <span class="text-muted small">
                <i class="bi bi-building me-1"></i>{{ $warehouse->branch->name ?? 'Sin sucursal' }}
            </span>
            <span class="text-muted small">
                <i class="bi bi-diagram-3 me-1"></i>{{ $warehouse->is_matrix ? 'Almacen matriz' : 'Almacen operativo' }}
            </span>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="stock-kpi">
                    <span class="stock-kpi-icon stock-kpi-primary"><i class="bi bi-box-seam"></i></span>
                    <div>
                        <div class="stock-kpi-label">Registros de inventario</div>
                        <div class="stock-kpi-value">{{ number_format($totalItems) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stock-kpi">
                    <span class="stock-kpi-icon stock-kpi-success"><i class="bi bi-check2-circle"></i></span>
                    <div>
                        <div class="stock-kpi-label">Con existencia</div>
                        <div class="stock-kpi-value">{{ number_format($availableItems) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stock-kpi">
                    <span class="stock-kpi-icon stock-kpi-info"><i class="bi bi-tags"></i></span>
                    <div>
                        <div class="stock-kpi-label">Productos unicos</div>
                        <div class="stock-kpi-value">{{ number_format($totalProducts) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stock-kpi">
                    <span class="stock-kpi-icon stock-kpi-danger"><i class="bi bi-calendar-x"></i></span>
                    <div>
                        <div class="stock-kpi-label">Lotes vencidos</div>
                        <div class="stock-kpi-value">{{ number_format($expiredItems) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stock-table-shell">
            <div class="stock-table-header">
                <div>
                    <h2 class="h6 fw-bold mb-1">Existencias por producto y lote</h2>
                    <div class="text-muted small">Cantidades netas calculadas por entradas menos salidas.</div>
                </div>
                <div class="input-group input-group-sm stock-search">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" class="form-control" id="stockTableSearch"
                        placeholder="Buscar producto, lote o presentacion">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle stock-table mb-0" id="stockProductsTable">
                    <thead>
                        <tr>
                            <th class="text-muted">#</th>
                            <th>Producto</th>
                            <th>Presentacion</th>
                            <th>Lote</th>
                            <th class="text-end">Cantidad</th>
                            <th>Caducidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products_data as $index => $product_data)
                            @php
                                $amount = (float) ($product_data['amount'] ?? 0);
                                $expirationLabel = $product_data['expiration_date'] ?? '-';
                                $isExpired = false;

                                if ($expirationLabel && $expirationLabel !== '-') {
                                    try {
                                        $isExpired = \Carbon\Carbon::parse($expirationLabel)->isPast();
                                    } catch (\Exception $exception) {
                                        $isExpired = false;
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="text-muted fw-semibold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $product_data['product'] }}</div>
                                    <div class="text-muted small">{{ $product_data['metric'] }}</div>
                                </td>
                                <td>{{ $product_data['presentation'] }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border fs-6">{{ $product_data['lot'] }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold {{ $amount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($amount, 2) }}
                                    </span>
                                    <span class="text-muted small">{{ $product_data['metric'] }}</span>
                                </td>
                                <td class="{{ $isExpired ? 'text-danger fw-semibold' : '' }}">
                                    {{ $expirationLabel }}
                                </td>
                                <td>
                                    @if ($amount <= 0)
                                        <span class="badge bg-light text-danger border">Sin stock</span>
                                    @elseif ($isExpired)
                                        <span class="badge bg-light text-warning border">Vencido</span>
                                    @else
                                        <span class="badge bg-light text-success border">Disponible</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-box text-muted fs-1 d-block mb-2"></i>
                                    <span class="fw-semibold text-muted">No hay registros de stock.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .stock-products-page {
            color: #263238;
        }

        .stock-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .stock-context-bar {
            min-height: 42px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .65rem;
            padding: .65rem .75rem;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .4rem;
        }

        .stock-kpi {
            min-height: 92px;
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .4rem;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
        }

        .stock-kpi-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: .35rem;
            font-size: 1.25rem;
            flex: 0 0 auto;
        }

        .stock-kpi-primary {
            color: #0d6efd;
            background: rgba(13, 110, 253, .1);
        }

        .stock-kpi-success {
            color: #198754;
            background: rgba(25, 135, 84, .12);
        }

        .stock-kpi-info {
            color: #0dcaf0;
            background: rgba(13, 202, 240, .14);
        }

        .stock-kpi-danger {
            color: #dc3545;
            background: rgba(220, 53, 69, .1);
        }

        .stock-kpi-label {
            color: #6c757d;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .stock-kpi-value {
            font-size: 1.55rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .stock-table-shell {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .4rem;
            overflow: hidden;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
        }

        .stock-table-header {
            padding: .9rem 1rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .stock-search {
            width: min(360px, 100%);
        }

        .stock-table thead th {
            background: #eef1f4;
            color: #495057;
            font-size: .76rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .stock-table td,
        .stock-table th {
            padding: .65rem .75rem;
        }

        @media (max-width: 768px) {
            .stock-table-header {
                align-items: stretch;
                flex-direction: column;
            }

            .stock-table-header > div {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('stockTableSearch');
            const table = document.getElementById('stockProductsTable');

            if (!searchInput || !table) return;

            searchInput.addEventListener('input', function() {
                const value = this.value.trim().toLowerCase();

                table.querySelectorAll('tbody tr').forEach((row) => {
                    row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
                });
            });
        });
    </script>
@endsection
