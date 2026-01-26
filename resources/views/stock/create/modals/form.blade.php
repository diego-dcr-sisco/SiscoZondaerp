<!-- CSS específico para el modal -->
<link rel="stylesheet" href="{{ asset('css/warehouse-modal.css') }}">

<div class="modal fade warehouse-modal" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content shadow-lg" action="{{ route('stock.store') }}" method="POST" id="warehouseForm" novalidate>
            @csrf
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <i class="fas fa-warehouse me-2"></i>
                    <h5 class="modal-title mb-0" id="createModalLabel">
                        <strong>Crear Nuevo Almacén</strong>
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-red" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Información Básica -->
                <div class="row mb-2">
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field" for="warehouse_name">
                            <i class="fas fa-tag"></i>Nombre del Almacén
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="warehouse_name" 
                               name="name" 
                               value="{{ old('name') }}"
                               placeholder="Ej: Almacén Central, Bodega Norte..." 
                               required 
                               maxlength="255"
                               autocomplete="off" />
                        <div class="invalid-feedback">
                            Por favor ingrese un nombre válido para el almacén.
                        </div>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field" for="branch_select">
                            <i class="fas fa-building"></i>Sucursal/Delegación
                        </label>
                        <select class="form-select @error('branch_id') is-invalid @enderror" 
                                id="branch_select" 
                                name="branch_id" 
                                required>
                            <option value="" selected disabled>Seleccione una sucursal</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione una sucursal.
                        </div>
                        @error('branch_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Configuración del Almacén -->
                <div class="row mb-0">
                    
                    <div class="col-md-6 mb-0">
                        <label class="form-label" for="technician_select">
                            <i class="fas fa-user-tie"></i>Técnico Responsable
                        </label>
                        <select class="form-select @error('technician_id') is-invalid @enderror" 
                                id="technician_select" 
                                name="technician_id">
                            <option value="" selected>Sin técnico asignado</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" {{ old('technician_id') == $technician->id ? 'selected' : '' }}>
                                    {{ $technician->user->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>Opcional. Asigne un técnico responsable del almacén.
                        </div>
                        @error('technician_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-0">
                        <label class="form-label">
                            <i class="fas fa-check-square"></i>Opciones del Almacén
                        </label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_matrix_checkbox"
                                       name="is_matrix" 
                                       value="1"
                                       {{ old('is_matrix') ? 'checked' : '' }}
                                       onchange="handleMatrixChange(this)" />
                                <label class="form-check-label" for="is_matrix_checkbox">
                                    <i class="fas fa-star text-warning"></i>Es almacén matriz
                                </label>
                                <div class="form-text small">
                                    Los almacenes matriz no pueden tener técnico asignado.
                                </div>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="allow_receipts_checkbox"
                                       name="allow_material_receipts" 
                                       value="1"
                                       {{ old('allow_material_receipts', true) ? 'checked' : '' }} />
                                <label class="form-check-label" for="allow_receipts_checkbox">
                                    <i class="fas fa-box-open text-success"></i>Permite recibos de material
                                </label>
                                <div class="form-text small">
                                    Permite recibir materiales y productos en este almacén.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="row mb-0">
                    <div class="col-12">
                        <label class="form-label" for="observations_textarea">
                            <i class="fas fa-sticky-note"></i>Observaciones
                        </label>
                        <textarea class="form-control @error('observations') is-invalid @enderror" 
                                  id="observations_textarea" 
                                  name="observations"
                                  placeholder="Descripción adicional, ubicación específica, notas importantes..."
                                  rows="3"
                                  maxlength="1000">{{ old('observations') }}</textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/1000 caracteres
                        </div>
                        @error('observations')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript específico para el modal -->
<script src="{{ asset('js/warehouse-modal.js') }}"></script>