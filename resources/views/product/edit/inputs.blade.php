@extends('layouts.app')
@section('content')
    @php
        function formatPath($path)
        {
            return str_replace(['/', ' '], ['-', ''], $path);
        }

        function extractFileName($filePath)
        {
            $fileNameWithExtension = basename($filePath);
            $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

            return $fileName;
        }
    @endphp

    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-black fw-bold fs-4">
                INSUMOS DEL PRODUCTO <span class="fs-5 fw-bold bg-warning p-1 rounded">{{ $product->name }}</span>
            </span>
        </div>

        <div class="m-3">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" id="btn-add-input" data-bs-toggle="modal"
                    data-bs-target="#inputModal">
                    <i class="bi bi-plus-lg"></i> Agregar insumo
                </button>
            </div>

            <div class="overflow-auto w-100">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="fw-bold" scope="col">Método de Aplicación</th>
                            <th class="fw-bold" scope="col">Categoría de Plaga</th>
                            <th class="fw-bold" scope="col">Cantidad (Dosis)</th>
                            <th class="fw-bold" scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inputs as $inputGroup)
                            @foreach ($inputGroup['pestCategories'] as $pest)
                                <tr>
                                    <td>{{ $inputGroup['application_method_name'] }}</td>
                                    <td>{{ $pest['category'] }}</td>
                                    <td>{{ $pest['amount'] }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-secondary btn-sm btn-edit-input"
                                                data-bs-toggle="modal" data-bs-target="#inputModal"
                                                data-method-id="{{ $inputGroup['application_method_id'] }}"
                                                data-pests='{{ json_encode($inputGroup['pestCategories']) }}'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button type="button" class="btn btn-danger btn-sm btn-delete-input"
                                                data-method-id="{{ $inputGroup['application_method_id'] }}"
                                                data-category-id="{{ $pest['id'] }}"
                                                data-all-pests='{{ json_encode($inputGroup['pestCategories']) }}'>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No hay insumos configurados para este producto.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form id="input-backend-form"
        action="{{ action([App\Http\Controllers\ProductController::class, 'input'], ['id' => $product->id]) }}"
        method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="application_method_id" id="backend-method-id">
        <input type="hidden" name="selected_categories" id="backend-selected-categories">
    </form>

    @include('product.modals.input')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Escuchamos el ID correcto: inputModal
            const modalEl = document.getElementById('inputModal');

            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function(event) {
                    const trigger = event.relatedTarget;
                    if (!trigger) return;

                    const methodId = trigger.getAttribute('data-method-id');
                    const pestsJson = trigger.getAttribute('data-pests');

                    if (trigger.id === 'btn-add-input') {
                        if (typeof window.clearInputModal === 'function') {
                            window.clearInputModal();
                        }
                        return;
                    }

                    if (trigger.classList.contains('btn-edit-input')) {
                        const pests = pestsJson ? JSON.parse(pestsJson) : [];
                        if (typeof window.populateInputModal === 'function') {
                            window.populateInputModal(methodId, pests);
                        }
                    }
                });
            }

            // Flujo de Eliminación Reactiva
            document.querySelectorAll('.btn-delete-input').forEach(button => {
                button.addEventListener('click', function() {
                    if (!confirm(
                            '¿Estás seguro de que deseas eliminar esta categoría de plaga para este método de aplicación?'
                            )) {
                        return;
                    }

                    const methodId = this.getAttribute('data-method-id');
                    const categoryIdToDelete = parseInt(this.getAttribute('data-category-id'), 10);
                    const allPests = JSON.parse(this.getAttribute('data-all-pests') || '[]');

                    const remainingCategories = allPests
                        .filter(pest => parseInt(pest.id, 10) !== categoryIdToDelete)
                        .map(pest => ({
                            category_id: pest.id,
                            amount: pest.amount,
                        }));

                    document.getElementById('backend-method-id').value = methodId;
                    document.getElementById('backend-selected-categories').value = JSON.stringify(
                        remainingCategories);
                    document.getElementById('input-backend-form').submit();
                });
            });
        });
    </script>
@endsection
