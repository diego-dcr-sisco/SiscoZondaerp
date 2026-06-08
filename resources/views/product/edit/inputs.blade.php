@extends('layouts.app')
@section('content')

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom bg-white ps-4 p-3 shadow-sm">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3 text-secondary">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-dark fw-bold fs-4">
                Insumos del Producto: <span
                    class="badge bg-warning text-dark fs-5 ms-2 px-3 py-1 shadow-sm">{{ $product->name }}</span>
            </span>
        </div>

        <div class="p-4">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                @php
                    $allDataForModal = [];
                    foreach ($inputs as $method) {
                        $allDataForModal[$method['application_method_id']] = [
                            'method_name' => $method['application_method_name'],
                            'pests' => $method['pestCategories'],
                        ];
                    }
                @endphp

                {{-- "Editar" → carga los datos existentes en el modal --}}
                <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#inputModal" data-action="edit" data-all-data="{{ json_encode($allDataForModal) }}">
                    ✏️ Editar insumos
                </button>

                {{-- "Actualizar" → abre el modal limpio para agregar desde cero --}}
                <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm fw-bold" id="btn-add-input"
                    data-bs-toggle="modal" data-bs-target="#inputModal" data-action="new">
                    <i class="bi bi-plus-lg me-1"></i> Actualizar insumos*
                </button>
            </div>

            <div class="card border rounded shadow-sm overflow-hidden">
                <div class="table-responsive w-100">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 50%;" class="ps-3 py-2"># Plagas</th>
                                <th style="width: 30%;" class="py-2">Método de Aplicación</th>
                                <th style="width: 20%;" class="py-2">Cantidad (Dosis)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($inputs))
                                @foreach($inputs as $method)
                                    @foreach($method['pestCategories'] as $input)
                                        @php
                                            $pids = is_array($input['pest_ids'])
                                                ? $input['pest_ids']
                                                : (json_decode($input['pest_ids'], true) ?? []);

                                            $subNames = \App\Models\PestCatalog::whereIn('id', $pids)
                                                ->pluck('name')->toArray();

                                            $pestText = ($input['category'] ?? 'Sin categoría')
                                                . (!empty($subNames) ? ' (' . implode(', ', $subNames) . ')' : '');

                                            $rawMetric = $input['metric'] ?? $input['unit'] ?? $input['short_metric'] ?? 'uds';
                                            $metricMap = [
                                                'units' => 'uds',
                                                'unit' => 'uds',
                                                'uds' => 'uds',
                                                'wt' => 'g',
                                                'weight' => 'g',
                                                'grams' => 'g',
                                                'gramos' => 'g',
                                                'g' => 'g',
                                                'vol' => 'ml',
                                                'volume' => 'ml',
                                                'mililiters' => 'ml',
                                                'mililitros' => 'ml',
                                                'ml' => 'ml',
                                                'l' => 'l',
                                                'kg' => 'kg',
                                            ];
                                            $finalDisplay = $metricMap[strtolower(trim($rawMetric))] ?? $rawMetric;
                                        @endphp
                                        <tr class="align-middle">
                                            <td class="ps-3">
                                                <strong>{{ $pestText }}</strong>
                                            </td>
                                            <td class="text-muted">
                                                {{ $method['application_method_name'] }}
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $input['amount'] }}</span>
                                                <small class="text-muted">{{ $finalDisplay }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3 bg-light">
                                        No hay insumos registrados.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="input-backend-form"
        action="{{ action([App\Http\Controllers\ProductController::class, 'input'], ['id' => $product->id]) }}"
        method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="application_method_id" id="backend-method-id">
        <input type="hidden" name="selected_categories" id="selected-categories">
    </form>

    @include('product.modals.input')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('inputModal');
            if (!modalEl) return;

            modalEl.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const action = trigger.getAttribute('data-action');

                if (action === 'edit') {
                    const rawData = trigger.getAttribute('data-all-data');
                    if (!rawData) return;

                    let allData;
                    try {
                        allData = JSON.parse(rawData);
                    } catch (e) {
                        console.error('Error parseando data-all-data:', e);
                        return;
                    }

                    const methodIds = Object.keys(allData);
                    if (methodIds.length > 0) {
                        const firstMethodId = methodIds[0];
                        const data = allData[firstMethodId];

                        if (typeof window.populateInputModal === 'function') {
                            window.populateInputModal(firstMethodId, data.method_name, data.pests);
                        }
                    }

                } else if (action === 'new') {
                    if (typeof window.clearInputModal === 'function') {
                        window.clearInputModal();
                    }
                }
            });
        });
    </script>
@endsection