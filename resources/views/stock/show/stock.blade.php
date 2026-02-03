@extends('layouts.app')

@section('content')
    @if (!auth()->check())
        <?php header('Location: /login');
        exit(); ?>
    @endif

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="#" onclick="window.history.back(); return false;" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                STOCK - ALMACÉN <span class="fs-5 fw-bold bg-warning p-1 rounded ms-2">{{ $warehouse->name }}</span>
            </span>

            <div class="ms-auto d-flex gap-2 align-items-center">
                <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'danger' }}">
                    {{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}
                </span>

                <span class="badge bg-warning text-dark">
                    {{ $warehouse->is_matrix ? 'Matriz' : 'Regular' }}
                </span>

                <span class="badge bg-info">
                    {{ $warehouse->allow_material_receipts ? 'Recibos permitidos' : 'Sin recibos' }}
                </span>
            </div>
        </div>

        <div class="row">
            {{-- INFORMACIÓN PRINCIPAL --}}
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Información general
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-hashtag text-primary"></i>
                                    <div>
                                        <small>ID del almacén</small>
                                        <div>{{ $warehouse->id }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-toggle-on text-success"></i>
                                    <div>
                                        <small>Estado</small>
                                        <div>{{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-box-open text-info"></i>
                                    <div>
                                        <small>Recibos de material</small>
                                        <div>{{ $warehouse->allow_material_receipts ? 'Permitidos' : 'No permitidos' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-building text-warning"></i>
                                    <div>
                                        <small>Tipo</small>
                                        <div>{{ $warehouse->is_matrix ? 'Almacén matriz' : 'Almacén regular' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-calendar-plus text-secondary"></i>
                                    <div>
                                        <small>Creado</small>
                                        <div>{{ optional($warehouse->created_at)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-calendar-check text-secondary"></i>
                                    <div>
                                        <small>Actualizado</small>
                                        <div>{{ optional($warehouse->updated_at)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="info-item">
                                    <i class="fas fa-comment-dots text-muted"></i>
                                    <div>
                                        <small>Observaciones</small>
                                        <div>{{ $warehouse->observations ?: 'Sin observaciones' }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-lg-4">
                {{-- SUCURSAL --}}
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-building me-2"></i>Sucursal
                    </div>
                    <div class="card-body">
                        <strong>{{ $warehouse->branch->name ?? '-' }}</strong>
                        <div class="text-muted small mb-2">{{ optional($warehouse->branch)->address ?? '-' }}</div>

                        <hr>

                        <div class="small text-muted">
                            📞 {{ optional($warehouse->branch)->phone ?? 'Sin teléfono' }} <br>
                            📍 {{ optional($warehouse->branch)->city ?? '-' }},
                            {{ optional($warehouse->branch)->state ?? '-' }} <br>
                            📮 {{ optional($warehouse->branch)->zip_code ?? 'Sin CP' }}
                        </div>
                    </div>
                </div>

                {{-- TÉCNICO --}}
                @if ($warehouse->technician)
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white fw-bold">
                            <i class="fas fa-user-tie me-2"></i>Técnico responsable
                        </div>
                        <div class="card-body">
                            {{ $warehouse->technician->user->name }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ACCIONES --}}
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body text-center">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-cogs me-2"></i>Acciones disponibles
                </h6>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @include('stock.action-buttons')
                </div>
            </div>
        </div>

        {{-- RESUMEN DE STOCK --}}
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-boxes me-2"></i> Resumen de stock
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 flex-wrap mb-3">
                    <span class="badge bg-primary">Filas: {{ $stockTotals['rows'] ?? 0 }}</span>
                    <span class="badge bg-secondary">Productos únicos: {{ $stockTotals['distinct_products'] ?? 0 }}</span>
                    <span class="badge bg-info">Lotes: {{ $stockTotals['distinct_lots'] ?? 0 }}</span>
                    <span class="badge bg-success">Total neto: {{ $stockTotals['total_net'] ?? 0 }}</span>
                    <a href="{{ route('stock.exportStock', ['id' => $warehouse->id]) }}"
                        class="btn btn-success btn-sm ms-auto">
                        <i class="bi bi-file-earmark-excel-fill"></i> Exportar a EXCEL
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th class="text-end">Entradas</th>
                                <th class="text-end">Salidas</th>
                                <th class="text-end">Neto</th>
                                <th>Unidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->product->name ?? '-' }}</td>
                                    <td>{{ $row->lot->registration_number ?? '-' }}</td>
                                    <td class="text-end">{{ $row->add_amount ?? 0 }}</td>
                                    <td class="text-end">{{ $row->less_amount ?? 0 }}</td>
                                    <td class="text-end">{{ ($row->add_amount ?? 0) - ($row->less_amount ?? 0) }}</td>
                                    <td>{{ $row->product->metric ? $row->product->metric->value : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No hay registros de stock.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#queryVars" aria-expanded="false">Mostrar variables de consulta</button>
                    <div class="collapse mt-2" id="queryVars">
                        <pre class="small bg-light p-2 border rounded">{{ json_encode($query_variables ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ESTILOS --}}
    <style>
        .info-item {
            display: flex;
            gap: 12px;
            padding: 14px;
            background: #f8f9fa;
            border-radius: 10px;
            height: 100%;
        }

        .info-item i {
            font-size: 1.4rem;
            margin-top: 2px;
        }

        .info-item small {
            display: block;
            color: #6c757d;
            font-weight: 600;
        }

        .info-item div>div {
            font-weight: 600;
        }

        .card {
            transition: all .2s ease;
        }

        .card:hover {
            transform: none !important; /* no levantar al hacer hover */
            box-shadow: none !important; /* quitar sombra al hacer hover */
        }
    </style>
@endsection