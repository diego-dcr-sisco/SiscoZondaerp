@extends('layouts.app')

@section('content')
    <div class="row w-100 h-100 m-0">
        @include('dashboard.stock.navigation')

        <div class="col-11 p-3 m-0">
            
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('consumptions.index') }}" class="col-auto btn-primary p-0 fs-3">
                            <i class="bi bi-arrow-left m-3"></i>
                        </a>
                        <h1 class="h3 mb-0">Editar Consumo</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="row mb-3 p-3">
                        <div class="col-lg-12">
                            <div class="card shadow-md">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="fw-bold">Cliente:</td>
                                                    <td>{{ $groupedConsumption->customer->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Zona:</td>
                                                    <td>
                                                        <span class="text-center">{{ $groupedConsumption->zone->name ?? 'Sin zona' }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Período:</td>
                                                    <td>{{ $groupedConsumption->period_formatted }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-lg-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="fw-bold">Registrado por:</td>
                                                    <td>{{ $groupedConsumption->user->name ?? 'Usuario desconocido' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Fecha de registro:</td>
                                                    <td>{{ $groupedConsumption->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                                @if($groupedConsumption->updated_at != $groupedConsumption->created_at)
                                                    <tr>
                                                        <td class="fw-bold">Última actualización:</td>
                                                        <td>{{ $groupedConsumption->updated_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 p-3">
                        <div class="col-lg-12">
                            <div class="card shadow-md">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos del Consumo</h5>
                                </div>
                                <div class="card-body">
                                    <form class="form" method="POST" action="{{ route('consumptions.update', $groupedConsumption->products->first()->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="consumption_id" value="{{ $groupedConsumption->id }}">
                                        <input type="hidden" name="customer" value="{{ $groupedConsumption->customer->id }}">
                                        <input type="hidden" name="zone" value="{{ $groupedConsumption->zone->id }}">
                                        <input type="hidden" name="month" value="{{ $groupedConsumption->month }}">
                                        <input type="hidden" name="year" value="{{ $groupedConsumption->year }}">
                                        <input type="hidden" name="status" value="{{ $groupedConsumption->status }}">       

                                        <div class="row p-3 mb-3 rounded border-bottom">
                                            <!-- Productos del Consumo -->
                                            <div class="col-12 mb-3">
                                                <label class="form-label is-required">Productos del Consumo</label>
                                                <div id="products-container">
                                                    
                                                    @foreach($groupedConsumption->products as $index => $productItem)
                                                        <div class="product-row border rounded p-3 mb-2" data-index="{{ $index }}">
                                                            <div class="row">
                                                                <input type="hidden" name="products[{{ $index }}][id]" value="{{ $productItem->id }}">
                                                                <div class="col-5">
                                                                    <label class="form-label">Producto</label>
                                                                    <select class="form-select product-select" name="products[{{ $index }}][product_id]" required>
                                                                        <option value="">Seleccione un producto</option>
                                                                        @foreach ($products as $product)
                                                                            <option value="{{ $product->id }}" 
                                                                                {{ $product->id == old("products.{$index}.product_id", $productItem->product_id) ? 'selected' : '' }}>
                                                                                {{ $product->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-3">
                                                                    <label class="form-label">Cantidad</label>
                                                                    <input type="number" 
                                                                        class="form-control" 
                                                                        name="products[{{ $index }}][amount]" 
                                                                        value="{{ old("products.{$index}.amount", $productItem->amount) }}" 
                                                                        min="0" 
                                                                        step="0.01" 
                                                                        required>
                                                                </div>
                                                                <div class="col-3">
                                                                    <label class="form-label">Unidad</label>
                                                                    <select class="form-select" name="products[{{ $index }}][units]" required>
                                                                        <option value="" disabled selected>Seleccione unidad</option>
                                                                        <option value="pza" {{ old("products.{$index}.units", $productItem->units) == 'pza' ? 'selected' : '' }}>Pieza (pza)</option>
                                                                        <option value="mg" {{ old("products.{$index}.units", $productItem->units) == 'mg' ? 'selected' : '' }}>Miligramo (mg)</option>
                                                                        <option value="g" {{ old("products.{$index}.units", $productItem->units) == 'g' ? 'selected' : '' }}>Gramo (gr)</option>
                                                                        <option value="kg" {{ old("products.{$index}.units", $productItem->units) == 'kg' ? 'selected' : '' }}>Kilogramo (kg)</option>
                                                                        <option value="m" {{ old("products.{$index}.units", $productItem->units) == 'm' ? 'selected' : '' }}>Metro (m)</option>
                                                                        <option value="ml" {{ old("products.{$index}.units", $productItem->units) == 'ml' ? 'selected' : '' }}>Mililitro (ml)</option>
                                                                        <option value="l" {{ old("products.{$index}.units", $productItem->units) == 'l' ? 'selected' : '' }}>Litro (l)</option>
                                                                        <option value="paq" {{ old("products.{$index}.units", $productItem->units) == 'paq' ? 'selected' : '' }}>Paquete (paq)</option>
                                                                        <option value="doc" {{ old("products.{$index}.units", $productItem->units) == 'doc' ? 'selected' : '' }}>Docena (doc)</option>
                                                                        <option value="bulto" {{ old("products.{$index}.units", $productItem->units) == 'bulto' ? 'selected' : '' }}>Bulto</option>
                                                                        <option value="rollo" {{ old("products.{$index}.units", $productItem->units) == 'rollo' ? 'selected' : '' }}>Rollo</option>
                                                                        <option value="otro" {{ old("products.{$index}.units", $productItem->units) == 'otro' ? 'selected' : '' }}>Otro</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-1 d-flex align-items-end">
                                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-product" title="Eliminar producto">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-product">
                                                    <i class="bi bi-plus"></i> Agregar Producto
                                                </button>
                                                @error('products')
                                                    <div class="form-text text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row p-3 mb-3">
                                            <!-- Observaciones -->
                                            <div class="col-12 mb-3">
                                                <label for="observation" class="form-label">Observaciones</label>
                                                <textarea class="form-control" 
                                                        id="observation" 
                                                        name="observation" 
                                                        rows="4" 
                                                        maxlength="500" 
                                                        placeholder="Especificaciones de servicios, comentarios, etc..">{{ old('observation', $groupedConsumption->observation) }}</textarea>
                                                @error('observation')
                                                    <div class="form-text text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row justify-content-end">
                                            <div class="col-auto">
                                                <a href="{{ route('consumptions.index') }}" class="btn btn-secondary me-2">
                                                    <i class="bi bi-x-circle"></i> Cancelar
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-check-circle"></i> Actualizar consumo
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let productIndex = {{ count($groupedConsumption->products) }};
            
            // Función para crear un nuevo producto
            function createProductRow(index) {
                return `
                    <div class="product-row border rounded p-3 mb-2" data-index="${index}">
                        <div class="row">
                            <input type="hidden" name="products[${index}][id]" value="">
                            <div class="col-5">
                                <label class="form-label">Producto</label>
                                <select class="form-select product-select" name="products[${index}][product_id]" required>
                                    <option value="">Seleccione un producto</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" 
                                    class="form-control" 
                                    name="products[${index}][amount]" 
                                    value="" 
                                    min="0" 
                                    step="0.01" 
                                    required>
                            </div>
                            <div class="col-3">
                                <label class="form-label">Unidad</label>
                                <select class="form-select" name="products[${index}][units]">
                                    <option value="">Seleccione unidad</option>
                                    <option value="pza">Pieza (pza)</option>
                                    <option value="mg">Miligramo (mg)</option>
                                    <option value="g">Gramo (gr)</option>
                                    <option value="kg">Kilogramo (kg)</option>
                                    <option value="m">Metro (m)</option>
                                    <option value="ml">Mililitro (ml)</option>
                                    <option value="l">Litro (l)</option>
                                    <option value="paq">Paquete (paq)</option>
                                    <option value="doc">Docena (doc)</option>
                                    <option value="bulto">Bulto</option>
                                    <option value="rollo">Rollo</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-1 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-product" title="Eliminar producto">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Agregar nuevo producto
            document.getElementById('add-product').addEventListener('click', function() {
                const container = document.getElementById('products-container');
                const newRow = createProductRow(productIndex);
                container.insertAdjacentHTML('beforeend', newRow);
                productIndex++;
                
                // Agregar evento de eliminar al nuevo botón
                const newRemoveButtons = container.querySelectorAll('.remove-product:not([data-event-added])');
                newRemoveButtons.forEach(button => {
                    button.setAttribute('data-event-added', 'true');
                    button.addEventListener('click', function() {
                        this.closest('.product-row').remove();
                        updateProductIndexes();
                    });
                });
            });

            // Función para actualizar los índices de los productos
            function updateProductIndexes() {
                const productRows = document.querySelectorAll('.product-row');
                productRows.forEach((row, index) => {
                    row.setAttribute('data-index', index);
                    
                    // Actualizar nombres de campos
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name && name.includes('products[')) {
                            const newName = name.replace(/products\[\d+\]/, `products[${index}]`);
                            input.setAttribute('name', newName);
                        }
                    });
                });
                
                productIndex = productRows.length;
            }

            // Agregar eventos a los botones de eliminar existentes
            document.querySelectorAll('.remove-product').forEach(button => {
                button.addEventListener('click', function() {
                    const productRows = document.querySelectorAll('.product-row');
                    if (productRows.length > 1) {
                        this.closest('.product-row').remove();
                        updateProductIndexes();
                    } else {
                        alert('Debe mantener al menos un producto en el consumo');
                    }
                });
            });

            // Validación antes de enviar el formulario
            document.querySelector('form').addEventListener('submit', function(e) {
                const productRows = document.querySelectorAll('.product-row');
                if (productRows.length === 0) {
                    e.preventDefault();
                    alert('Debe agregar al menos un producto al consumo');
                    return false;
                }

                // Validar que todos los productos tengan datos requeridos
                let isValid = true;
                productRows.forEach(row => {
                    const productSelect = row.querySelector('.product-select');
                    const amountInput = row.querySelector('input[name*="[amount]"]');
                    
                    if (!productSelect.value || !amountInput.value || parseFloat(amountInput.value) <= 0) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor complete todos los campos requeridos de los productos (producto y cantidad mayor a 0)');
                    return false;
                }
            });
        });
    </script>

@endsection 