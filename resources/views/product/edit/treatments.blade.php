@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">
        {{-- Encabezado idéntico al de Insumos --}}
        <div class="d-flex align-items-center border-bottom bg-white ps-4 p-3 shadow-sm">
            <a href="{{ route('product.index') }}" class="text-decoration-none pe-3 text-secondary">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <span class="text-dark fw-bold fs-4">
                Tratamientos del Producto: <span class="badge bg-warning text-dark fs-5 ms-2 px-3 py-1 shadow-sm">{{ $product->name }}</span>
            </span>
        </div>

        {{-- Contenedor de Contenido --}}
        <div class="p-4">
            <div class="row g-4">
                
                {{-- Columna Izquierda: Formulario de Registro --}}
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm border-0 rounded-3 p-3 bg-white">
                        <h5 class="fw-bold text-dark mb-3">Registrar Tratamiento</h5>
                        
                        <form action="{{ route('product.treatments.store', $product->id) }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="treatment_name" class="form-label fw-bold small text-secondary">Nombre del Tratamiento</label>
                                <input type="text" id="treatment_name" name="name" class="form-control shadow-none" placeholder="Ej: Fumigación Nocturna" required autocomplete="off">
                            </div>
                            
                            <div class="mb-3">
                                <label for="treatment_description" class="form-label fw-bold small text-secondary">Descripción</label>
                                <textarea id="treatment_description" name="description" class="form-control shadow-none" rows="3" placeholder="Detalles del procedimiento..." autocomplete="off"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="treatment_price" class="form-label fw-bold small text-secondary">Precio del Servicio</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" step="0.01" id="treatment_price" name="price" class="form-control shadow-none" placeholder="0.00" autocomplete="off">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                                <i class="fas fa-save me-2"></i> Registrar Tratamiento
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm border-0 rounded-3 p-3 bg-white">
                        <h5 class="fw-bold text-dark mb-3">Tratamientos Asignados</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light text-secondary small fw-bold">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($product->treatments as $treatment)
                                        <tr>
                                            <td class="fw-bold text-dark">{{ $treatment->name }}</td>
                                            <td class="text-muted small">{{ $treatment->description ?? 'Sin descripción' }}</td>
                                            <td class="text-end fw-bold text-success">${{ number_format($treatment->price, 2) }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('product.treatments.destroy', ['id' => $product->id, 'treatmentId' => $treatment->id]) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este tratamiento?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 shadow-none">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small">
                                                <i class="fas fa-info-circle me-2"></i> No hay tratamientos registrados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection